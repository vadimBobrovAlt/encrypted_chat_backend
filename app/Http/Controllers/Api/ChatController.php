<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\UserChat;
use App\Service\SMSService;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    private $sms;

    public function __construct(SMSService $SMSService)
    {
        $this->sms = $SMSService;
    }

    // get all users
    public function getUsers(Request $request)
    {
        $user = $request->user();
        $users = User::where('id', '!=', $user->id)->get();
        return response()->json($users, 200);
    }

    // get all chats
    public function getChats(Request $request)
    {
        $user = $request->user();
        $user_chat_ids = UserChat::where('user_id', $user->id)->get()->pluck('chats_id')->toArray();
        $chats = Chat::whereIn('id', $user_chat_ids)->get();
        return response()->json($chats, 200);
    }

    // get chat by ID
    public function getOne(Request $request, $id)
    {
        $chat_secret = $request->query('secret');
        $chat = Chat::find($id);

        if (!hash_equals(hash('sha512', $chat_secret), $chat->secret))
            return response()->json('Верификация не пройдена', 401);

        $array = [];

        $messages = Message::where('chats_id',$chat->id)->get();
        foreach ($messages as $item)
        {
            $key = substr($chat->secret, 0, 32);
            $encrypter = new Encrypter($key, 'AES-256-CBC');
            $obj=[
                'user'=>$item->user->name,
                'content'=> $encrypter->decrypt($item->message)
            ];
            $array[] = $obj;
        }

        return response()->json($array, 200);
    }

    // create web chat
    public function createChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:chats,name|max:255',
            'secret' => 'required|string|max:100',
            'users' => 'required|array|min:2',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 422);

        $data = $validator->valid();

        $chat = Chat::create([
            'name' => $data['name'],
            'secret' => hash('sha512', $data['secret'])
        ]);
        $user_creator = $request->user();
        foreach ($data['users'] as $user) {
            UserChat::create([
                'is_creator' => ($user_creator->id == $user) ? true : 0,
                'chats_id' => $chat->id,
                'user_id' => $user
            ]);
        }

        $this->usersSms($data['users'],$data['secret']);

        return response()->json(['message' => 'Чат успешно создан'], 200);
    }

    // save message in Db
    public function addMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
            'user_id' => 'required|integer',
            'secret' => 'required|string|max:100',
            'message' => 'required|string|max:3000',
        ]);

        if ($validator->fails())
            return response()->json(['error' => "Это письмо не может быть отправлено!"], 422);

        $data = $validator->valid();

        $chat = Chat::find($data['chat_id']);

        if (!hash_equals(hash('sha512', $data['secret']), $chat->secret))
            return response()->json(['error' => 'Неправильный код'], 401);

        $key = substr($chat->secret, 0, 32);
        $encrypter = new Encrypter($key, 'AES-256-CBC');

        Message::create([
            'chats_id' => $data['chat_id'],
            'user_id' => $data['user_id'],
            'message' => $encrypter->encrypt($data['message'])
        ]);

        return response()->json(['status' => true], 200);
    }

    // send sms in user
    private function usersSms($users_id, $secret)
    {
        $users = User::find($users_id);

        foreach ($users as $user) {
            $this->sms->sendMessage([
                [
                    "channel" => "char",
                    "sender" => "VIRTA",
                    "text" => 'Secret: ' . $secret,
                    "phone" => $user->phone
                ]
            ]);
        }
    }

}

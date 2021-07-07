<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'app_name' => ['required', 'string']
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 422);

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        User::create($input);

        return response()->json(['message'=>'Регистрация прошла успешно'], 200);
    }

    // Autentification 1 factor
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'app_name' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' =>
                'Токен недействителен или пользователь не найден!'
            ], 401);
        }

        $user->twoFactorCode();

        return response()->json(
            [
                'token' => $user->createToken($request->app_name)->plainTextToken,
                'message'=>'На ваш номер телефона отправлен код подтверждения'
            ]);
    }

    // Autentification 2 factor
    public function twoFactor(Request $request)
    {
        $user = $request->user();
        if($user->two_factor_expires_at->lt(now()))
            return response()->json(['error' => 'Срок действия двухфакторного кода истек. Пожалуйста, войдите еще раз.'], 401);

       if(!Hash::check($user->two_factor_code, $request->code))
       {
           $user->two_factor = true;
           $user->save();
           return response()->json([
               'user' => $user,
               'two_factor_status' => true,
               'message'=>'Авторизация прошла успешно'
           ]);
       }
        return response()->json('Возникла ошибка!',401);
    }

    // Exit of app
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->two_factor = 0;
        $user->save();
        $user->currentAccessToken()->delete();
    }
}

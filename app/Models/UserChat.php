<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_creator',
        'chats_id',
        'user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];





}

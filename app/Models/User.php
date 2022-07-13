<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'group_id',
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public static function createRules()
    {
        return [
            'group_id' => 'required|integer',
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
            ],
            'is_admin' => 'required|boolean',
        ];
    }

    public static function updateRules()
    {
        return [
            'group_id' => 'integer',
            'name' => 'string|max:255|min:3',
            'email' => 'email|max:255|min:3|unique:users,email,'.auth()->user()->id,
            'is_admin' => 'boolean',
        ];
    }

    public static function updateCurrentRules()
    {
        return [
            'name' => 'string|max:255|min:3',
            'email' => 'email|max:255|min:3|unique:users,email,'.auth()->user()->id,
        ];
    }

    public static function loginRules()
    {
        return [
            'email' => 'required|email|max:255|min:3',
        ];
    }
}

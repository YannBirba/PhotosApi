<?php

namespace App\Models;

use App\Http\Resources\User as UserResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

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
        'remember_token',
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
                'confirmed',
                env('APP_ENV') === 'production' ??
                    Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),

            ],
            'is_admin' => 'required|boolean',
            'is_active' => 'required|boolean',
        ];
    }

    public static function updateRules()
    {
        return [
            'group_id' => 'integer',
            'name' => 'string|max:255|min:3',
            'email' => 'email|max:255|min:3|unique:users,email,'.auth()->user()->id,
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function updateCurrentRules()
    {
        return [
            'name' => 'string|max:255|min:3',
            'email' => 'email|max:255|min:3|unique:users,email,'.auth()->user()->id,
            'is_active' => 'boolean',
        ];
    }

    public static function loginRules()
    {
        return [
            'email' => 'required|email|max:255|min:3',
            'password' => 'required|string',
            'remember' => 'required|boolean',
        ];
    }

    public static function resource(User | Collection $data): UserResource | AnonymousResourceCollection
    {
        if ($data instanceof Collection) {
            return UserResource::collection($data);
        } else {
            return new UserResource($data);
        }
    }
}

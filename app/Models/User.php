<?php

namespace App\Models;

use App\Http\Resources\User as UserResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property int $group_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $is_admin
 * @property int $is_active
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Group $group
 * @property-read Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 *
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'group_id',
        'name',
        'email',
        'password',
        'is_admin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Method group
     *
     * @return BelongsTo<Group,User>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Method createRules
     *
     * @return array<string,array<int,Password|string>|string>
     */
    public static function createRules(): array
    {
        if (env('APP_ENV') === 'production') {
            return [
                'group_id' => 'required|integer',
                'name' => 'required|string|max:255|min:3',
                'email' => 'required|string|email:dns|max:255|unique:users',
                'password' => [
                    'required',
                    'confirmed',
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

        return [
            'group_id' => 'required|integer',
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                'required',
                'string',
            ],
            'is_admin' => 'required|boolean',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Method updateRules
     *
     * @return array<string,string>
     */
    public static function updateRules(): array
    {
        if (env('APP_ENV') === 'production') {
            return [
                'group_id' => 'integer',
                'name' => 'string|max:255|min:3',
                'email' => 'email:dns|max:255|min:3|unique:users',
                'is_admin' => 'boolean',
                'is_active' => 'boolean',
            ];
        }

        return [
            'group_id' => 'integer',
            'name' => 'string|max:255|min:3',
            'email' => 'email|max:255|min:3|unique:users',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Method updateCurrentRules
     *
     * @return array<string,string>
     */
    public static function updateCurrentRules(): array
    {
        if (env('APP_ENV') === 'production') {
            return [
                'name' => 'string|max:255|min:3',
                'email' => 'email:dns|max:255|min:3|unique:users',
                'is_active' => 'boolean',
            ];
        }

        return [
            'name' => 'string|max:255|min:3',
            'email' => 'email|max:255|min:3|unique:users',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Method loginRules
     *
     * @return array<string,string>
     */
    public static function loginRules(): array
    {
        if (env('APP_ENV') === 'production') {
            return [
                'email' => 'required|email:dns|max:255|min:3',
                'password' => 'required|string',
                'remember' => 'required|boolean',
            ];
        }

        return [
            'email' => 'required|email|max:255|min:3',
            'password' => 'required|string',
            'remember' => 'required|boolean',
        ];
    }

    /**
     * Method resource
     *
     * @param  User|Collection<int,User>  $data [Date to be used to create the resource]
     * @return UserResource|AnonymousResourceCollection
     */
    public static function resource(User | Collection $data): UserResource | AnonymousResourceCollection
    {
        if ($data instanceof Collection) {
            return UserResource::collection($data);
        } else {
            return new UserResource($data);
        }
    }
}

<?php

namespace App\Models;

use App\Http\Resources\Group as GroupResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\App\Models\Event[] $events
 * @property-read int|null $events_count
 * @property-read Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\GroupFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Query\Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Group withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Group withoutTrashed()
 */
class Group extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Method users
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Method events
     *
     * @return BelongsToMany<Event>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'group_event');
    }

    /**
     * Method rules
     *
     * @return array<string,string>
     */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:50|min:3|unique:groups',
        ];
    }

    /**
     * Method resource
     *
     * @param Group|Collection<int,Group> $data [Data to be used to create the resource]
     *
     * @return GroupResource|AnonymousResourceCollection
     */
    public static function resource(Group | Collection $data): GroupResource | AnonymousResourceCollection
    {
        if ($data instanceof Collection) {
            return GroupResource::collection($data);
        } else {
            return new GroupResource($data);
        }
    }
}

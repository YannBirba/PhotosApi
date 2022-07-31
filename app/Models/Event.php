<?php

namespace App\Models;

use App\Http\Resources\Event as EventResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * App\Models\Event
 *
 * @property int $id
 * @property int|null $image_id
 * @property string $name
 * @property string|null $description
 * @property string|null $location
 * @property string $year
 * @property string $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @property-read \App\Models\Image|null $image
 * @property-read Collection|\App\Models\Image[] $images
 * @property-read int|null $images_count
 * @method static \Database\Factories\EventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Query\Builder|Event onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereYear($value)
 * @method static \Illuminate\Database\Query\Builder|Event withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Event withoutTrashed()
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'image_id',
        'description',
        'location',
        'year',
        'start_date',
        'end_date',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_event');
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public static function createRules()
    {
        return [
            'name' => 'required|string|max:50',
            'description' => 'string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'date',
            'location' => 'required|string|max:255',
            'image_id' => 'integer',
            'year' => 'required|integer',
        ];
    }

    public static function updateRules()
    {
        return [
            'name' => 'string|max:50',
            'description' => 'string|max:255',
            'start_date' => 'date',
            'end_date' => 'date',
            'location' => 'string|max:255',
            'image_id' => 'integer',
            'year' => 'integer',
        ];
    }

    public static function resource(User | Collection $data): EventResource | AnonymousResourceCollection
    {
        if ($data instanceof Collection) {
            return EventResource::collection($data);
        } else {
            return new EventResource($data);
        }
    }
}

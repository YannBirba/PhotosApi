<?php

namespace App\Models;

use App\Http\Resources\Image as ImageResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * App\Models\Image
 *
 * @property int $id
 * @property int $event_id
 * @property string|null $path
 * @property string|null $name
 * @property string|null $extension
 * @property string|null $alt
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Event $event
 * @method static \Database\Factories\ImageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Query\Builder|Image onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereAlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Image withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Image withoutTrashed()
 */
class Image extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'event_id',
        'path',
        'name',
        'extension',
        'alt',
        'title',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public static function createRules()
    {
        return [
            'event_id' => 'required|integer',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:8192',
            'alt' => 'required|string|max:255',
            'title' => 'required|string|max:255',
        ];
    }

    public static function updateRules()
    {
        return [
            'event_id' => 'integer',
            'file' => 'image|mimes:jpeg,png,jpg|max:8192',
            'alt' => 'string|max:255',
            'title' => 'string|max:255',
        ];
    }

    public static function resource(User | Collection $data): ImageResource | AnonymousResourceCollection
    {
        if ($data instanceof Collection) {
            return ImageResource::collection($data);
        } else {
            return new ImageResource($data);
        }
    }
}

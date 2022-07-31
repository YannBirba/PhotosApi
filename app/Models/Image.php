<?php

namespace App\Models;

use App\Http\Resources\Image as ImageResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

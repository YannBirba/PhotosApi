<?php

namespace App\Models;

use App\Http\Resources\Group as GroupResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'group_event');
    }

    public static function rules()
    {
        return [
            'name' => 'required|string|max:50|min:3|unique:groups',
        ];
    }

    public static function resource(User | Collection $data): GroupResource | AnonymousResourceCollection
    {
        if ($data instanceof Collection) {
            return GroupResource::collection($data);
        } else {
            return new GroupResource($data);
        }
    }
}

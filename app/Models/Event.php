<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\Image;

class Event extends Model
{
    use HasFactory;

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
        return $this->belongsToMany(Group::class,'group_event');
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
}
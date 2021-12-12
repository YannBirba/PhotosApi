<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;

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
        'description',
        'location',
        'image',
        'year',
        'start_date',
        'end_date',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_event');
    }

}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Image extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'event_id'=>$this->event_id,
            'path'=>$this->path,
            'name'=>$this->name,
            'extension'=>$this->extension,
            'alt'=>$this->alt,
            'title'=>$this->title,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
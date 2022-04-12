<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'group'=>$this->group,
            'name'=>$this->name,
            'image'=>$this->image,
            'email'=>$this->email,
            'is_admin'=>$this->is_admin,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Resources\Group as ResourcesGroup;
use App\Models\Group;
use App\Models\GroupEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * Display a listing of groups.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Group::all();
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Group::create($request->all())){
            return response()->json([
                'success' => 'Groupe créé avec succès'
            ],200
            );
        }
        else
        {
            return response()->json([
                'error' => 'Erreur lors de la création du groupe'
                ] ,500
            );
        }
    }

    /**
     * Display the specified group.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(ResourcesGroup $group)
    {
        return new ResourcesGroup($group);
    }

    /**
     * Update the specified group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        if($group->update($request->all())){
            return response()->json([
                'success' => 'Groupe modifié avec succès'
            ],200
            );
        }
        else
        {
            return response()->json([
                'error' => 'Erreur lors de la modification du groupe'
                ] ,500
            );
        }
    }

    /**
     * Remove the specified group from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        if($group->delete()){
            return response()->json([
                'success' => 'Groupe supprimé avec succès'
            ],200
            );
        }
        else
        {
            return response()->json([
                'error' => 'Erreur lors de la suppression du groupe'
                ] ,500
            );
        }
    }
    
    /**
     * Method get all events from a group
     *
     * @param int $group_id [Group id]
     *
     * @return array
     */
    public function events(int $group_id)
    {
        return Group::find($group_id)->events;
    }

        /**
     * Method get all events from a group
     *
     * @param int $group_id [Group id]
     *
     * @return array
     */
    public function addevent(Request $request)
    {
        $group_event = GroupEvent::create([
            'group_id' => $request->input('group_id'),
            'event_id' => $request->input('event_id'),
        ]);
        return response([
            'message'=> 'Inscription réussie!'
        ],Response::HTTP_ACCEPTED);
    }

    /**
     * Method get all users from a group
     *
     * @param int $group_id [Group id]
     *
     * @return array
     */
    public function users(int $group_id)
    {
        return Group::find($group_id)->users;
    }
}
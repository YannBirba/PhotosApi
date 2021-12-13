<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use App\Models\GroupEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupEventController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function groups(int $event_id)
    // {
    //     $user = Auth::user();
    //     $group_id = $user->group_id;
    //     $events = Group::find($group_id)->events;
    //     return $events;
    // }

    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function events()
    // {
    //     $user = Auth::user();
    //     $group_id = $user->group_id;
    //     $events = Group::find($group_id)->events;
    //     return $events;
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        GroupEvent::create([
            'group_id' => $request->input('group_id'),
            'event_id' => $request->input('event_id'),
        ]);
        return response([
            'message'=> 'L\'événement ' . $request->input('event_id').' a bien été ajouté au groupe ' . $request->input('group_id'),
        ],Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GroupEvent  $groupEvent
     * @return \Illuminate\Http\Response
     */
    public function show(GroupEvent $groupEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupEvent  $groupEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GroupEvent $groupEvent)
    {
        //le code doit etre faux il faut verifier si le group ou levent existe dans le cas contraire on renvoie une erreur
        //
        if ($request->has('group_id')) {
            Group::find($groupEvent->group_id)->events()->detach($groupEvent->event_id);
        }
        if ($request->has('event_id')){
            Event::find($groupEvent->event_id)->groups()->detach($groupEvent->group_id);
        }
        if($groupEvent->update($request->all())){
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupEvent  $groupEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(GroupEvent $groupEvent)
    {
        if($groupEvent->delete()){
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
}

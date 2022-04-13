<?php

namespace App\Http\Controllers;

use App\Http\Resources\Group as ResourcesGroup;
use App\Http\Resources\User as ResourcesUser;
use App\Http\Resources\Event as ResourcesEvent;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        return ResourcesGroup::collection(Group::all());
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Group::rules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            if(Group::create($request->all())){
                return response()->json([
                    'message' => 'Groupe créé avec succès',
                ], Response::HTTP_CREATED);
            }
            else
            {
                return response()->json([ 
                    'message' => 'Une erreur est survenue lors de la création du groupe',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
        $validator = Validator::make($request->all(), Group::rules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            if($group->update($request->all())){
                return response()->json([
                    'message' => 'Groupe modifié avec succès'
                ], Response::HTTP_OK);
            }
            else
            {
                return response()->json([
                    'message' => 'Une erreur est survenue lors de la modification du groupe'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
        if ($group->events()->detach()) {
            if ($group->users()->delete()) {
                if($group->delete()){
                    return response()->json([
                        'message' => 'Groupe supprimé avec succès'
                    ], Response::HTTP_OK);
                }
                else
                {
                    return response()->json([
                        'message' => 'Erreur lors de la suppression du groupe'
                        ] , Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            else {
                return response()->json([
                    'message' => 'Erreur lors de la suppression des utilisateurs rattachés au groupe'
                    ] , Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response()->json([
                'message' => 'Erreur lors du détachement des événements liés au groupe'
                ] , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Method get all events from a group
     *
     * @param int $group_id [Group id]
     *
     * @return array
     */
    public function events(Group $group)
    {
        return ResourcesEvent::collection($group->events);
    }

    /**
     * Method get all users from a group
     *
     * @param int $group_id [Group id]
     *
     * @return array
     */
    public function users(Group $group)
    {
        return ResourcesUser::collection($group->users);
    }
    
    /**
     * Method event
     *
     * @param int $group_id [explicite description]
     * @param Request $request [explicite description]
     *
     * @return Json
     */
    public function event(Group $group, Request $request)
    {
        $event_id = ($request->input('event_id'));
        if ($event_id !== null && $event_id) {
            if ($group && $group !== null) {
                $group->events()->attach($event_id);
                $event = Event::find($event_id);
                return response()->json([
                    'message' => 'L\'événement '. $event->name . ' a bien été lié au groupe '. $group->name . '.'
                    ] , Response::HTTP_OK);
            }
            else{
                return response()->json([
                    'message' => 'Aucun groupe n\'a été trouvé pour l\'identifiant renseigné'
                    ] , Response::HTTP_NOT_FOUND);
            }
        }
        else{
            return response()->json([
                'message' => 'Veuillez renseigner un événement dans la requète'
                ] , Response::HTTP_BAD_REQUEST);
        }
    }
}

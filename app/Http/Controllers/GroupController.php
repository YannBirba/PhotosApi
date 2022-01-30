<?php

namespace App\Http\Controllers;

use App\Http\Resources\Group as ResourcesGroup;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
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
        if (AuthController::isAdmin()) {
            return Group::all();
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (AuthController::isAdmin()) {
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
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
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
        if (AuthController::isAdmin()) {
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
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Remove the specified group from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        if (AuthController::isAdmin()) {
            if ($group->events()->detach()) {
                if ($group->users()->delete()) {
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
                else {
                    return response()->json([
                        'error' => 'Erreur lors de la suppression des utilisateurs rattachés au groupe'
                        ] ,500
                    );
                }
            }
            else {
                return response()->json([
                    'error' => 'Erreur lors du détachement des événements liés au groupe'
                    ] ,500
                );
            }
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
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
    
    /**
     * Method event
     *
     * @param int $group_id [explicite description]
     * @param Request $request [explicite description]
     *
     * @return Json
     */
    public function event(int $group_id, Request $request)
    {
        if (AuthController::isAdmin()) {
            $event_id = ($request->input('event_id'));
            if ($event_id !== null && $event_id) {
                $group = Group::find($group_id);
                if ($group && $group !== null) {
                    $group->events()->attach($event_id);
                    $event = Event::find($event_id);
                    return response()->json([
                        'message' => 'L\'événement '. $event->name . ' a bien été lié au groupe '. $group->name . '.'
                        ] ,500
                    );
                }
                else{
                    return response()->json([
                        'error' => 'Aucun groupe n\'a été trouvé pour l\'identifiant renseigné'
                        ] ,500
                    );
                }
            }
            else{
                return response()->json([
                    'error' => 'Veuillez renseigner un événement dans la requète'
                    ] ,500
                );
            }
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }
}

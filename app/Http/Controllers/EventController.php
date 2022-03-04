<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\Event as ResourcesEvent;
use App\Models\Group;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (AuthController::isAdmin()) {
            return ResourcesEvent::collection(Event::orderBy('start_date', 'desc')->get());
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * return all event of the user group from the actual year group_id come from group_event pivot table
     *
     * @return \Illuminate\Http\Response
     */
    public function usergroupindex()
    {
        $user = Auth::user();
        $group_id = $user->group_id;
        $group = Group::find($group_id);
        $events = $group->events;
        return $events->sortByDesc('start_date')->values();
    }
    /**
     * Store a newly created event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (AuthController::isAdmin()) {
            $validator = Validator::make($request->all(), Event::rules());
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            else{
                if($event = Event::create($request->all())){
                    return response()->json([
                        'success' => 'Evénement créé avec succès',
                        'event' => $event
                    ],200
                    );
                }
                else
                {
                    return response()->json([
                        'error' => 'Erreur lors de la création de l\'evénement'
                        ] ,500
                    );
                }
            }
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        return new ResourcesEvent($event);
    }

    /**
     * Update the specified event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        if (AuthController::isAdmin()) {
            $validator = Validator::make($request->all(), Event::rules());
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            else{
                if($event->update($request->all())){
                    return response()->json([
                        'success' => 'Evénement modifié avec succès'
                    ],200
                    );
                }
                else
                {
                    return response()->json([
                        'error' => 'Erreur lors de la modification de l\'evénement'
                        ] ,500
                    );
                }
            }
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        if (AuthController::isAdmin()) {

            if(Image::where('event_id', $event->id)->first()){
                return response()->json(
                    [
                        'error' => 'Impossible de supprimer l\'événement car il possède au moins image'
                    ] ,500
                );
            }

            if($event->groups->count( ) > 0){
                if ($event->groups()->detach()) {
                }
                else {
                return response()->json([
                    'error' => 'Erreur lors du détachement des groupes liés a l\'événement'
                    ] ,500
                );
            }
            }

            if($event->delete()){
                return response()->json([
                    'success' => 'Evénement supprimé avec succès'
                ],200
                );
            }
            else
            {
                return response()->json([
                    'error' => 'Erreur lors de la suppression de l\'événement'
                    ] ,500
                );
            }

        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }
    
    /**
     * Method get all groups of an event
     *
     * @param int $event_id [Event id]
     *
     * @return array
     */
    public function groups(int $event_id)
    {
        if (AuthController::isAdmin()) {
            return Event::find($event_id)->groups;
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

     /**
     * Search the specified event from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        // $data = $_GET['title'];
        // if($event = Event::where('title', 'like', "%{$data}%")->get()){
        //     return response()->json([
        //         'data' => $events
        //     ],200
        // ); 
        // }
        // else{
        //     return response()->json([
        //         'error' => 'Erreur lors de la recherche'
        //     ],500
        //     ); 
        // }
    }

     /**
     * Method event
     *
     * @param int $event_id [explicite description]
     * @param Request $request [explicite description]
     *
     * @return Json
     */
    public function group(int $event_id, Request $request)
    {
        if (AuthController::isAdmin()) {
            $group_id = ($request->input('group_id'));
            if ($group_id !== null && $group_id) {
                $event = Event::find($event_id);
                if ($event && $event !== null) {
                    $event->groups()->attach($group_id);
                    $group = Group::find($group_id);
                    return response()->json([
                        'message' => 'Le groupe '. $group->name . 'a bien été lié à l\'événement '. $event->name . '.'
                        ] ,500
                    );
                }
                else{
                    return response()->json([
                        'error' => 'Aucun événement n\'a été trouvé pour l\'identifiant renseigné'
                        ] ,500
                    );
                }
            }
            else{
                return response()->json([
                    'error' => 'Veuillez renseigner un groupe dans la requète'
                    ] ,500
                );
            }
        }
        return response()->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

     /**
     * Method get all images from an event
     *
     * @param int $event_id [Event id]
     *
     * @return array
     */
    public function images(int $event_id)
    {
        return Event::find($event_id)->images;
    }

    /**
     * Method get image of an event
     *
     * @param int $event_id [Event id]
     *
     * @return array
     */
    public function image(int $event_id)
    {
        if($image = Event::find($event_id)->image){
            return $image;
        }
        else {
            return response()->json([
                'error' => 'L\'image de l\'événement n\'a pas été trouvée'
                ] ,500
            );
        }
    }
}

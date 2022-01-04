<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\Event as ResourcesEvent;
use App\Models\Group;
use App\Models\Image;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return all events ordered by start_date descending
        return Event::orderBy('start_date', 'desc')->get();
    }

    /**
     * Display a listing of events of the actual year.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexactualyear()
    {
        return Event::orderBy('start_date', 'desc')->where('year', date('Y'))->get();
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Event::create($request->all())){
            return response()->json([
                'success' => 'Evénement créé avec succès'
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

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        if ($event->groups()->detach()) {
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
        else {
            return response()->json([
                'error' => 'Erreur lors du détachement des groupes liés a l\'événement'
                ] ,500
            );
        }
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
        return Event::find($event_id)->groups;
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

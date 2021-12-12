<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\Event as ResourcesEvent;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Event::all();
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
        if($event->delete()){
            return response()->json([
                'success' => 'Evénement supprimé avec succès'
            ],200
            );
        }
        else
        {
            return response()->json([
                'error' => 'Erreur lors de la suppression de l\'evénement'
                ] ,500
            );
        }
    }

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
}

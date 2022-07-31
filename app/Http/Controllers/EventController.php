<?php

namespace App\Http\Controllers;

use App\Http\Resources\Event as ResourcesEvent;
use App\Http\Resources\Image as ResourcesImage;
use App\Models\Event;
use App\Models\Group;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
        return ResourcesEvent::collection(Event::orderBy('start_date', 'desc')->get());
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
        $validator = Validator::make($request->all(), Event::createRules());
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if (Event::create($request->all())) {
                return response()->json([
                    'message' => 'Evénement créé avec succès',
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Erreur lors de la création de l\'evénement',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
        $validator = Validator::make($request->all(), Event::updateRules());
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if ($event->update($request->all())) {
                return response()->json([
                    'message' => 'Evénement modifié avec succès',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Erreur lors de la modification de l\'evénement',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
        if (Image::where('event_id', $event->id)->first()) {
            return response()->json(
                [
                    'message' => 'Impossible de supprimer l\'événement car il possède au moins image',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($event->groups->count() > 0) {
            if ($event->groups()->detach()) {
            } else {
                return response()->json([
                    'message' => 'Erreur lors du détachement des groupes liés a l\'événement',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        if ($event->delete()) {
            return response()->json([
                'message' => 'Evénement supprimé avec succès',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'événement',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Method get all groups of an event
     *
     * @param  Event  $event [Event to get groups]
     * @return AnonymousResourceCollection
     */
    public function groups(Event $event): AnonymousResourceCollection
    {
        return ResourcesEvent::collection($event->groups);
    }

    /**
     * Method event
     *
     * @param  Event  $event [Event to link group]
     * @param  Request  $request [Request]
     * @return JsonResponse
     */
    public function group(Event $event, Request $request): JsonResponse
    {
        $group_id = ($request->input('group_id'));
        if ($group_id !== null && $group_id) {
            if ($event && $event !== null) {
                $event->groups()->attach($group_id);
                $group = Group::find($group_id);

                return response()->json([
                    'message' => 'Le groupe '.$group->name.'a bien été lié à l\'événement '.$event->name.'.',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Aucun événement n\'a été trouvé pour l\'identifiant renseigné',
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            return response()->json([
                'message' => 'Veuillez renseigner un groupe dans la requète',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Method get all images from an event
     *
     * @param  Event $event [Event to get images]
     * @return AnonymousResourceCollection
     */
    public function images(Event $event): AnonymousResourceCollection
    {
        return ResourcesImage::collection($event->images);
    }

    /**
     * Method get image of an event
     *
     * @param  Event  $event [Event to get image]
     * @return ResourcesImage
     */
    public function image(Event $event): JsonResponse | ResourcesImage
    {
        if ($image = new ResourcesImage($event->image)) {
            return $image;
        } else {
            return response()->json([
                'message' => 'L\'image de l\'événement n\'a pas été trouvée',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}

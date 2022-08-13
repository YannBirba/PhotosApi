<?php

namespace App\Http\Controllers;

use App\Http\Resources\Event as ResourcesEvent;
use App\Http\Resources\Image as ResourcesImage;
use App\Models\Event;
use App\Models\Group;
use App\Models\Image;
use App\Utils\CacheHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ResourcesEvent::collection(Event::orderBy('start_date', 'desc')->get());
    }

    /**
     * return all event of the user group from the actual year group_id come from group_event pivot table
     *
     * @return ResourceCollection|JsonResponse
     */
    public function usergroupindex(): ResourceCollection | JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            $group = $user->group;
            if ($group && $group instanceof Group) {
                $events = $group->events;

                if ($events instanceof Event) {
                    return $events->sortByDesc('start_date')->values();
                }

                return response()->json([
                    'message' => "Aucun événement n'a été trouvé",
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => "Aucun groupe n'a été trouvé",
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => "L'utilisateur n'a pas été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), Event::createRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($event = Event::create($request->all())) {
            return response()->json([
                'message' => 'Evénement créé avec succès',
                'data' => CacheHelper::get($event),
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'message' => 'Erreur lors de la création de l\'evénement',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified event.
     *
     * @param  Event  $event
     * @return ResourcesEvent
     */
    public function show(Event $event): ResourcesEvent
    {
        return new ResourcesEvent($event);
    }

    /**
     * Update the specified event in storage.
     *
     * @param  Request  $request
     * @param  Event  $event
     * @return JsonResponse
     */
    public function update(Request $request, Event $event): JsonResponse
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
     * @param  Event  $event
     * @return JsonResponse
     */
    public function destroy(Event $event): JsonResponse
    {
        if (Image::where('event_id', $event->id)->first()) {
            return response()->json(
                [
                    'message' => 'Impossible de supprimer l\'événement car il possède au moins image',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if ($event->groups instanceof Collection && $event->groups->count() > 0) {
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
        $group_id = $request->group_id;
        if ($group_id !== null && $group_id) {
            if ($event) {
                $event->groups()->attach($group_id);
                $group = Group::find($group_id);

                if ($group instanceof Collection) {
                    $group = $group->first();
                }

                if ($group && $group instanceof Group) {
                    return response()->json([
                        'message' => 'Le groupe '.$group->name.'a bien été lié à l\'événement '.$event->name.'.',
                    ], Response::HTTP_OK);
                }

                return response()->json([
                    'message' => "Le groupe n'a pas été trouvé",
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => "Aucun événement n'a été trouvé pour l'identifiant renseigné",
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => "Veuillez renseigner un groupe dans la requète",
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method get all images from an event
     *
     * @param  Event  $event [Event to get images]
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
     * @return JsonResponse|ResourcesImage
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

<?php

namespace App\Http\Controllers;

use App\Http\Resources\Group as ResourcesGroup;
use App\Http\Resources\User as ResourcesUser;
use App\Models\Event;
use App\Models\Group;
use App\Utils\CacheHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends Controller
{
    /**
     * Display a listing of groups.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ResourcesGroup::collection(Group::all());
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), Group::rules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($group = Group::create($request->all())) {
            return response()->json([
                'message' => 'Groupe créé avec succès',
                'data' => CacheHelper::get($group)
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'message' => 'Une erreur est survenue lors de la création du groupe',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Display the specified group.
     *
     * @param  Group  $group
     * @return ResourcesGroup
     */
    public function show(Group $group): ResourcesGroup
    {
        return new ResourcesGroup($group);
    }

    /**
     * Update the specified group in storage.
     *
     * @param  Request  $request
     * @param  Group  $group
     * @return JsonResponse
     */
    public function update(Request $request, Group $group): JsonResponse
    {
        $validator = Validator::make($request->all(), Group::rules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if ($group->update($request->all())) {
            return response()->json([
                'message' => 'Groupe modifié avec succès',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Une erreur est survenue lors de la modification du groupe',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Remove the specified group from storage.
     *
     * @param  Group  $group
     * @return JsonResponse
     */
    public function destroy(Group $group): JsonResponse
    {
        if ($group->events()->detach()) {
            if ($group->users()->delete()) {
                if ($group->delete()) {
                    return response()->json([
                        'message' => 'Groupe supprimé avec succès',
                    ], Response::HTTP_OK);
                }

                return response()->json([
                    'message' => 'Erreur lors de la suppression du groupe',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json([
                'message' => 'Erreur lors de la suppression des utilisateurs rattachés au groupe',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Erreur lors du détachement des événements liés au groupe',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Method get all users from a group
     *
     * @param  Group  $group [Group to get users]
     * @return AnonymousResourceCollection
     */
    public function users(Group $group): AnonymousResourceCollection
    {
        return ResourcesUser::collection($group->users);
    }

    /**
     * Method event
     *
     * @param  Group  $group [Group to link event]
     * @param  Request  $request [Request]
     * @return JsonResponse
     */
    public function event(Group $group, Request $request): JsonResponse
    {
        $event_id = $request->event_id;
        if ($event_id !== null && $event_id) {
            if ($group !== null) {
                $group->events()->attach($event_id);
                $event = Event::find($event_id);

                if ($event instanceof Collection) {
                    $event = $event->first();
                }

                if ($event) {
                    return response()->json([
                        'message' => 'L\'événement ' . $event->name . ' a bien été lié au groupe ' . $group->name . '.',
                    ], Response::HTTP_OK);
                }
                return response()->json([
                    'message' => "L'événement n'a pas été trouvé.",
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => 'Aucun groupe n\'a été trouvé pour l\'identifiant renseigné',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Veuillez renseigner un événement dans la requète',
        ], Response::HTTP_BAD_REQUEST);
    }
}

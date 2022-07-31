<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as ResourcesUser;
use App\Models\User;
use App\Utils\CacheHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthController class
 */
class AuthController extends Controller
{
    /**
     * Method user
     *
     * @return JsonResponse
     */
    public function user(): ResourcesUser | JsonResponse
    {
        if ($user = Auth::user()) {
            if ($toReturn = CacheHelper::get($user)) {
                return $toReturn;
            }
        }

        return response()->json([
            'message' => "L'utilisateur n'a pas été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method index
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection | JsonResponse
    {
        if ($toReturn = CacheHelper::get(User::all())) {
            return $toReturn;
        }

        return response()->json([
            'message' => "Aucun utilisateur n'a été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method register
     *
     * @param Request $request [explicite description]
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only(['group_id', 'name', 'email', 'password', 'password_confirmation', 'is_admin']), User::createRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $user = User::create([
                'group_id' => $request->input('group_id'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'is_admin' => $request->input('is_admin'),
            ]);

            return response()->json([
                'message' => 'Inscription réussie!',
                'data' => CacheHelper::get($user),
            ], Response::HTTP_CREATED);
        }
    }

    /**
     * Method show
     *
     * @param User $user [explicite description]
     *
     * @return ResourcesUser
     */
    public static function show(User $user): ResourcesUser | JsonResponse
    {
        Cache::flush();
        if ($toReturn = CacheHelper::get($user)) {
            return $toReturn;
        }

        return response()->json([
            'message' => "L'utilisateur n'a pas été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method to login a user
     *
     * @param  Request  $request [Actual http request]
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only('email', 'password', 'remember'), User::loginRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if (! Auth::attempt($request->only('email', 'password', ), $request->input('remember'))) {
                return response()->json([
                    'message' => 'Erreur lors de la tentative de connexion',
                ], Response::HTTP_UNAUTHORIZED);
            }
            $user = Auth::user();
            $data = CacheHelper::get($user);

            return response()->json([
                'message' => 'Connexion réussie!',
                'data' => $data,
            ], Response::HTTP_ACCEPTED);
        }
    }

    /**
     * Method to logout a user
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();
        Auth::guard('web')->logout();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie!',
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Method update
     *
     * @param Request $request [Request]
     * @param User $user [User to update]
     *
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->has('email') && $request->email === Auth::user()->email) {
            $request->request->remove('email');
        }

        $validator = Validator::make($request->only('email', 'group_id', 'name', 'is_admin'), User::updateCurrentRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if ($user->update($request->all())) {
                $data = CacheHelper::get($user);

                return response()->json([
                    'message' => 'Modification réussie!',
                    'data' => $data,
                ], Response::HTTP_ACCEPTED);
            } else {
                return response()->json([
                    'message' => 'Erreur lors de la modification',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
    }

    /**
     * Method updateCurrent
     *
     * @param Request $request [Request]
     *
     * @return JsonResponse
     */
    public function updateCurrent(Request $request): JsonResponse
    {
        $user = Auth::user();
        $oldUser = clone $user;

        if ($request->has('email') && $request->email === Auth::user()->email) {
            $request->request->remove('email');
        }
        if ($request->has('group')) {
            $request->request->remove('group');
        }

        $validator = Validator::make($request->only('name', 'email'), User::updateRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if ($user->update($request->only('name', 'email')) && $oldUser) {
                $data = CacheHelper::update($oldUser, $user);

                return response()->json([
                    'message' => 'Modification réussie!',
                    'data' => $data,
                ], Response::HTTP_ACCEPTED);
            } else {
                return response()->json([
                    'message' => 'Erreur lors de la modification',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
    }

    /**
     * Method destroy
     *
     * @param User $user [User to delete]
     *
     * @return JsonResponse
     */
    public static function destroy(User $user): JsonResponse
    {
        if ($user->delete() && CacheHelper::delete($user)) {
            return response()->json([
                'message' => 'Suppression réussie!',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'Erreur lors de la suppression',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Method isAdmin
     *
     * @param User $user [User to check]
     *
     * @return bool
     */
    public static function isAdmin(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
            if ($user->is_admin) {
                return true;
            }
        } else {
            if ($user->is_admin) {
                return true;
            }
        }

        return false;
    }
}

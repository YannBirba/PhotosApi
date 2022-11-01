<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as ResourcesUser;
use App\Models\User;
use App\Utils\CacheHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
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
     * @return ResourcesUser|JsonResponse
     */
    public function user(): ResourcesUser | JsonResponse
    {
        if ($user = Auth::user()) {
            $toReturn = CacheHelper::get($user);
            if ($toReturn && $toReturn instanceof ResourcesUser) {
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
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function index(): AnonymousResourceCollection | JsonResponse
    {
        $toReturn = CacheHelper::get(User::all());
        if ($toReturn && $toReturn instanceof AnonymousResourceCollection) {
            return $toReturn;
        }

        return response()->json([
            'message' => "Aucun utilisateur n'a été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method indexWithTrashed
     *
     * @return AnonymousResourceCollection
     */
    public function indexWithTrashed(): AnonymousResourceCollection | JsonResponse
    {
        $toReturn = CacheHelper::get(User::withTrashed()->get());
        if ($toReturn && $toReturn instanceof AnonymousResourceCollection) {
            return $toReturn;
        }

        return response()->json([
            'message' => "Aucun utilisateur n'a été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method register
     *
     * @param  Request  $request [explicite description]
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only(['group_id', 'name', 'email', 'password', 'password_confirmation', 'is_admin', 'is_active']), User::createRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $password = "";
            if (is_string($request->input('password'))) {
                $password = (string) $request->input('password');
            }
            $user = User::create([
                'group_id' => $request->input('group_id'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($password),
                'is_admin' => $request->input('is_admin'),
                'is_active' => $request->input('is_active'),
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
     * @param  User  $user [explicite description]
     * @return ResourcesUser|JsonResponse
     */
    public function show(User $user): ResourcesUser | JsonResponse
    {
        $toReturn = CacheHelper::get($user);
        if ($toReturn && $toReturn instanceof ResourcesUser) {
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
            if (! Auth::attempt($request->only('email', 'password', ), (bool) $request->remember)) {
                return response()->json([
                    'message' => 'Erreur lors de la tentative de connexion',
                ], Response::HTTP_UNAUTHORIZED);
            }
            $user = Auth::user();
            $data = null;
            if ($user) {
                $data = CacheHelper::get($user);
            }

            return response()->json([
                'message' => 'Connexion réussie !',
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
        if ($user) {
            Auth::logout();
            CacheHelper::forget($user);

            return response()->json([
                'message' => 'Déconnexion réussie !',
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'message' => "L'utilisateur n'a pas été trouvé",
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Method update
     *
     * @param  Request  $request [Request]
     * @param  User  $user [User to update]
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => "L'utilisateur n'a pas été trouvé",
            ], Response::HTTP_NOT_FOUND);
        }

        if ($request->has('email') && $request->email === $user->email) {
            $request->request->remove('email');
        }

        $validator = Validator::make($request->only('email', 'group_id', 'name', 'is_admin'), User::updateCurrentRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($user->update($request->all())) {
            $data = CacheHelper::get($user);

            return response()->json([
                'message' => 'Modification réussie!',
                'data' => $data,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Erreur lors de la modification',
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Method updateCurrent
     *
     * @param  Request  $request [Request]
     * @return JsonResponse
     */
    public function updateCurrent(Request $request): JsonResponse
    {
        $user = Auth::user();
        $oldUser = null;
        if ($user) {
            $oldUser = clone $user;
        }

        if ($user && $request->has('email') && $request->email === $user->email) {
            $request->request->remove('email');
        }
        if ($user && $request->has('group')) {
            $request->request->remove('group');
        }

        $validator = Validator::make($request->only('name', 'email'), User::updateRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if ($user && $user->update($request->only('name', 'email')) && $oldUser) {
                $data = CacheHelper::update($oldUser, $user);

                return response()->json([
                    'message' => 'Modification réussie!',
                    'data' => $data,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Erreur lors de la modification',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }
    }

    /**
     * Method trash
     *
     * @param  User  $user [User to delete]
     * @return JsonResponse
     */
    public function trash(User $user): JsonResponse
    {
        if ($user->delete() && CacheHelper::delete($user)) {
            return response()->json([
                'message' => 'Suppression réussie!',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Erreur lors de la suppression',
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Method destroy
     *
     * @param  int  $user_id [explicite description]
     * @return JsonResponse
     */
    public function destroy(int $user_id): JsonResponse
    {
        $user = User::withTrashed()
            ->where('id', $user_id)
            ->first();

        if ($user && $user->forceDelete() && CacheHelper::delete($user)) {
            return response()->json([
                'message' => 'Suppression réussie!',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Erreur lors de la suppression',
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Method restore
     *
     * @param  int  $user_id [explicite description]
     * @return JsonResponse
     */
    public function restore(int $user_id): JsonResponse
    {
        $user = User::onlyTrashed()
            ->where('id', $user_id)
            ->first();

        if ($user && $user->trashed() && $user->restore() && $data = CacheHelper::get($user)) {
            return response()->json([
                'message' => 'Restauration réussie!',
                'data' => $data,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'Erreur lors de la restauration',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Method isAdmin
     *
     * @param  User  $user [User to check]
     * @return bool
     */
    public static function isAdmin(User $user = null): bool
    {
        if ($user === null) {
            $user = Auth::user();
            if ($user && $user->is_admin) {
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

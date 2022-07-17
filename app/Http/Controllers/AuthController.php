<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as ResourcesUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Utils\CacheHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * AuthController class
 */
class AuthController extends Controller
{
    public function user(): ResourcesUser
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
        return CacheHelper::get($user);
    }

    public function index(): AnonymousResourceCollection
    {
        return CacheHelper::get(User::all());
    }

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

    public static function show(User $user): ResourcesUser
    {
        return CacheHelper::get($user);
    }

    /**
     * Method to login a user
     *
     * @param  Request  $request [Actual http request]
     * @return void
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only('email', 'password', 'remember'), User::loginRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            if (!Auth::attempt($request->only('email', 'password',), $request->input('remember'))) {
                return response()->json([
                    'message' => 'Erreur lors de la tentative de connexion',
                ], Response::HTTP_UNAUTHORIZED);
            }
            /**
             * @var User $user
             */
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
     * @return void
     */
    public function logout(): JsonResponse
    {
        $user = self::user();
        Auth::guard('web')->logout();
        $user->tokens()->delete();
        return response()->json([
            'message' => 'Déconnexion réussie!'
        ], Response::HTTP_ACCEPTED);
    }

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

    public function updateCurrent(Request $request): JsonResponse
    {
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
            /**
             * @var User $user
             */
            $user = Auth::user();
            if ($user->update($request->only('name', 'email'))) {
                $data = CacheHelper::update($user);
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

    public static function destroy(User $user): JsonResponse
    {
        if ($user->delete()) {
            CacheHelper::delete($user);
            return response()->json([
                'message' => 'Suppression réussie!',
            ], Response::HTTP_ACCEPTED);
        } else {
            return response()->json([
                'message' => 'Erreur lors de la suppression',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

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

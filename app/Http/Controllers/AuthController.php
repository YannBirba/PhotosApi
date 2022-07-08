<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\User as ResourcesUser;
use Illuminate\Support\Facades\Validator;

/**
 * AuthController class
 */
class AuthController extends Controller
{
    public function user(){
        $user = Auth::user();
        return new ResourcesUser($user);
    }
    public function index(){
        return ResourcesUser::collection(User::all());
    }
    public function register(Request $request){

        $validator = Validator::make($request->all(), User::createRules());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            User::create([
                'group_id' => $request->input('group_id'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),                                                   //https://youtu.be/jIzPuM76-nI
                'is_admin' => $request->input('is_admin'),
            ]);
            return response([
                'message'=> 'Inscription réussie!'
            ],Response::HTTP_CREATED);
        }
    }

    public static function show(User $user){
        return new ResourcesUser($user);
    }

    /**
     * Method to login a user
     *
     * @param Request $request [Actual http request]
     * @return void
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), User::loginRules());

        if ($validator->fails()){
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            if (!Auth::attempt($request->only('email','password'))){
                return response([
                    'message'=> 'Erreur lors de la tentative de connexion'
                ],Response::HTTP_UNAUTHORIZED);
            }
                /** @var \App\Models\User $user **/
                $user = Auth::user();
                $token = $user->createToken('token')->plainTextToken;
                $cookie = cookie('jwt', $token, 60*24);
                return response([
                    'message'=> 'Connexion réussie!'
                ],Response::HTTP_ACCEPTED)->withCookie($cookie);
        }
    }
    /**
     * Method to logout a user
     *
     * @return void
     */
    public function logout(){
        $cookie = Cookie::forget('jwt');
        return response([
            'message'=> 'Déconnexion réussie!'
        ],Response::HTTP_ACCEPTED)->withCookie($cookie);
    }

    public function update(Request $request, User $user){

        if ($request->has('email') && $request->email === Auth::user()->email){
            $request->request->remove('email');
        }

        $validator = Validator::make($request->all(), User::updateCurrentRules());

        if ($validator->fails()){
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            if($user->update($request->all())){
                return response([
                    'message'=> 'Modification réussie!'
                ],Response::HTTP_ACCEPTED);
            }
            else{
                return response([
                    'message'=> 'Erreur lors de la modification'
                ],Response::HTTP_UNAUTHORIZED);
            }
        }

    }

    public function updateCurrent(Request $request){

        if ($request->has('email') && $request->email === Auth::user()->email){
            $request->request->remove('email');
        }
        if ($request->has('group')){
            $request->request->remove('group');
        }

        $validator = Validator::make($request->only('name','email'), User::updateRules());

        if ($validator->fails()){
            return response()->json(['message' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else{
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            if($user->update($request->only('name','email'))){
                return response([
                    'message'=> 'Modification réussie!'
                ],Response::HTTP_ACCEPTED);
            }
            else{
                return response([
                    'message'=> 'Erreur lors de la modification'
                ],Response::HTTP_UNAUTHORIZED);
            }
        }
    }

    public static function destroy(User $user){
        if($user->delete()){
            return response([
                'message'=> 'Suppression réussie!'
            ],Response::HTTP_ACCEPTED);
        }
        else{
            return response([
                'message'=> 'Erreur lors de la suppression'
            ],Response::HTTP_UNAUTHORIZED);
        }
    }

    public static function isAdmin(User $user = null){
        if ($user === null) {
            $user = Auth::user();
            if ($user->is_admin) {
                return true;
            }
        }
        else{
            if ($user->is_admin) {
                return true;
            }
        }
        return false;
    }
}

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
use App\Http\Resources\Event as ResourcesEvent;

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
        if ($request->input('is_admin') === null || !$request->input('is_admin')) {
            $is_admin = false;
        }
        else{
            $is_admin = $request->input('is_admin');
        }
        User::create([
            'group_id' => $request->input('group_id'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),                                                   //https://youtu.be/jIzPuM76-nI
            'is_admin' => $is_admin,
        ]);
        return response([
            'message'=> 'Inscription réussie!'
        ],Response::HTTP_CREATED);
    }    
    /**
     * Method to login a user
     *
     * @param Request $request [Actual http request]
     * @return void
     */
    public function login(Request $request){
        if (!Auth::attempt($request->only('email','password'))){
            return response([
                'message'=> 'Erreur lors de la tentative de connexion'
            ],Response::HTTP_UNAUTHORIZED);
        }
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;
            $cookie = cookie('jwt', $token, 60*24);
            return response([
                'message'=> 'Connexion réussie!'
            ],Response::HTTP_ACCEPTED)->withCookie($cookie);
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
    

    /**
     * Method get events of the user group of connected user
     *
     * @return array
     */
    public function events(){
        $user = Auth::user();
        $group_id = $user->group_id;
        $group = Group::find($group_id);
        $events = $group->events;
        return ResourcesEvent::collection($events);
    }

    public function update(Request $request, User $user){
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

    public function updateCurrent(Request $request){
        $user = Auth::user();
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

    public static function isAdmin(User $user = null){
        if ($user === null) {
            $user = Auth::user();
            if ($user->is_admin) {
                return true;
            }
        }
        return false;
    }
}

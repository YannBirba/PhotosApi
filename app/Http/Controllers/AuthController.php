<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthController class
 */
class AuthController extends Controller
{
    public function user(){
        $user = Auth::user();
        return $user;
    }
    public function register(Request $request){
        $user = User::create([
            'group_id' => $request->input('group_id'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),                                                   //https://youtu.be/jIzPuM76-nI
            'is_admin' => $request->input('is_admin'),
        ]);
        return response([
            'message'=> 'Inscription réussie!'
        ],Response::HTTP_ACCEPTED);
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
     * Method to check if the user is authenticated by checking if the token is valid
     * @return void
     */
    public function isloggedin(){
        $cookie = Cookie::get('jwt');
        if(isset($cookie)){
            return response([
                'message'=> 'connecté',
                'is_logged_in' => true,
            ],Response::HTTP_ACCEPTED)->withCookie($cookie);
        }
        else{
            return response([
                'message'=> 'déconnecté',
                'is_logged_in' => false,
            ],Response::HTTP_ACCEPTED);
        }
    }
    
    /**
     * Method get events of the user group of connected user
     *
     * @return array
     */
    public function events(){
        $user = Auth::user();
        $group_id = $user->group_id;
        $events = Group::find($group_id)->events;
        return $events;
    }
}

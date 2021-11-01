<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function user(){
        $user = Auth::user();
        return $user;
    }
    public function register(Request $request){
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),                                                   //https://youtu.be/jIzPuM76-nI
        ]);
        return response([
            'message'=> 'Inscription réussie!'
        ],Response::HTTP_ACCEPTED);
    }
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
    public function logout(){
        $cookie = Cookie::forget('jwt');
        return response([
            'message'=> 'Déconnexion réussie!'
        ],Response::HTTP_ACCEPTED)->withCookie($cookie);
    }

    public function islogin() :bool{
        $token = Cookie::get('jwt');
        if(isset($token)){
            return true;
        }
        else{
            return false;
        }
    }
}
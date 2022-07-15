<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (AuthController::isAdmin()) {
            return $next($request);
        }

        return response()->json(['error' => 'Non autorisé ! Il faut être administrateur pour accéder à cette fonctionnalité'], Response::HTTP_UNAUTHORIZED);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if($user->role == 'admin')
        {
            return $next($request);
        }
        return redirect()->route('index');
    }
}

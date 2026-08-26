<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to continue.');
        }

        if (Auth::guard('admin')->user()->status !== 'active') {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')->with('error', 'Your admin account is inactive.');
        }

        return $next($request);
    }
}

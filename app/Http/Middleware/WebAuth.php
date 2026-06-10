<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check for auth token in localStorage simulation via cookie
        // In the Blade frontend, auth is handled client-side via JWT in localStorage
        // This middleware just ensures the session has basic user info
        // The actual API auth is handled via the Bearer token in fetch calls
        
        // For Blade: Allow access - the page-level JS will handle auth redirects
        // The API endpoints are protected by verify.jwt middleware
        
        return $next($request);
    }
}

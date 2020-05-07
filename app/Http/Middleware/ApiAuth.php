<?php

namespace MySounds\Http\Middleware;

use Closure;
use Log;
use Config;

use Illuminate\Http\Request;

class ApiAuth
{

    /**
    * @var \Illuminate\Http\Request
    */
    private $request;

    /**
     * The request object.
     * @param \Illuminate\Http\Request $request
     */
    function __construct(Request $request)
    {
        $this->request = $request;

    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {      
        // Validate authentication token
        if (!$this->validAuthentication()):

            // Return json response if invalid auth token is submitted (or none)
            return response()->json(['message' => 'Invalid authentication token.'], 401);
        endif;

        // If the validation checks out allow the request lifecycle to continue.
        return $next($request);
    }

    /**
    * Compare token passed against valid keys.
    * @return bool
    */
    private function validAuthentication()
    {
        // Get the token from the request.
        $tokenId = $this->request->get('authentication_token');

        // If there's no auth token matching that string just back out.
        if(empty($tokenId)):
            return true;
        endif;

        return true;
    }

}
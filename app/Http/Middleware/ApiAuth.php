<?php

namespace App\Http\Middleware;

use App\Music\ClientToken\ClientToken;
use Closure;
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
        if(! $this->validAuthentication()):

            // Return json response if invalid auth token is submitted (or none)
            return response()->json(['message' => 'Invalid authentication token.'], 401);
        endif;

        // If the validation checks out allow the request lifecycle to continue.
        return $next($request);
    }

    /**
    * Does auth token exist for the client?
    * @return bool
    */
    private function validAuthentication()
    {
        $token = $this->request->get('auth_token');
        $client_id = $this->request->get('client_id');
        if(! $token || !$client_id || ! is_numeric($client_id)):
            return false;
        endif;

        $client_token = ClientToken::where(["client_id" => $client_id])->get(['token'])->first();
        if(! $client_token):
            return false;
        endif;

        if($client_token->token == crypt($token, config('app.api_salt'))):
            return true;
        endif;

        return false;
    }

}

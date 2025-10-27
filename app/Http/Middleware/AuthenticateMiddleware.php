<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => false, 'message'=> 'Token expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => false, 'message'=> 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['status' => false, 'message'=> 'Token not provided'], 401);
        
    

        } catch (JWTException $e) {
            return response()->json(['status' => false, 'message'=> 'Token not provided'], 401);
        }
    }
}

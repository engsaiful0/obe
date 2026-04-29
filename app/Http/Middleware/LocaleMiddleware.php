<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    try {
      // Locale is enabled and allowed to be change
      if (session()->has('locale') && in_array(session()->get('locale'), ['en', 'fr', 'ar', 'de'])) {
        app()->setLocale(session()->get('locale'));
      }

      $response = $next($request);
      
      // Debug logging
      Log::info('LocaleMiddleware - Response type: ' . gettype($response));
      Log::info('LocaleMiddleware - Response class: ' . (is_object($response) ? get_class($response) : 'not an object'));
      
      // Ensure we return a proper Response object
      if (!$response instanceof Response) {
        // If the response is not a proper Response object, try to convert it
        if (is_array($response)) {
          Log::warning('LocaleMiddleware - Converting array to JSON response');
          return response()->json($response);
        } elseif (is_string($response)) {
          Log::warning('LocaleMiddleware - Converting string to response');
          return new \Illuminate\Http\Response($response);
        } else {
          Log::error('LocaleMiddleware - Unknown response type: ' . gettype($response));
          return response()->json(['error' => 'Invalid response type: ' . gettype($response)], 500);
        }
      }
      
      return $response;
    } catch (\Exception $e) {
      // Log the error for debugging
      Log::error('LocaleMiddleware error: ' . $e->getMessage());
      Log::error('LocaleMiddleware stack trace: ' . $e->getTraceAsString());
      return response()->json(['error' => 'Middleware error: ' . $e->getMessage()], 500);
    }
  }
}

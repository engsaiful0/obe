<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller
{
    /**
     * Display the cache management page
     */
    public function index()
    {
        return view('content.settings.cache-clear');
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        try {
            // Clear application cache
            Artisan::call('cache:clear');
            
            // Clear configuration cache
            Artisan::call('config:clear');
            
            // Clear route cache
            Artisan::call('route:clear');
            
            // Clear view cache
            Artisan::call('view:clear');
            
            // Clear compiled services and packages cache
            Artisan::call('clear-compiled');
            
            return response()->json([
                'success' => true,
                'message' => 'All cache cleared successfully!',
                'cleared_caches' => [
                    'Application cache',
                    'Configuration cache', 
                    'Route cache',
                    'View cache',
                    'Compiled services cache'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }
}

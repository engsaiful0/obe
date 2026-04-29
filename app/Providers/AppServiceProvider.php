<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use App\Models\DeploymentPlan;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Force root URL to include subdirectory if APP_URL is set
    // This ensures route() and url() helpers include the subdirectory path
    $appUrl = config('app.url');
    if ($appUrl) {
      URL::forceRootUrl($appUrl);
    }
    
    Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
      if ($src !== null) {
        return [
          'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' :
                    (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
        ];
      }
      return [];
    });

    Blade::if('permission', function ($permission) {
        return Auth::check() && Auth::user()->hasPermissionTo($permission);
    });
    
    // Configure pagination to use Bootstrap 5
    Paginator::defaultView('vendor.pagination.custom-bootstrap-5');
    Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');
    
    // Add route model binding constraint for DeploymentPlan to only match numeric IDs
    Route::bind('deploymentPlan', function ($value) {
        // Only match if the value is numeric (to prevent filter parameters from matching)
        if (!is_numeric($value)) {
            abort(404);
        }
        return DeploymentPlan::findOrFail($value);
    });
    
    // Also bind for snake_case route parameter
    Route::bind('deployment_plan', function ($value) {
        // Only match if the value is numeric (to prevent filter parameters from matching)
        if (!is_numeric($value)) {
            abort(404);
        }
        return DeploymentPlan::findOrFail($value);
    });
  }
}
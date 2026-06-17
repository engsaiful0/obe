<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $appends = [
        'app_name',
        'brand_name',
        'display_name',
        'name',
        'institute_name',
        'logo_path',
        'logo_public_path',
        'logo_url',
    ];

    protected $fillable = [
        'university_name',
        'short_name',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'established_year',
        'vc_name',
        'pro_vc_name',
        'registrar_name',
        'controller_name',
        'time_zone',
        'academic_system',
        'status',
        'user_id',
    ];

    public function getAppNameAttribute(): string
    {
        return $this->resolvedAppName();
    }

    public function getBrandNameAttribute(): string
    {
        return $this->resolvedBrandName();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->resolvedBrandName();
    }

    public function getNameAttribute(): string
    {
        return $this->resolvedAppName();
    }

    public function getInstituteNameAttribute(): string
    {
        return $this->resolvedAppName();
    }

    public function getLogoPathAttribute(): ?string
    {
        return $this->resolvedLogoPath();
    }

    public function getLogoPublicPathAttribute(): ?string
    {
        $path = $this->resolvedLogoPath();

        if (!$path) {
            return null;
        }

        $absolutePath = base_path($path);

        return is_file($absolutePath) ? $absolutePath : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->resolvedLogoPath();

        if (!$path) {
            return null;
        }

        $url = asset($path);
        $absolutePath = base_path($path);

        if (is_file($absolutePath)) {
            return $url . '?v=' . filemtime($absolutePath);
        }

        return $url;
    }

    private function resolvedAppName(): string
    {
        return $this->university_name
            ?: $this->short_name
            ?: config('variables.templateName', config('app.name', 'Laravel'));
    }

    private function resolvedBrandName(): string
    {
        return $this->short_name
            ?: $this->university_name
            ?: config('variables.templateName', config('app.name', 'Laravel'));
    }

    private function resolvedLogoPath(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        $logo = ltrim($this->logo, '/');

        return str_contains($logo, '/') ? $logo : 'assets/img/branding/' . $logo;
    }
}

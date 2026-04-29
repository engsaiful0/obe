<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearLookupCache extends Command
{
    protected $signature = 'cache:clear-lookup';
    protected $description = 'Clear lookup data cache (nationalities, religions, etc.)';

    public function handle()
    {
        $keys = [
            'nationalities',
            'religions', 
            'boards',
            'technologies',
            'shifts',
            'academic_years',
            'semesters',
            'ssc_passing_years',
            'ssc_passing_sessions',
            'expense_heads'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        $this->info('Lookup data cache cleared successfully.');
    }
}

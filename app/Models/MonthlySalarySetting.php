<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonthlySalarySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'total_working_days',
        'official_holidays',
        'attendance_rules',
        'overtime_rules',
        'notes',
        'default_overtime_rate',
        'user_id',
    ];

    protected $casts = [
        'attendance_rules' => 'array',
        'overtime_rules' => 'array',
        'default_overtime_rate' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
        'total_working_days' => 'integer',
        'official_holidays' => 'integer',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for month name
    public function getMonthNameAttribute()
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F');
    }

    // Accessor for year-month display
    public function getYearMonthAttribute()
    {
        return $this->month_name . ' ' . $this->year;
    }

    // removed scopeActive as is_active is no longer used

    // Scope for specific year
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    // Scope for specific month
    public function scopeForMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    // Scope for specific year and month
    public function scopeForYearMonth($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    // Get default attendance rules
    public static function getDefaultAttendanceRules()
    {
        return [
            'full_day' => [
                'label' => 'Full Day',
                'value' => 1.0,
                'description' => 'Complete working day'
            ],
            'half_day' => [
                'label' => 'Half Day',
                'value' => 0.5,
                'description' => 'Half working day'
            ],
            'leave' => [
                'label' => 'Leave',
                'value' => 0.0,
                'description' => 'Authorized leave'
            ],
            'absence' => [
                'label' => 'Absence',
                'value' => 0.0,
                'description' => 'Unauthorized absence'
            ]
        ];
    }

    // Get default overtime rules
    public static function getDefaultOvertimeRules()
    {
        return [
            'enabled' => true,
            'rate_multiplier' => 1.5,
            'minimum_hours' => 1,
            'maximum_hours_per_day' => 4,
            'calculation_method' => 'hourly' // hourly, daily
        ];
    }

    // Calculate monthly salary
    public function calculateMonthlySalary($basicSalary, $actualPresentDays, $overtimeHours = 0, $deductions = 0)
    {
        $dailyRate = $basicSalary / $this->total_working_days;
        $baseSalary = $dailyRate * $actualPresentDays;
        
        $overtimeAmount = 0;
        if ($overtimeHours > 0 && $this->overtime_rules && $this->overtime_rules['enabled']) {
            $overtimeRate = $dailyRate * ($this->overtime_rules['rate_multiplier'] ?? 1.5);
            $overtimeAmount = $overtimeRate * $overtimeHours;
        }
        
        $totalSalary = $baseSalary + $overtimeAmount - $deductions;
        
        return [
            'basic_salary' => $basicSalary,
            'daily_rate' => $dailyRate,
            'actual_present_days' => $actualPresentDays,
            'base_salary' => $baseSalary,
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'deductions' => $deductions,
            'total_salary' => $totalSalary
        ];
    }

    // Check if setting exists for year and month
    public static function existsForYearMonth($year, $month, $userId = null)
    {
        $query = static::where('year', $year)->where('month', $month);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->exists();
    }

    // Get all months for a year
    public static function getMonthsForYear($year, $userId = null)
    {
        $query = static::where('year', $year);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('month')->get();
    }

    // Create settings for all 12 months of a year
    public static function createYearlySettings($year, $defaultWorkingDays, $defaultHolidays, $userId, $additionalData = [])
    {
        $settings = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
            $totalDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
            
            $settings[] = [
                'year' => $year,
                'month' => $month,
                'total_working_days' => $defaultWorkingDays,
                'official_holidays' => $defaultHolidays,
                // insert() bypasses casts; encode arrays explicitly
                'attendance_rules' => json_encode(static::getDefaultAttendanceRules()),
                'overtime_rules' => json_encode(static::getDefaultOvertimeRules()),
                'notes' => "Default settings for {$monthName} {$year}",
                'default_overtime_rate' => $additionalData['overtime_rate'] ?? 0,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return static::insert($settings);
    }
}
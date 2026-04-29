<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusHelper extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_helper_unique_id',
        'bus_helper_name',
        'father_name',
        'mother_name',
        'mobile',
        'present_address',
        'permanent_address',
        'nid_number',
        'gender_id',
        'marital_status_id',
        'nid_copy',
        'picture',
        'religion_id',
        'academic_qualification',
        'years_of_experience',
        'assigned_bus_id',
        'employee_type_id',
        'basic_salary',
        'daily_salary',
        'food_allowance',
        'house_rent',
        'medical_allowance',
        'other_allowance',
        'gross_salary',
        'user_id',
        'status_id',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'house_rent' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'years_of_experience' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function maritalStatus()
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }

    public function assignedBus()
    {
        return $this->belongsTo(Bus::class, 'assigned_bus_id');
    }

    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function helperUniqueId()
    {
        return $this->hasOne(HelperUniqueId::class, 'bus_helper_id');
    }

    // Accessors
    public function getFormattedAssistantIdAttribute()
    {
        return strtoupper($this->assistant_id);
    }

    public function getFormattedNidNumberAttribute()
    {
        return strtoupper($this->nid_number);
    }

    public function getFullNameAttribute()
    {
        return $this->assistant_name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->assistant_name . ' (' . $this->assistant_id . ')';
    }

    // Calculate gross salary automatically
    public function calculateGrossSalary()
    {
        return $this->basic_salary + $this->house_rent + $this->medical_allowance + $this->other_allowance;
    }

    // Check if assistant has assigned bus
    public function hasAssignedBus()
    {
        return !is_null($this->assigned_bus_id);
    }

    // Get assistant's experience level
    public function getExperienceLevelAttribute()
    {
        if ($this->years_of_experience <= 1) {
            return 'Beginner';
        } elseif ($this->years_of_experience <= 3) {
            return 'Intermediate';
        } elseif ($this->years_of_experience <= 5) {
            return 'Experienced';
        } else {
            return 'Senior';
        }
    }

    // Scope for filtering by experience
    public function scopeByExperience($query, $minYears = null, $maxYears = null)
    {
        if ($minYears !== null) {
            $query->where('years_of_experience', '>=', $minYears);
        }
        if ($maxYears !== null) {
            $query->where('years_of_experience', '<=', $maxYears);
        }
        return $query;
    }

    // Scope for filtering by salary range
    public function scopeBySalaryRange($query, $minSalary = null, $maxSalary = null)
    {
        if ($minSalary !== null) {
            $query->where('gross_salary', '>=', $minSalary);
        }
        if ($maxSalary !== null) {
            $query->where('gross_salary', '<=', $maxSalary);
        }
        return $query;
    }

    // Scope for assistants with assigned buses
    public function scopeWithAssignedBus($query)
    {
        return $query->whereNotNull('assigned_bus_id');
    }

    // Scope for assistants without assigned buses
    public function scopeWithoutAssignedBus($query)
    {
        return $query->whereNull('assigned_bus_id');
    }

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    // Scope for active assistants
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Scope for inactive assistants
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }
}
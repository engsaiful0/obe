<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        // Personal Information
        'full_name',
        'father_name',
        'date_of_birth',
        'national_id_passport',
        'photo',
        'contact_number',
        'alternative_contact_number',
        'email',
        'permanent_address',
        'present_address',
        'religion_id',
        'educational_qualification_id',
        'status_id',
        'experience_year_id',
        'issuing_authority_id',
        'marital_status_id',

        // License Information
        'license_number',
        'license_type_id',
        'issuing_authority',
        'license_issue_date',
        'license_expiry_date',
        'license_copy',
        'driving_experience',

        // Employment Details
        'driver_unique_id',
        'joining_date',
        
        'driver_type_id',
        'shift_timing',
        'salary_wage',
        'bank_account_number',
        'emergency_contact_person',
        'emergency_contact_number',

        // Documents & Verification
        'nid_copy',
        'police_verification_copy',
        'medical_certificate',
        'reference_name',
        'reference_contact_number',
        'basic_salary',
        'house_rent',
        'medical_allowance',
        'other_allowance',
        'daily_salary',
        'food_allowance',
        'gross_salary',


        // System fields
        'status',
        'user_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'joining_date' => 'date',
        'salary_wage' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'house_rent' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'daily_salary' => 'decimal:2',
        'food_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'driving_experience' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function driverStatus()  
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function licenseType()
    {
        return $this->belongsTo(LicenseType::class, 'license_type_id');
    }

    public function driverType()
    {
        return $this->belongsTo(DriverType::class, 'driver_type_id');
    }


    public function driverUniqueId()
    {
        return $this->hasOne(DriverUniqueId::class, 'driver_id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }

    public function educationalQualification()
    {
        return $this->belongsTo(EducationalQualification::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function experienceYear()
    {
        return $this->belongsTo(ExperienceYear::class);
    }
    public function issuingAuthority()
    {
        return $this->belongsTo(IssuingAuthority::class);
    }

    public function maritalStatus()
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    // Experience options for dropdown
    public static function getExperienceOptions()
    {
        $options = [];
        for ($i = 0; $i <= 30; $i++) {
            $options[$i] = $i . ' ' . ($i == 1 ? 'Year' : 'Years');
        }
        return $options;
    }

    // Check if license is expired
    public function isLicenseExpired()
    {
        return $this->license_expiry_date && $this->license_expiry_date < now();
    }

    // Get driver display name
    public function getDisplayNameAttribute()
    {
        return $this->full_name . ' (' . $this->driver_unique_id . ')';
    }

    // Get formatted contact numbers
    public function getFormattedContactNumberAttribute()
    {
        return $this->contact_number;
    }

    public function getFormattedAlternativeContactNumberAttribute()
    {
        return $this->alternative_contact_number;
    }

    // Check if documents are expired or missing
    public function getMissingDocuments()
    {
        $missing = [];
        
        if (!$this->photo) {
            $missing[] = 'Photo';
        }
        if (!$this->license_copy) {
            $missing[] = 'License Copy';
        }
        if (!$this->nid_copy) {
            $missing[] = 'NID Copy';
        }
        if (!$this->police_verification_copy) {
            $missing[] = 'Police Verification';
        }
        if (!$this->medical_certificate) {
            $missing[] = 'Medical Certificate';
        }
        
        return $missing;
    }
}

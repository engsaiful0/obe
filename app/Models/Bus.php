<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Basic Bus Information
        'bus_type_id',
        'bus_sub_type_id',
        'bus_number',
        'required_oil_per_km',
        'fixed_price',
        'rate_per_km',
        'model_name',
        'brand_id',
        'year_of_manufacture_id',
        'color_id',
        'chassis_number',
        'engine_number',
        
        // Registration & Legal Details
        'registration_number',
        'registration_date',
        'registration_expiry',
        'insurance_number',
        'insurance_company',
        'insurance_expiry',
        'fitness_certificate_number',
        'fitness_expiry',
        'permit_number',
        'permit_expiry',
        
        // Owner & Driver Information
        'supplier_id',
        'driver_id',
        'bus_helper_id',
        
        // Technical Specifications
        'fuel_type_id',
        'engine_capacity',
        'transmission_type',
        'seating_capacity',
        'gross_weight',
        'bus_length',
        'bus_height',
        'bus_width',
        
        // Operational Details
        'purchase_date',
        'assigned_route',
        'status',
        'last_service_date',
        'next_service_due',
        'current_mileage',
        
        // Attachments
        'bus_photo',
        'registration_document',
        'insurance_document',
        'fitness_certificate',
        'status_id',
        'user_id',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
        'fitness_expiry' => 'date',
        'permit_expiry' => 'date',
        'purchase_date' => 'date',
        'last_service_date' => 'date',
        'next_service_due' => 'date',
        'engine_capacity' => 'decimal:2',
        'gross_weight' => 'decimal:2',
        'bus_length' => 'decimal:2',
        'bus_height' => 'decimal:2',
        'bus_width' => 'decimal:2',
        'current_mileage' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'rate_per_km' => 'decimal:2',
        'seating_capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function busType()
    {
        return $this->belongsTo(BusType::class, 'bus_type_id');
    }
    public function statusOptions()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function yearOfManufacture()
    {
        return $this->belongsTo(Year::class, 'year_of_manufacture_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id');
    }

    public function busSubType()
    {
        return $this->belongsTo(BusSubType::class, 'bus_sub_type_id');
    }

    public function assignedBusHelpers()
    {
        return $this->hasMany(BusHelper::class, 'assigned_bus_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function busHelper()
    {
        return $this->belongsTo(BusHelper::class);
    }

    public function driverHelperAssignment()
    {
        return $this->hasOne(DriverHelperAssignment::class);
    }

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_UNDER_MAINTENANCE = 'under_maintenance';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_UNDER_MAINTENANCE => 'Under Maintenance',
        ];
    }

    // Transmission type constants
    const TRANSMISSION_MANUAL = 'manual';
    const TRANSMISSION_AUTOMATIC = 'automatic';

    public static function getTransmissionOptions()
    {
        return [
            self::TRANSMISSION_MANUAL => 'Manual',
            self::TRANSMISSION_AUTOMATIC => 'Automatic',
        ];
    }

    // Accessors
    public function getFormattedRegistrationNumberAttribute()
    {
        return strtoupper($this->registration_number);
    }

    public function getFormattedChassisNumberAttribute()
    {
        return strtoupper($this->chassis_number);
    }

    public function getFormattedEngineNumberAttribute()
    {
        return strtoupper($this->engine_number);
    }

    // Check if documents are expired
    public function isRegistrationExpired()
    {
        return $this->registration_expiry && $this->registration_expiry < now();
    }

    public function isInsuranceExpired()
    {
        return $this->insurance_expiry && $this->insurance_expiry < now();
    }

    public function isFitnessExpired()
    {
        return $this->fitness_expiry && $this->fitness_expiry < now();
    }

    public function isPermitExpired()
    {
        return $this->permit_expiry && $this->permit_expiry < now();
    }

    // Get all expired documents
    public function getExpiredDocuments()
    {
        $expired = [];
        
        if ($this->isRegistrationExpired()) {
            $expired[] = 'Registration';
        }
        if ($this->isInsuranceExpired()) {
            $expired[] = 'Insurance';
        }
        if ($this->isFitnessExpired()) {
            $expired[] = 'Fitness Certificate';
        }
        if ($this->isPermitExpired()) {
            $expired[] = 'Permit';
        }
        
        return $expired;
    }

    // Check if service is due
    public function isServiceDue()
    {
        return $this->next_service_due && $this->next_service_due <= now();
    }

    // Get bus display name
    public function getDisplayNameAttribute()
    {
        $parts = [];
        
        if ($this->brand) {
            $parts[] = $this->brand->brand_name;
        }
        
        if ($this->model_name) {
            $parts[] = $this->model_name;
        }
        
        if ($this->yearOfManufacture) {
            $parts[] = $this->yearOfManufacture->year_name;
        }
        
        return implode(' ', $parts) ?: 'Bus #' . $this->id;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Punishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'bus_sub_type_id',
        'punishment_type_id',
        'violation_type_id',
        'description',
        'punishment_date',
        'fine_amount',
        'suspension_days',
        'status',
        'remarks',
        'document_path',
        'user_id',
        'witness_employee_id',
    ];

    protected $casts = [
        'punishment_date' => 'date',
        'fine_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bus that owns the punishment
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the driver that owns the punishment
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the bus helper that owns the punishment
     */
    public function busHelper()
    {
        return $this->belongsTo(BusHelper::class);
    }

   

    /**
     * Get the user who created the record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the punishment type
     */
    public function punishmentType()
    {
        return $this->belongsTo(PunishmentType::class);
    }

    /**
     * Get the violation type
     */
    public function violationType()
    {
        return $this->belongsTo(ViolationType::class);
    }

    /**
     * Get the witness employee
     */
    public function witnessEmployee()
    {
        return $this->belongsTo(Employee::class, 'witness_employee_id');
    }

    /**
     * Get punishment type options
     */
    public static function getPunishmentTypes()
    {
        return [
            'warning' => 'Warning',
            'fine' => 'Fine',
            'suspension' => 'Suspension',
            'termination' => 'Termination',
        ];
    }

    /**
     * Get violation type options
     */
    public static function getViolationTypes()
    {
        return [
            'speeding' => 'Speeding',
            'accident' => 'Accident',
            'late' => 'Late Arrival',
            'policy_breach' => 'Policy Breach',
            'bus_damage' => 'Bus Damage',
            'unauthorized_use' => 'Unauthorized Use',
            'reckless_driving' => 'Reckless Driving',
            'documentation' => 'Documentation Issue',
            'other' => 'Other',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Scope a query to only include active punishments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed punishments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'driver_id',
        'alternate_driver_id',
        'bus_helper_id',
        'alternate_bus_helper_id',
        'start_stoppage_id',
        'end_stoppage_id',
        'bus_sub_type_id',
        'trip_type',
        'trip_number',
        'passengers',
        'in_time',
        'out_time',
        'trip_date',
        'total_distance',
        'remarks',
        'user_id',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'trip_number' => 'integer',
        'in_time' => 'datetime:H:i',
        'out_time' => 'datetime:H:i',
        'total_distance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bus that owns the trip
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the start stoppage
     */
    public function startStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'start_stoppage_id');
    }

    /**
     * Get the end stoppage
     */
    public function endStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'end_stoppage_id');
    }

    /**
     * Get the driver assigned to this trip
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the assistant assigned to this trip
     */
    public function assistant()
    {
        return $this->belongsTo(Assistant::class);
    }

    /**
     * Get the alternate driver assigned to this trip
     */
    public function alternateDriver()
    {
        return $this->belongsTo(Driver::class, 'alternate_driver_id');
    }

    /**
     * Get the bus helper assigned to this trip
     */
    public function busHelper()
    {
        return $this->belongsTo(BusHelper::class, 'bus_helper_id');
    }

    /**
     * Get the alternate bus helper assigned to this trip
     */
    public function alternateBusHelper()
    {
        return $this->belongsTo(BusHelper::class, 'alternate_bus_helper_id');
    }

    /**
     * Get the user who recorded the trip
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bus sub type for this trip
     */
    public function busSubType()
    {
        return $this->belongsTo(BusSubType::class, 'bus_sub_type_id');
    }

    /**
     * Scope for filtering by bus
     */
    public function scopeForBus($query, $busId)
    {
        return $query->where('bus_id', $busId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('trip_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by trip type
     */
    public function scopeTripType($query, $type)
    {
        return $query->where('trip_type', $type);
    }

    /**
     * Scope for monthly records
     */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('trip_date', $year)
                     ->whereMonth('trip_date', $month);
    }

    /**
     * Check if both In and Out trips completed for a date
     */
    public static function isDayComplete($busId, $date)
    {
        $inTrip = self::where('bus_id', $busId)
                      ->where('trip_date', $date)
                      ->where('trip_type', 'in')
                      ->exists();
                      
        $outTrip = self::where('bus_id', $busId)
                       ->where('trip_date', $date)
                       ->where('trip_type', 'out')
                       ->exists();
                       
        return $inTrip && $outTrip;
    }

    /**
     * Get monthly bill for a bus
     */
    public static function getMonthlyBill($busId, $year, $month)
    {
        $bus = Bus::find($busId);
        if (!$bus) {
            return 0;
        }

        $subType = $bus->busSubType;
        if (!$subType) {
            return 0;
        }

        // For BRTC Hired Bus - calculate based on distance
        if ($subType->sub_type_name === 'BRTC Hired Bus') {
            $totalDistance = self::forBus($busId)
                                ->forMonth($year, $month)
                                ->sum('total_distance');
            
            // Price per km is stored in bus->rate_per_km
            return $totalDistance * ($bus->rate_per_km ?? 0);
        }

        // For Hired Bus - calculate based on completed days
        if ($subType->sub_type_name === 'Hired Bus') {
            $trips = self::forBus($busId)
                         ->forMonth($year, $month)
                         ->get()
                         ->groupBy('trip_date');
            
            $completedDays = 0;
            foreach ($trips as $date => $tripRecords) {
                $hasIn = $tripRecords->where('trip_type', 'in')->isNotEmpty();
                $hasOut = $tripRecords->where('trip_type', 'out')->isNotEmpty();
                
                if ($hasIn && $hasOut) {
                    $completedDays++;
                }
            }
            
            // Daily fixed price is stored in bus->fixed_price
            return $completedDays * ($bus->fixed_price ?? 0);
        }

        return 0;
    }
}

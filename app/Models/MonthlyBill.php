<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlyBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'bill_month',
        'from_date',
        'to_date',
        'bus_type',
        'base_amount',
        'total_rewards',
        'total_punishments',
        'final_amount',
        'total_trips',
        'total_distance',
        'rate_per_km',
        'daily_rate',
        'remarks',
        'status',
        'user_id',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'base_amount' => 'decimal:2',
        'total_rewards' => 'decimal:2',
        'total_punishments' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'total_distance' => 'decimal:2',
        'rate_per_km' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForBus($query, $busId)
    {
        return $query->where('bus_id', $busId);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->where('bill_month', sprintf('%04d-%02d', $year, $month));
    }

    public function scopeForDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate]);
    }

    public function scopeByBusType($query, $busType)
    {
        return $query->where('bus_type', $busType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Static methods for bill generation
    public static function generateMonthlyBill($busId, $year, $month, $fromDate = null, $toDate = null)
    {
        $bus = Bus::with('busSubType')->find($busId);
        if (!$bus) {
            throw new \Exception('Bus not found');
        }

        // Set default date range if not provided
        if (!$fromDate) {
            $fromDate = Carbon::create($year, $month, 1)->startOfMonth();
        }
        if (!$toDate) {
            $toDate = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $subType = $bus->busSubType;
        if (!$subType) {
            throw new \Exception('Bus sub type not found');
        }

        $busType = null;
        $baseAmount = 0;
        $totalTrips = 0;
        $totalDistance = 0;
        $ratePerKm = null;
        $dailyRate = null;

        // Determine bus type and calculate base amount
        if ($subType->sub_type_name === 'Hired Bus') {
            $busType = 'hired';
            $dailyRate = $bus->fixed_price ?? 0;
            
            // Get completed days (both in and out trips for same date)
            $trips = BusTrip::forBus($busId)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->get()
                ->groupBy('trip_date');

            $completedDays = 0;
            foreach ($trips as $date => $tripRecords) {
                $hasIn = $trips->where('trip_type', 'in')->isNotEmpty();
                $hasOut = $trips->where('trip_type', 'out')->isNotEmpty();
                
                if ($hasIn && $hasOut) {
                    $completedDays++;
                }
            }
            
            $totalTrips = $completedDays;
            $baseAmount = $completedDays * $dailyRate;

        } elseif ($subType->sub_type_name === 'BRTC Hired Bus') {
            $busType = 'brtc';
            $ratePerKm = $bus->rate_per_km ?? 0;
            
            // Get total distance for the period
            $totalDistance = BusTrip::forBus($busId)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->sum('total_distance');
            
            $totalTrips = BusTrip::forBus($busId)
                ->whereBetween('trip_date', [$fromDate, $toDate])
                ->count();
            
            $baseAmount = $totalDistance * $ratePerKm;
        } else {
            throw new \Exception('Bus is not a hired bus or BRTC bus');
        }

        // Calculate rewards and punishments for the period
        $totalRewards = Reward::where('bus_id', $busId)
            ->whereBetween('reward_date', [$fromDate, $toDate])
            ->sum('reward_amount');

        $totalPunishments = Punishment::where('bus_id', $busId)
            ->whereBetween('punishment_date', [$fromDate, $toDate])
            ->sum('fine_amount');

        $finalAmount = $baseAmount + $totalRewards - $totalPunishments;

        // Create or update monthly bill
        return self::updateOrCreate(
            [
                'bus_id' => $busId,
                'bill_month' => sprintf('%04d-%02d', $year, $month),
            ],
            [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'bus_type' => $busType,
                'base_amount' => $baseAmount,
                'total_rewards' => $totalRewards,
                'total_punishments' => $totalPunishments,
                'final_amount' => $finalAmount,
                'total_trips' => $totalTrips,
                'total_distance' => $totalDistance,
                'rate_per_km' => $ratePerKm,
                'daily_rate' => $dailyRate,
                'status' => 'generated',
                'user_id' => Auth::check() ? Auth::id() : 1,
            ]
        );
    }

    // Helper methods
    public function getFormattedBillMonthAttribute()
    {
        return Carbon::createFromFormat('Y-m', $this->bill_month)->format('F Y');
    }

    public function getBusTypeNameAttribute()
    {
        return $this->bus_type === 'hired' ? 'Hired Bus' : 'BRTC Bus';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'draft' => 'badge-secondary',
            'generated' => 'badge-info',
            'approved' => 'badge-success',
            'paid' => 'badge-primary',
            default => 'badge-secondary'
        };
    }
}
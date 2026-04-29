<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonthlyFeePayment;
use App\Models\FeeSettings;
use App\Models\AcademicYear;
use App\Models\Student;
use Carbon\Carbon;

class TestMonthlyDueReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:monthly-due-report {month?} {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the monthly due report functionality for a specific month and year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->argument('month') ?? Carbon::now()->month;
        $year = $this->argument('year') ?? Carbon::now()->year;

        $this->info("Testing Monthly Due Report for {$month}/{$year}");
        $this->newLine();

        // Check if fee settings exist
        $feeSettings = FeeSettings::getActive();
        if (!$feeSettings) {
            $this->error('No active fee settings found. Please configure fee settings first.');
            return 1;
        }

        $this->info("Fee Settings:");
        $this->line("- Monthly Fee Amount: ৳{$feeSettings->monthly_fee_amount}");
        $this->line("- Payment Deadline Day: {$feeSettings->payment_deadline_day}");
        $this->line("- Fine Amount Per Day: ৳{$feeSettings->fine_amount_per_day}");
        $this->line("- Grace Period Days: {$feeSettings->grace_period_days}");
        $this->newLine();

        // Get current academic year
        $academicYear = AcademicYear::latest()->first();
        if (!$academicYear) {
            $this->error('No academic year found.');
            return 1;
        }

        $this->info("Academic Year: {$academicYear->academic_year_name}");
        $this->newLine();

        // Get payments for the specified month/year
        $payments = MonthlyFeePayment::with('student')
            ->where('academic_year_id', $academicYear->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        if ($payments->isEmpty()) {
            $this->warn("No payment records found for {$month}/{$year}");
            $this->info("You may need to generate monthly payments first using the fee settings page.");
            return 0;
        }

        $this->info("Found {$payments->count()} payment records for {$month}/{$year}");
        $this->newLine();

        // Update overdue status for all unpaid payments
        $this->info("Updating overdue status...");
        $updatedCount = 0;
        foreach ($payments->where('is_paid', false) as $payment) {
            $oldOverdue = $payment->is_overdue;
            $oldFine = $payment->fine_amount;
            $oldTotal = $payment->total_amount;
            
            $payment->calculateAndUpdateOverdue();
            
            if ($oldOverdue !== $payment->is_overdue || $oldFine !== $payment->fine_amount) {
                $updatedCount++;
            }
        }
        $this->info("Updated {$updatedCount} payment records");
        $this->newLine();

        // Display statistics
        $totalStudents = $payments->count();
        $paidCount = $payments->where('is_paid', true)->count();
        $unpaidCount = $payments->where('is_paid', false)->count();
        $overdueCount = $payments->where('is_overdue', true)->count();

        $totalFeeAmount = $payments->sum('fee_amount');
        $totalFineAmount = $payments->sum('fine_amount');
        $totalCollected = $payments->where('is_paid', true)->sum('total_amount');
        $totalPending = $payments->where('is_paid', false)->sum('total_amount');

        $this->info("Statistics:");
        $this->line("- Total Students: {$totalStudents}");
        $this->line("- Paid: {$paidCount}");
        $this->line("- Unpaid: {$unpaidCount}");
        $this->line("- Overdue: {$overdueCount}");
        $this->newLine();

        $this->info("Financial Summary:");
        $this->line("- Total Fee Amount: ৳" . number_format($totalFeeAmount, 2));
        $this->line("- Total Fine Amount: ৳" . number_format($totalFineAmount, 2));
        $this->line("- Total Collected: ৳" . number_format($totalCollected, 2));
        $this->line("- Total Pending: ৳" . number_format($totalPending, 2));
        $this->newLine();

        // Show some overdue examples
        $overduePayments = $payments->where('is_overdue', true)->take(5);
        if ($overduePayments->isNotEmpty()) {
            $this->warn("Sample Overdue Payments:");
            foreach ($overduePayments as $payment) {
                $this->line("- {$payment->student->full_name_in_english_block_letter} (ID: {$payment->student->student_unique_id})");
                $this->line("  Due: {$payment->due_date->format('Y-m-d')} | Days Overdue: {$payment->days_overdue}");
                $this->line("  Fee: ৳{$payment->fee_amount} | Fine: ৳{$payment->fine_amount} | Total: ৳{$payment->total_amount}");
            }
        } else {
            $this->info("No overdue payments found.");
        }

        $this->newLine();
        $this->info("Test completed successfully!");

        return 0;
    }
}


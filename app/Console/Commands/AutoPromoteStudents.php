<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StudentPromotionService;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class AutoPromoteStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:auto-promote 
                            {--academic-year= : Academic year ID to filter students}
                            {--semester= : Semester ID to filter students}
                            {--technology= : Technology ID to filter students}
                            {--dry-run : Show what would be promoted without actually promoting}
                            {--force : Force promotion even if students are not eligible}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically promote eligible students to the next semester/year';

    protected $promotionService;

    public function __construct(StudentPromotionService $promotionService)
    {
        parent::__construct();
        $this->promotionService = $promotionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic student promotion process...');

        // Get command options
        $academicYearId = $this->option('academic-year');
        $semesterId = $this->option('semester');
        $technologyId = $this->option('technology');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Build criteria
        $criteria = [];
        if ($academicYearId) {
            $criteria['academic_year_id'] = $academicYearId;
        }
        if ($semesterId) {
            $criteria['semester_id'] = $semesterId;
        }
        if ($technologyId) {
            $criteria['technology_id'] = $technologyId;
        }

        try {
            if ($dryRun) {
                $this->info('DRY RUN MODE - No actual promotions will be performed');
                $this->performDryRun($criteria);
            } else {
                $this->performPromotion($criteria, $force);
            }

            $this->info('Automatic promotion process completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during automatic promotion: ' . $e->getMessage());
            Log::error('Auto promotion command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    private function performDryRun($criteria)
    {
        $eligibleStudents = $this->promotionService->getEligibleStudents($criteria);
        
        $this->info("Found {$eligibleStudents->count()} eligible students for promotion:");
        
        if ($eligibleStudents->count() === 0) {
            $this->warn('No eligible students found for promotion.');
            return;
        }

        $table = [];
        foreach ($eligibleStudents as $student) {
            $nextInfo = $student->getNextSemester();
            $table[] = [
                'ID' => $student->id,
                'Name' => $student->full_name_in_english_block_letter,
                'Current Year' => $student->academicYear->academic_year_name ?? 'N/A',
                'Current Semester' => $student->semester->semester_name ?? 'N/A',
                'Next Year' => $nextInfo['academic_year']->academic_year_name ?? 'N/A',
                'Next Semester' => $nextInfo['semester']->semester_name ?? 'N/A',
                'Type' => $nextInfo['promotion_type'] ?? 'N/A'
            ];
        }

        $this->table([
            'ID', 'Name', 'Current Year', 'Current Semester', 
            'Next Year', 'Next Semester', 'Type'
        ], $table);
    }

    private function performPromotion($criteria, $force)
    {
        $eligibleStudents = $this->promotionService->getEligibleStudents($criteria);
        
        if ($eligibleStudents->count() === 0) {
            $this->warn('No eligible students found for promotion.');
            return;
        }

        $this->info("Promoting {$eligibleStudents->count()} eligible students...");

        // Show progress bar
        $progressBar = $this->output->createProgressBar($eligibleStudents->count());
        $progressBar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($eligibleStudents as $student) {
            try {
                $result = $this->promotionService->promoteStudent($student, [
                    'reason' => 'Automatic promotion via command',
                    'notes' => 'Promoted on ' . now()->format('Y-m-d H:i:s') . ' via auto-promotion command'
                ]);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                    $this->warn("Failed to promote student {$student->id}: {$result['message']}");
                }
            } catch (\Exception $e) {
                $failureCount++;
                $this->warn("Error promoting student {$student->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Show results
        $this->info("Promotion completed!");
        $this->info("Successfully promoted: {$successCount} students");
        if ($failureCount > 0) {
            $this->warn("Failed to promote: {$failureCount} students");
        }

        // Log the results
        Log::info('Auto promotion command completed', [
            'total_processed' => $eligibleStudents->count(),
            'successful' => $successCount,
            'failed' => $failureCount,
            'criteria' => $criteria
        ]);
    }
}

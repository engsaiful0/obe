<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Services\FeeManagementService;

class InitializeFeeSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:initialize-summaries {--academic-year-id= : Specific academic year ID} {--force : Force update existing summaries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize or update fee summaries for all students';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $feeManagementService = new FeeManagementService();
        
        $academicYearId = $this->option('academic-year-id');
        $force = $this->option('force');
        
        // Get academic years to process
        if ($academicYearId) {
            $academicYears = AcademicYear::where('id', $academicYearId)->get();
            if ($academicYears->isEmpty()) {
                $this->error("Academic year with ID {$academicYearId} not found.");
                return Command::FAILURE;
            }
        } else {
            $academicYears = AcademicYear::all();
        }
        
        $this->info('Starting fee summaries initialization...');
        $this->info('Academic Years: ' . $academicYears->count());
        
        $totalStudents = 0;
        $processedStudents = 0;
        $errorCount = 0;
        
        foreach ($academicYears as $academicYear) {
            $this->info("Processing Academic Year: {$academicYear->academic_year_name}");
            
            $students = Student::where('academic_year_id', $academicYear->id)->get();
            $totalStudents += $students->count();
            
            $this->info("Students in this academic year: {$students->count()}");
            
            if ($students->count() > 0) {
                $bar = $this->output->createProgressBar($students->count());
                $bar->start();
                
                foreach ($students as $student) {
                    try {
                        // Check if summary already exists
                        $existingSummary = $feeManagementService->updateFeeSummary($student->id, $academicYear->id);
                        
                        if ($existingSummary->wasRecentlyCreated || $force) {
                            // Log the creation or update
                            $this->line("\nInitialized/Updated summary for: {$student->student_unique_id} - {$student->full_name_in_english_block_letter}");
                            $this->line("  - Semesters Completed: {$existingSummary->semesters_completed}/8");
                            $this->line("  - Monthly Fees Completed: {$existingSummary->months_completed}/48");
                            $this->line("  - Total Paid: " . number_format($existingSummary->total_paid, 2));
                            $this->line("  - Total Due: " . number_format($existingSummary->total_due, 2));
                            $this->line("  - Completion: {$existingSummary->completion_percentage}%");
                        }
                        
                        $processedStudents++;
                        
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->error("\nError processing student {$student->student_unique_id}: " . $e->getMessage());
                    }
                    
                    $bar->advance();
                }
                
                $bar->finish();
                $this->line('');
            }
        }
        
        $this->info('Fee summaries initialization completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Students', $totalStudents],
                ['Successfully Processed', $processedStudents],
                ['Errors', $errorCount],
                ['Academic Years Processed', $academicYears->count()],
            ]
        );
        
        if ($errorCount > 0) {
            $this->warn("There were {$errorCount} errors during processing. Please review the error messages above.");
            return Command::FAILURE;
        }
        
        $this->info('All fee summaries have been successfully initialized/updated!');
        return Command::SUCCESS;
    }
}

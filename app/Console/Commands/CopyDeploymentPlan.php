<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeploymentPlan;
use App\Models\DeploymentPlanItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CopyDeploymentPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deployment-plan:copy-today-to-tomorrow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy today\'s deployment plans to tomorrow if they don\'t exist (runs at 4 PM daily)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $this->info("Checking deployment plans for {$tomorrow->format('Y-m-d')}...");

        // Get all plans for today
        $todayPlans = DeploymentPlan::whereDate('deployment_date', $today)
            ->with('items')
            ->get();

        if ($todayPlans->isEmpty()) {
            $this->warn("No deployment plans found for today ({$today->format('Y-m-d')}). Nothing to copy.");
            return Command::SUCCESS;
        }

        $copiedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            foreach ($todayPlans as $todayPlan) {
                // Check if a plan already exists for tomorrow with the same trip_time_id, bus_user_id, deployment_type_id, and trip_type
                $existingPlanQuery = DeploymentPlan::whereDate('deployment_date', $tomorrow)
                    ->where('trip_time_id', $todayPlan->trip_time_id)
                    ->where('bus_user_id', $todayPlan->bus_user_id);
                
                // Add deployment_type_id check if it exists
                if ($todayPlan->deployment_type_id) {
                    $existingPlanQuery->where('deployment_type_id', $todayPlan->deployment_type_id);
                }
                
                // Add trip_type check if it exists
                if ($todayPlan->trip_type) {
                    $existingPlanQuery->where('trip_type', $todayPlan->trip_type);
                }
                
                $existingPlan = $existingPlanQuery->first();

                if ($existingPlan) {
                    $skippedCount++;
                    $this->line("Plan already exists for tomorrow (Trip Time: {$todayPlan->trip_time_id}, Bus User: {$todayPlan->bus_user_id}). Skipping...");
                    continue;
                }

                // Copy the plan to tomorrow
                try {
                    $newPlan = DeploymentPlan::create([
                        'deployment_date' => $tomorrow,
                        'trip_time_id' => $todayPlan->trip_time_id,
                        'bus_user_id' => $todayPlan->bus_user_id,
                        'deployment_type_id' => $todayPlan->deployment_type_id,
                        'trip_type' => $todayPlan->trip_type,
                        'user_id' => $todayPlan->user_id, // Keep the original creator
                        'remarks' => $todayPlan->remarks,
                    ]);

                    // Copy all items
                    foreach ($todayPlan->items as $item) {
                        DeploymentPlanItem::create([
                            'deployment_plan_id' => $newPlan->id,
                            'stoppage_id' => $item->stoppage_id,
                            'bus_sub_type_id' => $item->bus_sub_type_id,
                            'bus_id' => $item->bus_id,
                        ]);
                    }

                    $copiedCount++;
                    $this->info("Copied plan for Trip Time: {$todayPlan->trip_time_id}, Bus User: {$todayPlan->bus_user_id}");

                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("Error copying plan (Trip Time: {$todayPlan->trip_time_id}, Bus User: {$todayPlan->bus_user_id}): {$e->getMessage()}");
                    Log::error('Error copying deployment plan', [
                        'today_plan_id' => $todayPlan->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();

            $this->newLine();
            $this->info("Deployment plan copy completed!");
            $this->info("Copied: {$copiedCount} plans");
            if ($skippedCount > 0) {
                $this->warn("Skipped: {$skippedCount} plans (already exist)");
            }
            if ($errorCount > 0) {
                $this->error("Errors: {$errorCount} plans");
            }

            Log::info('Deployment plan copy command completed', [
                'today' => $today->format('Y-m-d'),
                'tomorrow' => $tomorrow->format('Y-m-d'),
                'copied' => $copiedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Fatal error: {$e->getMessage()}");
            Log::error('Fatal error in deployment plan copy command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}


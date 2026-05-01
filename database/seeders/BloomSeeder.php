<?php

namespace Database\Seeders;

use App\Models\Bloom;
use App\Models\Status;
use Illuminate\Database\Seeder;

class BloomSeeder extends Seeder
{
    /**
     * Standard Bloom taxonomy levels (2001 revision verbs).
     */
    public function run(): void
    {
        $status = Status::query()
            ->whereHas('relatedTo', fn ($q) => $q->where('name', 'OBE'))
            ->where('status_name', 'Active')
            ->first();

        if (! $status) {
            $status = Status::query()->where('status_name', 'Active')->first();
        }

        if (! $status) {
            $this->command?->warn('BloomSeeder skipped: no Active status found.');

            return;
        }

        $levels = [
            1 => ['Remember', 'Recall facts, terms, concepts, or answers.'],
            2 => ['Understand', 'Construct meaning from instructional messages.'],
            3 => ['Apply', 'Carry out or use a procedure in a given situation.'],
            4 => ['Analyze', 'Break material into parts and determine relationships.'],
            5 => ['Evaluate', 'Make judgments based on criteria and standards.'],
            6 => ['Create', 'Put elements together to form a coherent or functional whole.'],
        ];

        foreach ($levels as $order => [$name, $desc]) {
            Bloom::query()->updateOrCreate(
                [
                    'level_order' => $order,
                ],
                [
                    'name' => $name,
                    'description' => $desc,
                    'status_id' => $status->id,
                ]
            );
        }
    }
}

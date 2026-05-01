<?php

namespace App\Services\StudentMarks;

use App\Models\AssessmentComponent;
use App\Models\QuestionCloMapping;
use Illuminate\Support\Collection;

class MarkRules
{
    /**
     * @param  array<int, array{question_clo_mapping_id: int|string, obtained_marks: float|string|int}>  $questions
     * @param  Collection<int, QuestionCloMapping>  $mappingsById  keyed by mapping id
     * @return array<int, string>
     */
    public static function validateQuestionsAgainstMappings(
        array $questions,
        Collection $mappingsById,
        float $declaredTotal,
        AssessmentComponent $component
    ): array {
        $errors = [];
        $sum = 0.0;
        $cap = round((float) $component->marks, 2);

        foreach ($questions as $i => $q) {
            $idx = $i + 1;
            $id = (int) ($q['question_clo_mapping_id'] ?? 0);
            $got = round((float) ($q['obtained_marks'] ?? 0), 2);
            $mapping = $mappingsById->get($id);
            if (! $mapping) {
                $errors[] = __('Row :idx: unknown question mapping.', ['idx' => $idx]);

                continue;
            }
            $max = round((float) $mapping->marks, 2);
            if ($got < 0) {
                $errors[] = __('Row :idx: :label cannot be negative.', [
                    'idx' => $idx,
                    'label' => $mapping->question_label,
                ]);

                continue;
            }
            if ($got > $max + 0.0001) {
                $errors[] = __('Row :idx: :label cannot exceed :max.', [
                    'idx' => $idx,
                    'label' => $mapping->question_label,
                    'max' => rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.'),
                ]);

                continue;
            }
            $sum += $got;
        }

        $sum = round($sum, 2);
        $declaredTotal = round($declaredTotal, 2);

        if (abs($sum - $declaredTotal) > 0.0001) {
            $errors[] = __(
                'Sum of question marks (:sum) must equal total marks (:total).',
                [
                    'sum' => rtrim(rtrim(number_format($sum, 2, '.', ''), '0'), '.'),
                    'total' => rtrim(rtrim(number_format($declaredTotal, 2, '.', ''), '0'), '.'),
                ]
            );
        }

        if ($declaredTotal > $cap + 0.0001) {
            $errors[] = __(
                'Total marks (:total) cannot exceed the assessment component cap (:cap).',
                [
                    'total' => rtrim(rtrim(number_format($declaredTotal, 2, '.', ''), '0'), '.'),
                    'cap' => rtrim(rtrim(number_format($cap, 2, '.', ''), '0'), '.'),
                ]
            );
        }

        return $errors;
    }

    /**
     * @param  array<int, array{question_clo_mapping_id: int|string, obtained_marks: mixed}>  $questions
     * @param  Collection<int, QuestionCloMapping>  $expectedMappings
     * @return array<int, string>
     */
    public static function validateCompleteQuestionSet(array $questions, Collection $expectedMappings): array
    {
        $byId = collect($questions)->groupBy(fn ($q) => (int) ($q['question_clo_mapping_id'] ?? 0));

        foreach ($expectedMappings->keys() as $id) {
            if (! $byId->has($id) || $byId->get($id)->count() !== 1) {
                return [__('Submit marks for every question part defined for this component and session.')];
            }
        }

        foreach ($byId->keys() as $id) {
            if ($id <= 0 || ! $expectedMappings->has($id)) {
                return [__('Unexpected or duplicate question mapping in submission.')];
            }
        }

        return [];
    }
}

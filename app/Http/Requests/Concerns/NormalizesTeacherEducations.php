<?php

namespace App\Http\Requests\Concerns;

trait NormalizesTeacherEducations
{
    /**
     * Educational qualifications are optional. Only rows with an explicit degree (BSc/MSc/PhD)
     * are validated and saved; placeholder rows are dropped.
     */
    protected function normalizeTeacherEducationsInput(): void
    {
        $educations = $this->input('educations');
        if (! is_array($educations)) {
            return;
        }

        $kept = [];
        foreach ($educations as $row) {
            if (! is_array($row)) {
                continue;
            }

            $degree = trim((string) ($row['degree'] ?? ''));
            if ($degree === '') {
                continue;
            }

            $kept[] = $row;
        }

        $this->merge(['educations' => $kept]);
    }
}

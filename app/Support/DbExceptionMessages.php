<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Throwable;

class DbExceptionMessages
{
    public static function isDuplicateKey(Throwable $e): bool
    {
        if ($e instanceof QueryException) {
            $sqlState = $e->errorInfo[0] ?? '';
            $driverCode = $e->errorInfo[1] ?? null;

            return $sqlState === '23000'
                || $sqlState === '23505'
                || $driverCode === 1062
                || $driverCode === 19;
        }

        $message = strtolower($e->getMessage());

        return str_contains($message, 'duplicate entry')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'integrity constraint violation');
    }

    public static function humanize(Throwable $e, ?string $context = null): ?string
    {
        if (! self::isDuplicateKey($e)) {
            return null;
        }

        $message = strtolower($e->getMessage());
        $isStudentMarks = $context === 'student_marks'
            || str_contains($message, 'student_marks');

        if ($isStudentMarks) {
            return __('Marks for this student are already saved for this course. Upload or save again to update the existing marks — do not add a second row for the same student.');
        }

        return __('This record already exists. Please update the existing entry instead of creating a duplicate.');
    }
}

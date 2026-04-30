<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Course;
use App\Models\Program;
use App\Models\Section;
use App\Models\AcademicSession;
use App\Models\Status;
use App\Models\RelatedTo;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;

class CourseAssignmentCascadeController extends Controller
{
    public function programBatches(Program $program): JsonResponse
    {
        $items = Batch::query()
            ->where('program_id', $program->getKey())
            ->orderBy('batch_name')
            ->get(['id', 'batch_name', 'batch_code'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'label' => $b->batch_name.($b->batch_code ? ' ('.$b->batch_code.')' : ''),
            ]);

        return response()->json(['items' => $items]);
    }

    public function programSemesters(Program $program): JsonResponse
    {
        $items = Semester::query()
            ->where('program_id', $program->getKey())
            ->orderBy('semester_order')
            ->orderBy('semester_name')
            ->get(['id', 'semester_name'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'label' => $s->semester_name,
            ]);

        return response()->json(['items' => $items]);
    }

    public function programCourses(Program $program): JsonResponse
    {
        $items = Course::query()
            ->where('program_id', $program->getKey())
            ->orderBy('course_code')
            ->get(['id', 'course_code', 'course_title'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'label' => $c->course_code.' — '.$c->course_title,
            ]);

        return response()->json(['items' => $items]);
    }

    public function batchSections(Batch $batch): JsonResponse
    {
        $items = Section::query()
            ->where('program_id', $batch->program_id)
            ->where('batch_id', $batch->getKey())
            ->orderBy('section_name')
            ->get(['id', 'section_name', 'section_code', 'semester_id'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'label' => $s->section_name.($s->section_code ? ' ('.$s->section_code.')' : ''),
                'semester_id' => $s->semester_id,
            ]);

        return response()->json(['items' => $items]);
    }

    public function semesterCourses(Semester $semester): JsonResponse
    {
        $items = Course::query()
            ->where('semester_id', $semester->getKey())
            ->where('program_id', $semester->program_id)
            ->orderBy('course_code')
            ->get(['id', 'course_code', 'course_title'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'label' => $c->course_code.' — '.$c->course_title,
            ]);

        return response()->json(['items' => $items]);
    }
}

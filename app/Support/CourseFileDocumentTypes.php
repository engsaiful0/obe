<?php

namespace App\Support;

class CourseFileDocumentTypes
{
    public const COURSE_OUTLINE = 'course_outline';

    public const COURSE_SPECIFICATION = 'course_specification';

    public const SYLLABUS = 'syllabus';

    public const QUIZ_QUESTIONS = 'quiz_questions';

    public const ASSIGNMENT_QUESTIONS = 'assignment_questions';

    public const MIDTERM_QUESTIONS = 'midterm_questions';

    public const LAB_QUESTIONS = 'lab_questions';

    public const FINAL_QUESTIONS = 'final_questions';

    public const PROJECT_EVALUATION = 'project_evaluation';

    public const SCRIPT_EXCELLENT = 'script_excellent';

    public const SCRIPT_AVERAGE = 'script_average';

    public const SCRIPT_WEAK = 'script_weak';

    public const ATTENDANCE_SHEET = 'attendance_sheet';

    public const ATTENDANCE_REPORT = 'attendance_report';

    public const LECTURE_NOTES = 'lecture_notes';

    public const SLIDES = 'slides';

    public const LAB_MANUAL = 'lab_manual';

    public const VIDEO_LINK = 'video_link';

    public const REFERENCE_MATERIAL = 'reference_material';

    public const STUDENT_FEEDBACK = 'student_feedback';

    public const COURSE_EVALUATION = 'course_evaluation';

    public const MODERATION_REPORT = 'moderation_report';

    public const COMMITTEE_REPORT = 'committee_report';

    public const MEETING_MINUTES = 'meeting_minutes';

    public const ACCREDITATION_EVIDENCE = 'accreditation_evidence';

    public const EXTERNAL_EXAMINER = 'external_examiner';

    public const OTHER = 'other';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::COURSE_OUTLINE => 'Course Outline',
            self::COURSE_SPECIFICATION => 'Course Specification',
            self::SYLLABUS => 'Syllabus',
            self::QUIZ_QUESTIONS => 'Quiz Questions',
            self::ASSIGNMENT_QUESTIONS => 'Assignment Questions',
            self::MIDTERM_QUESTIONS => 'Midterm Questions',
            self::LAB_QUESTIONS => 'Lab Questions',
            self::FINAL_QUESTIONS => 'Final Questions',
            self::PROJECT_EVALUATION => 'Project Evaluation Form',
            self::SCRIPT_EXCELLENT => 'Excellent Student Script',
            self::SCRIPT_AVERAGE => 'Average Student Script',
            self::SCRIPT_WEAK => 'Weak Student Script',
            self::ATTENDANCE_SHEET => 'Attendance Sheet',
            self::ATTENDANCE_REPORT => 'Attendance Report',
            self::LECTURE_NOTES => 'Lecture Notes',
            self::SLIDES => 'Slides',
            self::LAB_MANUAL => 'Lab Manual',
            self::VIDEO_LINK => 'Video Link',
            self::REFERENCE_MATERIAL => 'Reference Materials',
            self::STUDENT_FEEDBACK => 'Student Feedback Summary',
            self::COURSE_EVALUATION => 'Course Evaluation Summary',
            self::MODERATION_REPORT => 'Moderation Report',
            self::COMMITTEE_REPORT => 'Committee Report',
            self::MEETING_MINUTES => 'Meeting Minutes',
            self::ACCREDITATION_EVIDENCE => 'Accreditation Evidence',
            self::EXTERNAL_EXAMINER => 'External Examiner Report',
            self::OTHER => 'Other Document',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function sections(): array
    {
        return [
            'course_outline' => [self::COURSE_OUTLINE, self::COURSE_SPECIFICATION, self::SYLLABUS],
            'question_papers' => [
                self::QUIZ_QUESTIONS,
                self::ASSIGNMENT_QUESTIONS,
                self::MIDTERM_QUESTIONS,
                self::LAB_QUESTIONS,
                self::FINAL_QUESTIONS,
                self::PROJECT_EVALUATION,
            ],
            'student_scripts' => [self::SCRIPT_EXCELLENT, self::SCRIPT_AVERAGE, self::SCRIPT_WEAK],
            'attendance' => [self::ATTENDANCE_SHEET, self::ATTENDANCE_REPORT],
            'teaching_materials' => [
                self::LECTURE_NOTES,
                self::SLIDES,
                self::LAB_MANUAL,
                self::VIDEO_LINK,
                self::REFERENCE_MATERIAL,
            ],
            'feedback' => [self::STUDENT_FEEDBACK, self::COURSE_EVALUATION],
            'additional_documents' => [
                self::MODERATION_REPORT,
                self::COMMITTEE_REPORT,
                self::MEETING_MINUTES,
                self::ACCREDITATION_EVIDENCE,
                self::EXTERNAL_EXAMINER,
                self::OTHER,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allowedMimes(string $documentType): array
    {
        if ($documentType === self::VIDEO_LINK) {
            return [];
        }

        $pdfDocImage = ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
        $outline = ['pdf', 'doc', 'docx', 'xlsx', 'xls'];
        $papers = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
        $scripts = ['pdf', 'png', 'jpg', 'jpeg'];
        $teaching = ['pdf', 'doc', 'docx', 'pptx', 'ppt', 'zip'];
        $additional = ['pdf', 'doc', 'docx', 'xlsx', 'xls'];

        return match (true) {
            in_array($documentType, self::sections()['course_outline'], true) => $outline,
            in_array($documentType, self::sections()['question_papers'], true) => $papers,
            in_array($documentType, self::sections()['student_scripts'], true) => $scripts,
            in_array($documentType, self::sections()['teaching_materials'], true) => $teaching,
            in_array($documentType, self::sections()['attendance'], true) => ['pdf', 'xlsx', 'xls', 'doc', 'docx'],
            default => $additional,
        };
    }
}

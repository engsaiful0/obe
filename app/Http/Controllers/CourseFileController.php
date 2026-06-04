<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseFileCqiRequest;
use App\Http\Requests\CourseFileDocumentRequest;
use App\Models\CourseAssignment;
use App\Models\CourseFileDocument;
use App\Services\CourseFileService;
use App\Support\CourseFileDocumentTypes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseFileController extends Controller
{
    public function __construct(
        protected CourseFileService $courseFileService,
    ) {}

    public function index(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);

        $canManage = Gate::allows('manage', $courseAssignment);
        $data = $this->courseFileService->buildPageData($courseAssignment, $canManage);

        return view('content.my-courses.course-file.index', array_merge($data, [
            'courseAssignment' => $courseAssignment->load([
                'course', 'program', 'semester', 'academicSession', 'section', 'teacher',
            ]),
        ]));
    }

    public function uploadDocument(
        CourseFileDocumentRequest $request,
        CourseAssignment $courseAssignment
    ): JsonResponse {
        Gate::authorize('manage', $courseAssignment);

        $courseFile = $this->courseFileService->getOrCreate($courseAssignment);
        $type = (string) $request->validated('document_type');

        if ($type === CourseFileDocumentTypes::VIDEO_LINK) {
            $document = $courseFile->documents()->create([
                'document_type' => $type,
                'title' => $request->input('title') ?: 'Video Link',
                'file_name' => 'video-link',
                'file_path' => (string) $request->validated('video_url'),
                'mime_type' => 'text/url',
                'file_size' => 0,
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now(),
            ]);
        } else {
            $file = $request->file('file');
            $path = $file->store(
                'course-files/'.$courseFile->id.'/'.$type,
                'public'
            );

            $document = $courseFile->documents()->create([
                'document_type' => $type,
                'title' => $request->input('title') ?: $file->getClientOriginalName(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => (int) $file->getSize(),
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now(),
            ]);
        }

        $this->courseFileService->recalculateCompletion($courseFile->fresh(), $courseAssignment);

        return response()->json([
            'message' => __('Document uploaded successfully.'),
            'document' => $this->documentPayload($document),
            'completion_percent' => (float) $courseFile->fresh()->completion_percentage,
        ]);
    }

    public function deleteDocument(
        CourseAssignment $courseAssignment,
        CourseFileDocument $document
    ): JsonResponse {
        Gate::authorize('manage', $courseAssignment);

        $courseFile = $this->courseFileService->getOrCreate($courseAssignment);
        abort_if((int) $document->course_file_id !== (int) $courseFile->id, 404);

        if ($document->mime_type !== 'text/url' && $document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->courseFileService->recalculateCompletion($courseFile->fresh(), $courseAssignment);

        return response()->json([
            'message' => __('Document removed.'),
            'completion_percent' => (float) $courseFile->fresh()->completion_percentage,
        ]);
    }

    public function downloadDocument(
        CourseAssignment $courseAssignment,
        CourseFileDocument $document
    ) {
        Gate::authorize('view', $courseAssignment);

        $courseFile = $this->courseFileService->getOrCreate($courseAssignment);
        abort_if((int) $document->course_file_id !== (int) $courseFile->id, 404);

        if ($document->mime_type === 'text/url') {
            return redirect()->away($document->file_path);
        }

        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function previewDocument(
        CourseAssignment $courseAssignment,
        CourseFileDocument $document
    ): View|JsonResponse {
        Gate::authorize('view', $courseAssignment);

        $courseFile = $this->courseFileService->getOrCreate($courseAssignment);
        abort_if((int) $document->course_file_id !== (int) $courseFile->id, 404);

        if ($document->mime_type === 'text/url') {
            return view('content.my-courses.course-file.preview-link', [
                'document' => $document,
                'courseAssignment' => $courseAssignment,
            ]);
        }

        $url = Storage::disk('public')->url($document->file_path);
        $ext = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));

        return view('content.my-courses.course-file.preview', [
            'document' => $document,
            'url' => $url,
            'ext' => $ext,
            'courseAssignment' => $courseAssignment,
        ]);
    }

    public function saveCqi(CourseFileCqiRequest $request, CourseAssignment $courseAssignment): JsonResponse
    {
        Gate::authorize('manage', $courseAssignment);

        $courseFile = $this->courseFileService->getOrCreate($courseAssignment);
        $this->courseFileService->saveCqi($courseFile, $request->validated());
        $this->courseFileService->recalculateCompletion($courseFile->fresh(), $courseAssignment);

        return response()->json([
            'message' => __('CQI saved successfully.'),
            'completion_percent' => (float) $courseFile->fresh()->completion_percentage,
        ]);
    }

    public function exportPdf(CourseAssignment $courseAssignment)
    {
        Gate::authorize('view', $courseAssignment);

        $data = $this->courseFileService->buildPageData($courseAssignment, false);
        $data['courseAssignment'] = $courseAssignment->load([
            'course', 'program', 'semester', 'academicSession', 'section', 'teacher',
        ]);

        $pdf = Pdf::loadView('content.my-courses.course-file.pdf-full', $data)
            ->setPaper('A4', 'portrait');

        $code = $courseAssignment->course?->course_code ?? 'course';

        return $pdf->download('course_file_'.$code.'_'.$courseAssignment->id.'.pdf');
    }

    public function printSection(Request $request, CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);

        $section = (string) $request->query('section', 'info');
        $data = $this->courseFileService->buildPageData($courseAssignment, false);
        $data['courseAssignment'] = $courseAssignment;
        $data['printSection'] = $section;

        return view('content.my-courses.course-file.print', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function documentPayload(CourseFileDocument $document): array
    {
        return [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'title' => $document->title,
            'file_name' => $document->file_name,
            'file_size' => $document->file_size,
            'uploaded_at' => $document->uploaded_at?->format('Y-m-d H:i'),
            'is_link' => $document->mime_type === 'text/url',
        ];
    }
}

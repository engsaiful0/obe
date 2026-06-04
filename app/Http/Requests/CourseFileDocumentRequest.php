<?php

namespace App\Http\Requests;

use App\Support\CourseFileDocumentTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseFileDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = (string) $this->input('document_type', '');

        if ($type === CourseFileDocumentTypes::VIDEO_LINK) {
            return [
                'document_type' => ['required', Rule::in(array_keys(CourseFileDocumentTypes::labels()))],
                'title' => ['nullable', 'string', 'max:255'],
                'video_url' => ['required', 'url', 'max:2000'],
            ];
        }

        $mimes = CourseFileDocumentTypes::allowedMimes($type);
        $mimeRule = $mimes !== []
            ? 'mimes:'.implode(',', $mimes)
            : 'file';

        return [
            'document_type' => ['required', Rule::in(array_keys(CourseFileDocumentTypes::labels()))],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:20480', $mimeRule],
        ];
    }
}

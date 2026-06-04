@php
    use App\Support\CourseFileDocumentTypes;
@endphp
@foreach ($types as $type)
    @php
        $docs = ($documentsByType[$type] ?? collect());
        $label = $documentTypes[$type] ?? $type;
        $isVideo = $type === CourseFileDocumentTypes::VIDEO_LINK;
    @endphp
    <div class="border rounded p-3 mb-3 cf-doc-block" data-document-type="{{ $type }}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">{{ $label }}</h6>
            @if ($canManage)
                <button type="button" class="btn btn-sm btn-primary cf-upload-trigger" data-type="{{ $type }}" data-video="{{ $isVideo ? '1' : '0' }}">
                    {{ $isVideo ? __('Add Link') : __('Upload') }}
                </button>
            @endif
        </div>
        @if ($canManage)
            <div class="cf-dropzone border border-dashed rounded p-3 mb-2 text-center small text-muted d-none" data-drop-type="{{ $type }}">
                {{ __('Drag & drop file here or click Upload') }}
            </div>
            <input type="file" class="d-none cf-file-input" data-type="{{ $type }}" accept="{{ $isVideo ? '' : '.pdf,.doc,.docx,.xlsx,.xls,.png,.jpg,.jpeg,.pptx,.zip' }}">
            <div class="cf-upload-progress d-none mb-2">
                <div class="progress" style="height: 6px;"><div class="progress-bar" style="width:0%"></div></div>
            </div>
        @endif
        <ul class="list-group list-group-flush cf-doc-list">
            @forelse ($docs as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0" data-doc-id="{{ $doc->id }}">
                    <span>
                        {{ $doc->title ?: $doc->file_name }}
                        <small class="text-muted d-block">{{ $doc->uploaded_at?->format('Y-m-d H:i') }}</small>
                    </span>
                    <span class="btn-group btn-group-sm">
                        <a href="{{ route('my-courses.course-file.documents.preview', [$courseAssignment, $doc]) }}" class="btn btn-outline-info" target="_blank">{{ __('Preview') }}</a>
                        <a href="{{ route('my-courses.course-file.documents.download', [$courseAssignment, $doc]) }}" class="btn btn-outline-primary">{{ __('Download') }}</a>
                        @if ($canManage)
                            <button type="button" class="btn btn-outline-danger cf-delete-doc" data-id="{{ $doc->id }}">{{ __('Delete') }}</button>
                        @endif
                    </span>
                </li>
            @empty
                <li class="list-group-item text-muted px-0 cf-empty-msg">{{ __('No file uploaded yet.') }}</li>
            @endforelse
        </ul>
    </div>
@endforeach

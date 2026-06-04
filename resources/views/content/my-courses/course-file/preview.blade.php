<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->title ?? __('Preview') }}</title>
    <style>body{margin:0;font-family:sans-serif}iframe,img{max-width:100%}</style>
</head>
<body class="p-2">
    @if (in_array($ext, ['pdf']))
        <iframe src="{{ $url }}" style="width:100%;height:90vh;border:0"></iframe>
    @elseif (in_array($ext, ['png','jpg','jpeg','gif','webp']))
        <img src="{{ $url }}" alt="">
    @else
        <p>{{ __('Preview not available. ') }}<a href="{{ route('my-courses.course-file.documents.download', [$courseAssignment, $document]) }}">{{ __('Download file') }}</a></p>
    @endif
</body>
</html>

<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>ICHES 2027 — Academic Decision</title></head>
<body style="font-family:Arial,sans-serif;color:#17362e;line-height:1.7">
    <h2>ICHES 2027 | Academic Decision</h2>
    <p>Dear {{ $notice->recipient_name }},</p>
    @if ($notice->event_type === 'corrected')
        <p>An authorized correction has been published for your conference paper. This notice supersedes the previous decision.</p>
    @else
        <p>The academic decision for your conference paper has been published.</p>
    @endif
    <p><strong>Paper ID:</strong> {{ $notice->paper_reference }}<br>
       <strong>Title:</strong> {{ $notice->paper_title }}<br>
       <strong>Published decision:</strong> {{ ucfirst($notice->outcome) }}</p>
    @if (filled($notice->public_comments))
        <p><strong>Reviewer General Comments:</strong></p>
        <p style="white-space:pre-line">{{ $notice->public_comments }}</p>
    @endif
    <p>Please <a href="{{ route('papers.show', $notice->paper->public_id) }}">view your submission in ICHES 2027</a> for the published record.</p>
    <p>Acceptance does not yet constitute an issued Letter of Acceptance, verified payment or permission to upload a Full Paper.</p>
    <p>Best regards,<br>ICHES 2027 Conference Secretariat</p>
</body>
</html>

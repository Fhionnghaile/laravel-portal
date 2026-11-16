<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Requests</title></head>
<body style="font-family:system-ui;max-width:760px;margin:40px auto;">
  <h1>Credential requests</h1>
  @if (session('status'))<p style="color:green">{{ session('status') }}</p>@endif
  <ul>
    @forelse ($requests as $r)
      <li><a href="{{ route('requests.show', $r) }}">{{ $r->candidate_email }}</a>
          — {{ $r->status }} ({{ implode(', ', $r->scopes) }})</li>
    @empty
      <li>No requests yet.</li>
    @endforelse
  </ul>
</body>
</html>

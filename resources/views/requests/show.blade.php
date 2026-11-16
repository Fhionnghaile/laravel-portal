<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Request</title></head>
<body style="font-family:system-ui;max-width:760px;margin:40px auto;">
  <h1>Request for {{ $request->candidate_email }}</h1>
  <p>Status: <strong>{{ $request->status }}</strong></p>
  <p>Scopes: {{ implode(', ', $request->scopes) }}</p>
  <p>Expires: {{ $request->expires_at }}</p>
  @if ($request->result)
    <h2>Verification result</h2>
    <p>Outcome: <strong>{{ $request->result->outcome }}</strong></p>
    @if ($request->result->claims)
      <pre>{{ json_encode($request->result->claims, JSON_PRETTY_PRINT) }}</pre>
    @endif
  @endif
</body>
</html>

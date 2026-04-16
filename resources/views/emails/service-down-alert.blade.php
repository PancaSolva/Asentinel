{{-- ADDED: alerting system email template. --}}
<p>Service/API DOWN alert detected.</p>
<p><strong>Service:</strong> {{ $alert['service_name'] }}</p>
<p><strong>URL:</strong> {{ $alert['service_url'] }}</p>
<p><strong>Detected at:</strong> {{ $alert['timestamp'] }}</p>

@if (! empty($alert['status_code']))
<p><strong>HTTP status code:</strong> {{ $alert['status_code'] }}</p>
@endif

@if (! empty($alert['error_message']))
<p><strong>Error message:</strong> {{ $alert['error_message'] }}</p>
@endif

@if (! empty($alert['failure_duration']))
<p><strong>Failure duration:</strong> {{ $alert['failure_duration'] }}</p>
@endif

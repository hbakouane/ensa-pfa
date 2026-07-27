@component('mail::message')
# Interview Scheduled

Hello {{ $candidate->first_name }},

We are pleased to inform you that an interview has been scheduled for the **{{ $interview->application->job->title }}** position.

## Interview Details

| Detail | Info |
|:-------|:-----|
| **Date** | {{ $interview->scheduled_at->format('l, F j, Y') }} |
| **Time** | {{ $interview->scheduled_at->format('g:i A') }} |
| **Duration** | {{ $interview->duration_minutes }} minutes |
| **Type** | {{ str_replace('_', ' ', ucfirst($interview->type)) }} |
@if($interview->location)
| **Location** | {{ $interview->location }} |
@endif
@if($interview->meeting_url)
| **Meeting Link** | [Join Meeting]({{ $interview->meeting_url }}) |
@endif

@if($interview->notes)
**Additional Notes:**
{{ $interview->notes }}
@endif

@component('mail::button', ['url' => $viewUrl])
View Interview Details
@endcomponent

Please make sure to join on time. If you need to reschedule, please reach out to us as soon as possible.

Regards,<br>
{{ config('app.name') }}
@endcomponent

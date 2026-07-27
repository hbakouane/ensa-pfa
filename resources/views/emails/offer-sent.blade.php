@component('mail::message')
# Congratulations, {{ $candidate->first_name }}!

We are thrilled to extend an offer for the **{{ $offer->application->job->title }}** position at **{{ $offer->company->name ?? config('app.name') }}**.

We were impressed with your qualifications and believe you would be a great addition to our team.

@component('mail::button', ['url' => $responseUrl, 'color' => 'success'])
View Your Offer
@endcomponent

@if($offer->expiry_date)
> **Please note:** This offer will expire on **{{ $offer->expiry_date->format('F j, Y') }}**. Please review and respond before this date.
@endif

If you have any questions about the offer or the role, please do not hesitate to reach out to us.

Regards,<br>
{{ config('app.name') }}
@endcomponent

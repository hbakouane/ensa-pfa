@component('mail::message')
# Offer Approval Requested

Hello {{ $approver->name }},

An offer requires your approval before it can be sent to the candidate.

## Offer Details

| Detail | Info |
|:-------|:-----|
| **Candidate** | {{ $offer->application->candidate->full_name }} |
| **Position** | {{ $offer->application->job->title }} |
| **Salary** | {{ number_format($offer->salary, 2) }} {{ $offer->salary_currency ?? 'USD' }} / {{ $offer->salary_period ?? 'yearly' }} |
@if($offer->start_date)
| **Start Date** | {{ $offer->start_date->format('F j, Y') }} |
@endif
@if($offer->expiry_date)
| **Offer Expires** | {{ $offer->expiry_date->format('F j, Y') }} |
@endif

@component('mail::button', ['url' => $dashboardUrl])
Review Offer
@endcomponent

Please review the offer details and approve or reject it at your earliest convenience.

Regards,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# You've Been Invited!

Hello,

**{{ $invitedBy->name }}** has invited you to join **{{ $company->name }}** on {{ config('app.name') }}.

You have been assigned the **{{ ucfirst(str_replace('_', ' ', $role)) }}** role.

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation
@endcomponent

@if($expiresAt)
> **Please note:** This invitation will expire on **{{ $expiresAt->format('F j, Y \a\t g:i A') }}**. Please accept the invitation before this date.
@endif

If you were not expecting this invitation, you can safely ignore this email.

Regards,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Vous êtes invité(e) !

Bonjour,

**{{ $invitedBy->name }}** vous a invité(e) à rejoindre **{{ $company->name }}** sur {{ config('app.name') }}.

Le rôle **{{ ucfirst(str_replace('_', ' ', $role)) }}** vous a été attribué.

@component('mail::button', ['url' => $acceptUrl])
Accepter l'invitation
@endcomponent

@if($expiresAt)
> **Veuillez noter :** Cette invitation expirera le **{{ $expiresAt->format('d/m/Y \à H:i') }}**. Veuillez accepter l'invitation avant cette date.
@endif

Si vous n'attendiez pas cette invitation, vous pouvez ignorer cet e-mail en toute sécurité.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Félicitations, {{ $candidate->first_name }} !

Nous avons le plaisir de vous proposer une offre pour le poste **{{ $offer->application->job->title }}** chez **{{ $offer->company->name ?? config('app.name') }}**.

Nous avons été impressionnés par vos qualifications et pensons que vous seriez un excellent atout pour notre équipe.

@component('mail::button', ['url' => $responseUrl, 'color' => 'success'])
Voir votre offre
@endcomponent

@if($offer->expiry_date)
> **Veuillez noter :** Cette offre expirera le **{{ $offer->expiry_date->format('d/m/Y') }}**. Veuillez la consulter et y répondre avant cette date.
@endif

Si vous avez des questions concernant l'offre ou le poste, n'hésitez pas à nous contacter.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent

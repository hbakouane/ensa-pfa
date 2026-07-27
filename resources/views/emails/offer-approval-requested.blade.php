@component('mail::message')
# Approbation d'offre demandée

Bonjour {{ $approver->name }},

Une offre nécessite votre approbation avant de pouvoir être envoyée au candidat.

## Détails de l'offre

| Détail | Info |
|:-------|:-----|
| **Candidat** | {{ $offer->application->candidate->full_name }} |
| **Poste** | {{ $offer->application->job->title }} |
| **Salaire** | {{ number_format($offer->salary, 2) }} {{ $offer->salary_currency ?? 'USD' }} / {{ $offer->salary_period ?? 'yearly' }} |
@if($offer->start_date)
| **Date de début** | {{ $offer->start_date->format('d/m/Y') }} |
@endif
@if($offer->expiry_date)
| **Expiration de l'offre** | {{ $offer->expiry_date->format('d/m/Y') }} |
@endif

@component('mail::button', ['url' => $dashboardUrl])
Examiner l'offre
@endcomponent

Veuillez examiner les détails de l'offre et l'approuver ou la rejeter dans les meilleurs délais.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent

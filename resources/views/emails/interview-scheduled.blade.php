@component('mail::message')
# Entretien planifié

Bonjour {{ $candidate->first_name }},

Nous avons le plaisir de vous informer qu'un entretien a été planifié pour le poste **{{ $interview->application->job->title }}**.

## Détails de l'entretien

| Détail | Info |
|:-------|:-----|
| **Date** | {{ $interview->scheduled_at->format('l j F Y') }} |
| **Heure** | {{ $interview->scheduled_at->format('H:i') }} |
| **Durée** | {{ $interview->duration_minutes }} minutes |
| **Type** | {{ str_replace('_', ' ', ucfirst($interview->type)) }} |
@if($interview->location)
| **Lieu** | {{ $interview->location }} |
@endif
@if($interview->meeting_url)
| **Lien de la réunion** | [Rejoindre la réunion]({{ $interview->meeting_url }}) |
@endif

@if($interview->notes)
**Notes supplémentaires :**
{{ $interview->notes }}
@endif

@component('mail::button', ['url' => $viewUrl])
Voir les détails de l'entretien
@endcomponent

Veuillez vous connecter à l'heure prévue. Si vous devez reporter l'entretien, veuillez nous contacter dès que possible.

Cordialement,<br>
{{ config('app.name') }}
@endcomponent

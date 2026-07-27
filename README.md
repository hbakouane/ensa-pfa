# RecruitAI — Plateforme de recrutement propulsée par l'IA

Une application SaaS Laravel de taille moyenne à grande permettant aux entreprises de publier des offres d'emploi, aux candidats de postuler avec leur CV, et à l'IA de gérer l'analyse des CV, la notation des candidats et les suggestions de correspondance. Inclut la planification d'entretiens, la gestion de pipeline Kanban, la génération de lettres d'offre, la collaboration d'équipe, les tableaux de bord analytiques et la facturation via Stripe.

---

## Stack technique

| Couche | Technologie |
|--------|------------|
| **Backend** | Laravel 11+ avec Inertia.js |
| **Frontend** | Vue 3 + Inertia |
| **IA** | API OpenAI (GPT-4o) via Structured Outputs |
| **Authentification** | Multi-tenant (isolation par colonne `company_id`) + Spatie Permissions (mode équipes) |
| **Base de données** | MySQL (SQLite pour le développement local) |
| **Files d'attente** | Redis (3 files : `default`, `ai`, `notifications`) |
| **Temps réel** | Laravel Reverb + Echo |
| **Paiements** | Stripe via Laravel Cashier |
| **Recherche** | Laravel Scout + Meilisearch |
| **PDF** | barryvdh/laravel-dompdf |
| **Stockage** | S3 pour les CV/fichiers |

---

## Décisions d'architecture

1. **Multi-tenancy** : Basé sur une colonne `company_id` + un trait `BelongsToCompany` qui applique un scope global. Les candidats sont globaux (partagés entre entreprises via une table pivot), tout le reste est isolé par entreprise.

2. **Couche de services IA** : Namespace `app/Services/AI/` — `OpenAIClient` (tentatives, limites de débit, journalisation des tokens), `ResumeParser`, `CandidateScorer`, `CandidateSummarizer`, `InterviewQuestionGenerator`. Tous les appels IA sont dispatchés comme des jobs en file d'attente sur la file `ai`.

3. **Nommage des modèles** : Modèle `JobPosting` (et non `Job`) pour éviter la collision avec la classe Job des files d'attente de Laravel, avec `protected $table = 'jobs'`.

4. **Kanban** : Points de terminaison RESTful PATCH, `vuedraggable` côté frontend, UI optimiste avec réconciliation serveur + synchronisation temps réel via Echo.

5. **Pattern Actions** : La logique métier est encapsulée dans des classes Action à responsabilité unique sous `app/Actions/`, gardant les contrôleurs légers.

---

## Installation

```bash
# Cloner et installer
cd RecruitAI
composer install
npm install

# Environnement
cp .env.example .env
php artisan key:generate

# Configurez votre .env :
# - Identifiants de base de données (DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
# - Clé API OpenAI (OPENAI_API_KEY)
# - Clés Stripe (STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET)
# - Meilisearch (MEILISEARCH_HOST, MEILISEARCH_KEY)
# - Redis pour les files d'attente
# - Identifiants S3 pour le stockage de fichiers (AWS_*)

# Base de données
php artisan migrate
php artisan db:seed

# Compiler le frontend
npm run dev

# Démarrer les workers de files d'attente (terminaux séparés)
php artisan queue:work --queue=default
php artisan queue:work --queue=ai
php artisan queue:work --queue=notifications

# Démarrer le serveur WebSocket
php artisan reverb:start

# Démarrer Meilisearch (si vous utilisez la recherche)
# meilisearch --master-key=your_key

# Indexation Scout
php artisan scout:import "App\Models\JobPosting"
php artisan scout:import "App\Models\Candidate"
```

---

## Packages

| Package | Utilité |
|---------|---------|
| `laravel/breeze` | Scaffolding d'authentification (Inertia + Vue 3) |
| `spatie/laravel-permission` | Rôles et permissions avec support d'équipes |
| `laravel/cashier` | Abonnements Stripe |
| `laravel/scout` + `meilisearch/meilisearch-php` | Recherche plein texte |
| `laravel/reverb` | Serveur WebSocket |
| `barryvdh/laravel-dompdf` | Génération de PDF |
| `smalot/pdfparser` | Extraction de texte depuis les CV |
| `league/flysystem-aws-s3-v3` | Stockage S3 |
| `vuedraggable@next` (npm) | Glisser-déposer Kanban |
| `@tiptap/vue-3` (npm) | Éditeur de texte enrichi |
| `chart.js` + `vue-chartjs` (npm) | Graphiques analytiques |
| `pinia` (npm) | Gestion d'état |

---

## Implémentation par phases

### Phase 1 : Fondation

**Objectif** : Scaffolding du projet, authentification, multi-tenancy, structure de la mise en page.

**Ce qui a été construit** :
- Projet Laravel avec Breeze (Vue + Inertia), tous les packages installés
- Fichiers de configuration : `config/ai.php` (paramètres OpenAI, limites de débit, pondérations de notation), `config/recruiting.php` (étapes du pipeline, statuts des offres, types d'emploi, définitions des forfaits, variables de remplacement des offres)
- Migrations : `companies`, `users` modifié (company_id, type, avatar_path), `company_invitations`, `plans`, tables de permissions Spatie
- Modèles : `Company` (avec Billable), `User` (avec HasRoles), `CompanyInvitation`, `Plan`
- Trait `BelongsToCompany` avec scope global `CompanyScope` — fondation de l'isolation des données
- Middleware : `SetCurrentCompany` (définit le contexte du tenant), `EnsureCompanySubscription` (vérifie le statut de facturation)
- Seeders : `RolesAndPermissionsSeeder` (rôles propriétaire, admin, recruteur, responsable du recrutement, intervieweur avec permissions granulaires), `PlanSeeder` (Gratuit, Starter, Pro, Enterprise)
- Authentification : `CompanyRegistrationController` (enregistre une entreprise + utilisateur propriétaire), `InvitationController` (inviter/accepter des membres d'équipe), `RegisterCompanyAction`
- Mises en page : `AppLayout.vue` (barre latérale + navigation supérieure), `Sidebar.vue`, `TopNav.vue`, `PublicLayout.vue`, `FlashMessages.vue`, `Dashboard/Index.vue`

**Vérification** : Enregistrer une entreprise, se connecter, voir le tableau de bord, inviter un membre d'équipe.

---

### Phase 2 : Fonctionnalités principales

**Objectif** : Offres d'emploi, candidats, candidatures, pipeline Kanban, tableau d'offres public.

**Ce qui a été construit** :
- Migrations : `departments`, `locations`, `job_categories`, `jobs` (avec schéma complet : salaire, politique de télétravail, workflow de statut), `job_skills`, `job_templates`, `candidates` (global), `candidate_skills`, `candidate_experiences`, `candidate_educations`, `candidate_company` (pivot), `tags`, `candidate_tag`, `pipeline_stages`, `applications` (avec champs de score IA), `rejection_reasons`, `application_stage_history`
- Modèles pour tout ce qui précède avec relations complètes, casts, scopes
- Modèle `JobPosting` avec `$table = 'jobs'`, recherchable, scopes published/active/draft
- Modèle `Candidate` (global, non isolé par entreprise), recherchable, avec accesseur de nom complet
- Contrôleurs : `JobController` (CRUD + publier/clôturer/archiver), `JobBoardController` (public), `CandidateController` (CRUD), `CandidateTagController`, `ApplicationController`, `PipelineController` (déplacement/réordonnancement Kanban), `PipelineStageController`
- Actions : `CreateJobAction`, `PublishJobAction`, `CreateCandidateAction`, `CreateApplicationAction`, `MoveApplicationStageAction` (enregistre l'historique, déclenche des événements), `RejectApplicationAction`
- Pages Vue : Jobs (Index, Create, Edit, Show), Candidates (Index, Show, Create), Pipeline/Show (tableau Kanban)
- Composants Vue : `KanbanBoard.vue`, `KanbanColumn.vue`, `KanbanCard.vue`, composants UI de base (Button, Modal, DataTable, Badge, Pagination, Input, Select, Textarea)
- Composable : `useKanban.js` — glisser-déposer optimiste avec réconciliation serveur
- Pages publiques : `JobBoard.vue`, `JobDetail.vue`, `ApplicationForm.vue`

**Vérification** : Créer une offre d'emploi, la publier, postuler via le tableau public, voir la candidature sur le Kanban, glisser entre les étapes.

---

### Phase 3 : Intégration IA

**Objectif** : Analyse de CV, notation des candidats, résumés, questions d'entretien.

**Ce qui a été construit** :
- Migration : `ai_usage_logs` (suivi des tokens, coûts, statut par appel IA)
- Service `OpenAIClient` — wrapper HTTP avec logique de tentatives, backoff exponentiel, limitation de débit, journalisation de l'utilisation des tokens, support des Structured Outputs
- Schémas JSON : `ResumeSchema` (extraction structurée de données de CV), `ScoreSchema` (notation 0-100 avec détail)
- `ResumeParser` — extrait le texte d'un PDF via smalot/pdfparser, envoie à OpenAI pour une analyse structurée
- `CandidateScorer` — compare les données du candidat aux exigences du poste, retourne un score pondéré 0-100 avec détail (compétences, expérience, formation, adéquation)
- `CandidateSummarizer` — génère des résumés professionnels de 2-3 paragraphes
- `InterviewQuestionGenerator` — génère des questions sur mesure avec catégorie, difficulté et critères d'évaluation
- Jobs en file d'attente (tous sur la file `ai`) : `ParseResumeJob`, `ScoreCandidateJob`, `BulkScoreCandidatesJob`, `GenerateCandidateSummaryJob`, `GenerateInterviewQuestionsJob`
- `AIController` — points de terminaison pour déclencher manuellement les actions IA
- `ScoreDisplay.vue` — affichage circulaire SVG du score avec code couleur (rouge/jaune/vert)

**Vérification** : Téléverser un CV, vérifier que l'IA l'analyse, consulter le score du candidat, générer un résumé.

---

### Phase 4 : Fonctionnalités avancées

**Objectif** : Entretiens, fiches d'évaluation, offres, collaboration, temps réel.

**Ce qui a été construit** :
- Migrations : `interviews`, `interview_interviewers` (pivot avec suivi des réponses), `interview_scorecards`, `scorecard_criteria`, `offer_templates`, `offers` (avec réponse publique par token), `offer_approvals`, `activities` (journal d'activité polymorphe), `comments` (avec fils de discussion et @mentions), `media` (pièces jointes polymorphes)
- Modèles pour tout ce qui précède avec relations
- Actions : `ScheduleInterviewAction`, `CreateOfferAction`, `SendOfferAction`, `GenerateOfferPdfAction`
- Services : `OfferRenderer` (remplacement de variables), `OfferPdfGenerator` (DomPDF vers S3)
- Contrôleurs : `InterviewController`, `ScorecardController`, `OfferController`, `OfferTemplateController`, `OfferResponseController` (public, par token), `CommentController`, `ActivityController`, `NotificationController`
- Notifications : `InterviewScheduledNotification`, `OfferSentNotification`, `OfferApprovalRequestedNotification`, `MentionedInCommentNotification`
- Événements + Diffusion : `ApplicationStageChanged`, `CommentAdded`, `InterviewScheduled` — diffusés sur les canaux `private-company.{id}`
- Pages Vue : Interviews (Index, Show), Offers (Index, Create, Show, Templates), OfferResponse (public)
- Composants Vue : `SchedulerModal.vue`, `ScorecardForm.vue`, `OfferPreview.vue`, `TemplateEditor.vue`, `CommentThread.vue`, `CommentInput.vue` (avec @mentions), `ActivityFeed.vue`, `NotificationDropdown.vue`
- Composables : `useRealtime.js` (abonnements Echo), `useNotifications.js` (gestion des notifications)

**Vérification** : Planifier un entretien, remplir une fiche d'évaluation, créer et envoyer une offre, vérifier les notifications en temps réel.

---

### Phase 5 : Analytique et facturation

**Objectif** : Tableau de bord analytique, gestion des abonnements Stripe.

**Ce qui a été construit** :
- Migrations : `source_tracking`, colonnes Cashier sur `companies`, `subscriptions`, `subscription_items`
- Services : `AnalyticsService` (statistiques d'ensemble, filtrées par date), `TimeToHireCalculator` (moyenne/médiane/par département/tendances), `PipelineConversionCalculator` (taux de conversion et d'abandon étape par étape)
- Services : `SubscriptionService` (s'abonner, changer de forfait, résilier, reprendre via Cashier), `UsageLimitChecker` (applique les limites du forfait : offres d'emploi, candidats, utilisateurs, analyses IA)
- Middleware : `CheckUsageLimits` — bloque les routes de création lorsque les limites du forfait sont dépassées
- Contrôleurs : `AnalyticsController` (vue d'ensemble, délai d'embauche, conversion du pipeline, sources, performance de l'équipe), `BillingController` (s'abonner, changer de forfait, résilier, reprendre, factures, moyen de paiement)
- Pages Vue : `Analytics/Index.vue`, `Settings/Billing.vue`
- Composants Vue : `MetricCard.vue`, `PipelineFunnel.vue` (entonnoir en CSS), `TimeToHireChart.vue` (graphique linéaire chart.js), `SourceBreakdownChart.vue` (graphique en anneau chart.js), `PricingTable.vue`, `SubscriptionManager.vue`, `UsageIndicator.vue` (barre de progression avec code couleur)

**Vérification** : S'abonner à un forfait payant, vérifier que les limites sont appliquées, consulter le tableau de bord analytique.

---

### Phase 6 : Finition

**Objectif** : Recherche, e-mails, performance, tests.

**Ce qui a été construit** :
- Scout + Meilisearch configurés, `JobPosting` et `Candidate` recherchables
- `GlobalSearch.vue` — palette de commandes Cmd+K avec recherche différée, résultats groupés, navigation au clavier
- `SearchController` — recherche parmi les offres d'emploi et les candidats
- Templates d'e-mails Blade : entretien planifié, offre envoyée, demande d'approbation d'offre, invitation
- Performance : `preventLazyLoading()` et `preventSilentlyDiscardingAttributes()` en développement
- Tests :
  - Unitaires : `ResumeParserTest`, `CandidateScorerTest`, `TimeToHireCalculatorTest`, `UsageLimitCheckerTest`
  - Fonctionnels : `CompanyRegistrationTest`, `JobControllerTest`, `PipelineControllerTest`, `OfferControllerTest`

**Vérification** : Rechercher des candidats/offres d'emploi, vérifier l'envoi des e-mails, lancer la suite complète de tests avec `php artisan test`.

---

## Fichiers clés

| Fichier | Pourquoi il est important |
|---------|--------------------------|
| `app/Models/Concerns/BelongsToCompany.php` | Fondation du multi-tenancy — isolation des données |
| `app/Models/Scopes/CompanyScope.php` | Scope global qui filtre toutes les requêtes par company_id |
| `app/Http/Middleware/SetCurrentCompany.php` | Définit le contexte du tenant pour chaque requête |
| `app/Services/AI/OpenAIClient.php` | Toutes les fonctionnalités IA dépendent de ce wrapper |
| `app/Services/AI/ResumeParser.php` | Fonctionnalité IA principale — extraction structurée de CV |
| `app/Services/AI/CandidateScorer.php` | Moteur de notation IA pour la correspondance candidat-poste |
| `app/Actions/Applications/MoveApplicationStageAction.php` | Logique principale du pipeline — historique, événements |
| `resources/js/Components/Pipeline/KanbanBoard.vue` | Interface principale — glisser-déposer avec synchronisation temps réel |
| `app/Services/Billing/UsageLimitChecker.php` | Applique les limites du forfait dans tout le système |
| `config/recruiting.php` | Configuration centrale du pipeline, forfaits, types d'emploi |

---

## Rôles et permissions

| Rôle | Permissions clés |
|------|-----------------|
| **Propriétaire** | Accès complet incluant la gestion de la facturation |
| **Admin** | Tout sauf la facturation |
| **Recruteur** | Offres d'emploi, candidats, candidatures, entretiens, offres, fonctionnalités IA |
| **Responsable du recrutement** | Voir les offres/candidats, déplacer les étapes du pipeline, entretiens, approuver les offres |
| **Intervieweur** | Voir les candidats/candidatures, soumettre des fiches d'évaluation |

---

## API / Groupes de routes

| Préfixe | Utilité |
|---------|---------|
| `/` | Page d'accueil publique |
| `/careers/{company:slug}` | Tableau d'offres public par entreprise |
| `/offers/respond/{token}` | Réponse publique à une offre (par token) |
| `/dashboard` | Tableau de bord principal |
| `/jobs` | Gestion des offres d'emploi (CRUD) |
| `/candidates` | Gestion des candidats |
| `/pipeline/{job}` | Vue pipeline Kanban |
| `/interviews` | Planification des entretiens |
| `/offers` | Gestion des offres |
| `/analytics` | Tableau de bord analytique |
| `/billing` | Gestion des abonnements |
| `/ai/*` | Points de terminaison IA |
| `/search` | Recherche globale |
| `/notifications` | Gestion des notifications |

---

## Configuration des files d'attente

```
default        — jobs standards (e-mails, traitement général)
ai             — jobs intensifs en IA (analyse de CV, notation, résumé)
notifications  — envoi de notifications
```

Démarrer les workers :
```bash
php artisan queue:work --queue=default,notifications
php artisan queue:work --queue=ai --timeout=120
```

---

## Tests

```bash
# Lancer tous les tests
php artisan test

# Lancer des suites spécifiques
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Lancer avec couverture de code
php artisan test --coverage
```

---

## Licence

Propriétaire. Tous droits réservés.

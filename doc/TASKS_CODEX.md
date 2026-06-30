# Tâches pour Codex

## Règles générales

Codex doit travailler par petites étapes cohérentes.

Chaque tâche doit produire du code propre, testé et documenté.

Ne jamais modifier le cœur Dolibarr.

Ne jamais introduire de dépendance lourde sans justification.

## Tâche 1 - Initialiser le module

Créer la structure suivante :

```text
htdocs/custom/choruspro/
```

Créer :

- `core/modules/modChorusPro.class.php`
- `admin/setup.php`
- `langs/fr_FR/choruspro.lang`
- `langs/en_US/choruspro.lang`
- `sql/llx_choruspro_submission.sql`
- `sql/llx_choruspro_log.sql`
- `README.md`

Critère d'acceptation :

- le module apparaît dans Dolibarr ;
- il peut être activé ;
- la page de configuration est accessible.

## Tâche 2 - Ajouter les permissions

Créer les permissions :

- lire les statuts ;
- envoyer les factures ;
- administrer le module ;
- consulter les logs.

Critère d'acceptation :

- les permissions apparaissent dans Dolibarr ;
- les pages sensibles vérifient les droits.

## Tâche 3 - Créer la configuration

Créer les constantes Dolibarr :

```text
CHORUSPRO_ENV
CHORUSPRO_CLIENT_ID
CHORUSPRO_CLIENT_SECRET
CHORUSPRO_TECHNICAL_ACCOUNT
CHORUSPRO_TECHNICAL_PASSWORD
CHORUSPRO_EMITTER_SIRET
CHORUSPRO_DEBUG
CHORUSPRO_LOG_LEVEL
```

Critère d'acceptation :

- les valeurs sont sauvegardées ;
- les secrets ne sont pas réaffichés en clair.

## Tâche 4 - Créer les classes de persistance

Créer :

```text
class/chorusprosubmission.class.php
class/chorusprolog.class.php
```

Critère d'acceptation :

- création d'un enregistrement d'envoi ;
- mise à jour de statut ;
- ajout d'un log.

## Tâche 5 - Créer le client API

Créer :

```text
class/chorusproclient.class.php
```

Méthodes minimales :

```php
authenticate()
callApi()
searchStructureBySiret()
getServicesByStructure()
uploadInvoicePdf()
submitInvoice()
getInvoiceStatus()
```

Critère d'acceptation :

- aucun appel HTTP direct hors de cette classe ;
- les erreurs HTTP sont transformées en erreurs exploitables ;
- les secrets ne sont pas logués.

## Tâche 6 - Créer le service métier

Créer :

```text
class/chorusproinvoiceservice.class.php
```

Méthodes minimales :

```php
validateInvoice()
preparePayload()
submitInvoice()
syncInvoiceStatus()
```

Critère d'acceptation :

- une facture incomplète est refusée proprement ;
- une facture déjà envoyée est détectée ;
- le service ne contient pas de HTML.

## Tâche 7 - Ajouter le bouton facture

Ajouter un hook sur les factures.

Afficher le bouton uniquement si :

- facture validée ;
- utilisateur autorisé ;
- facture non déjà envoyée.

Critère d'acceptation :

- le bouton apparaît au bon moment ;
- le bouton n'apparaît pas sur les brouillons.

## Tâche 8 - Envoi sandbox

Relier le bouton au service métier.

Critère d'acceptation :

- appel API réalisé en sandbox ;
- retour enregistré ;
- message utilisateur clair ;
- logs créés.

## Tâche 9 - Synchronisation cron

Créer :

```text
scripts/choruspro_sync.php
```

Critère d'acceptation :

- le script met à jour les statuts ;
- il est idempotent ;
- il journalise les erreurs sans bloquer tout le traitement.

## Tâche 10 - Documentation et tests

Créer ou compléter :

- README ;
- ARCHITECTURE ;
- SECURITY ;
- TESTING ;
- CHANGELOG ;
- exemples de configuration.

Critère d'acceptation :

- un développeur peut installer le module ;
- un administrateur peut configurer l'accès Chorus Pro ;
- les tests peuvent tourner sans serveur Chorus réel.

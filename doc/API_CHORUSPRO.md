# Intégration API Chorus Pro

## Objectif

Centraliser ici toutes les hypothèses et règles liées aux API Chorus Pro.

Avant développement effectif, vérifier la documentation officielle la plus récente de l'AIFE, de PISTE et de Chorus Pro.

## Environnements

Le module doit supporter :

- sandbox / qualification ;
- production.

La configuration doit permettre de basculer d'un environnement à l'autre sans modifier le code.

## Authentification

Le client API doit gérer :

- OAuth 2.0 côté PISTE ;
- compte technique Chorus Pro ;
- header ou mécanisme d'identification du compte Chorus Pro selon documentation officielle ;
- expiration du token ;
- renouvellement automatique.

## Configuration attendue

Constantes Dolibarr proposées :

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

## Endpoints à encapsuler

Le nom exact des endpoints doit être confirmé dans la documentation officielle.

Le client doit prévoir des méthodes de haut niveau :

```php
authenticate(): string
searchStructureBySiret(string $siret): array
getServicesByStructure(string $structureId): array
uploadInvoicePdf(string $filePath): array
submitInvoice(array $payload): array
getInvoiceStatus(string $chorusInvoiceId): array
```

## Gestion des réponses

Ne pas se limiter au code HTTP.

Certaines API peuvent retourner une réponse HTTP correcte avec une erreur métier dans le corps de réponse.

Toujours contrôler :

- code HTTP ;
- code retour métier ;
- message retour ;
- identifiants retournés ;
- statut fonctionnel.

## Exceptions

Créer des exceptions dédiées si utile :

```php
ChorusProAuthenticationException
ChorusProApiException
ChorusProBusinessException
ChorusProConfigurationException
```

## Règle importante

Toute la logique HTTP doit rester dans `ChorusProClient`.

Aucune page Dolibarr, aucun hook et aucun script cron ne doit appeler directement cURL ou un client HTTP.

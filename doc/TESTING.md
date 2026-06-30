# Tests

## Objectif

Garantir que le module reste fiable sans dépendre d'appels réels aux API Chorus Pro dans les tests automatisés.

## Types de tests

### Tests unitaires

À prévoir pour :

- validation des factures ;
- construction du payload ;
- mapping des statuts ;
- gestion des erreurs ;
- masquage des secrets dans les logs.

### Tests d'intégration simulés

Utiliser des mocks pour :

- OAuth ;
- recherche structure ;
- upload PDF ;
- dépôt facture ;
- récupération statut.

### Tests manuels sandbox

À documenter séparément.

Scénario minimal :

1. configurer le module en sandbox ;
2. créer un tiers public test ;
3. créer une facture ;
4. valider la facture ;
5. générer le PDF ;
6. envoyer vers Chorus Pro ;
7. vérifier l'identifiant retourné ;
8. synchroniser le statut.

## Données de test

Ne jamais versionner :

- identifiants PISTE réels ;
- tokens ;
- mots de passe ;
- factures réelles ;
- données personnelles.

Créer plutôt :

```text
.env.example
fixtures/
```

avec des valeurs fictives.

## Critères minimum avant release

- activation module OK ;
- configuration OK ;
- permissions OK ;
- validation facture OK ;
- prévention double envoi OK ;
- logs sans secret OK ;
- cron idempotent OK.

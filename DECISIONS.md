# Architecture Decisions

Ce document recense les décisions d'architecture importantes du projet.

L'objectif est d'expliquer **pourquoi** certains choix ont été faits afin de conserver une architecture cohérente au fil des évolutions.

Chaque nouvelle décision importante devra être ajoutée à ce document.

---

# AD-001

## Le projet est un module Dolibarr externe

**Statut**

Accepté

### Décision

Le projet sera développé exclusivement comme un module externe installé dans :

```
htdocs/custom/choruspro/
```

Aucune modification du cœur de Dolibarr n'est autorisée.

### Raisons

- faciliter les mises à jour Dolibarr ;
- permettre une installation simple ;
- conserver la compatibilité avec les futures versions ;
- faciliter une éventuelle diffusion officielle.

### Conséquences

Toutes les intégrations passent par :

- hooks
- triggers
- menus
- permissions
- cron
- extrafields
- constantes

---

# AD-002

## Toute communication HTTP est centralisée

**Statut**

Accepté

### Décision

Toutes les communications avec Chorus Pro seront réalisées exclusivement dans :

```
ChorusProClient
```

### Raisons

- faciliter les tests ;
- isoler les appels API ;
- simplifier le débogage ;
- faciliter les futures évolutions de l'API.

### Conséquences

Aucun autre composant du projet ne doit effectuer d'appel HTTP.

---

# AD-003

## Séparation stricte des responsabilités

**Statut**

Accepté

### Décision

Le projet est organisé selon plusieurs couches.

```
Interface utilisateur

↓

Service métier

↓

Client API

↓

Persistance

↓

Base de données
```

### Raisons

Limiter le couplage.

Faciliter les tests.

Simplifier les évolutions.

---

# AD-004

## Une classe = une responsabilité

**Statut**

Accepté

### Décision

Chaque classe possède une responsabilité unique.

Exemples :

```
ChorusProClient
```

Communication API.

```
ChorusProInvoiceService
```

Logique métier.

```
ChorusProSubmission
```

Persistance.

### Raisons

Code plus simple.

Classes plus courtes.

Maintenance facilitée.

---

# AD-005

## Les secrets ne sont jamais journalisés

**Statut**

Accepté

### Décision

Les éléments suivants ne doivent jamais apparaître dans les logs :

- Client Secret
- OAuth Token
- mot de passe
- Authorization Header
- cookies
- données sensibles

### Raisons

Respect des bonnes pratiques de sécurité.

---

# AD-006

## Les erreurs métier sont distinctes des erreurs techniques

**Statut**

Accepté

### Décision

Les erreurs seront séparées en deux catégories.

Erreurs utilisateur :

- SIRET manquant
- facture invalide
- code service absent

Erreurs techniques :

- OAuth
- HTTP
- timeout
- API indisponible

### Raisons

Fournir un message compréhensible à l'utilisateur tout en conservant les détails techniques dans les journaux.

---

# AD-007

## Les appels Chorus Pro doivent être testables

**Statut**

Accepté

### Décision

Toutes les communications réseau devront pouvoir être simulées.

### Raisons

Les tests automatisés ne doivent jamais dépendre des serveurs Chorus Pro.

---

# AD-008

## Développement incrémental

**Statut**

Accepté

### Décision

Le projet sera développé par petites fonctionnalités indépendantes.

Ordre :

1. structure du module
2. configuration
3. client API
4. bouton facture
5. envoi
6. synchronisation
7. optimisation

### Raisons

Limiter les régressions.

Faciliter les revues.

Permettre des versions utilisables rapidement.

---

# AD-009

## Compatibilité maximale avec Dolibarr

**Statut**

Accepté

### Décision

Avant toute nouvelle implémentation, rechercher si Dolibarr propose déjà un mécanisme équivalent.

### Ordre de préférence

1. API native Dolibarr

2. Classe native Dolibarr

3. Hook officiel

4. Trigger officiel

5. Nouvelle implémentation

### Raisons

Réduire la maintenance.

Conserver la compatibilité.

---

# AD-010

## Dépendances minimales

**Statut**

Accepté

### Décision

Le projet doit utiliser le moins de dépendances externes possible.

Avant d'ajouter Composer :

- vérifier PHP natif ;
- vérifier Dolibarr ;
- vérifier les extensions PHP.

### Raisons

Installation plus simple.

Maintenance plus faible.

Surface d'attaque réduite.

---

# AD-011

## Priorité au MVP

**Statut**

Accepté

### Décision

Le premier objectif est un module fonctionnel permettant :

- configuration ;
- authentification ;
- envoi d'une facture PDF ;
- suivi du statut.

Les fonctionnalités avancées (Factur-X, PDP, e-reporting, etc.) seront traitées dans des versions ultérieures.

### Raisons

Livrer rapidement un module utile.

Recueillir des retours utilisateurs avant d'élargir le périmètre.

---

# AD-012

## Compatibilité avec la réforme de la facturation électronique

**Statut**

Accepté

### Décision

L'architecture doit rester suffisamment modulaire pour intégrer ultérieurement :

- Factur-X ;
- PDP (Plateforme de Dématérialisation Partenaire) ;
- PPF si son périmètre évolue ;
- e-reporting ;
- nouveaux formats ou nouvelles API.

Le module ne doit pas être conçu comme un simple connecteur "PDF vers Chorus Pro", mais comme une base extensible.

### Raisons

Anticiper les évolutions réglementaires sans remettre en cause l'architecture existante.

---

# Règle d'évolution

Toute décision modifiant significativement l'architecture du projet doit être documentée ici avant son implémentation.

Les décisions ne doivent jamais être supprimées.

Si une décision est abandonnée :

- conserver son historique ;
- changer son statut en :

```
Superseded
```

ou

```
Deprecated
```

en expliquant la nouvelle orientation.

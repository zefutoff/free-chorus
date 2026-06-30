# Coding Standards

## Objectif

Maintenir un code homogène, simple à comprendre et facilement maintenable.

---

# PHP

Version cible :

PHP 8.2+

Toujours utiliser :

- strict_types
- type hints
- return types
- propriétés typées

---

# Style

Respecter :

PSR-12

Indentation :

4 espaces

Pas de tabulations.

---

# Longueur des méthodes

Objectif :

20 à 40 lignes maximum.

Si une méthode dépasse 60 lignes :

réfléchir à l'extraire.

---

# Longueur des classes

Objectif :

moins de 400 lignes.

Si une classe devient trop grosse :

la découper.

---

# Nommage

Classes :

```
PascalCase
```

Exemple :

```
ChorusProClient
```

Méthodes :

```
camelCase
```

Variables :

```
camelCase
```

Constantes :

```
UPPER_SNAKE_CASE
```

---

# Responsabilité unique

Une classe = une responsabilité.

Exemple :

✓ ChorusProClient

✓ ChorusProInvoiceService

✗ ChorusProEverything

---

# Couplage

Limiter le couplage.

Privilégier l'injection de dépendances.

Éviter les variables globales.

---

# Exceptions

Préférer :

```
throw new Exception(...)
```

plutôt que :

```
return false;
```

Les exceptions doivent être :

- explicites
- documentées

---

# SQL

Ne jamais concaténer des valeurs utilisateur.

Toujours utiliser les mécanismes sécurisés proposés par Dolibarr.

---

# HTML

Éviter le HTML dans les classes métier.

Le HTML reste dans les pages.

---

# API

Toutes les requêtes HTTP passent uniquement par :

```
ChorusProClient
```

Aucune exception.

---

# Logs

Trois niveaux :

INFO

WARNING

ERROR

Les secrets sont toujours masqués.

---

# Commentaires

Expliquer :

Pourquoi.

Pas :

Ce que fait le code.

Mauvais :

```php
// Incrémente i
$i++;
```

Bon :

```php
// Chorus Pro peut renvoyer plusieurs statuts intermédiaires.
// Ils sont regroupés ici pour simplifier l'affichage utilisateur.
```

---

# PHPDoc

Toutes les méthodes publiques.

Toutes les classes.

Toutes les interfaces.

---

# Magic numbers

Interdits.

Créer des constantes.

---

# Duplication

Si du code est copié une troisième fois :

Créer une méthode dédiée.

---

# Tests

Toute logique métier doit être testable.

Les appels HTTP doivent être mockables.

---

# Commits

Un commit = une fonctionnalité.

Messages de commit :

```
feat:
fix:
docs:
test:
refactor:
chore:
```

---

# Architecture

Respecter les couches :

Interface utilisateur

↓

Service métier

↓

Client API

↓

Persistance

↓

Base de données

Ne jamais contourner cette architecture.

---

# Qualité

Avant toute validation :

- code compilable ;
- aucune erreur PHP ;
- documentation à jour ;
- aucune donnée sensible ;
- respect des standards du projet.

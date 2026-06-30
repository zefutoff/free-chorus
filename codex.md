# CODEX.md

## Mission

Tu participes au développement d'un module open source Dolibarr permettant l'envoi des factures vers Chorus Pro via les API officielles de l'AIFE.

L'objectif n'est pas uniquement de produire du code fonctionnel, mais un module de qualité professionnelle pouvant devenir une référence de l'écosystème Dolibarr.

---

# Avant chaque tâche

Avant toute modification :

1. Lire le README.md
2. Lire les documents du dossier `doc/`
3. Vérifier l'architecture existante
4. Identifier les conventions déjà utilisées
5. Vérifier si Dolibarr fournit déjà une solution native

Ne jamais commencer à coder sans comprendre la tâche.

---

# Philosophie

Privilégier :

- simplicité
- lisibilité
- robustesse
- maintenabilité
- compatibilité avec les futures versions de Dolibarr

Éviter les solutions "rapides" qui compliqueraient la maintenance.

---

# Principe fondamental

Toujours privilégier la réutilisation du code existant.

Avant d'écrire une nouvelle classe ou une nouvelle méthode :

- rechercher si Dolibarr propose déjà cette fonctionnalité ;
- rechercher si une classe native permet de faire le travail ;
- rechercher si un hook officiel existe.

Ne jamais réinventer une fonctionnalité déjà disponible.

---

# Modification du cœur Dolibarr

Interdiction absolue.

Le projet doit rester un module externe.

Aucun fichier du cœur Dolibarr ne doit être modifié.

Toute intégration doit passer par :

- hooks
- triggers
- modules
- menus
- permissions
- extrafields
- constantes
- cron

---

# Développement

Toujours développer une seule fonctionnalité à la fois.

Ne jamais réaliser plusieurs fonctionnalités dans la même tâche.

Ne jamais modifier des fichiers non concernés.

Limiter les changements au strict nécessaire.

---

# Avant de créer une classe

Vérifier :

- qu'une classe équivalente n'existe pas déjà ;
- que sa responsabilité est unique ;
- qu'elle respecte l'architecture du projet.

---

# API Chorus Pro

Toute communication HTTP doit être centralisée dans :

```
ChorusProClient
```

Aucune autre classe ne doit effectuer de requête HTTP.

---

# Base de données

Toute persistance doit passer par les classes dédiées.

Éviter les requêtes SQL dispersées dans le projet.

---

# Journalisation

Toujours produire des logs utiles.

Ne jamais enregistrer :

- Client Secret
- OAuth Token
- Mot de passe
- données sensibles

---

# Gestion des erreurs

Les erreurs doivent être :

- explicites
- contextualisées
- journalisées

Ne jamais masquer une exception sans justification.

---

# Documentation

Toute nouvelle classe publique doit être documentée.

Toute méthode publique doit posséder un PHPDoc.

Tout comportement complexe doit être expliqué.

---

# Pull Requests

Chaque PR doit rester petite.

Objectif :

moins de 500 lignes modifiées lorsque c'est possible.

---

# Refactoring

Ne jamais effectuer de refactoring global pendant une fonctionnalité.

Si un refactoring est nécessaire :

Créer une tâche dédiée.

---

# Dépendances

Éviter toute nouvelle dépendance.

Avant d'ajouter une bibliothèque Composer :

- vérifier si PHP natif suffit ;
- vérifier si Dolibarr possède déjà un équivalent.

---

# Sécurité

Toujours considérer :

- l'injection SQL
- le XSS
- le CSRF
- la validation des entrées
- la protection des secrets

---

# Compatibilité

Le module doit fonctionner sur les versions stables récentes de Dolibarr.

Éviter toute API expérimentale.

---

# Tests

Toute fonctionnalité importante doit être testable.

Les appels Chorus Pro doivent pouvoir être simulés.

Les tests ne doivent jamais nécessiter des identifiants réels.

---

# Si un doute existe

Ne pas inventer.

Documenter le doute.

Proposer plusieurs solutions.

Expliquer les avantages et inconvénients.

Attendre une validation avant de poursuivre.

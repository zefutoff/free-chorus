# Module Dolibarr Chorus Pro

## Objectif

Ce projet vise à développer un module externe Dolibarr permettant d'envoyer gratuitement les factures Dolibarr vers Chorus Pro via les API officielles.

Le module doit rester indépendant du cœur Dolibarr et être publiable en open source.

## Périmètre initial

Le MVP doit permettre :

- la configuration des accès API Chorus Pro ;
- l'ajout d'un bouton sur les factures validées ;
- le contrôle des champs obligatoires ;
- l'envoi du PDF de facture vers Chorus Pro ;
- l'enregistrement des identifiants de dépôt ;
- la consultation du statut ;
- la synchronisation automatique des statuts via cron.

## Installation cible

Le module doit être placé dans :

```text
htdocs/custom/choruspro/
```

## Contraintes principales

- Ne jamais modifier le cœur de Dolibarr.
- Utiliser les hooks, permissions, menus, cron et constantes Dolibarr.
- Ne jamais journaliser les secrets.
- Séparer affichage, métier, API et persistance.
- Garder un code lisible, documenté et maintenable.

## Licence

Licence recommandée : GPL-3.0-or-later, compatible avec l'écosystème Dolibarr.

## Documentation

Voir les fichiers suivants :

- `PROJECT_BRIEF.md`
- `ARCHITECTURE.md`
- `API_CHORUSPRO.md`
- `DATA_MODEL.md`
- `SECURITY.md`
- `ROADMAP.md`
- `TASKS_CODEX.md`
- `TESTING.md`
- `CONTRIBUTING.md`

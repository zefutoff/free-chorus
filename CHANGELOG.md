# Changelog

Toutes les modifications notables du projet seront documentées dans ce fichier.

Le format suit l'esprit de Keep a Changelog et ce fichier racine est la référence du projet.

## [Unreleased]

### Added

- Initialisation du changelog de référence à la racine du dépôt.
- Ajout du squelette initial du module Dolibarr Chorus Pro.
- Ajout des permissions et constantes de configuration Chorus Pro.
- Ajout des classes internes de base du module Chorus Pro.
- Implémentation de l’authentification OAuth 2.0 PISTE avec cache mémoire du jeton.
- Ajout du centre de diagnostic technique du module Chorus Pro.
- Amélioration du diagnostic OAuth PISTE avec contexte sûr et erreurs HTTP détaillées.
- Ajout du code HTTP, du scope et du body d’erreur nettoyé au diagnostic OAuth PISTE.
- Ajout de la recherche Structures par SIRET dans le client API et dans le centre de diagnostic.
- Ajout des diagnostics sur les headers `Authorization` et `cpro-account` pour l’appel Structures.
- Ajout d’un export texte et JSON du rapport de diagnostic avec masquage des données sensibles.
- Ajout de l’outillage qualité Composer, PHP-CS-Fixer, PHPStan et GitHub Actions.
- Ajout du diagnostic de récupération des codes services d’une structure Chorus Pro.
- Ajout de la préparation locale des factures Dolibarr pour Chorus Pro sans envoi ni upload.

# Roadmap

## v0.1 - Socle module

Objectif : module installable et configurable.

- Structure du module.
- Déclaration Dolibarr.
- Activation/désactivation.
- Page de configuration.
- Permissions.
- Tables SQL.
- Traductions FR/EN.

## v0.2 - Client API

Objectif : préparer l'intégration Chorus Pro.

- Classe `ChorusProClient`.
- Gestion OAuth.
- Gestion environnement sandbox/production.
- Gestion erreurs HTTP.
- Logs API.
- Tests unitaires avec mocks.

## v0.3 - Intégration facture

Objectif : connecter le module aux factures Dolibarr.

- Hook sur fiche facture.
- Bouton "Envoyer vers Chorus Pro".
- Vérification facture validée.
- Récupération du PDF.
- Contrôle SIRET, code service, engagement.

## v0.4 - Envoi Chorus Pro

Objectif : envoyer une facture en sandbox.

- Recherche structure destinataire.
- Vérification code service.
- Upload PDF.
- Création/dépôt facture.
- Sauvegarde identifiants Chorus.
- Affichage résultat.

## v0.5 - Synchronisation

Objectif : suivre les statuts.

- Script cron.
- Récupération des statuts.
- Mise à jour locale.
- Historique des changements.

## v0.6 - Robustesse

Objectif : rendre le module fiable.

- Gestion des erreurs avancée.
- Prévention double envoi.
- Amélioration logs.
- Documentation installation.
- Tests.

## v1.0 - Première version stable

Objectif : version utilisable publiquement.

- README complet.
- Guide configuration PISTE/Chorus.
- Captures d'écran.
- CI GitHub.
- Licence.
- Changelog.

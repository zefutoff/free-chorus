# Project Brief - Module Dolibarr Chorus Pro

## Contexte

Dolibarr permet de gérer les factures clients, mais l'envoi direct vers Chorus Pro n'est pas disponible nativement dans une solution libre et gratuite suffisamment simple à utiliser.

Le but du projet est de créer un module externe permettant aux utilisateurs Dolibarr d'envoyer leurs factures vers Chorus Pro sans dépendre d'un module propriétaire ou payant.

## But final

Créer un module open source qui devienne une base fiable pour l'intégration Chorus Pro dans Dolibarr.

## Utilisateurs ciblés

- collectivités ;
- associations ;
- entreprises facturant des organismes publics ;
- intégrateurs Dolibarr ;
- utilisateurs avancés de Dolibarr.

## Fonctionnalités attendues

### MVP

- Page de configuration du module.
- Stockage des paramètres API.
- Bouton d'envoi sur les factures validées.
- Vérification des prérequis avant envoi.
- Génération ou récupération du PDF de facture.
- Envoi vers Chorus Pro.
- Enregistrement des identifiants retournés.
- Affichage du statut local.
- Journalisation technique.
- Script cron de synchronisation.

### Hors périmètre initial

- Factur-X complet.
- PDP / PPF.
- E-reporting.
- Gestion avancée de tout le cycle de vie Chorus Pro.
- Support multi-structure complexe.
- Modification du cœur Dolibarr.

## Critères de réussite

Le module est considéré comme utilisable lorsque :

1. il s'installe comme module externe ;
2. il peut être activé/désactivé depuis Dolibarr ;
3. il permet de configurer sandbox et production ;
4. il ajoute un bouton sur une facture validée ;
5. il refuse proprement les factures incomplètes ;
6. il transmet une facture test en sandbox ;
7. il sauvegarde les identifiants Chorus Pro ;
8. il récupère le statut ;
9. il documente clairement l'installation et la configuration.

## Principes de développement

- Simplicité avant exhaustivité.
- Code explicite plutôt que magique.
- Séparation stricte des responsabilités.
- Erreurs compréhensibles pour l'utilisateur.
- Logs utiles pour l'administrateur.
- Aucun secret dans les logs.

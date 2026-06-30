# Contribuer

## Philosophie

Ce module doit rester libre, gratuit, compréhensible et maintenable.

Les contributions doivent respecter l'architecture existante et éviter les ajouts trop spécifiques.

## Règles de contribution

- Ne pas modifier le cœur Dolibarr.
- Respecter les conventions de nommage Dolibarr.
- Documenter les nouvelles classes.
- Ajouter des tests lorsque c'est pertinent.
- Ne jamais commiter de secrets.
- Garder les PR petites et relisibles.

## Branches

Branches recommandées :

```text
main
develop
feature/nom-court
fix/nom-court
```

## Pull requests

Chaque PR doit contenir :

- objectif ;
- résumé technique ;
- limites connues ;
- tests réalisés ;
- captures si interface modifiée.

## Style PHP

Recommandations :

- code lisible ;
- méthodes courtes ;
- PHPDoc sur méthodes publiques ;
- exceptions explicites ;
- pas de logique HTML dans les services ;
- pas d'appel HTTP hors du client API.

## Issues

Utiliser les labels :

```text
bug
enhancement
documentation
security
chorus-api
dolibarr
good-first-issue
```

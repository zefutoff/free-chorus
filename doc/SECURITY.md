# Sécurité

## Secrets

Ne jamais afficher ni journaliser :

- Client Secret ;
- token OAuth ;
- mot de passe du compte technique ;
- payload complet contenant des informations sensibles, sauf mode debug explicitement activé.

## Stockage

Les secrets sont stockés dans les constantes Dolibarr.

Si une solution standard Dolibarr existe pour masquer ou chiffrer les valeurs sensibles, l'utiliser.

Dans la page de configuration :

- ne jamais réafficher un secret en clair ;
- afficher uniquement une indication du type `********` ;
- permettre de remplacer la valeur.

## Permissions

Créer au minimum :

```text
choruspro->read
choruspro->send
choruspro->admin
choruspro->logs
```

Règles :

- seul `admin` peut configurer le module ;
- seul `send` peut envoyer une facture ;
- seul `logs` peut consulter les logs techniques ;
- `read` peut consulter les statuts.

## Logs

Les logs doivent être utiles mais sobres.

En mode normal, stocker :

- endpoint ;
- code HTTP ;
- code métier ;
- message ;
- hash du payload si utile.

En mode debug, stocker davantage d'informations, mais toujours masquer les secrets.

## Erreurs utilisateur

Les erreurs affichées à l'utilisateur doivent être compréhensibles.

Les détails techniques doivent rester dans les logs.

## Idempotence

Le module doit empêcher les doubles envois involontaires.

Avant envoi :

- vérifier si la facture a déjà un identifiant Chorus ;
- vérifier si un envoi existe déjà ;
- demander une confirmation explicite pour un renvoi si cette fonctionnalité est ajoutée.

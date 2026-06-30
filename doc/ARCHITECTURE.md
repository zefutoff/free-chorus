# Architecture du module

## Emplacement

```text
htdocs/custom/choruspro/
```

## Structure cible

```text
choruspro/
├── admin/
│   └── setup.php
├── class/
│   ├── chorusproclient.class.php
│   ├── chorusproinvoiceservice.class.php
│   ├── chorusprosubmission.class.php
│   └── chorusprolog.class.php
├── core/
│   ├── modules/
│   │   └── modChorusPro.class.php
│   └── triggers/
├── langs/
│   ├── fr_FR/
│   │   └── choruspro.lang
│   └── en_US/
│       └── choruspro.lang
├── lib/
│   └── choruspro.lib.php
├── scripts/
│   └── choruspro_sync.php
├── sql/
│   ├── llx_choruspro_submission.sql
│   └── llx_choruspro_log.sql
├── tests/
├── README.md
└── LICENSE
```

## Couches logicielles

### 1. Module Dolibarr

Fichier principal :

```text
core/modules/modChorusPro.class.php
```

Responsabilités :

- déclarer le module ;
- déclarer les permissions ;
- déclarer les constantes ;
- déclarer les menus ;
- déclarer les hooks ;
- déclarer les dépendances éventuelles.

### 2. Interface utilisateur

Responsabilités :

- page de configuration ;
- boutons dans les fiches factures ;
- pages de consultation des logs ;
- messages utilisateur.

L'interface ne doit pas contenir de logique API.

### 3. Service métier

Classe principale :

```php
ChorusProInvoiceService
```

Responsabilités :

- contrôler la facture ;
- préparer les données ;
- coordonner l'appel API ;
- enregistrer les résultats ;
- gérer les erreurs métier.

### 4. Client API

Classe principale :

```php
ChorusProClient
```

Responsabilités :

- authentification OAuth ;
- gestion du token ;
- appel des endpoints Chorus Pro ;
- gestion HTTP ;
- décodage des réponses ;
- conversion des erreurs API en exceptions ou résultats exploitables.

### 5. Persistance

Classes :

```php
ChorusProSubmission
ChorusProLog
```

Responsabilités :

- stocker les envois ;
- stocker les identifiants Chorus ;
- stocker les statuts ;
- stocker les logs ;
- fournir des méthodes de lecture propres.

## Flux d'envoi

```text
Facture Dolibarr validée
        ↓
Utilisateur clique "Envoyer vers Chorus Pro"
        ↓
ChorusProInvoiceService::validateInvoice()
        ↓
Génération/récupération du PDF
        ↓
ChorusProClient::authenticate()
        ↓
Recherche structure destinataire
        ↓
Vérification code service / engagement
        ↓
Upload ou dépôt facture
        ↓
Réponse Chorus Pro
        ↓
Sauvegarde llx_choruspro_submission
        ↓
Journalisation llx_choruspro_log
        ↓
Message utilisateur
```

## Flux de synchronisation

```text
Cron Dolibarr
        ↓
Recherche des factures envoyées non finales
        ↓
Appel API de statut
        ↓
Mise à jour llx_choruspro_submission
        ↓
Journalisation du changement
```

## Règles de conception

- Pas de SQL dans les pages d'affichage, sauf cas très simple et justifié.
- Pas d'appel HTTP direct hors `ChorusProClient`.
- Pas de logique d'affichage dans `ChorusProClient`.
- Pas de secret dans les logs.
- Pas d'état global non maîtrisé.

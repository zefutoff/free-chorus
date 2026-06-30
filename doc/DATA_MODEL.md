# Modèle de données

## Table `llx_choruspro_submission`

Stocke un envoi de facture vers Chorus Pro.

Champs recommandés :

```sql
rowid integer primary key
entity integer not null
fk_facture integer not null
fk_soc integer not null
siret_destinataire varchar(20)
code_service varchar(128)
engagement_juridique varchar(128)
chorus_invoice_id varchar(128)
chorus_deposit_id varchar(128)
status varchar(64)
status_label varchar(255)
environment varchar(32)
last_sync datetime
datec datetime
tms timestamp
fk_user_author integer
fk_user_modif integer
import_key varchar(14)
```

## Table `llx_choruspro_log`

Stocke les événements techniques et fonctionnels.

```sql
rowid integer primary key
entity integer not null
fk_submission integer
level varchar(16)
direction varchar(16)
endpoint varchar(255)
http_code integer
business_code varchar(64)
message text
payload_hash varchar(128)
datec datetime
fk_user integer
```

## Champs extrafields recommandés

Sur les tiers :

```text
choruspro_siret
choruspro_code_service_default
choruspro_engagement_required
choruspro_engagement_default
choruspro_structure_id
```

Sur les factures :

```text
choruspro_code_service
choruspro_engagement_juridique
```

## Statuts locaux

Statuts minimaux :

```text
draft
ready
sent
processing
accepted
rejected
suspended
paid
error
```

## Règles

- Une facture ne doit pas être envoyée deux fois sans confirmation explicite.
- Un envoi doit être lié à une facture Dolibarr.
- Un envoi doit être lié à une entité Dolibarr pour compatibilité multi-company.
- Les changements de statut doivent être historisés.

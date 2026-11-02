# FreeChorus — connecteur Chorus Pro pour Dolibarr

> [!IMPORTANT]
> **Développement arrêté le 2 novembre 2026.** Ce dépôt est conservé à titre de référence historique et technique. Aucune maintenance, assistance ou publication n'est prévue. **Le module est incomplet : il ne permet pas de déposer une facture sur Chorus Pro et ne doit pas être utilisé en production.**

## Présentation du projet

FreeChorus avait pour objectif de fournir un module externe, libre et gratuit pour [Dolibarr](https://www.dolibarr.org/), facilitant l'envoi des factures destinées au secteur public français vers [Chorus Pro](https://chorus-pro.gouv.fr/) au moyen des API officielles, sans modification du cœur de Dolibarr.

Le périmètre envisagé comprenait la configuration des accès PISTE/Chorus Pro, la vérification des données de facturation, le dépôt des factures, la conservation des identifiants de transmission et la synchronisation des statuts.

## Pourquoi le développement s'arrête

Cette décision, prise le **2 novembre 2026**, est un choix d'orientation et de maintenance ; elle ne résulte **ni de la fermeture de Chorus Pro ni d'une interdiction générale de ses API**.

1. **Évolution du circuit de facturation électronique.** Dans le cadre de la réforme française déployée à partir de septembre 2026, les fournisseurs du secteur public peuvent transmettre leurs factures au moyen d'une **plateforme agréée raccordée à Chorus Pro**. Cette voie constitue le circuit cible décrit par la DGFiP et permet d'éviter un dépôt manuel distinct sur le portail Chorus Pro pour les opérations prises en charge. Le dépôt direct sur Chorus Pro (portail, API, EDI) reste possible pendant la phase transitoire ; l'administration présente cette modalité comme provisoire, avec un horizon de généralisation fixé au 1er septembre 2027.
2. **Évolution de l'écosystème Dolibarr.** La communauté Dolibarr propose désormais un [module EInvoicing (Générique FR | Officiel)](https://www.dolistore.com/product.php?id=3309&l=fr), destiné à l'émission et à la réception des factures électroniques avec certaines plateformes agréées. Ce module est encore en évolution, mais il constitue une base communautaire à privilégier plutôt que de multiplier les connecteurs parallèles.
3. **Pérennité et maîtrise du périmètre.** Achever puis maintenir un connecteur direct spécialisé impliquerait des travaux importants (dépôt, suivi, erreurs, sécurité, compatibilité Dolibarr et API) alors que le besoin général évolue vers l'interopérabilité avec les plateformes agréées. Les éventuels besoins particuliers de la facturation publique mériteraient d'abord d'être comparés aux fonctionnalités déjà disponibles.

**Précision :** Chorus Pro conserve son rôle dans la facturation du secteur public. Certains flux spécifiques, notamment liés aux marchés de travaux et à d'autres procédures propres à Chorus Pro, nécessitent un traitement particulier. Le présent arrêt **ne préjuge pas de la date de disponibilité future des API**.

## État technique à l'arrêt

Travaux réalisés à titre expérimental :

- squelette d'un module externe Dolibarr, configuration, permissions, traductions et champs supplémentaires ;
- authentification OAuth 2.0 via PISTE et outils de diagnostic ;
- consultation des structures Chorus Pro et de leurs codes services ;
- vérification et préparation *locale* des factures, sans transmission ;
- classes et tables SQL préparatoires pour les soumissions et les journaux.

Fonctionnalités **non finalisées ou non implémentées** :

- dépôt ou envoi effectif d'une facture ou de son PDF sur Chorus Pro ;
- persistance complète des soumissions, prévention fiable des doubles envois et gestion des erreurs de bout en bout ;
- récupération et synchronisation des statuts ;
- tests d'intégration sur un parcours complet et version stable distribuable.

Les dernières exécutions CI connues avant l'arrêt sont en échec. Les documents de conception et de feuille de route du dossier [`doc/`](doc/) décrivent **des intentions historiques**, et non des fonctionnalités livrées. Aucune garantie de compatibilité ou de sécurité ne peut être donnée.

## Réutilisation et pistes futures

Le dépôt reste consultable afin de conserver les travaux exploratoires sur les API PISTE/Chorus Pro. Toute reprise nécessiterait une nouvelle validation du cadre réglementaire, de la documentation API, de la compatibilité Dolibarr/PHP, de la sécurité et des tests.

Pour un nouveau besoin d'intégration, il est recommandé d'évaluer d'abord les solutions existantes dans l'écosystème Dolibarr et, lorsque pertinent, d'y contribuer. Ce dépôt n'accepte plus de demandes d'évolution ni de support.

## Sources de référence (consultées lors de la décision)

- [DGFiP — Chorus Pro reste la plateforme de référence du secteur public](https://www.impots.gouv.fr/actualite/chorus-pro-restera-la-plateforme-de-reference-pour-la-facturation-electronique-du-secteur)
- [DGFiP — Facturation électronique : opérations à destination des entités publiques (fiche)](https://www.impots.gouv.fr/sites/default/files/media/1_metier/2_professionnel/EV/2_gestion/290_facturation_electronique/fiches_reforme/fiche-entites_publiques.pdf)
- [Documentation Dolibarr — EInvoicing (Generic, FR, ...)](https://wiki.dolibarr.org/index.php/Module_EInvoicing_%28Generic%2C_FR%2C_...%29)

## Licence

Le projet indique `GPL-3.0-or-later` dans `composer.json` et dans les en-têtes de ses sources. Le fichier [`LICENSE.md`](LICENSE.md) contient actuellement une note d'intention plutôt que le texte intégral de la licence. Toute redistribution ou reprise doit vérifier les mentions de licence et les droits applicables.

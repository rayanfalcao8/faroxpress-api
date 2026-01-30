# FaroXpress — Vision produit (v0.1)

## Problème
Envoyer de l’argent CAD → XAF (Canada → Cameroun) est souvent opaque (frais/taux), stressant (suivi faible), et lent (support / exceptions).

## Utilisateurs
- Client (Canada): initie un transfert, paie, suit le statut.
- Bénéficiaire (Cameroun): reçoit via Mobile Money.
- Opérateur: exécute, valide, gère échecs/retours.
- Admin: configure taux/frais, plafonds, providers, supervise.

## Proposition de valeur
- Transparence: frais + taux + montant reçu avant validation.
- Suivi: statuts simples + notifications.
- Exécution: providers (1 provider au départ, extensible).

## MVP (phase 1)
- Devise/pays: CAD → XAF uniquement
- Réception: Mobile Money (choix MVP: ORANGE_MONEY)
- Paiement Canada: placeholder (simulé) au début

## Statuts
CREATED → PENDING_PAYMENT → PAID → PROCESSING → COMPLETED | FAILED | CANCELLED

## Contraintes non négociables
- Plafonds par jour/semaine
- Audit log (qui a fait quoi)
- Idempotence (paiement/provider)
- Sécurité: validation, rate limiting, tokens

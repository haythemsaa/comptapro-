# 🚀 Guide Utilisateur ComptaPro

Bienvenue sur ComptaPro, votre solution SaaS de comptabilité professionnelle !

## 📋 Table des matières

1. [Démarrage rapide](#-démarrage-rapide)
2. [Fonctionnalités principales](#-fonctionnalités-principales)
3. [Raccourcis clavier](#-raccourcis-clavier)
4. [Gestion des clients](#-gestion-des-clients)
5. [Facturation](#-facturation)
6. [Rapports et analyses](#-rapports-et-analyses)
7. [Personnalisation](#-personnalisation)
8. [Export de données](#-export-de-données)
9. [Astuces et conseils](#-astuces-et-conseils)

---

## 🎯 Démarrage rapide

### Première connexion

1. **Accédez** à l'application via votre navigateur
2. **Connectez-vous** avec vos identifiants
3. **Complétez** votre profil d'entreprise
4. **Suivez** l'assistant de configuration pour configurer votre plan comptable

### Navigation principale

L'application est organisée en modules accessibles via la barre latérale :

- **📊 Dashboard** : Vue d'ensemble de votre activité
- **👥 Clients** : Gestion de votre portefeuille clients
- **💰 Ventes** : Factures, devis et produits
- **🛒 Achats** : Fournisseurs et factures d'achat
- **📖 Comptabilité** : Plan comptable, journaux et écritures
- **📈 Rapports** : Analyses et rapports financiers
- **🏢 Sociétés** : Gestion multi-sociétés

---

## ✨ Fonctionnalités principales

### 1. **Recherche globale** 🔍

Accédez rapidement à n'importe quelle ressource :

- **Raccourci** : `Ctrl + K`
- **Ou** : Cliquez sur l'icône 🔍 dans l'en-tête
- **Recherchez** : Clients, factures, produits, etc.
- **Naviguez** : Utilisez les flèches ↑↓ et Entrée

### 2. **Actions rapides** ⚡

Le bouton flottant en bas à droite vous donne un accès rapide à :

- ➕ Nouvelle facture
- 👤 Nouveau client
- 📦 Nouveau produit
- 📊 Rapports
- 💾 Export
- 🔍 Recherche

### 3. **Mode sombre** 🌙

Protégez vos yeux avec le mode sombre :

- **Cliquez** sur l'icône 🌙/☀️ dans l'en-tête
- **3 modes disponibles** :
  - ☀️ Clair
  - 🌙 Sombre
  - 🔄 Automatique (selon votre système)
- **Sauvegarde automatique** de votre préférence

### 4. **Filtres avancés** 🎛️

Filtrez vos données efficacement :

- **Recherche textuelle** : Nom, numéro, référence
- **Filtres par statut** : Payé, en attente, en retard
- **Filtres par type** : Devis, facture, avoir
- **Plages de dates** : Période personnalisée
- **Montants** : Min/Max
- **Sauvegarde des filtres** : Vos filtres sont mémorisés

### 5. **Graphiques interactifs** 📊

Visualisez vos données avec Chart.js :

- **Évolution du CA** : Graphique linéaire sur 12 mois
- **Répartition** : Graphiques en anneau
- **Comparaisons** : Graphiques à barres
- **Export** : Téléchargez les graphiques en PNG
- **Interactif** : Survolez pour voir les détails

### 6. **Drag & Drop** 🖱️

Réorganisez vos listes facilement :

- **Cliquez et maintenez** sur l'icône de poignée
- **Déplacez** l'élément
- **Relâchez** pour valider
- **Feedback visuel** pendant le déplacement

---

## ⌨️ Raccourcis clavier

### Navigation

| Raccourci | Action |
|-----------|--------|
| `Ctrl + Shift + D` | Aller au Dashboard |
| `Ctrl + Shift + C` | Aller aux Clients |
| `Ctrl + Shift + F` | Aller aux Factures |
| `Ctrl + Shift + P` | Aller aux Produits |
| `Ctrl + Shift + R` | Aller aux Rapports |
| `Alt + Backspace` | Page précédente |

### Création rapide

| Raccourci | Action |
|-----------|--------|
| `Ctrl + Alt + N` | Nouvelle facture |
| `Ctrl + Alt + C` | Nouveau client |

### Actions

| Raccourci | Action |
|-----------|--------|
| `Ctrl + K` | Recherche globale |
| `Ctrl + R` | Rafraîchir |
| `?` | Aide raccourcis |
| `Esc` | Fermer modal |

### Navigation dans les listes

| Raccourci | Action |
|-----------|--------|
| `J` | Élément suivant |
| `K` | Élément précédent |

---

## 👥 Gestion des clients

### Créer un client

1. **Cliquez** sur "Nouveau Client" (ou `Ctrl + Alt + C`)
2. **Remplissez** les informations :
   - Nom et prénom / Raison sociale
   - Email et téléphone
   - Adresse complète
   - Informations fiscales (SIREN, TVA)
3. **Enregistrez**

### Rechercher un client

- **Barre de recherche** : Tapez le nom, email ou numéro
- **Filtres** : Actif/Inactif
- **Tri** : Nom, date de création, CA

### Fiche client

Accédez à la vue détaillée :

- 📊 **Statistiques** : CA total, nombre de factures
- 📄 **Factures** : Historique complet
- 📝 **Notes** : Informations importantes
- 📧 **Communications** : Historique des emails
- ✏️ **Modifier** : Mettre à jour les informations

---

## 💰 Facturation

### Types de documents

- **Devis** : Proposition commerciale
- **Facture** : Document de vente
- **Avoir** : Note de crédit

### Créer une facture

1. **Cliquez** sur "Nouvelle Facture" (ou `Ctrl + Alt + N`)
2. **Sélectionnez** :
   - Type de document
   - Client (créez-le si nécessaire)
   - Date et échéance
3. **Ajoutez** des lignes :
   - Produits du catalogue
   - Ou saisie libre
   - Quantité et prix
4. **Configurez** :
   - Taux de TVA
   - Remises
   - Conditions de paiement
5. **Enregistrez** ou **Envoyez**

### Statuts de facture

- 🟦 **Brouillon** : En cours de rédaction
- 🟡 **En attente** : Envoyée, non payée
- 🟢 **Payée** : Réglée
- 🔴 **En retard** : Échéance dépassée
- ⚪ **Annulée** : Annulée

### Actions disponibles

- 📧 **Envoyer par email** : PDF automatique
- 📥 **Télécharger PDF** : Pour impression
- 💳 **Enregistrer un paiement**
- 🔄 **Dupliquer** : Créer une copie
- ✏️ **Modifier** : Tant que brouillon
- 🗑️ **Supprimer** : Si non envoyée

---

## 📈 Rapports et analyses

### Dashboard

Le tableau de bord affiche :

- **KPIs** : CA, factures, clients
- **Graphiques interactifs** :
  - Évolution mensuelle du CA
  - Répartition des types de factures
  - Comparaison année N vs N-1
- **Factures récentes**
- **Alertes** : Retards de paiement

### Rapports disponibles

#### Compte de résultat
- Produits et charges
- Résultat net
- Export PDF/Excel

#### Rapport de TVA
- TVA collectée
- TVA déductible
- TVA à payer/récupérer
- Déclaration CA3

#### Bilan comptable
- Actif et passif
- Situation patrimoniale
- Export PDF

#### Grand livre
- Mouvements par compte
- Soldes
- Export CSV/Excel

#### Balance
- Soldes de tous les comptes
- Débit/Crédit
- Export Excel

### Export des rapports

1. **Ouvrez** le rapport souhaité
2. **Cliquez** sur "Exporter"
3. **Choisissez** le format :
   - 📄 PDF : Pour impression
   - 📊 Excel : Pour analyse
   - 📋 CSV : Pour import
   - 🖨️ Impression directe

---

## 🎨 Personnalisation

### Paramètres d'affichage

- **Thème** : Clair / Sombre / Auto
- **Langue** : Français (par défaut)
- **Format de date** : JJ/MM/AAAA
- **Devise** : EUR (€)

### Paramètres de l'entreprise

Dans **Sociétés > Ma Société** :

- Logo et coordonnées
- Informations légales (SIRET, TVA)
- Coordonnées bancaires (RIB)
- Mentions légales personnalisées
- Modèles de documents

### Personnalisation des factures

- **Logo** : Votre identité visuelle
- **Couleurs** : Thème personnalisé
- **Mentions** : CGV, pénalités de retard
- **Pied de page** : Informations supplémentaires

---

## 💾 Export de données

### Formats disponibles

#### CSV (Comma-Separated Values)
- **Usage** : Import dans Excel, LibreOffice
- **Avantage** : Universel, léger
- **Encodage** : UTF-8 avec BOM

#### Excel (XLS)
- **Usage** : Analyse avancée
- **Avantage** : Formatage préservé
- **Compatible** : Excel 2007+

#### JSON
- **Usage** : Développeurs, API
- **Avantage** : Structure complète
- **Format** : Pretty-printed

### Export de listes

1. **Affichez** la liste (clients, factures, etc.)
2. **Appliquez** vos filtres si nécessaire
3. **Cliquez** sur le bouton "Export"
4. **Sélectionnez** le format
5. **Téléchargement** automatique

### Export de graphiques

Sur les graphiques interactifs :

- 🖼️ **PNG** : Image haute qualité
- 📊 **JPEG** : Image compressée
- 📋 **Copier** : Vers presse-papier
- 🖨️ **Imprimer** : Impression directe

### Copier vers le presse-papier

Pour coller dans Excel :

1. **Export** > **Copier**
2. **Ouvrez** Excel
3. **Ctrl + V** pour coller
4. **Formatage** automatique

---

## 💡 Astuces et conseils

### Productivité

1. **Maîtrisez les raccourcis** : Appuyez sur `?` pour les voir
2. **Utilisez la recherche globale** : `Ctrl + K` pour tout trouver
3. **Sauvegardez vos filtres** : Ils sont mémorisés automatiquement
4. **Dupliquez** vos factures récurrentes : Gain de temps
5. **Créez des modèles** : Pour les documents répétitifs

### Organisation

1. **Numérotez** vos factures de façon cohérente
2. **Utilisez des références** produit claires
3. **Catégorisez** vos clients (tags)
4. **Archivez** les anciennes factures
5. **Exportez régulièrement** vos données

### Sécurité

1. **Mot de passe fort** : 12+ caractères
2. **Déconnexion** après usage
3. **Sauvegardez** vos données (exports)
4. **Vérifiez** les accès utilisateurs
5. **Mettez à jour** vos informations

### Performance

1. **Filtres** : Pour limiter les résultats
2. **Pagination** : Naviguez page par page
3. **Recherche** : Plus rapide que le scroll
4. **Cache navigateur** : Actualiser si problème
5. **Navigation** : Utilisez les raccourcis

---

## 🆘 Support et assistance

### Aide en ligne

- **Centre d'aide** : Documentation complète
- **FAQ** : Questions fréquentes
- **Tutoriels vidéo** : Pas à pas
- **Blog** : Actualités et astuces

### Contactez-nous

- **📧 Email** : support@comptapro.fr
- **💬 Chat** : En bas à droite (horaires ouvrés)
- **📞 Téléphone** : +33 1 23 45 67 89
- **🎫 Tickets** : Espace client

### Communauté

- **Forum** : Échangez avec d'autres utilisateurs
- **Groupe Facebook** : Actualités et entraide
- **Newsletter** : Nouveautés mensuelles

---

## 📱 Accessibilité mobile

ComptaPro est **responsive** et fonctionne sur :

- 💻 Desktop (recommandé)
- 📱 Tablettes (iPad, Android)
- 📱 Smartphones (consultation)

**Note** : Pour une expérience optimale, nous recommandons l'utilisation sur desktop pour la création de documents.

---

## 🔄 Mises à jour

### Version actuelle : **1.0.0**

#### Nouveautés v1.0.0

✨ **Nouvelles fonctionnalités**
- Recherche globale intelligente
- Raccourcis clavier complets
- Mode sombre avec mode auto
- Graphiques interactifs (Chart.js)
- Export avancé (CSV, Excel, JSON)
- Actions rapides (FAB)
- Filtres avancés avec sauvegarde
- Drag & Drop pour réorganisation
- Animations fluides
- Composants UI modernisés

🎨 **Design**
- Interface repensée
- Animations améliorées
- Dark mode complet
- Responsive optimisé

⚡ **Performance**
- Chargement optimisé
- Cache intelligent
- Recherche instantanée

### Feuille de route

🚀 **Prochaines fonctionnalités**
- Paiements en ligne
- Tableau de bord personnalisable
- API publique
- Application mobile native
- Synchronisation bancaire
- IA pour suggestions

---

## 📖 Glossaire

- **CA** : Chiffre d'Affaires
- **TVA** : Taxe sur la Valeur Ajoutée
- **HT** : Hors Taxes
- **TTC** : Toutes Taxes Comprises
- **SIRET** : Système d'Identification du Répertoire des Établissements
- **RIB** : Relevé d'Identité Bancaire
- **CGV** : Conditions Générales de Vente
- **KPI** : Key Performance Indicator (indicateur de performance)
- **FAB** : Floating Action Button (bouton d'action flottant)

---

## 📄 Mentions légales

ComptaPro SaaS © 2025

**Éditeur** : ComptaPro SaaS
**Hébergeur** : [À compléter]
**Contact** : contact@comptapro.fr

---

**Merci d'utiliser ComptaPro !** 🎉

Pour toute question ou suggestion, n'hésitez pas à nous contacter.

*Dernière mise à jour : 19/11/2025*

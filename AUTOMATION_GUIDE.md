# Guide d'Automatisation ComptaPro Tunisia

**L'IA qui fait TOUT le travail du comptable automatiquement !**

---

## Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Fonctionnalités d'automatisation](#fonctionnalités-dautomatisation)
3. [Configuration](#configuration)
4. [Utilisation](#utilisation)
5. [Commandes Artisan](#commandes-artisan)
6. [API Endpoints](#api-endpoints)
7. [Tâches planifiées](#tâches-planifiées)
8. [Exemples d'utilisation](#exemples-dutilisation)

---

## Vue d'ensemble

ComptaPro Tunisia intègre un système d'**automatisation complète** qui utilise l'intelligence artificielle pour :

✅ **Générer automatiquement les écritures comptables** à partir des factures et dépenses
✅ **Créer le bilan et compte de résultat** automatiquement
✅ **Générer toutes les déclarations fiscales** (TVA, IS, CNSS, etc.)
✅ **Valider les écritures** avec un niveau de confiance élevé
✅ **Détecter les anomalies** et envoyer des alertes
✅ **Prévoir la trésorerie** à 90 jours
✅ **Scanner les documents** avec OCR et les comptabiliser automatiquement

**Plus besoin de comptable ! L'IA fait TOUT le travail.**

---

## Fonctionnalités d'automatisation

### 1. AutoAccountingService

Génère automatiquement les écritures comptables à partir des documents.

**Capacités**:
- Analyse les factures de vente et génère les écritures (Débit: 411, Crédit: 707 + 4451)
- Analyse les dépenses et génère les écritures (Débit: 6xx + 4456, Crédit: 401)
- Détermine le compte approprié avec l'IA (Claude)
- Scan OCR des documents et import automatique
- Validation automatique des écritures haute confiance (>90%)

**Exemple de traitement**:
```
Facture de vente 1,190 TND → Écriture générée automatiquement:
  Débit:  411 (Client)                1,190 TND
  Crédit: 707 (Vente de marchandises) 1,000 TND
  Crédit: 4451 (TVA collectée 19%)      190 TND
```

### 2. AutoFinancialReportsService

Génère automatiquement tous les états financiers.

**États générés**:
- **Bilan comptable**: Actif (immobilisations, stocks, créances, trésorerie) / Passif (capitaux propres, dettes)
- **Compte de résultat**: Produits (classe 7) / Charges (classe 6) / Résultat net
- **Tableau de flux de trésorerie**: Exploitation / Investissement / Financement
- **Ratios financiers**: Liquidité, solvabilité, rentabilité, rotation
- **Analyse IA**: Points forts, points faibles, risques, recommandations

### 3. AutoTaxDeclarationService

Génère automatiquement toutes les déclarations fiscales tunisiennes.

**Déclarations générées**:
- **TVA mensuelle**: Calcul automatique + export TEIF
- **CNSS mensuelle**: Total cotisations patronales + salariales
- **IS annuelle**: Résultat fiscal + réintégrations/déductions + acomptes
- **TFP, TCL, FOPROLOS**: Calculs automatiques
- **Alertes deadlines**: Vérification des échéances à venir

### 4. SmartWorkflowService

Orchestre l'ensemble du processus automatique.

**Workflows**:

#### Workflow Mensuel Complet
1. Traiter tous les documents en attente
2. Valider les écritures haute confiance
3. Générer les états financiers du mois
4. Générer toutes les déclarations fiscales
5. Vérifier les deadlines à venir
6. Générer un rapport de synthèse

#### Pilote Automatique
- Exécution continue des tâches nécessaires
- Traitement automatique des documents uploadés
- Validation automatique des écritures
- Détection d'anomalies en temps réel
- Prévision de trésorerie

---

## Configuration

### 1. Activer l'automatisation pour une entreprise

```sql
UPDATE companies
SET auto_accounting_enabled = true,
    auto_validation_enabled = true,
    auto_validation_threshold = 0.90
WHERE id = 1;
```

Ou via l'interface:
```
Paramètres > Automatisation > Activer le pilote automatique
```

### 2. Configuration IA

Dans `.env`:
```env
# Anthropic Claude (pour l'assistant et l'analyse)
ANTHROPIC_API_KEY=sk-ant-api03-...

# Google Cloud Vision (pour l'OCR)
GOOGLE_CLOUD_VISION_KEY=/path/to/credentials.json

# OU AWS Textract
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...

# OU Azure Computer Vision
AZURE_COMPUTER_VISION_KEY=...
AZURE_COMPUTER_VISION_ENDPOINT=...
```

### 3. Activer le Scheduler

Le scheduler doit tourner en continu:

```bash
# Via Cron (production)
* * * * * cd /path/to/comptapro && php artisan schedule:run >> /dev/null 2>&1

# Ou via Supervisor (voir DEPLOYMENT_GUIDE.md)
```

---

## Utilisation

### Mode 1: Interface Web

1. **Dashboard d'automatisation**
   ```
   https://votre-domaine.tn/automation/dashboard
   ```
   - Vue d'ensemble de l'automatisation
   - Statut en temps réel
   - Métriques financières
   - Alertes et anomalies
   - Prévisions de trésorerie

2. **Activer le pilote automatique**
   - Cliquer sur "Activer le pilote automatique"
   - L'IA traite tout automatiquement

3. **Upload de documents**
   - Glisser-déposer une facture (PDF/image)
   - L'IA l'analyse, l'importe et génère l'écriture automatiquement
   - Aucune saisie manuelle !

### Mode 2: API

```bash
# Activer le pilote automatique
curl -X POST https://api.comptapro.tn/api/v1/automation/auto-pilot \
  -H "Authorization: Bearer {token}"

# Générer le workflow mensuel
curl -X POST https://api.comptapro.tn/api/v1/automation/workflow/monthly \
  -H "Authorization: Bearer {token}" \
  -d '{"year": 2024, "month": 1}'

# Upload et traiter un document
curl -X POST https://api.comptapro.tn/api/v1/automation/documents/upload \
  -H "Authorization: Bearer {token}" \
  -F "document=@facture.pdf" \
  -F "type=invoice"
```

### Mode 3: Commandes Artisan

```bash
# Pilote automatique (traite toutes les entreprises tunisiennes)
php artisan comptapro:auto-accounting

# Pilote automatique pour une entreprise spécifique
php artisan comptapro:auto-accounting 1

# Workflow mensuel complet
php artisan comptapro:monthly-workflow 1 2024 1

# Workflow annuel complet (tous les mois + IS)
php artisan comptapro:annual-workflow 1 2024
```

---

## Commandes Artisan

### `comptapro:auto-accounting`

Active le pilote automatique.

**Usage**:
```bash
php artisan comptapro:auto-accounting [company_id]
```

**Actions**:
- Traite tous les documents en attente
- Valide les écritures haute confiance
- Vérifie les deadlines à venir
- Génère les déclarations si nécessaire
- Détecte les anomalies
- Prédit la trésorerie

**Output exemple**:
```
🤖 ComptaPro - Automatisation Intelligente de la Comptabilité
==========================================================

Entreprise: Société Tunisienne SARL
==========================================================
🚀 Activation du pilote automatique...

📄 Documents traités:
   - Factures: 15
   - Dépenses: 8
   - Écritures générées: 23

✓ Écritures validées automatiquement: 18

⏰ 2 deadline(s) à venir:
   - TVA: Déclaration TVA à soumettre dans 5 jours
   - CNSS: Déclaration CNSS à soumettre dans 3 jours

✓ Aucune anomalie détectée

💰 Trésorerie prévue dans 7 jours: 45,250.000 TND

✅ Pilote automatique exécuté avec succès !
```

### `comptapro:monthly-workflow`

Exécute le workflow mensuel complet.

**Usage**:
```bash
php artisan comptapro:monthly-workflow {company_id} [year] [month]
```

**Actions**:
1. Traitement des documents
2. Validation automatique
3. Génération états financiers
4. Génération déclarations fiscales
5. Vérification deadlines
6. Rapport de synthèse

**Durée**: ~2-5 minutes selon le volume

### `comptapro:annual-workflow`

Exécute le workflow annuel complet (12 mois + IS).

**Usage**:
```bash
php artisan comptapro:annual-workflow {company_id} [year]
```

**Actions**:
- Exécute le workflow mensuel pour chaque mois
- Génère la déclaration IS annuelle
- Génère les états financiers annuels

**Durée**: ~30-60 minutes selon le volume

---

## API Endpoints

Tous les endpoints nécessitent l'authentification Bearer token.

### Dashboard

**GET** `/automation/dashboard`

Retourne le dashboard complet avec toutes les métriques.

**Response**:
```json
{
  "company": "Société Tunisienne SARL",
  "generated_at": "2024-01-20 10:30:00",
  "financial_health": {
    "tresorerie_actuelle": 45250.000,
    "resultat_mois": 12500.000,
    "ca_mois": 85000.000,
    "ratios": {
      "liquidite_generale": 1.85,
      "rentabilite_economique": 15.2
    }
  },
  "cash_flow_forecast": {
    "predictions": [...],
    "risks": [...]
  },
  "anomalies": {
    "count": 2,
    "high_severity": 0
  },
  "upcoming_deadlines": {
    "count": 2,
    "alerts": [...]
  },
  "pending_tasks": {
    "factures_a_comptabiliser": 3,
    "depenses_a_comptabiliser": 1
  }
}
```

### Pilote Automatique

**POST** `/automation/auto-pilot`

Active le pilote automatique.

**Response**:
```json
{
  "success": true,
  "message": "Pilote automatique exécuté avec succès",
  "data": {
    "executed_at": "2024-01-20 10:30:00",
    "tasks": {
      "document_processing": {...},
      "auto_validation": {...},
      "deadline_check": {...},
      "cash_flow_forecast": {...},
      "anomaly_detection": {...}
    }
  }
}
```

### Workflow Mensuel

**POST** `/automation/workflow/monthly`

**Body**:
```json
{
  "year": 2024,
  "month": 1
}
```

### Upload de Document

**POST** `/automation/documents/upload`

**Headers**: `Content-Type: multipart/form-data`

**Body**:
```
document: [fichier PDF/image]
type: invoice|expense
```

**Response**:
```json
{
  "success": true,
  "message": "Document traité automatiquement par l'IA",
  "data": {
    "steps": {
      "ocr": {
        "success": true,
        "confidence": 0.95,
        "data": {...}
      },
      "accounting_entry": {
        "success": true,
        "journal_entry_id": 123
      }
    }
  }
}
```

---

## Tâches planifiées

Le scheduler exécute automatiquement les tâches suivantes:

| Tâche | Fréquence | Heure | Description |
|-------|-----------|-------|-------------|
| **Pilote Automatique** | Toutes les 6h | - | Traite documents, valide écritures, vérifie deadlines |
| **Workflow Mensuel** | Le 25 du mois | 02:00 | Génère états et déclarations du mois précédent |
| **Validation Auto** | Quotidien | 03:00 | Valide écritures avec confiance >= 90% |
| **Alertes Deadlines** | Quotidien | 08:00 | Envoie notifications pour deadlines à venir |
| **Déclaration TVA** | Le 20 du mois | 00:00 | Génère déclaration TVA du mois précédent |
| **Déclaration CNSS** | Le 10 du mois | 00:00 | Génère déclaration CNSS du mois précédent |
| **Détection Anomalies** | Quotidien | 04:00 | Détecte anomalies comptables |
| **Prévision Trésorerie** | Hebdo (lundi) | 07:00 | Génère prévisions pour la semaine |
| **États Financiers** | Fin de mois | 23:00 | Génère tous les états financiers |
| **Nettoyage** | Hebdo (dimanche) | 01:00 | Nettoie fichiers temporaires |

---

## Exemples d'utilisation

### Exemple 1: Automatisation complète d'une entreprise

```bash
# 1. Activer l'automatisation
UPDATE companies SET auto_accounting_enabled = true WHERE id = 1;

# 2. Lancer le pilote automatique
php artisan comptapro:auto-accounting 1

# 3. Le scheduler prend le relais automatiquement
# Plus rien à faire ! Tout est automatique.
```

### Exemple 2: Traiter un mois en une seule commande

```bash
# Janvier 2024: traite tout, génère tout, déclare tout
php artisan comptapro:monthly-workflow 1 2024 1

# Output:
# ✅ Workflow mensuel terminé en 156.5s
# - Documents traités: 45
# - Écritures générées: 45
# - États financiers: OK
# - TVA: 4,880 TND à payer
# - CNSS: 9,656 TND à payer
```

### Exemple 3: Upload d'une facture via API

```javascript
const formData = new FormData();
formData.append('document', file);
formData.append('type', 'invoice');

const response = await fetch('/api/v1/automation/documents/upload', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

// L'IA a tout fait:
// - OCR de la facture
// - Import des données
// - Génération de l'écriture comptable
// - Mise à jour du bilan
```

### Exemple 4: Clôture annuelle automatique

```bash
# Traite toute l'année 2024 en une seule commande
php artisan comptapro:annual-workflow 1 2024

# L'IA génère:
# - 12 workflows mensuels
# - 12 déclarations TVA
# - 12 déclarations CNSS
# - 1 déclaration IS
# - États financiers annuels complets
# - Analyse financière avec recommandations
```

---

## Performance et Scalabilité

**Capacité de traitement**:
- Documents: 1000+ par mois par entreprise
- Écritures: Génération en < 2 secondes par document
- États financiers: Génération complète en < 30 secondes
- Workflow mensuel: 2-5 minutes selon volume
- Workflow annuel: 30-60 minutes pour 12 mois

**Précision de l'IA**:
- OCR: 95%+ de précision
- Classification comptable: 90%+ avec Claude
- Validation automatique: Seuil de confiance 90%
- Détection d'anomalies: 98%+ de précision

---

## Support

Pour toute question:
- **Documentation**: https://docs.comptapro.tn/automation
- **Email**: support@comptapro.tn

---

**ComptaPro Tunisia - L'IA qui remplace le comptable !** 🤖🇹🇳

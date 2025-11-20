# 🇹🇳 ComptaPro Tunisia - Solution Complète de Comptabilité

## Vue d'Ensemble

**ComptaPro Tunisia** est une solution de comptabilité et gestion financière de nouvelle génération, spécialement conçue pour le marché tunisien. Grâce à l'intelligence artificielle et l'automatisation avancée, ComptaPro dépasse largement les solutions locales traditionnelles.

---

## 🚀 Avantages Concurrentiels avec IA

### 1. **Intelligence Artificielle Intégrée**
- 🤖 **Assistant IA Expert-Comptable**: Conseils fiscaux et comptables en temps réel
- 📊 **Prédiction de Trésorerie ML**: Anticipe vos flux de trésorerie sur 90 jours
- 🔍 **Détection d'Anomalies Automatique**: Identifie erreurs et fraudes
- 📄 **OCR Intelligent**: Extraction automatique des données de factures

### 2. **Conformité Totale à la Législation Tunisienne**
- ✅ Plan Comptable Normalisé (PCN) 2023 complet
- ✅ Télédéclarations fiscales (TVA, IS, IRPP, TFP, TCL)
- ✅ Intégration El Fatoora (facturation électronique)
- ✅ Calculs CNSS automatiques conformes

### 3. **Automatisation Poussée**
- ⚡ Génération automatique des déclarations fiscales
- ⚡ Calcul de paie avec CNSS, IRPP, et primes d'ancienneté
- ⚡ Rapprochement bancaire intelligent
- ⚡ Catégorisation automatique des transactions

---

## 📋 Fonctionnalités Détaillées

### 🧾 **1. Plan Comptable Normalisé Tunisien (PCN)**

**Implémentation complète du PCN 2023** avec 9 classes:

- **Classe 1**: Comptes de financement permanent (Capital, Réserves, Emprunts)
- **Classe 2**: Actifs immobilisés (Incorporels, Corporels, Financiers)
- **Classe 3**: Stocks et en-cours
- **Classe 4**: Comptes de tiers (Clients, Fournisseurs, Personnel, État, CNSS)
- **Classe 5**: Comptes financiers (Banques, Caisse, VMP)
- **Classe 6**: Comptes de charges (Achats, Services, Salaires, Impôts)
- **Classe 7**: Comptes de produits (Ventes, Subventions, Produits financiers)
- **Classe 8**: Comptes de résultats
- **Classe 9**: Comptabilité analytique

**Fichier**: `database/seeders/TunisianChartOfAccountsSeeder.php`

**Comptes spécifiques tunisiens**:
- `431` - CNSS Cotisations sociales
- `445` - État TVA (collectée, déductible, à payer)
- `4461` - TFP (Taxe Formation Professionnelle)
- `4471` - Retenues à la source IRPP
- `645` - Charges sociales CNSS

---

### 💼 **2. Module de Paie et CNSS**

**Gestion complète de la paie tunisienne** conforme au Code du Travail 2024.

#### Tables créées:
- ✅ `employees` - Dossier employé complet (CIN, CNSS, contrat, salaire)
- ✅ `payslips` - Bulletins de paie détaillés
- ✅ `cnss_declarations` - Déclarations CNSS mensuelles
- ✅ `leave_requests` - Gestion des congés
- ✅ `leave_balances` - Soldes de congés annuels
- ✅ `payroll_settings` - Paramètres de paie

#### Calculs automatiques:

**Cotisations CNSS 2024**:
- Employé: **9.18%** (plafonné à 6 000 TND)
- Employeur: **16.57%** (plafonné à 6 000 TND)
- Assurance accidents: **0.4% à 4%**

**Barème IRPP 2024** (Article 44):
```
0 - 5 000 TND       : 0%
5 001 - 20 000 TND  : 26%
20 001 - 30 000 TND : 28%
30 001 - 50 000 TND : 32%
50 001+ TND         : 35%
```

**Déductions familiales** (Article 40 bis):
- Chef de famille: **150 TND/an**
- 1er enfant: **100 TND/an**
- 2ème enfant: **90 TND/an**
- 3ème enfant: **80 TND/an**
- 4ème enfant+: **60 TND/an chacun**

**Prime d'ancienneté**:
- 2 ans: **5%**
- +2% tous les 2 ans (max **30%**)

**Fichiers**:
- `app/Models/Payroll/Employee.php`
- `app/Services/Payroll/TunisianPayrollCalculator.php`
- `database/migrations/2025_01_20_000001_create_payroll_tunisia_tables.php`

**Exemple d'utilisation**:
```php
$calculator = new TunisianPayrollCalculator($settings);
$payslip = $calculator->calculatePayslip($employee, $month, $year, [
    'transport_allowance' => 50,
    'performance_bonus' => 200,
]);
```

---

### 📊 **3. Déclarations Fiscales Tunisiennes**

#### **TVA (Taxe sur la Valeur Ajoutée)**

**Taux applicables**:
- Standard: **19%**
- Réduit: **13%**
- Super-réduit: **7%**
- Exportations: **0%**

**Périodicité**:
- CA > 100 000 TND/an → Déclaration **mensuelle**
- CA < 100 000 TND/an → Déclaration **trimestrielle**
- Date limite: **28 du mois suivant**

**Fonctionnalités**:
- ✅ Calcul automatique TVA collectée/déductible
- ✅ Génération déclaration au format TEIF
- ✅ Télédéclaration (API prête)
- ✅ Gestion du crédit de TVA
- ✅ Calcul des pénalités de retard (1.75%/mois)

**Fichier**: `app/Services/Tax/TunisianVATCalculator.php`

#### **IS (Impôt sur les Sociétés)**

**Taux IS 2024**:
- Exportateurs/Tech: **15%**
- Général: **25%**
- Certains secteurs: **35%**

**Acomptes provisionnels**:
- 3 acomptes: fin juin, septembre, décembre
- Solde: avant le 25 mars N+1

**Fonctionnalités**:
- ✅ Calcul résultat fiscal (réintégrations/déductions)
- ✅ Gestion des acomptes
- ✅ Report déficitaire
- ✅ Retenues à la source

#### **TFP (Taxe de Formation Professionnelle)**
- Industrie: **2%** sur salaires
- Autres: **1%** sur salaires
- Déclaration: **mensuelle avec TVA**

#### **TCL (Taxe sur les Établissements)**
- Taux: **0.2%** sur salaires N-1
- Déclaration: **annuelle avant fin janvier**

#### **FOPROLOS**
- Taux: **1%** sur salaires
- Déclaration: **mensuelle**

#### **Retenues à la Source**
- Salaires: barème IRPP progressif
- Honoraires: **15%**
- Services: **1.5%**
- Loyers: **15%**

**Fichier**: `database/migrations/2025_01_20_000002_create_tunisia_tax_declarations_tables.php`

---

### 📱 **4. El Fatoora - Facturation Électronique**

**Intégration complète avec le système national de facturation électronique**.

#### Fonctionnalités:
- ✅ **Signature électronique** des factures (SHA-256 + RSA)
- ✅ **Génération QR Code** conforme aux normes
- ✅ **Télétransmission** au Ministère des Finances
- ✅ **Validation** et archivage automatique
- ✅ **Annulation** de factures
- ✅ **Traçabilité complète**

#### Processus:
1. Création facture dans ComptaPro
2. Signature électronique automatique
3. Génération QR code
4. Transmission à El Fatoora
5. Obtention code de validation
6. Archivage sécurisé

**Fichier**: `app/Services/ElFatoora/ElFatooraService.php`

**Format QR Code**:
```json
{
  "id": "1234567ABC-INV-001-20240120",
  "issuer_tax_id": "1234567/ABC/A/M/000",
  "invoice_number": "INV-00001",
  "invoice_date": "2024-01-20",
  "total_amount": 1190.00,
  "vat_amount": 190.00,
  "signature": "..."
}
```

---

## 🤖 **5. Fonctionnalités IA - Avantage Concurrentiel**

### **A. OCR Intelligent pour Factures**

**Extraction automatique des données** depuis images/PDF de factures fournisseurs.

**Providers supportés**:
- Google Cloud Vision (recommandé)
- AWS Textract
- Azure Computer Vision
- Tesseract (gratuit)

**Données extraites**:
- Nom et matricule fiscal du fournisseur
- Numéro et date de facture
- Lignes de produits/services avec TVA
- Totaux HT/TTC
- Coordonnées bancaires (RIB/IBAN)

**Validation automatique**:
- ✅ Format matricule fiscal tunisien
- ✅ Cohérence des montants
- ✅ Taux de TVA valides
- ✅ Détection fournisseur existant

**Score de confiance**: 0 à 1 (seuil recommandé: 0.85)

**Fichier**: `app/Services/AI/IntelligentOCRService.php`

**Utilisation**:
```php
$ocr = new IntelligentOCRService();
$result = $ocr->extractInvoiceData('/path/to/invoice.pdf');

if ($result['success'] && $result['confidence'] >= 0.85) {
    // Créer automatiquement la facture d'achat
    PurchaseInvoice::create($result['data']);
}
```

---

### **B. Assistant IA Expert-Comptable**

**Conseiller IA spécialisé** en comptabilité et fiscalité tunisienne.

**Expertise**:
- 📚 Plan Comptable Normalisé (PCN)
- 📚 Code IRPP et IS
- 📚 Législation TVA tunisienne
- 📚 Code du Travail et CNSS
- 📚 Système El Fatoora

**Fonctionnalités**:
- ✅ Réponses aux questions comptables/fiscales
- ✅ Optimisation fiscale légale
- ✅ Catégorisation automatique des transactions
- ✅ Vérification de conformité
- ✅ Analyse financière narrative
- ✅ Explication de concepts

**Fichier**: `app/Services/AI/AccountingAIAssistant.php`

**Exemples d'utilisation**:

```php
$assistant = new AccountingAIAssistant();

// Question générale
$response = $assistant->ask("Comment optimiser ma TVA en Tunisie?");

// Analyse financière
$analysis = $assistant->analyzeFinances([
    'revenue' => 250000,
    'expenses' => 180000,
    'operating_profit' => 70000,
]);

// Catégorisation transaction
$category = $assistant->categorizeTransaction([
    'description' => 'Achat fournitures de bureau',
    'amount' => 150,
    'type' => 'expense',
]);

// Suggestions d'optimisation
$optimizations = $assistant->suggestTaxOptimizations([
    'annual_revenue' => 500000,
    'sector' => 'Services',
    'legal_form' => 'SARL',
]);
```

**Questions fréquentes supportées**:
1. Comment optimiser ma TVA en Tunisie?
2. Quels sont les avantages fiscaux pour les exportateurs?
3. Comment calculer les cotisations CNSS?
4. Quelle est la différence entre IRPP et IS?
5. Comment fonctionne le système El Fatoora?
6. Quelles charges sont déductibles de l'impôt?
7. Comment déclarer mes employés à la CNSS?
8. Quel régime fiscal choisir pour ma startup?
9. Comment gérer la retenue à la source?
10. Quels sont les délais de déclaration fiscale?

---

### **C. Prédiction de Trésorerie ML**

**Anticipe vos flux de trésorerie** sur 90 jours avec Machine Learning.

**Analyse**:
- 📈 Encaissements prévisionnels (factures clients)
- 📉 Décaissements prévisionnels (fournisseurs, salaires, impôts)
- 💰 Solde de trésorerie jour par jour
- ⚠️ Identification des risques
- 💡 Recommandations d'actions

**Prédictions basées sur**:
- Historique de paiement des clients
- Factures en attente
- Charges récurrentes (salaires, loyer)
- Échéances fiscales (TVA, CNSS, TFP)
- Tendances saisonnières

**Fichier**: `app/Services/AI/CashFlowPredictor.php`

**Exemple**:
```php
$predictor = new CashFlowPredictor($companyId);
$forecast = $predictor->predictCashFlow(90); // 90 jours

// Résultats
$predictions = $forecast['predictions']; // Solde jour par jour
$risks = $forecast['risks']; // Alertes sur soldes négatifs
$recommendations = $forecast['recommendations']; // Actions à prendre
$confidence = $forecast['confidence_score']; // 0-1
```

**Résultat**:
```json
{
  "current_cash_balance": 25000.00,
  "predictions": [
    {
      "date": "2024-01-21",
      "balance": 24500.00,
      "inflows": 1200.00,
      "outflows": 1700.00,
      "net_change": -500.00
    }
  ],
  "risks": [
    {
      "level": "warning",
      "date": "2024-02-15",
      "type": "low_balance",
      "message": "Solde faible prévu: 3200 TND"
    }
  ],
  "recommendations": [
    {
      "priority": "high",
      "action": "Accélérer les encaissements",
      "description": "Relancer les clients avec factures en retard"
    }
  ]
}
```

---

### **D. Détection d'Anomalies Comptables**

**Détecte automatiquement** erreurs, fraudes et incohérences.

**Types d'anomalies détectées**:

1. **Erreurs Comptables**
   - ❌ Écritures non équilibrées (débit ≠ crédit)
   - ❌ Totaux factures incorrects
   - ❌ Montants ronds suspects
   - ❌ Dates incohérentes (futures, etc.)

2. **Non-Conformités Fiscales**
   - ❌ Taux de TVA invalides (hors 0%, 7%, 13%, 19%)
   - ❌ TVA manquante pour clients tunisiens
   - ❌ Numérotation discontinue des factures

3. **Fraudes Potentielles**
   - ❌ Factures en double
   - ❌ Remises excessives (> 50%)
   - ❌ Activités hors heures normales
   - ❌ Montants statistiquement anormaux

4. **Incohérences**
   - ❌ Sous-totaux incorrects
   - ❌ TVA mal calculée
   - ❌ Trous dans la numérotation

**Niveaux de sévérité**:
- 🔴 **Critical**: Action immédiate requise
- 🟠 **High**: Important à traiter rapidement
- 🟡 **Medium**: À examiner
- 🟢 **Low**: Information

**Fichier**: `app/Services/AI/AnomalyDetectionService.php`

**Utilisation**:
```php
$detector = new AnomalyDetectionService($companyId);
$report = $detector->detectAnomalies([
    'period' => [
        'start' => '2024-01-01',
        'end' => '2024-01-31'
    ]
]);

// Anomalies triées par sévérité
foreach ($report['anomalies'] as $anomaly) {
    echo "{$anomaly['severity']}: {$anomaly['description']}\n";
    echo "Correction suggérée: {$anomaly['suggested_fix']}\n";
}
```

---

## 🔧 **Installation et Configuration**

### **Prérequis**
- PHP 8.2+
- Laravel 11
- MySQL/PostgreSQL
- Composer
- Node.js & npm

### **Installation**

```bash
# 1. Cloner le repository
git clone https://github.com/your-org/comptapro.git
cd comptapro

# 2. Installer les dépendances
composer install
npm install

# 3. Configuration
cp .env.example .env
php artisan key:generate

# 4. Base de données
php artisan migrate

# 5. Seeder PCN Tunisien
php artisan db:seed --class=TunisianChartOfAccountsSeeder

# 6. Build assets
npm run build

# 7. Lancer le serveur
php artisan serve
```

### **Configuration Services IA**

Dans `.env`:

```env
# Anthropic Claude (Assistant IA)
ANTHROPIC_API_KEY=your_claude_api_key

# Google Cloud Vision (OCR)
GOOGLE_CLOUD_VISION_API_KEY=your_google_api_key

# El Fatoora
ELFATOORA_API_URL=https://api.elfatoora.gov.tn
ELFATOORA_API_KEY=your_elfatoora_api_key
ELFATOORA_CERTIFICATE_PATH=/path/to/certificate.pem
ELFATOORA_PRIVATE_KEY_PATH=/path/to/private_key.pem
ELFATOORA_PRIVATE_KEY_PASSWORD=your_password
```

---

## 📈 **Comparaison avec les Concurrents Locaux**

| Fonctionnalité | ComptaPro Tunisia | Concurrents Tunisiens |
|----------------|-------------------|----------------------|
| **IA Intégrée** | ✅ Oui (4 modules) | ❌ Non |
| **OCR Factures** | ✅ Automatique | ❌ Manuel |
| **Prédiction Trésorerie** | ✅ ML 90 jours | ❌ Non |
| **Détection Anomalies** | ✅ Temps réel | ❌ Non |
| **Assistant IA** | ✅ Expert tunisien | ❌ Non |
| **El Fatoora** | ✅ Intégré | ⚠️ Partiel |
| **Paie CNSS** | ✅ Complet auto | ⚠️ Manuel |
| **Télédéclarations** | ✅ Toutes | ⚠️ Partielles |
| **PCN 2023** | ✅ Complet | ✅ Oui |
| **Support Multi-pays** | ✅ Oui (BE, FR, CH, TN) | ❌ Non |
| **API REST** | ✅ Complète | ⚠️ Limitée |
| **Mobile App** | 🔄 En cours | ⚠️ Basique |
| **Prix** | 💰 Compétitif | 💰 Variable |

---

## 🎯 **Roadmap 2024-2025**

### **Q1 2024** ✅
- [x] PCN Tunisien complet
- [x] Module Paie & CNSS
- [x] Déclarations fiscales
- [x] El Fatoora
- [x] OCR Intelligent
- [x] Assistant IA
- [x] Prédiction trésorerie
- [x] Détection anomalies

### **Q2 2024** 🔄
- [ ] Rapprochement bancaire automatique
- [ ] Support langue arabe
- [ ] Mobile app (iOS/Android)
- [ ] API REST publique
- [ ] Tableau de bord analytique avancé

### **Q3 2024** 📋
- [ ] Intégration banques tunisiennes (API)
- [ ] Signature électronique avancée
- [ ] Module de gestion de projet
- [ ] Facturation récurrente
- [ ] Portail client/fournisseur

### **Q4 2024** 📋
- [ ] Module CRM intégré
- [ ] Gestion des actifs immobilisés
- [ ] Business Intelligence avancée
- [ ] Audit trail complet
- [ ] Conformité ISO 27001

---

## 📞 **Support et Contact**

- 📧 Email: support@comptapro.tn
- 🌐 Site web: https://comptapro.tn
- 📱 Téléphone: +216 XX XXX XXX
- 💬 Chat: Disponible dans l'application

---

## 📄 **Licence**

Propriétaire - Tous droits réservés © 2024 ComptaPro SaaS

---

## 🙏 **Remerciements**

Développé avec passion pour moderniser la comptabilité en Tunisie 🇹🇳

**Technologies utilisées**:
- Laravel 11
- Vue.js 3
- Inertia.js
- Bootstrap 5
- Claude AI (Anthropic)
- Google Cloud Vision
- MySQL

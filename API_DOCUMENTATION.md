# Documentation API ComptaPro Tunisia

Documentation complète de l'API REST pour ComptaPro Tunisia.

## Table des Matières

1. [Introduction](#introduction)
2. [Authentification](#authentification)
3. [Endpoints](#endpoints)
   - [Paie & CNSS](#paie--cnss)
   - [Déclarations Fiscales](#déclarations-fiscales)
   - [El Fatoora](#el-fatoora)
   - [Assistant IA](#assistant-ia)
   - [Rapprochement Bancaire](#rapprochement-bancaire)
4. [Codes d'Erreur](#codes-derreur)
5. [Rate Limiting](#rate-limiting)
6. [Exemples](#exemples)

---

## Introduction

**Base URL**: `https://api.comptapro.tn/api/v1`

**Format**: JSON uniquement

**Version**: 1.0.0

Toutes les requêtes nécessitent une authentification via token Bearer.

---

## Authentification

### Obtenir un Token

**Endpoint**: `POST /auth/login`

**Body**:
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Réponse**:
```json
{
  "success": true,
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

### Utiliser le Token

Inclure le token dans chaque requête:

```
Authorization: Bearer 1|abc123def456...
```

---

## Endpoints

### Paie & CNSS

#### 1. Liste des Employés

**GET** `/payroll/employees`

**Query Parameters**:
- `page` (integer, optional): Numéro de page
- `per_page` (integer, optional): Éléments par page (défaut: 15)
- `search` (string, optional): Recherche par nom ou CIN

**Réponse**:
```json
{
  "data": [
    {
      "id": 1,
      "employee_number": "EMP-00001",
      "first_name": "Mohamed",
      "last_name": "Ben Ali",
      "cin": "12345678",
      "cnss_number": "123456789012",
      "position": "Développeur",
      "base_salary": 2500.000,
      "is_active": true
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

#### 2. Créer un Employé

**POST** `/payroll/employees`

**Body**:
```json
{
  "first_name": "Fatma",
  "last_name": "Trabelsi",
  "cin": "87654321",
  "cnss_number": "210987654321",
  "birth_date": "1990-05-15",
  "hire_date": "2024-01-01",
  "position": "Comptable",
  "department": "Finance",
  "contract_type": "CDI",
  "base_salary": 3000.000,
  "marital_status": "married",
  "children_count": 2,
  "email": "fatma.trabelsi@example.com",
  "phone": "+21698765432"
}
```

**Validation Rules**:
- `first_name`: requis, string, max:100
- `last_name`: requis, string, max:100
- `cin`: requis, 8 chiffres, unique
- `cnss_number`: requis, 8-12 chiffres, unique
- `base_salary`: requis, numeric, min:0
- `contract_type`: requis, enum: CDI, CDD, CIVP, KARAMA, Stage

**Réponse**:
```json
{
  "success": true,
  "employee": {
    "id": 2,
    "employee_number": "EMP-00002",
    "first_name": "Fatma",
    "last_name": "Trabelsi",
    ...
  }
}
```

#### 3. Générer un Bulletin de Paie

**POST** `/payroll/payslips/generate`

**Body**:
```json
{
  "employee_id": 1,
  "month": 1,
  "year": 2024,
  "worked_days": 22,
  "overtime_hours": 10,
  "absences": 0,
  "bonuses": {
    "performance": 200.000,
    "transport": 50.000
  },
  "deductions": {
    "advance": 100.000
  }
}
```

**Réponse**:
```json
{
  "success": true,
  "payslip": {
    "id": 1,
    "payslip_number": "PAY-202401-00001",
    "employee_id": 1,
    "employee_name": "Mohamed Ben Ali",
    "period_month": 1,
    "period_year": 2024,
    "base_salary": 2500.000,
    "gross_salary": 2750.000,
    "cnss_employee": 252.450,
    "cnss_employer": 455.775,
    "irpp": 45.000,
    "net_salary": 2452.550,
    "total_cost_company": 3205.775,
    "calculation_details": {
      "worked_days": 22,
      "total_days": 26,
      "overtime_amount": 250.000,
      "bonuses_total": 250.000,
      "deductions_total": 100.000
    }
  }
}
```

#### 4. Générer Déclaration CNSS

**POST** `/payroll/cnss-declarations/generate`

**Body**:
```json
{
  "month": 1,
  "year": 2024
}
```

**Réponse**:
```json
{
  "success": true,
  "declaration": {
    "id": 1,
    "declaration_number": "CNSS-202401-00001",
    "period_month": 1,
    "period_year": 2024,
    "employee_count": 15,
    "total_gross_salary": 37500.000,
    "total_cnss_employee": 3442.500,
    "total_cnss_employer": 6213.750,
    "total_cnss": 9656.250,
    "due_date": "2024-02-15",
    "status": "draft"
  }
}
```

#### 5. Calculer la Paie (API Publique)

**POST** `/payroll/calculate`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body**:
```json
{
  "base_salary": 2500.000,
  "contract_type": "CDI",
  "marital_status": "married",
  "children_count": 2,
  "seniority_years": 5,
  "worked_days": 22,
  "total_days": 26,
  "overtime_hours": 0,
  "bonuses": {
    "transport": 50.000,
    "food": 100.000
  }
}
```

**Réponse**:
```json
{
  "success": true,
  "calculation": {
    "base_salary": 2500.000,
    "prorated_salary": 2500.000,
    "seniority_bonus": 250.000,
    "bonuses": 150.000,
    "gross_salary": 2900.000,
    "cnss_employee": 266.220,
    "cnss_employer": 480.530,
    "taxable_income": 2633.780,
    "family_deductions": 450.000,
    "irpp": 567.383,
    "net_salary": 2066.397,
    "total_cost_company": 3380.530
  }
}
```

---

### Déclarations Fiscales

#### 1. Générer Déclaration TVA

**POST** `/tax/vat/generate`

**Body**:
```json
{
  "period_month": 1,
  "period_year": 2024
}
```

**Réponse**:
```json
{
  "success": true,
  "declaration": {
    "id": 1,
    "declaration_number": "TVA-202401-00001",
    "period_month": 1,
    "period_year": 2024,
    "sales_19_ht": 50000.000,
    "sales_19_vat": 9500.000,
    "sales_13_ht": 10000.000,
    "sales_13_vat": 1300.000,
    "sales_7_ht": 5000.000,
    "sales_7_vat": 350.000,
    "sales_0_ht": 2000.000,
    "export_ht": 8000.000,
    "vat_collected": 11150.000,
    "purchases_immobilization_ht": 5000.000,
    "purchases_immobilization_vat": 950.000,
    "purchases_goods_ht": 20000.000,
    "purchases_goods_vat": 3800.000,
    "purchases_services_ht": 8000.000,
    "purchases_services_vat": 1520.000,
    "vat_deductible": 6270.000,
    "vat_to_pay": 4880.000,
    "due_date": "2024-02-28",
    "status": "draft"
  }
}
```

#### 2. Exporter en Format TEIF

**GET** `/tax/vat/{id}/teif`

**Réponse** (text/plain):
```
TEIF|TVA|202401
VENTE|19|50000.000|9500.000
VENTE|13|10000.000|1300.000
VENTE|7|5000.000|350.000
VENTE|0|2000.000|0.000
EXPORT|0|8000.000|0.000
ACHAT_IMM|19|5000.000|950.000
ACHAT_BIEN|19|20000.000|3800.000
ACHAT_SERV|19|8000.000|1520.000
TVA_COLLECTEE|11150.000
TVA_DEDUCTIBLE|6270.000
TVA_A_PAYER|4880.000
```

#### 3. Générer Déclaration IS

**POST** `/tax/corporate-tax/generate`

**Body**:
```json
{
  "fiscal_year": 2024
}
```

**Réponse**:
```json
{
  "success": true,
  "declaration": {
    "id": 1,
    "declaration_number": "IS-2024-00001",
    "fiscal_year": 2024,
    "accounting_result": 150000.000,
    "reintegrations": 20000.000,
    "deductions": 10000.000,
    "fiscal_result": 160000.000,
    "tax_loss_carryforward": 0.000,
    "taxable_base": 160000.000,
    "tax_rate": 25.00,
    "corporate_tax_due": 40000.000,
    "advances_paid": 30000.000,
    "balance_to_pay": 10000.000,
    "due_date": "2025-03-25",
    "status": "draft"
  }
}
```

#### 4. Calculer TVA (API Publique)

**POST** `/vat/calculate`

**Body**:
```json
{
  "amount_ht": 1000.000,
  "vat_rate": 19
}
```

**Réponse**:
```json
{
  "success": true,
  "amount_ht": 1000.000,
  "vat_rate": 19,
  "vat_amount": 190.000,
  "amount_ttc": 1190.000
}
```

---

### El Fatoora

#### 1. Signer une Facture

**POST** `/elfatoora/invoices/{id}/sign`

**Réponse**:
```json
{
  "success": true,
  "invoice": {
    "id": 1,
    "invoice_number": "INV-2024-00001",
    "elfatoora_status": "signed",
    "elfatoora_signature": "SHA256withRSA...",
    "elfatoora_qr_code": "data:image/png;base64,..."
  }
}
```

#### 2. Transmettre à El Fatoora

**POST** `/elfatoora/invoices/{id}/transmit`

**Réponse**:
```json
{
  "success": true,
  "invoice": {
    "id": 1,
    "elfatoora_status": "transmitted",
    "elfatoora_id": "TN123456789",
    "elfatoora_transmission_date": "2024-01-15 10:30:00"
  }
}
```

#### 3. Traitement Complet

**POST** `/elfatoora/invoices/{id}/process`

Signe, transmet et valide en une seule opération.

**Réponse**:
```json
{
  "success": true,
  "invoice": {
    "id": 1,
    "elfatoora_status": "validated",
    "elfatoora_id": "TN123456789",
    "elfatoora_signature": "SHA256withRSA...",
    "elfatoora_qr_code": "data:image/png;base64,....."
  }
}
```

#### 4. Vérifier le Statut

**GET** `/elfatoora/invoices/{id}/status`

**Réponse**:
```json
{
  "success": true,
  "status": "validated",
  "elfatoora_id": "TN123456789",
  "last_check": "2024-01-15 14:30:00"
}
```

---

### Assistant IA

#### 1. Poser une Question

**POST** `/ai/assistant/ask`

**Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

**Body**:
```json
{
  "question": "Comment puis-je optimiser mes charges sociales?",
  "context": {
    "company_sector": "Services",
    "annual_revenue": 500000,
    "employee_count": 10
  }
}
```

**Réponse**:
```json
{
  "success": true,
  "answer": "Pour optimiser vos charges sociales en Tunisie, voici plusieurs stratégies:\n\n1. **Optimisation des contrats**: Envisagez les contrats SIVP ou KARAMA pour les jeunes diplômés, qui bénéficient de taux CNSS réduits...",
  "model": "claude-sonnet-4"
}
```

#### 2. Analyser les Finances

**POST** `/ai/analyze-finances`

**Réponse**:
```json
{
  "success": true,
  "analysis": {
    "overall_health": "good",
    "score": 78,
    "strengths": [
      "Marge nette de 24% supérieure à la moyenne du secteur",
      "Trésorerie positive de 45 000 TND"
    ],
    "weaknesses": [
      "Créances clients élevées (35 000 TND)",
      "Ratio de liquidité à améliorer"
    ],
    "recommendations": [
      "Mettre en place un système de relance automatique",
      "Négocier des délais de paiement plus courts"
    ]
  }
}
```

#### 3. Extraction OCR de Facture

**POST** `/ai/ocr/extract`

**Headers**:
```
Content-Type: multipart/form-data
```

**Body**:
```
file: [binary PDF/image]
```

**Réponse**:
```json
{
  "success": true,
  "data": {
    "supplier_name": "Fournisseur SARL",
    "supplier_tax_id": "1234567/ABC/A/M/000",
    "invoice_number": "FACT-2024-001",
    "invoice_date": "2024-01-15",
    "total_ht": 1000.000,
    "vat_rate": 19,
    "vat_amount": 190.000,
    "total_ttc": 1190.000,
    "items": [
      {
        "description": "Prestation de service",
        "quantity": 1,
        "unit_price": 1000.000,
        "total": 1000.000
      }
    ]
  },
  "confidence": 0.95
}
```

#### 4. Prédiction de Trésorerie

**POST** `/ai/cash-flow/predict`

**Body**:
```json
{
  "days_ahead": 90
}
```

**Réponse**:
```json
{
  "success": true,
  "predictions": [
    {
      "date": "2024-02-01",
      "predicted_balance": 47500.000,
      "confidence": 0.92,
      "inflows": 15000.000,
      "outflows": 12500.000
    },
    {
      "date": "2024-02-02",
      "predicted_balance": 49000.000,
      "confidence": 0.91,
      "inflows": 2000.000,
      "outflows": 500.000
    }
  ],
  "risks": [
    {
      "date": "2024-03-15",
      "type": "low_balance",
      "severity": "medium",
      "description": "Trésorerie prévue à 5000 TND",
      "recommendation": "Prévoir un apport de trésorerie ou reporter certaines dépenses"
    }
  ]
}
```

#### 5. Détection d'Anomalies

**POST** `/ai/anomalies/detect`

**Body** (optionnel):
```json
{
  "period_start": "2024-01-01",
  "period_end": "2024-01-31"
}
```

**Réponse**:
```json
{
  "success": true,
  "anomalies": [
    {
      "type": "suspicious_amount",
      "severity": "high",
      "description": "Facture avec montant anormalement élevé",
      "details": {
        "invoice_id": 123,
        "invoice_number": "INV-2024-123",
        "amount": 50000.000,
        "expected_range": [500, 5000]
      },
      "recommendation": "Vérifier la facture et sa justification"
    },
    {
      "type": "duplicate",
      "severity": "medium",
      "description": "Facture potentiellement en double",
      "details": {
        "invoice_ids": [124, 125],
        "similarity": 0.98
      }
    }
  ],
  "summary": {
    "total_anomalies": 2,
    "high_severity": 1,
    "medium_severity": 1,
    "low_severity": 0
  }
}
```

---

### Rapprochement Bancaire

#### 1. Importer un Relevé Bancaire

**POST** `/banking/statements/import`

**Headers**:
```
Content-Type: multipart/form-data
```

**Body**:
```
bank_account_id: 1
file: [binary CSV/OFX/QIF]
format: csv
```

**Réponse**:
```json
{
  "success": true,
  "imported_count": 45,
  "skipped_count": 2,
  "message": "45 transactions importées avec succès"
}
```

#### 2. Effectuer le Rapprochement

**POST** `/banking/reconciliation/perform`

**Body**:
```json
{
  "bank_account_id": 1,
  "start_date": "2024-01-01",
  "end_date": "2024-01-31"
}
```

**Réponse**:
```json
{
  "success": true,
  "reconciliation": {
    "id": 1,
    "matched_count": 38,
    "unmatched_bank_count": 7,
    "unmatched_accounting_count": 3,
    "matched_amount": 125000.000,
    "balances": {
      "accounting_balance": 45000.000,
      "bank_balance": 45150.000,
      "difference": 150.000
    }
  },
  "matched_transactions": [
    {
      "bank_transaction": {
        "id": 1,
        "transaction_date": "2024-01-05",
        "description": "Virement client ABC",
        "amount": 5000.000
      },
      "journal_entry": {
        "id": 45,
        "reference": "VT-001",
        "amount": 5000.000
      },
      "matching_score": 1.0,
      "matching_type": "exact"
    }
  ]
}
```

#### 3. Rapprochement Manuel

**POST** `/banking/reconciliation/manual-match`

**Body**:
```json
{
  "bank_transaction_id": 10,
  "journal_entry_id": 50
}
```

**Réponse**:
```json
{
  "success": true,
  "message": "Transaction rapprochée manuellement"
}
```

---

## Codes d'Erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Ressource créée |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Non autorisé |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 429 | Trop de requêtes (rate limit) |
| 500 | Erreur serveur |

**Format d'erreur**:
```json
{
  "success": false,
  "error": "Message d'erreur détaillé",
  "errors": {
    "field_name": [
      "Le champ est requis"
    ]
  }
}
```

---

## Rate Limiting

Les limites de requêtes varient selon le type d'endpoint:

| Endpoint Type | Limite | Période |
|--------------|--------|---------|
| Standard | 100 | 1 minute |
| IA Assistant | 60 | 1 minute |
| OCR | 20 | 1 minute |
| Analyses IA | 30 | 1 minute |

**Headers de réponse**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642345678
```

**Erreur de rate limit** (429):
```json
{
  "success": false,
  "error": "Trop de requêtes. Veuillez réessayer dans 30 secondes.",
  "retry_after": 30
}
```

---

## Exemples

### Exemple PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://api.comptapro.tn/api/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

// Générer une déclaration TVA
$response = $client->post('tax/vat/generate', [
    'json' => [
        'period_month' => 1,
        'period_year' => 2024
    ]
]);

$declaration = json_decode($response->getBody(), true);
```

### Exemple JavaScript (Fetch)

```javascript
const API_BASE = 'https://api.comptapro.tn/api/v1';
const token = 'your-token-here';

// Poser une question à l'IA
async function askAI(question) {
  const response = await fetch(`${API_BASE}/ai/assistant/ask`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      question: question
    })
  });

  const data = await response.json();
  return data.answer;
}
```

### Exemple Python (Requests)

```python
import requests

API_BASE = 'https://api.comptapro.tn/api/v1'
token = 'your-token-here'

headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

# Générer un bulletin de paie
response = requests.post(
    f'{API_BASE}/payroll/payslips/generate',
    headers=headers,
    json={
        'employee_id': 1,
        'month': 1,
        'year': 2024,
        'worked_days': 22
    }
)

payslip = response.json()
```

---

## Support

Pour toute question sur l'API:
- **Documentation**: https://docs.comptapro.tn/api
- **Email**: api@comptapro.tn
- **Discord**: https://discord.gg/comptapro

---

**Version**: 1.0.0
**Dernière mise à jour**: Janvier 2025

# 🇧🇪 ComptaPro Belgium

**Solution comptable intelligente pour la Belgique avec automatisation IA**

## 🎯 Caractéristiques Principales

- ✅ **PCMN complet** - 400+ comptes (Plan Comptable Minimum Normalisé)
- ✅ **TVA automatique** - Taux 21%, 12%, 6%, 0%
- ✅ **Paie & ONSS** - Calcul automatique (13.07% + ~27%)
- ✅ **Impôt des Sociétés** - 25% normal, 20% PME
- ✅ **IA avancée** - OCR 98%+, écritures auto
- ✅ **Multilingue** - FR, NL, EN

## 🚀 Installation Rapide

```bash
# Cloner et installer
git clone https://github.com/yourorg/comptapro.git
cd comptapro
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Database + PCMN
php artisan migrate
php artisan db:seed --class=BelgiumChartOfAccountsSeeder

# Lancer
npm run build
php artisan serve
```

Accéder: `http://localhost:8000/belgium/automation`

## 📊 PCMN Belgique

```
Classe 1: Capitaux propres et dettes LT
Classe 2: Actifs immobilisés
Classe 3: Stocks
Classe 4: Créances/dettes court terme
Classe 5: Trésorerie
Classe 6: Charges
Classe 7: Produits
Classe 0: Hors bilan
```

## 💶 TVA Belgique

| Taux | Application |
|------|-------------|
| 21% | Standard |
| 12% | Travaux, restaurants |
| 6% | Aliments, livres |
| 0% | Export |

**Déclaration**: Mensuelle (>2.5M€) ou trimestrielle
**Deadline**: 20 du mois suivant

## 👥 Paie & ONSS

```
Salaire brut:    3.500€
ONSS employé:    -457€  (13.07%)
Précompte:       -609€
= Net:           2.434€

ONSS employeur:  +945€  (~27%)
= Coût total:    4.445€
```

## 🤖 Automatisation IA

**Upload document → Résultat en < 20 secondes:**
1. OCR (98%+ précision)
2. Extraction données
3. Détermination comptes PCMN
4. Génération écriture
5. Validation auto si confiance ≥ 90%

## 📝 Usage

### Générer TVA

```bash
php artisan belgium:monthly-workflow 1 2024 11
```

### API

```bash
# TVA
POST /api/v1/belgium/vat-declarations
{
  "year": 2024,
  "month": 11,
  "period_type": "monthly"
}

# Paie
POST /api/v1/belgium/payrolls
{
  "employee_id": 1,
  "year": 2024,
  "month": 11
}
```

## 📚 Documentation

- [BELGIUM_GUIDE.md](BELGIUM_GUIDE.md) - Guide complet
- [ARCHITECTURE_INTERFACE.md](ARCHITECTURE_INTERFACE.md) - Architecture interface

## 🆘 Support

- Email: support@comptapro.be
- Documentation: https://docs.comptapro.be
- Issues: https://github.com/yourorg/comptapro/issues

## 📄 Licence

© 2024 ComptaPro Belgium - Tous droits réservés

---

**Fait avec ❤️ en Belgique 🇧🇪**

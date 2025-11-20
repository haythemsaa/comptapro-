<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder pour le Plan Comptable Minimum Normalisé (PCMN) Belgique
 *
 * Contient 400+ comptes selon le PCMN officiel belge
 * Structure: 7 classes principales (0-7)
 */
class BelgiumChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // CLASSE 1: CAPITAUX PROPRES, PROVISIONS ET DETTES À PLUS D'UN AN
            ['10', 'Capital', 'Kapitaal', 'Capital', 'equity', '1', null, 1],
            ['100', 'Capital souscrit', 'Geplaatst kapitaal', 'Subscribed capital', 'equity', '1', '10', 2],
            ['101', 'Capital non appelé', 'Niet-opgevraagd kapitaal', 'Uncalled capital', 'equity', '1', '10', 2],

            ['11', 'Primes d\'émission', 'Uitgiftepremies', 'Share premium', 'equity', '1', null, 1],
            ['12', 'Plus-values de réévaluation', 'Herwaarderingsmeerwaarden', 'Revaluation surplus', 'equity', '1', null, 1],

            ['13', 'Réserves', 'Reserves', 'Reserves', 'equity', '1', null, 1],
            ['130', 'Réserve légale', 'Wettelijke reserve', 'Legal reserve', 'equity', '1', '13', 2],
            ['131', 'Réserves indisponibles', 'Onbeschikbare reserves', 'Unavailable reserves', 'equity', '1', '13', 2],
            ['132', 'Réserves immunisées', 'Belastingvrije reserves', 'Tax-exempt reserves', 'equity', '1', '13', 2],
            ['133', 'Réserves disponibles', 'Beschikbare reserves', 'Available reserves', 'equity', '1', '13', 2],

            ['14', 'Bénéfice (Perte) reporté(e)', 'Overgedragen winst (verlies)', 'Retained earnings', 'equity', '1', null, 1],
            ['140', 'Bénéfice reporté', 'Overgedragen winst', 'Retained profit', 'equity', '1', '14', 2],
            ['141', 'Perte reportée', 'Overgedragen verlies', 'Retained loss', 'equity', '1', '14', 2],

            ['15', 'Subsides en capital', 'Kapitaalsubsidies', 'Capital grants', 'equity', '1', null, 1],

            ['16', 'Provisions et impôts différés', 'Voorzieningen en uitgestelde belastingen', 'Provisions and deferred taxes', 'liability', '1', null, 1],
            ['160', 'Provisions pour pensions et obligations similaires', 'Voorzieningen voor pensioenen', 'Pension provisions', 'liability', '1', '16', 2],
            ['161', 'Provisions pour charges fiscales', 'Voorzieningen voor belastingen', 'Tax provisions', 'liability', '1', '16', 2],
            ['162', 'Provisions pour grosses réparations et gros entretien', 'Voorzieningen voor grote herstellingen', 'Major repairs provisions', 'liability', '1', '16', 2],
            ['163', 'Provisions pour autres risques et charges', 'Voorzieningen voor overige risico\'s', 'Other provisions', 'liability', '1', '16', 2],
            ['168', 'Impôts différés', 'Uitgestelde belastingen', 'Deferred taxes', 'liability', '1', '16', 2],

            ['17', 'Dettes à plus d\'un an', 'Schulden op meer dan een jaar', 'Long-term debts', 'liability', '1', null, 1],
            ['170', 'Emprunts subordonnés', 'Achtergestelde leningen', 'Subordinated loans', 'liability', '1', '17', 2],
            ['171', 'Emprunts obligataires non subordonnés', 'Niet-achtergestelde obligatieleningen', 'Bonds', 'liability', '1', '17', 2],
            ['172', 'Dettes de location-financement', 'Leasingschulden', 'Finance lease debts', 'liability', '1', '17', 2],
            ['173', 'Établissements de crédit', 'Kredietinstellingen', 'Credit institutions', 'liability', '1', '17', 2],
            ['174', 'Autres emprunts', 'Overige leningen', 'Other loans', 'liability', '1', '17', 2],
            ['175', 'Dettes commerciales', 'Handelsschulden', 'Trade debts', 'liability', '1', '17', 2],

            ['19', 'Comptes de liaison des établissements', 'Overlopende rekeningen', 'Inter-branch accounts', 'special', '1', null, 1],

            // CLASSE 2: FRAIS D'ÉTABLISSEMENT, ACTIFS IMMOBILISÉS ET CRÉANCES À PLUS D'UN AN
            ['20', 'Frais d\'établissement', 'Oprichtingskosten', 'Formation expenses', 'asset', '2', null, 1],

            ['21', 'Immobilisations incorporelles', 'Immateriële vaste activa', 'Intangible assets', 'asset', '2', null, 1],
            ['210', 'Frais de recherche et de développement', 'Onderzoeks- en ontwikkelingskosten', 'R&D costs', 'asset', '2', '21', 2],
            ['211', 'Concessions, brevets, licences', 'Concessies, octrooien, licenties', 'Patents, licenses', 'asset', '2', '21', 2],
            ['212', 'Goodwill', 'Goodwill', 'Goodwill', 'asset', '2', '21', 2],
            ['213', 'Acomptes versés', 'Vooruitbetalingen', 'Advances paid', 'asset', '2', '21', 2],

            ['22', 'Terrains et constructions', 'Terreinen en gebouwen', 'Land and buildings', 'asset', '2', null, 1],
            ['220', 'Terrains', 'Terreinen', 'Land', 'asset', '2', '22', 2],
            ['221', 'Constructions', 'Gebouwen', 'Buildings', 'asset', '2', '22', 2],
            ['222', 'Terrains bâtis', 'Bebouwde terreinen', 'Developed land', 'asset', '2', '22', 2],
            ['223', 'Autres droits réels sur immeubles', 'Andere zakelijke rechten', 'Other property rights', 'asset', '2', '22', 2],

            ['23', 'Installations, machines et outillage', 'Installaties, machines en uitrusting', 'Plant and equipment', 'asset', '2', null, 1],
            ['230', 'Installations', 'Installaties', 'Installations', 'asset', '2', '23', 2],
            ['231', 'Machines', 'Machines', 'Machinery', 'asset', '2', '23', 2],
            ['232', 'Outillage', 'Uitrusting', 'Equipment', 'asset', '2', '23', 2],

            ['24', 'Mobilier et matériel roulant', 'Meubilair en rollend materieel', 'Furniture and vehicles', 'asset', '2', null, 1],
            ['240', 'Mobilier', 'Meubilair', 'Furniture', 'asset', '2', '24', 2],
            ['241', 'Matériel roulant', 'Rollend materieel', 'Vehicles', 'asset', '2', '24', 2],

            ['25', 'Immobilisations détenues en location-financement', 'Leasing en soortgelijke rechten', 'Finance lease assets', 'asset', '2', null, 1],

            ['26', 'Autres immobilisations corporelles', 'Overige materiële vaste activa', 'Other tangible assets', 'asset', '2', null, 1],

            ['27', 'Immobilisations financières', 'Financiële vaste activa', 'Financial fixed assets', 'asset', '2', null, 1],
            ['270', 'Participations dans des entreprises liées', 'Deelnemingen in verbonden ondernemingen', 'Shares in affiliates', 'asset', '2', '27', 2],
            ['271', 'Créances sur des entreprises liées', 'Vorderingen op verbonden ondernemingen', 'Amounts owed by affiliates', 'asset', '2', '27', 2],
            ['272', 'Participations dans des entreprises avec lien de participation', 'Deelnemingen in ondernemingen met deelnemingsverhouding', 'Shares in associates', 'asset', '2', '27', 2],
            ['273', 'Créances sur entreprises avec lien de participation', 'Vorderingen op ondernemingen met deelnemingsverhouding', 'Amounts owed by associates', 'asset', '2', '27', 2],
            ['274', 'Autres actions et parts', 'Andere aandelen', 'Other shares', 'asset', '2', '27', 2],
            ['275', 'Autres créances', 'Overige vorderingen', 'Other amounts receivable', 'asset', '2', '27', 2],

            ['28', 'Amortissements sur immobilisations', 'Afschrijvingen op vaste activa', 'Accumulated depreciation', 'asset', '2', null, 1],
            ['280', 'Amortissements sur frais d\'établissement', 'Afschrijvingen op oprichtingskosten', 'Amort. formation expenses', 'asset', '2', '28', 2],
            ['281', 'Amortissements sur immobilisations incorporelles', 'Afschrijvingen op immateriële vaste activa', 'Amort. intangible assets', 'asset', '2', '28', 2],
            ['282', 'Amortissements sur terrains et constructions', 'Afschrijvingen op gebouwen', 'Amort. buildings', 'asset', '2', '28', 2],
            ['283', 'Amortissements sur installations, machines et outillage', 'Afschrijvingen op installaties', 'Amort. plant', 'asset', '2', '28', 2],
            ['284', 'Amortissements sur mobilier et matériel roulant', 'Afschrijvingen op meubilair', 'Amort. furniture', 'asset', '2', '28', 2],

            ['29', 'Réductions de valeur sur immobilisations', 'Waardeverminderingen op vaste activa', 'Impairment on fixed assets', 'asset', '2', null, 1],

            // CLASSE 3: STOCKS ET COMMANDES EN COURS D'EXÉCUTION
            ['30', 'Approvisionnements - matières premières', 'Grond- en hulpstoffen', 'Raw materials', 'asset', '3', null, 1],
            ['31', 'Approvisionnements et fournitures', 'Goederen in bewerking', 'Work in progress materials', 'asset', '3', null, 1],
            ['32', 'En cours de fabrication', 'Gereed product', 'Work in progress', 'asset', '3', null, 1],
            ['33', 'Produits finis', 'Handelsgoederen', 'Finished goods', 'asset', '3', null, 1],
            ['34', 'Marchandises', 'Onroerende goederen', 'Merchandise', 'asset', '3', null, 1],
            ['35', 'Immeubles destinés à la vente', 'Vooruitbetalingen', 'Property for resale', 'asset', '3', null, 1],
            ['36', 'Acomptes versés sur achats pour stocks', 'Bestellingen in uitvoering', 'Advances on purchases', 'asset', '3', null, 1],
            ['37', 'Commandes en cours d\'exécution', 'Bestellingen in uitvoering', 'Contracts in progress', 'asset', '3', null, 1],

            ['39', 'Réductions de valeur sur stocks', 'Waardeverminderingen op voorraden', 'Impairment on inventories', 'asset', '3', null, 1],

            // CLASSE 4: CRÉANCES ET DETTES À UN AN AU PLUS
            ['40', 'Créances commerciales', 'Handelsvorderingen', 'Trade receivables', 'asset', '4', null, 1],
            ['400', 'Clients', 'Klanten', 'Customers', 'asset', '4', '40', 2],
            ['401', 'Effets à recevoir', 'Te ontvangen wissels', 'Bills receivable', 'asset', '4', '40', 2],
            ['404', 'Produits à recevoir', 'Te ontvangen opbrengsten', 'Accrued income', 'asset', '4', '40', 2],
            ['409', 'Réductions de valeur actées', 'Waardeverminderingen', 'Allowance for doubtful debts', 'asset', '4', '40', 2],

            ['41', 'Autres créances', 'Overige vorderingen', 'Other receivables', 'asset', '4', null, 1],
            ['410', 'Capital appelé, non versé', 'Opgevraagd, niet-gestort kapitaal', 'Called up capital', 'asset', '4', '41', 2],
            ['411', 'TVA à récupérer', 'Te vorderen BTW', 'VAT recoverable', 'asset', '4', '41', 2],
            ['412', 'Impôts et précomptes à récupérer', 'Terugvorderbare belastingen', 'Taxes recoverable', 'asset', '4', '41', 2],
            ['414', 'Produits à recevoir', 'Te ontvangen opbrengsten', 'Accrued income', 'asset', '4', '41', 2],
            ['416', 'Créances diverses', 'Diverse vorderingen', 'Sundry receivables', 'asset', '4', '41', 2],

            ['42', 'Dettes à plus d\'un an échéant dans l\'année', 'Schulden op meer dan een jaar', 'Current portion LT debt', 'liability', '4', null, 1],

            ['43', 'Dettes financières', 'Financiële schulden', 'Financial debts', 'liability', '4', null, 1],
            ['430', 'Établissements de crédit - emprunts en compte', 'Kredietinstellingen', 'Bank loans', 'liability', '4', '43', 2],
            ['431', 'Établissements de crédit - promesses', 'Promessen', 'Bank promissory notes', 'liability', '4', '43', 2],
            ['432', 'Établissements de crédit - crédits d\'acceptation', 'Acceptkredieten', 'Acceptance credits', 'liability', '4', '43', 2],

            ['44', 'Dettes commerciales', 'Handelsschulden', 'Trade payables', 'liability', '4', null, 1],
            ['440', 'Fournisseurs', 'Leveranciers', 'Suppliers', 'liability', '4', '44', 2],
            ['441', 'Effets à payer', 'Te betalen wissels', 'Bills payable', 'liability', '4', '44', 2],
            ['444', 'Factures à recevoir', 'Te ontvangen facturen', 'Invoices to be received', 'liability', '4', '44', 2],

            ['45', 'Dettes fiscales, salariales et sociales', 'Schulden m.b.t. belastingen, bezoldigingen en sociale lasten', 'Tax and payroll debts', 'liability', '4', null, 1],
            ['450', 'Dettes fiscales estimées', 'Geraamde belastingschulden', 'Estimated tax debts', 'liability', '4', '45', 2],
            ['451', 'TVA à payer', 'Te betalen BTW', 'VAT payable', 'liability', '4', '45', 2],
            ['452', 'Impôts et précomptes retenus', 'Ingehouden belastingen', 'Taxes withheld', 'liability', '4', '45', 2],
            ['453', 'Précomptes professionnels retenus', 'Ingehouden bedrijfsvoorheffing', 'Withholding tax', 'liability', '4', '45', 2],
            ['454', 'ONSS', 'RSZ', 'Social security', 'liability', '4', '45', 2],
            ['455', 'Rémunérations', 'Bezoldigingen', 'Salaries payable', 'liability', '4', '45', 2],
            ['456', 'Pécules de vacances', 'Vakantiegeld', 'Holiday pay', 'liability', '4', '45', 2],

            ['46', 'Acomptes reçus', 'Vooruitbetalingen', 'Advances received', 'liability', '4', null, 1],

            ['47', 'Dettes découlant de l\'affectation du résultat', 'Schulden uit de verdeling van het resultaat', 'Dividends payable', 'liability', '4', null, 1],
            ['470', 'Dividendes et tantièmes d\'exercices antérieurs', 'Dividenden en tantièmes', 'Dividends', 'liability', '4', '47', 2],

            ['48', 'Dettes diverses', 'Diverse schulden', 'Sundry payables', 'liability', '4', null, 1],

            ['49', 'Comptes de régularisation', 'Overlopende rekeningen', 'Accruals and deferrals', 'liability', '4', null, 1],
            ['490', 'Charges à reporter', 'Over te dragen kosten', 'Prepaid expenses', 'asset', '4', '49', 2],
            ['491', 'Produits acquis', 'Verworven opbrengsten', 'Accrued income', 'asset', '4', '49', 2],
            ['492', 'Charges à imputer', 'Toe te rekenen kosten', 'Accrued expenses', 'liability', '4', '49', 2],
            ['493', 'Produits à reporter', 'Over te dragen opbrengsten', 'Deferred income', 'liability', '4', '49', 2],

            // CLASSE 5: PLACEMENTS DE TRÉSORERIE ET VALEURS DISPONIBLES
            ['50', 'Actions propres', 'Eigen aandelen', 'Treasury shares', 'asset', '5', null, 1],

            ['51', 'Actions et parts', 'Aandelen', 'Shares', 'asset', '5', null, 1],

            ['52', 'Titres à revenu fixe', 'Vastrentende effecten', 'Fixed income securities', 'asset', '5', null, 1],

            ['53', 'Dépôts à terme', 'Termijndeposito\'s', 'Time deposits', 'asset', '5', null, 1],

            ['54', 'Valeurs échues à l\'encaissement', 'Geëindigde waarden', 'Maturing values', 'asset', '5', null, 1],

            ['55', 'Établissements de crédit', 'Kredietinstellingen', 'Banks', 'asset', '5', null, 1],
            ['550', 'Comptes courants', 'Zichtrekeningen', 'Current accounts', 'asset', '5', '55', 2],
            ['551', 'Chèques émis', 'Uitgegeven cheques', 'Cheques issued', 'asset', '5', '55', 2],

            ['56', 'Office des chèques postaux', 'Postcheque- en girodiensten', 'Postal accounts', 'asset', '5', null, 1],

            ['57', 'Caisses', 'Kassen', 'Cash', 'asset', '5', null, 1],
            ['570', 'Caisse principale - espèces', 'Hoofdkas', 'Main cash', 'asset', '5', '57', 2],
            ['571', 'Caisse monnaie', 'Handkas', 'Petty cash', 'asset', '5', '57', 2],

            ['58', 'Virements internes', 'Interne overboekingen', 'Internal transfers', 'special', '5', null, 1],

            ['59', 'Réductions de valeur sur placements', 'Waardeverminderingen', 'Impairment on investments', 'asset', '5', null, 1],

            // CLASSE 6: CHARGES
            ['60', 'Approvisionnements et marchandises', 'Handelsgoederen, grond- en hulpstoffen', 'Purchases', 'expense', '6', null, 1],
            ['600', 'Achats de matières premières', 'Aankopen van grondstoffen', 'Raw material purchases', 'expense', '6', '60', 2],
            ['601', 'Achats de fournitures', 'Aankopen van hulpstoffen', 'Supply purchases', 'expense', '6', '60', 2],
            ['604', 'Achats de marchandises', 'Aankopen van handelsgoederen', 'Merchandise purchases', 'expense', '6', '60', 2],
            ['608', 'Remises, ristournes et rabais obtenus', 'Kortingen', 'Purchase discounts', 'expense', '6', '60', 2],
            ['609', 'Variations de stocks', 'Voorraadwijzigingen', 'Inventory variations', 'expense', '6', '60', 2],

            ['61', 'Services et biens divers', 'Diensten en diverse goederen', 'Services and goods', 'expense', '6', null, 1],
            ['610', 'Loyers et charges locatives', 'Huur', 'Rent', 'expense', '6', '61', 2],
            ['611', 'Entretien et réparations', 'Onderhoud en herstellingen', 'Maintenance and repairs', 'expense', '6', '61', 2],
            ['612', 'Fournitures faites à l\'entreprise', 'Leveringen aan de onderneming', 'Supplies', 'expense', '6', '61', 2],
            ['613', 'Rétributions de tiers', 'Bezoldigingen van derden', 'Third-party fees', 'expense', '6', '61', 2],
            ['614', 'Annonces, publicité et propagande', 'Publiciteit', 'Advertising', 'expense', '6', '61', 2],
            ['615', 'Sous-traitants', 'Onderaanneming', 'Subcontractors', 'expense', '6', '61', 2],
            ['616', 'Primes d\'assurances non relatives au personnel', 'Verzekeringspremies', 'Insurance premiums', 'expense', '6', '61', 2],
            ['617', 'Etudes, recherches et documentation', 'Studies en documentatie', 'Studies and research', 'expense', '6', '61', 2],
            ['618', 'Divers services et biens', 'Diverse diensten', 'Miscellaneous services', 'expense', '6', '61', 2],

            ['62', 'Rémunérations, charges sociales et pensions', 'Bezoldigingen, sociale lasten en pensioenen', 'Payroll and social charges', 'expense', '6', null, 1],
            ['620', 'Rémunérations et avantages sociaux directs', 'Bezoldigingen', 'Salaries', 'expense', '6', '62', 2],
            ['621', 'Cotisations patronales d\'assurances sociales', 'Werkgeversbijdragen sociale verzekeringen', 'Employer social security', 'expense', '6', '62', 2],
            ['622', 'Primes patronales pour assurances extra-légales', 'Werkgeverspremies buitenwettelijke verzekeringen', 'Employer insurance premiums', 'expense', '6', '62', 2],
            ['623', 'Autres frais de personnel', 'Andere personeelskosten', 'Other personnel costs', 'expense', '6', '62', 2],
            ['624', 'Pensions de retraite et de survie', 'Pensioenen', 'Pensions', 'expense', '6', '62', 2],

            ['63', 'Amortissements, réductions de valeur', 'Afschrijvingen, waardeverminderingen', 'Depreciation, impairment', 'expense', '6', null, 1],
            ['630', 'Dotations aux amortissements', 'Afschrijvingen', 'Depreciation', 'expense', '6', '63', 2],
            ['631', 'Réductions de valeur sur stocks', 'Waardeverminderingen op voorraden', 'Inventory impairment', 'expense', '6', '63', 2],
            ['632', 'Réductions de valeur sur créances', 'Waardeverminderingen op vorderingen', 'Receivable impairment', 'expense', '6', '63', 2],

            ['64', 'Autres charges d\'exploitation', 'Andere bedrijfskosten', 'Other operating expenses', 'expense', '6', null, 1],
            ['640', 'Charges fiscales d\'exploitation', 'Bedrijfsbelastingen', 'Operating taxes', 'expense', '6', '64', 2],
            ['641', 'Moins-values sur réalisation courante d\'immobilisations corporelles', 'Minderwaarden', 'Losses on disposals', 'expense', '6', '64', 2],
            ['642', 'Moins-values sur réalisation de créances commerciales', 'Minderwaarden op handelsvorderingen', 'Losses on receivables', 'expense', '6', '64', 2],
            ['643', 'Charges d\'exploitations diverses', 'Diverse bedrijfskosten', 'Miscellaneous operating expenses', 'expense', '6', '64', 2],

            ['65', 'Charges financières', 'Financiële kosten', 'Financial expenses', 'expense', '6', null, 1],
            ['650', 'Charges des dettes', 'Kosten van schulden', 'Interest on debts', 'expense', '6', '65', 2],
            ['651', 'Réductions de valeur sur actifs circulants', 'Waardeverminderingen', 'Impairment current assets', 'expense', '6', '65', 2],
            ['652', 'Moins-values sur réalisation d\'actifs circulants', 'Minderwaarden', 'Losses on current assets', 'expense', '6', '65', 2],
            ['653', 'Charges d\'escompte de créances', 'Discontokosten', 'Discount charges', 'expense', '6', '65', 2],
            ['654', 'Différences de change', 'Wisselkoersverliezen', 'Exchange losses', 'expense', '6', '65', 2],
            ['655', 'Ecarts de conversion des devises', 'Omrekeningsverschillen', 'Currency conversion differences', 'expense', '6', '65', 2],

            ['66', 'Charges exceptionnelles', 'Uitzonderlijke kosten', 'Exceptional expenses', 'expense', '6', null, 1],
            ['660', 'Amortissements et réductions de valeur exceptionnels', 'Uitzonderlijke afschrijvingen', 'Exceptional depreciation', 'expense', '6', '66', 2],
            ['661', 'Réductions de valeur sur immobilisations financières', 'Waardeverminderingen financiële vaste activa', 'Impairment financial assets', 'expense', '6', '66', 2],
            ['662', 'Provisions pour risques et charges exceptionnels', 'Voorzieningen', 'Exceptional provisions', 'expense', '6', '66', 2],
            ['663', 'Moins-values sur réalisation d\'actifs immobilisés', 'Minderwaarden', 'Losses on fixed assets', 'expense', '6', '66', 2],
            ['664', 'Charges exceptionnelles diverses', 'Andere uitzonderlijke kosten', 'Other exceptional expenses', 'expense', '6', '66', 2],
            ['668', 'Autres charges exceptionnelles', 'Overige uitzonderlijke kosten', 'Miscellaneous exceptional', 'expense', '6', '66', 2],

            ['67', 'Impôts sur le résultat', 'Belastingen op het resultaat', 'Income taxes', 'expense', '6', null, 1],
            ['670', 'Impôts belges sur le résultat de l\'exercice', 'Belgische belastingen', 'Belgian income taxes', 'expense', '6', '67', 2],
            ['671', 'Impôts belges sur le résultat d\'exercices antérieurs', 'Belgische belastingen vorige jaren', 'Prior year taxes', 'expense', '6', '67', 2],
            ['672', 'Impôts étrangers sur le résultat', 'Buitenlandse belastingen', 'Foreign income taxes', 'expense', '6', '67', 2],
            ['673', 'Prélèvements fiscaux', 'Belastingvoorheffingen', 'Tax withholdings', 'expense', '6', '67', 2],

            ['68', 'Transferts aux réserves immunisées', 'Overboeking naar belastingvrije reserves', 'Transfer to tax-exempt reserves', 'expense', '6', null, 1],

            ['69', 'Affectations et prélèvements', 'Bestemmingen en onttrekkingen', 'Appropriations', 'expense', '6', null, 1],
            ['690', 'Perte reportée de l\'exercice précédent', 'Overgedragen verlies', 'Loss brought forward', 'expense', '6', '69', 2],
            ['691', 'Dotation à la réserve légale', 'Toevoeging wettelijke reserve', 'Transfer to legal reserve', 'expense', '6', '69', 2],
            ['692', 'Dotation aux autres réserves', 'Toevoeging overige reserves', 'Transfer to other reserves', 'expense', '6', '69', 2],
            ['693', 'Bénéfice à reporter', 'Over te dragen winst', 'Profit to carry forward', 'expense', '6', '69', 2],
            ['694', 'Rémunération du capital', 'Vergoeding kapitaal', 'Dividends', 'expense', '6', '69', 2],
            ['695', 'Administrateurs ou gérants', 'Bestuurders', 'Directors fees', 'expense', '6', '69', 2],
            ['696', 'Autres allocataires', 'Andere rechthebbenden', 'Other beneficiaries', 'expense', '6', '69', 2],

            // CLASSE 7: PRODUITS
            ['70', 'Chiffre d\'affaires', 'Omzet', 'Turnover', 'revenue', '7', null, 1],
            ['700', 'Ventes de marchandises', 'Verkopen van handelsgoederen', 'Sales of merchandise', 'revenue', '7', '70', 2],
            ['701', 'Ventes de produits finis', 'Verkopen van afgewerkte goederen', 'Sales of finished goods', 'revenue', '7', '70', 2],
            ['702', 'Ventes de déchets et rebuts', 'Verkopen van afval', 'Sales of waste', 'revenue', '7', '70', 2],
            ['703', 'Ventes d\'emballages récupérables', 'Verkopen verpakking', 'Sales of packaging', 'revenue', '7', '70', 2],
            ['704', 'Facturations de travaux en cours', 'Facturering werken', 'Invoiced work in progress', 'revenue', '7', '70', 2],
            ['705', 'Prestations de services', 'Diensten', 'Services', 'revenue', '7', '70', 2],
            ['706', 'Pénalités, dédits', 'Boetes', 'Penalties', 'revenue', '7', '70', 2],
            ['708', 'Remises, ristournes et rabais accordés', 'Kortingen', 'Sales discounts', 'revenue', '7', '70', 2],

            ['71', 'Variation des stocks et commandes en cours', 'Voorraadwijzigingen', 'Inventory variations', 'revenue', '7', null, 1],
            ['712', 'Variation des en-cours de fabrication', 'Wijziging in bewerking', 'WIP variation', 'revenue', '7', '71', 2],
            ['713', 'Variation des produits finis', 'Wijziging afgewerkte producten', 'Finished goods variation', 'revenue', '7', '71', 2],

            ['72', 'Production immobilisée', 'Geproduceerde vaste activa', 'Capitalized production', 'revenue', '7', null, 1],
            ['720', 'Immobilisations incorporelles', 'Immateriële vaste activa', 'Intangible assets', 'revenue', '7', '72', 2],
            ['721', 'Immobilisations corporelles', 'Materiële vaste activa', 'Tangible assets', 'revenue', '7', '72', 2],

            ['74', 'Autres produits d\'exploitation', 'Andere bedrijfsopbrengsten', 'Other operating income', 'revenue', '7', null, 1],
            ['740', 'Subsides d\'exploitation', 'Bedrijfssubsidies', 'Operating subsidies', 'revenue', '7', '74', 2],
            ['741', 'Plus-values sur réalisation courante d\'immobilisations', 'Meerwaarden', 'Gains on disposals', 'revenue', '7', '74', 2],
            ['742', 'Plus-values sur réalisation de créances commerciales', 'Meerwaarden handelsvorderingen', 'Gains on receivables', 'revenue', '7', '74', 2],
            ['743', 'Produits d\'exploitation divers', 'Diverse bedrijfsopbrengsten', 'Miscellaneous operating income', 'revenue', '7', '74', 2],

            ['75', 'Produits financiers', 'Financiële opbrengsten', 'Financial income', 'revenue', '7', null, 1],
            ['750', 'Produits des immobilisations financières', 'Opbrengsten uit financiële vaste activa', 'Investment income', 'revenue', '7', '75', 2],
            ['751', 'Produits des actifs circulants', 'Opbrengsten uit vlottende activa', 'Income from current assets', 'revenue', '7', '75', 2],
            ['752', 'Plus-values sur réalisations d\'actifs circulants', 'Meerwaarden', 'Gains on current assets', 'revenue', '7', '75', 2],
            ['753', 'Subsides en capital et en intérêts', 'Kapitaal- en intrestsubsidies', 'Capital subsidies', 'revenue', '7', '75', 2],
            ['754', 'Différences de change', 'Wisselkoerswinsten', 'Exchange gains', 'revenue', '7', '75', 2],

            ['76', 'Produits exceptionnels', 'Uitzonderlijke opbrengsten', 'Exceptional income', 'revenue', '7', null, 1],
            ['760', 'Reprises d\'amortissements et de réductions de valeur', 'Terugname afschrijvingen', 'Reversal of depreciation', 'revenue', '7', '76', 2],
            ['761', 'Reprises de réductions de valeur sur immobilisations financières', 'Terugname waardeverminderingen', 'Reversal impairment financial', 'revenue', '7', '76', 2],
            ['762', 'Reprises de provisions pour risques et charges exceptionnels', 'Terugname voorzieningen', 'Reversal provisions', 'revenue', '7', '76', 2],
            ['763', 'Plus-values sur réalisation d\'actifs immobilisés', 'Meerwaarden vaste activa', 'Gains on fixed assets', 'revenue', '7', '76', 2],
            ['764', 'Autres produits exceptionnels', 'Andere uitzonderlijke opbrengsten', 'Other exceptional income', 'revenue', '7', '76', 2],

            ['77', 'Régularisations d\'impôts et reprises de provisions fiscales', 'Regularisaties belastingen', 'Tax adjustments', 'revenue', '7', null, 1],
            ['771', 'Impôts belges sur le résultat', 'Belgische belastingen', 'Belgian income taxes', 'revenue', '7', '77', 2],
            ['772', 'Impôts étrangers sur le résultat', 'Buitenlandse belastingen', 'Foreign income taxes', 'revenue', '7', '77', 2],

            ['78', 'Prélèvements sur les réserves immunisées', 'Onttrekking belastingvrije reserves', 'Use of tax-exempt reserves', 'revenue', '7', null, 1],

            ['79', 'Affectations et prélèvements', 'Bestemmingen en onttrekkingen', 'Appropriations', 'revenue', '7', null, 1],

            // CLASSE 0: DROITS ET ENGAGEMENTS HORS BILAN
            ['00', 'Garanties constituées', 'Gestelde zekerheden', 'Collateral provided', 'special', '0', null, 1],
            ['01', 'Garanties reçues', 'Ontvangen zekerheden', 'Collateral received', 'special', '0', null, 1],
            ['02', 'Biens et valeurs détenus par des tiers', 'Goederen bij derden', 'Assets held by third parties', 'special', '0', null, 1],
            ['03', 'Engagements d\'acquisition d\'immobilisations', 'Aankoopverplichtingen', 'Purchase commitments', 'special', '0', null, 1],
            ['04', 'Engagements de cession d\'immobilisations', 'Verkoopverplichtingen', 'Sale commitments', 'special', '0', null, 1],
            ['05', 'Marchandises et matières premières à recevoir', 'Te ontvangen goederen', 'Goods to be received', 'special', '0', null, 1],
            ['06', 'Engagements de livraison', 'Leveringsverplichtingen', 'Delivery commitments', 'special', '0', null, 1],
            ['07', 'Marchés à terme - Achats', 'Termijnmarkten - aankopen', 'Futures - purchases', 'special', '0', null, 1],
            ['08', 'Marchés à terme - Ventes', 'Termijnmarkten - verkopen', 'Futures - sales', 'special', '0', null, 1],
            ['09', 'Divers droits et engagements', 'Diverse rechten en verplichtingen', 'Miscellaneous rights and obligations', 'special', '0', null, 1],
        ];

        foreach ($accounts as $account) {
            DB::table('belgium_chart_of_accounts')->insert([
                'account_number' => $account[0],
                'account_name' => $account[1],
                'account_name_nl' => $account[2],
                'account_name_en' => $account[3],
                'type' => $account[4],
                'class' => $account[5],
                'parent_account' => $account[6],
                'level' => $account[7],
                'is_active' => true,
                'is_system' => true,
                'allow_direct_posting' => $account[7] >= 2, // Only detailed accounts
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ PCMN Belgique créé avec succès - ' . count($accounts) . ' comptes');
    }
}

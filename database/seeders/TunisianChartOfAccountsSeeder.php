<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Plan Comptable Normalisé Tunisien (PCN) - Complet
 * Conforme aux normes comptables tunisiennes 2023
 */
class TunisianChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // CLASSE 1 : COMPTES DE FINANCEMENT PERMANENT
            ['code' => '10', 'name' => 'Capital', 'type' => 'equity', 'parent_code' => null],
            ['code' => '101', 'name' => 'Capital social', 'type' => 'equity', 'parent_code' => '10'],
            ['code' => '1011', 'name' => 'Capital souscrit non appelé', 'type' => 'equity', 'parent_code' => '101'],
            ['code' => '1012', 'name' => 'Capital souscrit appelé non versé', 'type' => 'equity', 'parent_code' => '101'],
            ['code' => '1013', 'name' => 'Capital souscrit appelé versé', 'type' => 'equity', 'parent_code' => '101'],

            ['code' => '11', 'name' => 'Réserves', 'type' => 'equity', 'parent_code' => null],
            ['code' => '111', 'name' => 'Réserve légale', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '112', 'name' => 'Réserves statutaires', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '113', 'name' => 'Réserves ordinaires', 'type' => 'equity', 'parent_code' => '11'],
            ['code' => '114', 'name' => 'Réserves réglementées', 'type' => 'equity', 'parent_code' => '11'],

            ['code' => '12', 'name' => 'Report à nouveau', 'type' => 'equity', 'parent_code' => null],
            ['code' => '121', 'name' => 'Report à nouveau créditeur', 'type' => 'equity', 'parent_code' => '12'],
            ['code' => '129', 'name' => 'Report à nouveau débiteur', 'type' => 'equity', 'parent_code' => '12'],

            ['code' => '13', 'name' => 'Résultat net de l\'exercice', 'type' => 'equity', 'parent_code' => null],
            ['code' => '131', 'name' => 'Résultat net : Bénéfice', 'type' => 'equity', 'parent_code' => '13'],
            ['code' => '139', 'name' => 'Résultat net : Perte', 'type' => 'equity', 'parent_code' => '13'],

            ['code' => '14', 'name' => 'Provisions réglementées', 'type' => 'liability', 'parent_code' => null],
            ['code' => '141', 'name' => 'Provisions pour investissement', 'type' => 'liability', 'parent_code' => '14'],
            ['code' => '142', 'name' => 'Provisions pour reconstitution de gisements', 'type' => 'liability', 'parent_code' => '14'],

            ['code' => '15', 'name' => 'Provisions pour risques et charges', 'type' => 'liability', 'parent_code' => null],
            ['code' => '151', 'name' => 'Provisions pour litiges', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '152', 'name' => 'Provisions pour garanties données aux clients', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '153', 'name' => 'Provisions pour pensions et obligations similaires', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '154', 'name' => 'Provisions pour impôts', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '155', 'name' => 'Provisions pour amendes et pénalités', 'type' => 'liability', 'parent_code' => '15'],
            ['code' => '158', 'name' => 'Autres provisions pour risques et charges', 'type' => 'liability', 'parent_code' => '15'],

            ['code' => '16', 'name' => 'Emprunts et dettes assimilées', 'type' => 'liability', 'parent_code' => null],
            ['code' => '161', 'name' => 'Emprunts obligataires', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '162', 'name' => 'Emprunts auprès des établissements de crédit', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '163', 'name' => 'Avances de l\'État', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '164', 'name' => 'Avances reçues et comptes courants bloqués', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '165', 'name' => 'Dépôts et cautionnements reçus', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '166', 'name' => 'Participation des salariés aux bénéfices', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '167', 'name' => 'Emprunts et dettes assortis de conditions particulières', 'type' => 'liability', 'parent_code' => '16'],
            ['code' => '168', 'name' => 'Autres emprunts et dettes assimilées', 'type' => 'liability', 'parent_code' => '16'],

            ['code' => '17', 'name' => 'Comptes de liaison des établissements et sociétés en participation', 'type' => 'equity', 'parent_code' => null],
            ['code' => '171', 'name' => 'Comptes de liaison : Siège', 'type' => 'equity', 'parent_code' => '17'],
            ['code' => '172', 'name' => 'Comptes de liaison : Établissements', 'type' => 'equity', 'parent_code' => '17'],

            // CLASSE 2 : COMPTES D'ACTIFS IMMOBILISÉS
            ['code' => '20', 'name' => 'Immobilisations incorporelles', 'type' => 'asset', 'parent_code' => null],
            ['code' => '201', 'name' => 'Frais de développement', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '202', 'name' => 'Brevets, marques, droits et valeurs similaires', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '203', 'name' => 'Fonds commercial', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '204', 'name' => 'Logiciels informatiques', 'type' => 'asset', 'parent_code' => '20'],
            ['code' => '208', 'name' => 'Autres immobilisations incorporelles', 'type' => 'asset', 'parent_code' => '20'],

            ['code' => '21', 'name' => 'Immobilisations corporelles', 'type' => 'asset', 'parent_code' => null],
            ['code' => '211', 'name' => 'Terrains', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '212', 'name' => 'Agencements et aménagements de terrains', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '213', 'name' => 'Constructions', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '214', 'name' => 'Constructions sur sol d\'autrui', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '215', 'name' => 'Installations techniques, matériels et outillages', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '218', 'name' => 'Autres immobilisations corporelles', 'type' => 'asset', 'parent_code' => '21'],
            ['code' => '2181', 'name' => 'Installations générales, agencements et aménagements divers', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2182', 'name' => 'Matériel de transport', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2183', 'name' => 'Matériel de bureau et mobilier', 'type' => 'asset', 'parent_code' => '218'],
            ['code' => '2184', 'name' => 'Emballages récupérables', 'type' => 'asset', 'parent_code' => '218'],

            ['code' => '22', 'name' => 'Immobilisations en cours', 'type' => 'asset', 'parent_code' => null],
            ['code' => '221', 'name' => 'Immobilisations incorporelles en cours', 'type' => 'asset', 'parent_code' => '22'],
            ['code' => '222', 'name' => 'Immobilisations corporelles en cours', 'type' => 'asset', 'parent_code' => '22'],
            ['code' => '223', 'name' => 'Avances et acomptes versés sur immobilisations', 'type' => 'asset', 'parent_code' => '22'],

            ['code' => '23', 'name' => 'Immobilisations financières', 'type' => 'asset', 'parent_code' => null],
            ['code' => '231', 'name' => 'Titres de participation', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '232', 'name' => 'Créances rattachées à des participations', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '233', 'name' => 'Titres immobilisés de l\'activité de portefeuille', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '234', 'name' => 'Autres titres immobilisés', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '235', 'name' => 'Prêts', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '236', 'name' => 'Autres créances immobilisées', 'type' => 'asset', 'parent_code' => '23'],
            ['code' => '237', 'name' => 'Créances rattachées à des sociétés en participation', 'type' => 'asset', 'parent_code' => '23'],

            ['code' => '28', 'name' => 'Amortissements des immobilisations', 'type' => 'asset', 'parent_code' => null],
            ['code' => '280', 'name' => 'Amortissements des immobilisations incorporelles', 'type' => 'asset', 'parent_code' => '28'],
            ['code' => '281', 'name' => 'Amortissements des immobilisations corporelles', 'type' => 'asset', 'parent_code' => '28'],

            ['code' => '29', 'name' => 'Provisions pour dépréciation des immobilisations', 'type' => 'asset', 'parent_code' => null],
            ['code' => '290', 'name' => 'Provisions pour dépréciation des immobilisations incorporelles', 'type' => 'asset', 'parent_code' => '29'],
            ['code' => '291', 'name' => 'Provisions pour dépréciation des immobilisations corporelles', 'type' => 'asset', 'parent_code' => '29'],
            ['code' => '293', 'name' => 'Provisions pour dépréciation des immobilisations financières', 'type' => 'asset', 'parent_code' => '29'],

            // CLASSE 3 : COMPTES DE STOCKS ET EN-COURS
            ['code' => '31', 'name' => 'Matières premières et fournitures', 'type' => 'asset', 'parent_code' => null],
            ['code' => '311', 'name' => 'Matières premières', 'type' => 'asset', 'parent_code' => '31'],
            ['code' => '312', 'name' => 'Matières et fournitures consommables', 'type' => 'asset', 'parent_code' => '31'],
            ['code' => '313', 'name' => 'Emballages', 'type' => 'asset', 'parent_code' => '31'],

            ['code' => '32', 'name' => 'Autres approvisionnements', 'type' => 'asset', 'parent_code' => null],
            ['code' => '321', 'name' => 'Matières consommables', 'type' => 'asset', 'parent_code' => '32'],
            ['code' => '322', 'name' => 'Fournitures consommables', 'type' => 'asset', 'parent_code' => '32'],
            ['code' => '323', 'name' => 'Emballages', 'type' => 'asset', 'parent_code' => '32'],

            ['code' => '33', 'name' => 'En-cours de production de biens', 'type' => 'asset', 'parent_code' => null],
            ['code' => '331', 'name' => 'Produits en cours', 'type' => 'asset', 'parent_code' => '33'],
            ['code' => '332', 'name' => 'Travaux en cours', 'type' => 'asset', 'parent_code' => '33'],

            ['code' => '34', 'name' => 'En-cours de production de services', 'type' => 'asset', 'parent_code' => null],
            ['code' => '341', 'name' => 'Études en cours', 'type' => 'asset', 'parent_code' => '34'],
            ['code' => '342', 'name' => 'Prestations de services en cours', 'type' => 'asset', 'parent_code' => '34'],

            ['code' => '35', 'name' => 'Stocks de produits', 'type' => 'asset', 'parent_code' => null],
            ['code' => '351', 'name' => 'Produits intermédiaires', 'type' => 'asset', 'parent_code' => '35'],
            ['code' => '352', 'name' => 'Produits finis', 'type' => 'asset', 'parent_code' => '35'],
            ['code' => '353', 'name' => 'Produits résiduels', 'type' => 'asset', 'parent_code' => '35'],

            ['code' => '36', 'name' => 'Stocks provenant d\'immobilisations', 'type' => 'asset', 'parent_code' => null],

            ['code' => '37', 'name' => 'Stocks de marchandises', 'type' => 'asset', 'parent_code' => null],
            ['code' => '371', 'name' => 'Marchandises A', 'type' => 'asset', 'parent_code' => '37'],
            ['code' => '372', 'name' => 'Marchandises B', 'type' => 'asset', 'parent_code' => '37'],

            ['code' => '39', 'name' => 'Provisions pour dépréciation des stocks', 'type' => 'asset', 'parent_code' => null],
            ['code' => '391', 'name' => 'Provisions pour dépréciation des matières premières', 'type' => 'asset', 'parent_code' => '39'],
            ['code' => '393', 'name' => 'Provisions pour dépréciation des en-cours de production', 'type' => 'asset', 'parent_code' => '39'],
            ['code' => '395', 'name' => 'Provisions pour dépréciation des produits', 'type' => 'asset', 'parent_code' => '39'],
            ['code' => '397', 'name' => 'Provisions pour dépréciation des marchandises', 'type' => 'asset', 'parent_code' => '39'],

            // CLASSE 4 : COMPTES DE TIERS
            ['code' => '40', 'name' => 'Fournisseurs et comptes rattachés', 'type' => 'liability', 'parent_code' => null],
            ['code' => '401', 'name' => 'Fournisseurs', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '403', 'name' => 'Fournisseurs - Effets à payer', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '404', 'name' => 'Fournisseurs d\'immobilisations', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '405', 'name' => 'Fournisseurs d\'immobilisations - Effets à payer', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '408', 'name' => 'Fournisseurs - Factures non parvenues', 'type' => 'liability', 'parent_code' => '40'],
            ['code' => '409', 'name' => 'Fournisseurs débiteurs', 'type' => 'asset', 'parent_code' => '40'],

            ['code' => '41', 'name' => 'Clients et comptes rattachés', 'type' => 'asset', 'parent_code' => null],
            ['code' => '411', 'name' => 'Clients', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '413', 'name' => 'Clients - Effets à recevoir', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '416', 'name' => 'Clients douteux ou litigieux', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '418', 'name' => 'Clients - Produits à recevoir', 'type' => 'asset', 'parent_code' => '41'],
            ['code' => '419', 'name' => 'Clients créditeurs', 'type' => 'liability', 'parent_code' => '41'],

            ['code' => '42', 'name' => 'Personnel et comptes rattachés', 'type' => 'liability', 'parent_code' => null],
            ['code' => '421', 'name' => 'Personnel - Rémunérations dues', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '422', 'name' => 'Comités d\'entreprise, d\'établissement', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '423', 'name' => 'Participation des salariés aux bénéfices', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '424', 'name' => 'Personnel - Œuvres sociales', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '425', 'name' => 'Personnel - Avances et acomptes', 'type' => 'asset', 'parent_code' => '42'],
            ['code' => '426', 'name' => 'Personnel - Dépôts reçus', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '427', 'name' => 'Personnel - Oppositions sur salaires', 'type' => 'liability', 'parent_code' => '42'],
            ['code' => '428', 'name' => 'Personnel - Charges à payer et produits à recevoir', 'type' => 'liability', 'parent_code' => '42'],

            ['code' => '43', 'name' => 'Organismes sociaux', 'type' => 'liability', 'parent_code' => null],
            ['code' => '431', 'name' => 'CNSS - Cotisations sociales', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '432', 'name' => 'CNRPS - Cotisations retraite', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '433', 'name' => 'Autres organismes sociaux', 'type' => 'liability', 'parent_code' => '43'],
            ['code' => '438', 'name' => 'Organismes sociaux - Charges à payer', 'type' => 'liability', 'parent_code' => '43'],

            ['code' => '44', 'name' => 'État et collectivités publiques', 'type' => 'liability', 'parent_code' => null],
            ['code' => '441', 'name' => 'État - Subventions à recevoir', 'type' => 'asset', 'parent_code' => '44'],
            ['code' => '442', 'name' => 'État - Impôts et taxes recouvrables', 'type' => 'asset', 'parent_code' => '44'],
            ['code' => '443', 'name' => 'Opérations particulières avec l\'État', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '444', 'name' => 'État - Impôts sur les bénéfices', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '445', 'name' => 'État - TVA', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '4451', 'name' => 'État - TVA collectée', 'type' => 'liability', 'parent_code' => '445'],
            ['code' => '4452', 'name' => 'État - TVA déductible', 'type' => 'asset', 'parent_code' => '445'],
            ['code' => '4453', 'name' => 'État - TVA à décaisser', 'type' => 'liability', 'parent_code' => '445'],
            ['code' => '4454', 'name' => 'État - TVA à récupérer', 'type' => 'asset', 'parent_code' => '445'],
            ['code' => '446', 'name' => 'État - Autres impôts et taxes', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '4461', 'name' => 'État - TFP (Taxe de Formation Professionnelle)', 'type' => 'liability', 'parent_code' => '446'],
            ['code' => '4462', 'name' => 'État - FOPROLOS', 'type' => 'liability', 'parent_code' => '446'],
            ['code' => '4463', 'name' => 'État - TCL (Taxe sur les établissements)', 'type' => 'liability', 'parent_code' => '446'],
            ['code' => '447', 'name' => 'État - Retenues à la source', 'type' => 'liability', 'parent_code' => '44'],
            ['code' => '4471', 'name' => 'État - Retenue à la source IRPP', 'type' => 'liability', 'parent_code' => '447'],
            ['code' => '4472', 'name' => 'État - Retenue à la source honoraires', 'type' => 'liability', 'parent_code' => '447'],
            ['code' => '448', 'name' => 'État - Charges à payer et produits à recevoir', 'type' => 'liability', 'parent_code' => '44'],

            ['code' => '45', 'name' => 'Associés et comptes rattachés', 'type' => 'liability', 'parent_code' => null],
            ['code' => '451', 'name' => 'Associés - Comptes courants', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '455', 'name' => 'Associés - Dividendes à payer', 'type' => 'liability', 'parent_code' => '45'],
            ['code' => '456', 'name' => 'Associés - Capital à rembourser', 'type' => 'liability', 'parent_code' => '45'],

            ['code' => '46', 'name' => 'Débiteurs et créditeurs divers', 'type' => 'asset', 'parent_code' => null],
            ['code' => '461', 'name' => 'Créances sur cessions d\'immobilisations', 'type' => 'asset', 'parent_code' => '46'],
            ['code' => '462', 'name' => 'Créances sur cessions de valeurs mobilières', 'type' => 'asset', 'parent_code' => '46'],
            ['code' => '464', 'name' => 'Dettes sur acquisitions de valeurs mobilières', 'type' => 'liability', 'parent_code' => '46'],
            ['code' => '465', 'name' => 'Créances diverses', 'type' => 'asset', 'parent_code' => '46'],
            ['code' => '467', 'name' => 'Autres comptes débiteurs ou créditeurs', 'type' => 'asset', 'parent_code' => '46'],

            ['code' => '47', 'name' => 'Comptes transitoires ou d\'attente', 'type' => 'asset', 'parent_code' => null],
            ['code' => '471', 'name' => 'Débiteurs divers', 'type' => 'asset', 'parent_code' => '47'],
            ['code' => '472', 'name' => 'Créditeurs divers', 'type' => 'liability', 'parent_code' => '47'],
            ['code' => '475', 'name' => 'Comptes d\'attente', 'type' => 'asset', 'parent_code' => '47'],

            ['code' => '48', 'name' => 'Charges et produits constatés d\'avance', 'type' => 'asset', 'parent_code' => null],
            ['code' => '481', 'name' => 'Charges à répartir sur plusieurs exercices', 'type' => 'asset', 'parent_code' => '48'],
            ['code' => '486', 'name' => 'Charges constatées d\'avance', 'type' => 'asset', 'parent_code' => '48'],
            ['code' => '487', 'name' => 'Produits constatés d\'avance', 'type' => 'liability', 'parent_code' => '48'],

            ['code' => '49', 'name' => 'Provisions pour dépréciation des comptes de tiers', 'type' => 'asset', 'parent_code' => null],
            ['code' => '491', 'name' => 'Provisions pour dépréciation des comptes clients', 'type' => 'asset', 'parent_code' => '49'],
            ['code' => '495', 'name' => 'Provisions pour dépréciation des débiteurs divers', 'type' => 'asset', 'parent_code' => '49'],

            // CLASSE 5 : COMPTES FINANCIERS
            ['code' => '50', 'name' => 'Valeurs mobilières de placement', 'type' => 'asset', 'parent_code' => null],
            ['code' => '501', 'name' => 'Actions', 'type' => 'asset', 'parent_code' => '50'],
            ['code' => '502', 'name' => 'Obligations', 'type' => 'asset', 'parent_code' => '50'],
            ['code' => '503', 'name' => 'Bons du Trésor', 'type' => 'asset', 'parent_code' => '50'],
            ['code' => '508', 'name' => 'Autres valeurs mobilières de placement', 'type' => 'asset', 'parent_code' => '50'],

            ['code' => '51', 'name' => 'Banques, établissements financiers et assimilés', 'type' => 'asset', 'parent_code' => null],
            ['code' => '511', 'name' => 'Valeurs à l\'encaissement', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '512', 'name' => 'Banques', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '5121', 'name' => 'Banque - Compte courant TND', 'type' => 'asset', 'parent_code' => '512'],
            ['code' => '5122', 'name' => 'Banque - Compte en devises', 'type' => 'asset', 'parent_code' => '512'],
            ['code' => '515', 'name' => 'Caisse nationale d\'épargne', 'type' => 'asset', 'parent_code' => '51'],
            ['code' => '517', 'name' => 'Autres organismes financiers', 'type' => 'asset', 'parent_code' => '51'],

            ['code' => '52', 'name' => 'Instruments de trésorerie', 'type' => 'asset', 'parent_code' => null],

            ['code' => '53', 'name' => 'Caisse', 'type' => 'asset', 'parent_code' => null],
            ['code' => '531', 'name' => 'Caisse - Espèces', 'type' => 'asset', 'parent_code' => '53'],
            ['code' => '532', 'name' => 'Caisse - Devises', 'type' => 'asset', 'parent_code' => '53'],

            ['code' => '54', 'name' => 'Régies d\'avances et accréditifs', 'type' => 'asset', 'parent_code' => null],

            ['code' => '59', 'name' => 'Provisions pour dépréciation des valeurs mobilières', 'type' => 'asset', 'parent_code' => null],

            // CLASSE 6 : COMPTES DE CHARGES
            ['code' => '60', 'name' => 'Achats', 'type' => 'expense', 'parent_code' => null],
            ['code' => '601', 'name' => 'Achats de matières premières', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '602', 'name' => 'Achats de fournitures', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '604', 'name' => 'Achats d\'études et prestations de services', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '605', 'name' => 'Achats de matériels, équipements et travaux', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '607', 'name' => 'Achats de marchandises', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '608', 'name' => 'Frais accessoires sur achats', 'type' => 'expense', 'parent_code' => '60'],
            ['code' => '609', 'name' => 'Rabais, remises et ristournes obtenus', 'type' => 'revenue', 'parent_code' => '60'],

            ['code' => '61', 'name' => 'Variation des stocks', 'type' => 'expense', 'parent_code' => null],
            ['code' => '611', 'name' => 'Variation des stocks de matières premières', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '612', 'name' => 'Variation des stocks de fournitures', 'type' => 'expense', 'parent_code' => '61'],
            ['code' => '617', 'name' => 'Variation des stocks de marchandises', 'type' => 'expense', 'parent_code' => '61'],

            ['code' => '62', 'name' => 'Autres charges externes', 'type' => 'expense', 'parent_code' => null],
            ['code' => '621', 'name' => 'Locations', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '622', 'name' => 'Redevances de crédit-bail', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '623', 'name' => 'Entretien et réparations', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '624', 'name' => 'Assurances', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '625', 'name' => 'Documentation et frais de formation', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '626', 'name' => 'Honoraires et frais d\'actes', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '627', 'name' => 'Publicité, publications, relations publiques', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '628', 'name' => 'Déplacements, missions et réceptions', 'type' => 'expense', 'parent_code' => '62'],
            ['code' => '629', 'name' => 'Services bancaires et assimilés', 'type' => 'expense', 'parent_code' => '62'],

            ['code' => '63', 'name' => 'Impôts, taxes et versements assimilés', 'type' => 'expense', 'parent_code' => null],
            ['code' => '631', 'name' => 'Taxe de formation professionnelle (TFP)', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '632', 'name' => 'FOPROLOS', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '633', 'name' => 'Taxe sur les établissements (TCL)', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '634', 'name' => 'Droits d\'enregistrement', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '635', 'name' => 'Taxes sur véhicules', 'type' => 'expense', 'parent_code' => '63'],
            ['code' => '638', 'name' => 'Autres impôts et taxes', 'type' => 'expense', 'parent_code' => '63'],

            ['code' => '64', 'name' => 'Charges de personnel', 'type' => 'expense', 'parent_code' => null],
            ['code' => '641', 'name' => 'Salaires et appointements', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '642', 'name' => 'Primes et gratifications', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '643', 'name' => 'Indemnités et avantages divers', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '644', 'name' => 'Congés payés', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '645', 'name' => 'Charges de sécurité sociale - CNSS', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '6451', 'name' => 'Cotisations CNSS - Part patronale', 'type' => 'expense', 'parent_code' => '645'],
            ['code' => '6452', 'name' => 'Cotisations CNRPS - Part patronale', 'type' => 'expense', 'parent_code' => '645'],
            ['code' => '646', 'name' => 'Charges sociales diverses', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '647', 'name' => 'Autres charges sociales', 'type' => 'expense', 'parent_code' => '64'],
            ['code' => '648', 'name' => 'Rémunérations allouées aux gérants et administrateurs', 'type' => 'expense', 'parent_code' => '64'],

            ['code' => '65', 'name' => 'Autres charges de gestion courante', 'type' => 'expense', 'parent_code' => null],
            ['code' => '651', 'name' => 'Redevances pour concessions, brevets, licences', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '652', 'name' => 'Dons et libéralités', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '653', 'name' => 'Jetons de présence', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '654', 'name' => 'Pertes sur créances irrécouvrables', 'type' => 'expense', 'parent_code' => '65'],
            ['code' => '658', 'name' => 'Autres charges de gestion', 'type' => 'expense', 'parent_code' => '65'],

            ['code' => '66', 'name' => 'Charges financières', 'type' => 'expense', 'parent_code' => null],
            ['code' => '661', 'name' => 'Charges d\'intérêts', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '662', 'name' => 'Pertes sur créances liées à des participations', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '663', 'name' => 'Pertes de change', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '664', 'name' => 'Escomptes accordés', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '665', 'name' => 'Charges nettes sur cessions de valeurs mobilières', 'type' => 'expense', 'parent_code' => '66'],
            ['code' => '668', 'name' => 'Autres charges financières', 'type' => 'expense', 'parent_code' => '66'],

            ['code' => '67', 'name' => 'Charges exceptionnelles', 'type' => 'expense', 'parent_code' => null],
            ['code' => '671', 'name' => 'Charges exceptionnelles sur opérations de gestion', 'type' => 'expense', 'parent_code' => '67'],
            ['code' => '672', 'name' => 'Valeurs comptables des immobilisations cédées', 'type' => 'expense', 'parent_code' => '67'],
            ['code' => '675', 'name' => 'Amendes et pénalités', 'type' => 'expense', 'parent_code' => '67'],
            ['code' => '678', 'name' => 'Autres charges exceptionnelles', 'type' => 'expense', 'parent_code' => '67'],

            ['code' => '68', 'name' => 'Dotations aux amortissements et provisions', 'type' => 'expense', 'parent_code' => null],
            ['code' => '681', 'name' => 'Dotations aux amortissements', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '6811', 'name' => 'Dotations aux amortissements des immobilisations incorporelles', 'type' => 'expense', 'parent_code' => '681'],
            ['code' => '6812', 'name' => 'Dotations aux amortissements des immobilisations corporelles', 'type' => 'expense', 'parent_code' => '681'],
            ['code' => '685', 'name' => 'Dotations aux provisions d\'exploitation', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '686', 'name' => 'Dotations aux provisions financières', 'type' => 'expense', 'parent_code' => '68'],
            ['code' => '687', 'name' => 'Dotations aux provisions exceptionnelles', 'type' => 'expense', 'parent_code' => '68'],

            ['code' => '69', 'name' => 'Impôts sur les bénéfices', 'type' => 'expense', 'parent_code' => null],
            ['code' => '691', 'name' => 'Impôt sur les sociétés (IS)', 'type' => 'expense', 'parent_code' => '69'],
            ['code' => '695', 'name' => 'Impôt minimum forfaitaire', 'type' => 'expense', 'parent_code' => '69'],

            // CLASSE 7 : COMPTES DE PRODUITS
            ['code' => '70', 'name' => 'Ventes de produits et marchandises', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '701', 'name' => 'Ventes de produits finis', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '702', 'name' => 'Ventes de produits intermédiaires', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '703', 'name' => 'Ventes de produits résiduels', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '704', 'name' => 'Ventes de travaux', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '705', 'name' => 'Ventes d\'études', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '706', 'name' => 'Ventes de prestations de services', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '707', 'name' => 'Ventes de marchandises', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '708', 'name' => 'Produits des activités annexes', 'type' => 'revenue', 'parent_code' => '70'],
            ['code' => '709', 'name' => 'Rabais, remises et ristournes accordés', 'type' => 'expense', 'parent_code' => '70'],

            ['code' => '71', 'name' => 'Production stockée ou déstockée', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '711', 'name' => 'Variation des stocks de produits en cours', 'type' => 'revenue', 'parent_code' => '71'],
            ['code' => '712', 'name' => 'Variation des stocks de biens produits', 'type' => 'revenue', 'parent_code' => '71'],
            ['code' => '713', 'name' => 'Variation des stocks de services en cours', 'type' => 'revenue', 'parent_code' => '71'],

            ['code' => '72', 'name' => 'Production immobilisée', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '721', 'name' => 'Production immobilisée - Immobilisations incorporelles', 'type' => 'revenue', 'parent_code' => '72'],
            ['code' => '722', 'name' => 'Production immobilisée - Immobilisations corporelles', 'type' => 'revenue', 'parent_code' => '72'],

            ['code' => '74', 'name' => 'Subventions d\'exploitation', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '741', 'name' => 'Subventions d\'équilibre', 'type' => 'revenue', 'parent_code' => '74'],
            ['code' => '748', 'name' => 'Autres subventions d\'exploitation', 'type' => 'revenue', 'parent_code' => '74'],

            ['code' => '75', 'name' => 'Autres produits de gestion courante', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '751', 'name' => 'Redevances pour concessions, brevets, licences', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '752', 'name' => 'Revenus des immeubles non affectés aux activités professionnelles', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '754', 'name' => 'Ristournes perçues des coopératives', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '755', 'name' => 'Quotes-parts de résultat sur opérations faites en commun', 'type' => 'revenue', 'parent_code' => '75'],
            ['code' => '758', 'name' => 'Autres produits divers de gestion courante', 'type' => 'revenue', 'parent_code' => '75'],

            ['code' => '76', 'name' => 'Produits financiers', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '761', 'name' => 'Produits de participations', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '762', 'name' => 'Produits des autres immobilisations financières', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '763', 'name' => 'Revenus des autres créances', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '764', 'name' => 'Revenus des valeurs mobilières de placement', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '765', 'name' => 'Escomptes obtenus', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '766', 'name' => 'Gains de change', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '767', 'name' => 'Produits nets sur cessions de valeurs mobilières', 'type' => 'revenue', 'parent_code' => '76'],
            ['code' => '768', 'name' => 'Autres produits financiers', 'type' => 'revenue', 'parent_code' => '76'],

            ['code' => '77', 'name' => 'Produits exceptionnels', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '771', 'name' => 'Produits exceptionnels sur opérations de gestion', 'type' => 'revenue', 'parent_code' => '77'],
            ['code' => '772', 'name' => 'Produits de cessions d\'immobilisations', 'type' => 'revenue', 'parent_code' => '77'],
            ['code' => '773', 'name' => 'Subventions d\'investissement virées au résultat', 'type' => 'revenue', 'parent_code' => '77'],
            ['code' => '778', 'name' => 'Autres produits exceptionnels', 'type' => 'revenue', 'parent_code' => '77'],

            ['code' => '78', 'name' => 'Reprises sur amortissements et provisions', 'type' => 'revenue', 'parent_code' => null],
            ['code' => '781', 'name' => 'Reprises sur amortissements', 'type' => 'revenue', 'parent_code' => '78'],
            ['code' => '785', 'name' => 'Reprises sur provisions d\'exploitation', 'type' => 'revenue', 'parent_code' => '78'],
            ['code' => '786', 'name' => 'Reprises sur provisions financières', 'type' => 'revenue', 'parent_code' => '78'],
            ['code' => '787', 'name' => 'Reprises sur provisions exceptionnelles', 'type' => 'revenue', 'parent_code' => '78'],

            // CLASSE 8 : COMPTES DE RÉSULTATS
            ['code' => '80', 'name' => 'Résultats d\'exploitation', 'type' => 'equity', 'parent_code' => null],
            ['code' => '86', 'name' => 'Résultat financier', 'type' => 'equity', 'parent_code' => null],
            ['code' => '88', 'name' => 'Résultat extraordinaire', 'type' => 'equity', 'parent_code' => null],
            ['code' => '89', 'name' => 'Résultat net de l\'exercice', 'type' => 'equity', 'parent_code' => null],

            // CLASSE 9 : COMPTES ANALYTIQUES (Optionnel)
            ['code' => '90', 'name' => 'Comptes de reclassement', 'type' => 'equity', 'parent_code' => null],
            ['code' => '91', 'name' => 'Charges indirectes', 'type' => 'expense', 'parent_code' => null],
            ['code' => '92', 'name' => 'Sections analytiques', 'type' => 'expense', 'parent_code' => null],
            ['code' => '93', 'name' => 'Coûts de production', 'type' => 'expense', 'parent_code' => null],
            ['code' => '94', 'name' => 'Coûts de distribution', 'type' => 'expense', 'parent_code' => null],
            ['code' => '95', 'name' => 'Coûts hors exploitation', 'type' => 'expense', 'parent_code' => null],
            ['code' => '97', 'name' => 'Différences sur niveau d\'activité', 'type' => 'expense', 'parent_code' => null],
            ['code' => '98', 'name' => 'Résultats analytiques', 'type' => 'equity', 'parent_code' => null],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')->insert([
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'parent_code' => $account['parent_code'],
                'country_code' => 'TN',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

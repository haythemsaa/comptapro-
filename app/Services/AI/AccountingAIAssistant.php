<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Assistant IA pour la Comptabilité Tunisienne
 *
 * Fonctionnalités:
 * - Conseils comptables personnalisés
 * - Optimisation fiscale (conforme à la loi tunisienne)
 * - Réponses aux questions comptables et fiscales
 * - Recommandations basées sur l'analyse des données
 * - Support en français et arabe
 */
class AccountingAIAssistant
{
    private string $apiKey;
    private array $context = [];

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
    }

    /**
     * Pose une question à l'assistant IA
     */
    public function ask(string $question, array $context = []): array
    {
        try {
            $this->context = $context;

            // Construire le prompt avec contexte tunisien
            $systemPrompt = $this->buildSystemPrompt();
            $userPrompt = $this->buildUserPrompt($question);

            // Appel à Claude API
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-sonnet-20240229',
                    'max_tokens' => 4096,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $answer = $result['content'][0]['text'] ?? '';

                return [
                    'success' => true,
                    'answer' => $answer,
                    'model' => $result['model'] ?? '',
                    'usage' => $result['usage'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => 'API request failed',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('AI Assistant error', [
                'question' => $question,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyse les finances de l'entreprise et donne des recommandations
     */
    public function analyzeFinances(array $financialData): array
    {
        $prompt = $this->buildFinancialAnalysisPrompt($financialData);

        return $this->ask($prompt, $financialData);
    }

    /**
     * Suggère des optimisations fiscales
     */
    public function suggestTaxOptimizations(array $companyData): array
    {
        $prompt = <<<PROMPT
Analyse cette entreprise tunisienne et suggère des optimisations fiscales légales:

**Données de l'entreprise:**
- Chiffre d'affaires annuel: {$companyData['annual_revenue']} TND
- Secteur d'activité: {$companyData['sector']}
- Forme juridique: {$companyData['legal_form']}
- Nombre d'employés: {$companyData['employees_count']}
- Régime fiscal actuel: {$companyData['tax_regime']}

**Charges actuelles:**
- Impôt sur les sociétés: {$companyData['corporate_tax']} TND
- TVA moyenne mensuelle: {$companyData['avg_monthly_vat']} TND
- Charges sociales: {$companyData['social_charges']} TND

Fournis des recommandations concrètes pour:
1. Optimiser l'impôt sur les sociétés
2. Mieux gérer la TVA
3. Profiter des avantages fiscaux disponibles en Tunisie
4. Réduire les charges sociales légalement
5. Investissements déductibles recommandés

Format: Liste numérotée avec explications claires.
PROMPT;

        return $this->ask($prompt, $companyData);
    }

    /**
     * Aide à la catégorisation comptable automatique
     */
    public function categorizeTransaction(array $transaction): array
    {
        $cacheKey = 'ai_categorize_' . md5(json_encode($transaction));

        return Cache::remember($cacheKey, 3600, function () use ($transaction) {
            $prompt = <<<PROMPT
Catégorise cette transaction comptable selon le Plan Comptable Normalisé tunisien (PCN):

**Transaction:**
- Description: {$transaction['description']}
- Montant: {$transaction['amount']} TND
- Type: {$transaction['type']}
- Fournisseur/Client: {$transaction['party_name']}

Réponds au format JSON:
{
  "account_code": "Code du compte PCN (ex: 607 pour achats de marchandises)",
  "account_name": "Nom du compte",
  "confidence": 0.95,
  "reasoning": "Explication courte du choix"
}
PROMPT;

            $result = $this->ask($prompt, $transaction);

            if ($result['success']) {
                // Parser la réponse JSON
                $jsonMatch = [];
                if (preg_match('/\{[^}]+\}/', $result['answer'], $jsonMatch)) {
                    $categorization = json_decode($jsonMatch[0], true);
                    return [
                        'success' => true,
                        'categorization' => $categorization,
                    ];
                }
            }

            return $result;
        });
    }

    /**
     * Vérifie la conformité d'une opération comptable
     */
    public function checkCompliance(array $operation): array
    {
        $prompt = <<<PROMPT
Vérifie la conformité de cette opération comptable selon la législation tunisienne:

**Opération:**
- Type: {$operation['type']}
- Montant: {$operation['amount']} TND
- Date: {$operation['date']}
- Description: {$operation['description']}

**Éléments à vérifier:**
1. Respect du Plan Comptable Normalisé (PCN)
2. Traitement TVA correct
3. Conformité avec le Code de l'IRPP et de l'IS
4. Respect des obligations de facturation (El Fatoora si applicable)
5. Délais légaux respectés

Réponds:
- ✅ Conforme / ⚠️ Attention / ❌ Non conforme
- Points de non-conformité éventuels
- Recommandations correctives
PROMPT;

        return $this->ask($prompt, $operation);
    }

    /**
     * Explique un concept comptable ou fiscal
     */
    public function explainConcept(string $concept): array
    {
        $prompt = <<<PROMPT
Explique ce concept comptable ou fiscal dans le contexte tunisien de manière simple et claire:

**Concept:** {$concept}

Inclus:
1. Définition simple
2. Exemple concret en Tunisie
3. Réglementation applicable
4. Impact pratique pour une entreprise
5. Bonnes pratiques

Utilise un langage accessible, comme si tu expliquais à un entrepreneur sans formation comptable.
PROMPT;

        return $this->ask($prompt);
    }

    /**
     * Génère un rapport d'analyse financière narratif
     */
    public function generateFinancialNarrative(array $financialMetrics): array
    {
        $prompt = $this->buildNarrativePrompt($financialMetrics);

        return $this->ask($prompt, $financialMetrics);
    }

    /**
     * Construit le prompt système avec expertise tunisienne
     */
    private function buildSystemPrompt(): string
    {
        return <<<SYSTEM
Tu es un expert-comptable tunisien spécialisé dans:
- Le Plan Comptable Normalisé (PCN) tunisien
- Le Code de l'IRPP (Impôt sur le Revenu des Personnes Physiques)
- Le Code de l'IS (Impôt sur les Sociétés)
- La législation TVA tunisienne (taux 19%, 13%, 7%)
- Le Code du Travail tunisien et les cotisations CNSS
- Les déclarations fiscales (TEIF, télédéclaration)
- Le système El Fatoora (facturation électronique)

**Principes:**
1. Toujours respecter la législation tunisienne en vigueur
2. Fournir des conseils pratiques et applicables
3. Citer les textes de loi quand pertinent
4. Expliquer clairement les implications fiscales
5. JAMAIS suggérer de fraude ou d'évasion fiscale
6. Privilégier l'optimisation légale et éthique

**Taux et informations clés (2024):**
- TVA standard: 19%
- TVA réduite: 13% et 7%
- IS: 15% (exportateurs/tech), 25% (général), 35% (certains secteurs)
- CNSS employé: 9.18%
- CNSS employeur: 16.57%
- TFP: 2% (industrie) ou 1% (autres)
- Barème IRPP progressif: 0%, 26%, 28%, 32%, 35%

Fournis des réponses claires, structurées et professionnelles.
SYSTEM;
    }

    /**
     * Construit le prompt utilisateur avec contexte
     */
    private function buildUserPrompt(string $question): string
    {
        if (empty($this->context)) {
            return $question;
        }

        $contextStr = "**Contexte:**\n";
        foreach ($this->context as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
            $contextStr .= "- {$key}: {$value}\n";
        }

        return $contextStr . "\n**Question:**\n" . $question;
    }

    /**
     * Construit le prompt d'analyse financière
     */
    private function buildFinancialAnalysisPrompt(array $data): string
    {
        return <<<PROMPT
Analyse ces données financières et fournis un rapport complet:

**Résultats:**
- Chiffre d'affaires: {$data['revenue']} TND
- Charges d'exploitation: {$data['expenses']} TND
- Résultat d'exploitation: {$data['operating_profit']} TND
- Résultat net: {$data['net_profit']} TND

**Ratios:**
- Marge brute: {$data['gross_margin']}%
- Marge nette: {$data['net_margin']}%

**Trésorerie:**
- Solde actuel: {$data['cash_balance']} TND
- Créances clients: {$data['receivables']} TND
- Dettes fournisseurs: {$data['payables']} TND

**Analyse demandée:**
1. Santé financière globale
2. Points forts et points faibles
3. Risques identifiés
4. Recommandations d'amélioration
5. Opportunités de croissance
6. Optimisations possibles

Format: Rapport structuré avec sections claires.
PROMPT;
    }

    /**
     * Construit le prompt pour génération de narrative
     */
    private function buildNarrativePrompt(array $metrics): string
    {
        return <<<PROMPT
Rédige un rapport narratif professionnel analysant ces métriques financières:

{$this->formatMetrics($metrics)}

**Style:**
- Professionnel mais accessible
- Utilise des transitions fluides
- Inclus des insights actionnables
- Met en évidence les tendances importantes
- Fournis du contexte économique tunisien si pertinent

**Structure:**
1. Résumé exécutif
2. Performance opérationnelle
3. Situation financière
4. Analyse des risques
5. Recommandations stratégiques
PROMPT;
    }

    /**
     * Formate les métriques pour le prompt
     */
    private function formatMetrics(array $metrics): string
    {
        $formatted = "**Métriques:**\n";

        foreach ($metrics as $key => $value) {
            $label = $this->humanizeKey($key);

            if (is_numeric($value)) {
                $formatted .= "- {$label}: " . number_format($value, 2, '.', ' ') . " TND\n";
            } elseif (is_array($value)) {
                $formatted .= "- {$label}: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                $formatted .= "- {$label}: {$value}\n";
            }
        }

        return $formatted;
    }

    /**
     * Humanise une clé pour affichage
     */
    private function humanizeKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Détecte la langue de la question
     */
    private function detectLanguage(string $text): string
    {
        // Détection simple arabe vs français
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }

        return 'fr';
    }

    /**
     * Suggestions de questions fréquentes
     */
    public function getSuggestedQuestions(): array
    {
        return [
            'Comment optimiser ma TVA en Tunisie?',
            'Quels sont les avantages fiscaux pour les exportateurs?',
            'Comment calculer les cotisations CNSS?',
            'Quelle est la différence entre IRPP et IS?',
            'Comment fonctionne le système El Fatoora?',
            'Quelles charges sont déductibles de l\'impôt?',
            'Comment déclarer mes employés à la CNSS?',
            'Quel régime fiscal choisir pour ma startup?',
            'Comment gérer la retenue à la source?',
            'Quels sont les délais de déclaration fiscale?',
        ];
    }
}

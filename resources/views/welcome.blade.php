<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ComptaPro - Solution Comptable Multi-Pays</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .feature-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    {{-- Hero Section --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animate__animated animate__fadeInLeft">
                    <h1 class="display-3 fw-bold mb-4">ComptaPro</h1>
                    <p class="lead mb-4">
                        Solution comptable intelligente pour la Tunisie 🇹🇳 et la Belgique 🇧🇪
                    </p>
                    <ul class="list-unstyled mb-4 fs-5">
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Plan Comptable Normalisé (PCN & PCMN)</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Gestion de la paie automatisée</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Déclarations fiscales (TVA, IS)</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> IA pour comptabilisation automatique</li>
                    </ul>
                    <div class="d-flex gap-3">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-lg">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard Admin
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">
                            En savoir plus
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center animate__animated animate__fadeInRight">
                    <i class="bi bi-calculator display-1" style="font-size: 15rem; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Fonctionnalités</h2>
                <p class="text-muted">Une plateforme complète pour gérer votre comptabilité</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto">
                                <i class="bi bi-list-ul"></i>
                            </div>
                            <h4>Plans Comptables</h4>
                            <p class="text-muted">
                                PCN tunisien (300+ comptes) et PCMN belge (400+ comptes) avec support multilingue
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-success bg-opacity-10 text-success mx-auto">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <h4>Gestion de la Paie</h4>
                            <p class="text-muted">
                                Calculs automatiques CNSS/IRPP (TN) et ONSS/Précompte (BE) avec génération de bulletins
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-info bg-opacity-10 text-info mx-auto">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <h4>Déclarations Fiscales</h4>
                            <p class="text-muted">
                                TVA et IS pour TN et BE, avec exports El Fatoora, Intervat et Biztax
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-warning bg-opacity-10 text-warning mx-auto">
                                <i class="bi bi-robot"></i>
                            </div>
                            <h4>IA Intégrée</h4>
                            <p class="text-muted">
                                4 modules IA pour comptabilisation automatique avec 97%+ de précision
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-danger bg-opacity-10 text-danger mx-auto">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h4>Rapports Financiers</h4>
                            <p class="text-muted">
                                Bilan, compte de résultat, balance générale et grand livre en temps réel
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-dark bg-opacity-10 text-dark mx-auto">
                                <i class="bi bi-building"></i>
                            </div>
                            <h4>Multi-Entreprises</h4>
                            <p class="text-muted">
                                Gérez plusieurs entreprises depuis une seule plateforme avec isolation complète
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Countries Section --}}
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Pays Supportés</h2>
                <p class="text-muted">Conformité totale avec les réglementations locales</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <div class="display-1 mb-3">🇹🇳</div>
                            <h3>Tunisie</h3>
                            <ul class="list-unstyled mt-3">
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> PCN (7 classes)</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> TVA: 19%, 13%, 7%, 0%</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> CNSS: 9.18% + 16.57%</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> IRPP progressif</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> El Fatoora (e-facture)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border-info">
                        <div class="card-body text-center">
                            <div class="display-1 mb-3">🇧🇪</div>
                            <h3>Belgique</h3>
                            <ul class="list-unstyled mt-3">
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> PCMN (8 classes)</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> TVA: 21%, 12%, 6%, 0%</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> ONSS: 13.07% + 27%</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Précompte professionnel</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i> Intervat & Biztax</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 ComptaPro. Solution comptable multi-pays avec IA intégrée.</p>
            <p class="text-muted small mt-2">Tunisia 🇹🇳 | Belgium 🇧🇪</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

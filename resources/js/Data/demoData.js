/**
 * Données de démonstration pour ComptaPro
 * Données réalistes pour tester l'application immédiatement
 */

export const demoCustomers = [
    {
        id: 1,
        name: 'Acme Corporation',
        customer_number: 'C-2024-001',
        email: 'contact@acme-corp.fr',
        phone: '+33 1 23 45 67 89',
        address: '123 Avenue des Champs-Élysées',
        city: 'Paris',
        postal_code: '75008',
        country: 'France',
        siret: '12345678901234',
        vat_number: 'FR12345678901',
        is_active: true,
        invoices_count: 15,
        total_revenue: 125000,
        created_at: '2024-01-15'
    },
    {
        id: 2,
        name: 'TechStart SAS',
        customer_number: 'C-2024-002',
        email: 'hello@techstart.io',
        phone: '+33 6 12 34 56 78',
        address: '45 Rue de la Tech',
        city: 'Lyon',
        postal_code: '69002',
        country: 'France',
        siret: '98765432109876',
        vat_number: 'FR98765432109',
        is_active: true,
        invoices_count: 8,
        total_revenue: 68500,
        created_at: '2024-02-20'
    },
    {
        id: 3,
        name: 'Digital Agency Pro',
        customer_number: 'C-2024-003',
        email: 'contact@digitalagency.com',
        phone: '+33 4 56 78 90 12',
        address: '78 Boulevard Innovation',
        city: 'Marseille',
        postal_code: '13001',
        country: 'France',
        siret: '11223344556677',
        vat_number: 'FR11223344556',
        is_active: true,
        invoices_count: 22,
        total_revenue: 198000,
        created_at: '2023-11-10'
    },
    {
        id: 4,
        name: 'Solutions Innovantes SARL',
        customer_number: 'C-2024-004',
        email: 'info@solutions-innov.fr',
        phone: '+33 2 34 56 78 90',
        address: '12 Rue des Solutions',
        city: 'Nantes',
        postal_code: '44000',
        country: 'France',
        siret: '55667788990011',
        vat_number: 'FR55667788990',
        is_active: true,
        invoices_count: 5,
        total_revenue: 32500,
        created_at: '2024-05-08'
    },
    {
        id: 5,
        name: 'Commerce en Ligne SA',
        customer_number: 'C-2024-005',
        email: 'contact@commercenligne.fr',
        phone: '+33 5 67 89 01 23',
        address: '89 Avenue E-Commerce',
        city: 'Bordeaux',
        postal_code: '33000',
        country: 'France',
        siret: '99887766554433',
        vat_number: 'FR99887766554',
        is_active: true,
        invoices_count: 12,
        total_revenue: 89700,
        created_at: '2024-03-12'
    }
];

export const demoInvoices = [
    {
        id: 1,
        number: 'FA-2024-001',
        type: 'invoice',
        status: 'paid',
        customer_id: 1,
        customer_name: 'Acme Corporation',
        issue_date: '2024-10-01',
        due_date: '2024-10-31',
        amount_ht: 10000,
        amount_tva: 2000,
        amount_ttc: 12000,
        paid_amount: 12000,
        payment_date: '2024-10-25'
    },
    {
        id: 2,
        number: 'FA-2024-002',
        type: 'invoice',
        status: 'paid',
        customer_id: 2,
        customer_name: 'TechStart SAS',
        issue_date: '2024-10-05',
        due_date: '2024-11-04',
        amount_ht: 7500,
        amount_tva: 1500,
        amount_ttc: 9000,
        paid_amount: 9000,
        payment_date: '2024-10-30'
    },
    {
        id: 3,
        number: 'FA-2024-003',
        type: 'invoice',
        status: 'pending',
        customer_id: 3,
        customer_name: 'Digital Agency Pro',
        issue_date: '2024-10-15',
        due_date: '2024-11-14',
        amount_ht: 15000,
        amount_tva: 3000,
        amount_ttc: 18000,
        paid_amount: 0
    },
    {
        id: 4,
        number: 'FA-2024-004',
        type: 'invoice',
        status: 'overdue',
        customer_id: 1,
        customer_name: 'Acme Corporation',
        issue_date: '2024-09-20',
        due_date: '2024-10-20',
        amount_ht: 5000,
        amount_tva: 1000,
        amount_ttc: 6000,
        paid_amount: 0
    },
    {
        id: 5,
        number: 'FA-2024-005',
        type: 'invoice',
        status: 'overdue',
        customer_id: 4,
        customer_name: 'Solutions Innovantes SARL',
        issue_date: '2024-09-15',
        due_date: '2024-10-15',
        amount_ht: 3500,
        amount_tva: 700,
        amount_ttc: 4200,
        paid_amount: 0
    },
    {
        id: 6,
        number: 'DE-2024-001',
        type: 'quote',
        status: 'draft',
        customer_id: 5,
        customer_name: 'Commerce en Ligne SA',
        issue_date: '2024-11-10',
        due_date: '2024-12-10',
        amount_ht: 8500,
        amount_tva: 1700,
        amount_ttc: 10200,
        paid_amount: 0
    },
    {
        id: 7,
        number: 'FA-2024-006',
        type: 'invoice',
        status: 'pending',
        customer_id: 2,
        customer_name: 'TechStart SAS',
        issue_date: '2024-11-01',
        due_date: '2024-12-01',
        amount_ht: 12000,
        amount_tva: 2400,
        amount_ttc: 14400,
        paid_amount: 0
    },
    {
        id: 8,
        number: 'FA-2024-007',
        type: 'invoice',
        status: 'overdue',
        customer_id: 3,
        customer_name: 'Digital Agency Pro',
        issue_date: '2024-09-05',
        due_date: '2024-10-05',
        amount_ht: 9500,
        amount_tva: 1900,
        amount_ttc: 11400,
        paid_amount: 0
    }
];

export const demoProducts = [
    {
        id: 1,
        name: 'Licence Logiciel Pro',
        reference: 'LIC-PRO-001',
        description: 'Licence annuelle logiciel professionnel',
        price_ht: 299,
        tva_rate: 20,
        category: 'Logiciels',
        stock: null, // Service
        is_active: true
    },
    {
        id: 2,
        name: 'Support Premium',
        reference: 'SUP-PREM-001',
        description: 'Support technique premium 24/7',
        price_ht: 99,
        tva_rate: 20,
        category: 'Services',
        stock: null,
        is_active: true
    },
    {
        id: 3,
        name: 'Formation en ligne',
        reference: 'FORM-ONLINE-001',
        description: 'Formation complète en ligne (10h)',
        price_ht: 450,
        tva_rate: 20,
        category: 'Formation',
        stock: null,
        is_active: true
    },
    {
        id: 4,
        name: 'Consultation Expert',
        reference: 'CONS-EXP-001',
        description: 'Consultation expert comptable (1h)',
        price_ht: 150,
        tva_rate: 20,
        category: 'Conseil',
        stock: null,
        is_active: true
    },
    {
        id: 5,
        name: 'Audit Comptable',
        reference: 'AUD-COMPT-001',
        description: 'Audit comptable complet',
        price_ht: 2500,
        tva_rate: 20,
        category: 'Conseil',
        stock: null,
        is_active: true
    }
];

export const dashboardStats = {
    revenue: {
        current: 427500,
        previous: 385200,
        trend: 11
    },
    invoices: {
        total: 124,
        paid: 95,
        pending: 21,
        overdue: 8
    },
    customers: {
        total: 48,
        active: 42,
        new_this_month: 5
    },
    products: {
        total: 32,
        services: 18,
        goods: 14
    }
};

export const revenueData = {
    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
    datasets: [{
        label: 'Chiffre d\'affaires 2024',
        data: [28500, 32000, 29500, 35000, 38500, 42000, 41500, 39000, 43500, 45000, 48500, 52000],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4
    }, {
        label: 'Chiffre d\'affaires 2023',
        data: [25000, 28000, 27500, 30000, 32500, 35000, 34500, 36000, 38500, 39000, 41000, 43000],
        borderColor: '#9ca3af',
        backgroundColor: 'rgba(156, 163, 175, 0.05)',
        fill: true,
        tension: 0.4
    }]
};

export const invoiceTypeData = {
    labels: ['Factures', 'Devis', 'Avoirs'],
    datasets: [{
        data: [95, 21, 8],
        backgroundColor: [
            '#10b981',
            '#f59e0b',
            '#ef4444'
        ],
        borderWidth: 0
    }]
};

export const paymentStatusData = {
    labels: ['Payées', 'En attente', 'En retard'],
    datasets: [{
        data: [95, 21, 8],
        backgroundColor: [
            '#10b981',
            '#f59e0b',
            '#ef4444'
        ]
    }]
};

export const recentActivities = [
    {
        id: 1,
        type: 'invoice_paid',
        title: 'Facture payée',
        description: 'FA-2024-125 - Acme Corporation - 12 500 €',
        timestamp: Date.now() - 3600000 * 2,
        icon: 'bi-check-circle-fill',
        color: 'success'
    },
    {
        id: 2,
        type: 'customer_added',
        title: 'Nouveau client',
        description: 'TechStart SAS ajouté',
        timestamp: Date.now() - 3600000 * 5,
        icon: 'bi-person-plus-fill',
        color: 'info'
    },
    {
        id: 3,
        type: 'invoice_sent',
        title: 'Facture envoyée',
        description: 'FA-2024-126 - Digital Agency Pro - 18 000 €',
        timestamp: Date.now() - 86400000,
        icon: 'bi-send-fill',
        color: 'primary'
    },
    {
        id: 4,
        type: 'payment_reminder',
        title: 'Relance envoyée',
        description: 'FA-2024-120 - Solutions Innovantes - 4 200 €',
        timestamp: Date.now() - 86400000 * 2,
        icon: 'bi-bell-fill',
        color: 'warning'
    },
    {
        id: 5,
        type: 'quote_created',
        title: 'Devis créé',
        description: 'DE-2024-045 - Commerce en Ligne - 10 200 €',
        timestamp: Date.now() - 86400000 * 3,
        icon: 'bi-file-earmark-text-fill',
        color: 'info'
    }
];

export const upcomingDeadlines = [
    {
        id: 1,
        title: 'Déclaration TVA',
        date: new Date(Date.now() + 86400000 * 5),
        type: 'tax',
        priority: 'high'
    },
    {
        id: 2,
        title: 'Facture FA-2024-123 échue',
        date: new Date(Date.now() + 86400000 * 3),
        type: 'invoice',
        priority: 'medium'
    },
    {
        id: 3,
        title: 'Bilan comptable T4',
        date: new Date(Date.now() + 86400000 * 15),
        type: 'accounting',
        priority: 'high'
    },
    {
        id: 4,
        title: 'Renouvellement abonnement',
        date: new Date(Date.now() + 86400000 * 30),
        type: 'subscription',
        priority: 'low'
    }
];

export default {
    demoCustomers,
    demoInvoices,
    demoProducts,
    dashboardStats,
    revenueData,
    invoiceTypeData,
    paymentStatusData,
    recentActivities,
    upcomingDeadlines
};

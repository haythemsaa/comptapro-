<template>
    <Teleport to="body">
        <div class="notifications-center">
            <!-- Notifications List -->
            <TransitionGroup name="notification-list" tag="div" class="notifications-container">
                <div
                    v-for="notification in visibleNotifications"
                    :key="notification.id"
                    :class="['notification-card', `notification-${notification.type}`, { 'notification-unread': !notification.read }]"
                    @click="markAsRead(notification)"
                >
                    <div class="notification-icon">
                        <i :class="getIcon(notification.type)"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">{{ notification.title }}</div>
                        <div class="notification-message">{{ notification.message }}</div>
                        <div class="notification-time">{{ formatTime(notification.timestamp) }}</div>
                    </div>
                    <div class="notification-actions">
                        <button
                            v-if="notification.action"
                            @click.stop="handleAction(notification)"
                            class="btn-action"
                        >
                            {{ notification.actionLabel || 'Voir' }}
                        </button>
                        <button @click.stop="dismiss(notification.id)" class="btn-dismiss">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </TransitionGroup>

            <!-- Empty State -->
            <div v-if="visibleNotifications.length === 0" class="notifications-empty">
                <i class="bi bi-bell-slash"></i>
                <p>Aucune notification</p>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    maxVisible: {
        type: Number,
        default: 5
    },
    autoCleanRead: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['notification-click', 'notification-read', 'notification-dismiss']);

const notifications = ref([
    {
        id: 1,
        type: 'warning',
        title: 'Factures en retard',
        message: '5 factures impayées dépassent leur échéance',
        timestamp: Date.now() - 3600000 * 2,
        read: false,
        action: () => router.visit(route('invoices.index', { status: 'overdue' })),
        actionLabel: 'Voir les factures'
    },
    {
        id: 2,
        type: 'success',
        title: 'Paiement reçu',
        message: 'Facture FA-2024-123 payée - 1 250,00 €',
        timestamp: Date.now() - 86400000,
        read: false,
        action: () => router.visit(route('invoices.show', 123)),
        actionLabel: 'Détails'
    },
    {
        id: 3,
        type: 'info',
        title: 'Nouveau client',
        message: 'TechStart SAS a été ajouté à vos clients',
        timestamp: Date.now() - 86400000 * 3,
        read: true,
        action: () => router.visit(route('customers.index')),
        actionLabel: 'Voir'
    },
    {
        id: 4,
        type: 'danger',
        title: 'Déclaration TVA',
        message: 'La déclaration TVA du mois dernier est à faire avant le 20/11',
        timestamp: Date.now() - 86400000 * 5,
        read: false,
        action: () => router.visit(route('reports.vat')),
        actionLabel: 'Déclarer'
    },
    {
        id: 5,
        type: 'info',
        title: 'Sauvegarde automatique',
        message: 'Vos données ont été sauvegardées avec succès',
        timestamp: Date.now() - 86400000 * 7,
        read: true
    }
]);

const visibleNotifications = computed(() => {
    let items = props.autoCleanRead
        ? notifications.value.filter(n => !n.read)
        : notifications.value;

    return items.slice(0, props.maxVisible);
});

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
});

const getIcon = (type) => {
    const icons = {
        success: 'bi bi-check-circle-fill',
        error: 'bi bi-x-circle-fill',
        warning: 'bi bi-exclamation-triangle-fill',
        info: 'bi bi-info-circle-fill',
        danger: 'bi bi-exclamation-octagon-fill'
    };
    return icons[type] || icons.info;
};

const formatTime = (timestamp) => {
    const now = Date.now();
    const diff = now - timestamp;

    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'À l\'instant';
    if (minutes < 60) return `Il y a ${minutes} min`;
    if (hours < 24) return `Il y a ${hours}h`;
    if (days < 7) return `Il y a ${days}j`;

    return new Date(timestamp).toLocaleDateString('fr-FR');
};

const markAsRead = (notification) => {
    notification.read = true;
    emit('notification-read', notification);

    if (notification.action) {
        handleAction(notification);
    }
};

const handleAction = (notification) => {
    emit('notification-click', notification);

    if (typeof notification.action === 'function') {
        notification.action();
    }
};

const dismiss = (id) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
        const notification = notifications.value[index];
        notifications.value.splice(index, 1);
        emit('notification-dismiss', notification);
    }
};

const addNotification = (notification) => {
    notifications.value.unshift({
        id: Date.now(),
        timestamp: Date.now(),
        read: false,
        ...notification
    });
};

const clearAll = () => {
    notifications.value = [];
};

const markAllAsRead = () => {
    notifications.value.forEach(n => n.read = true);
};

defineExpose({
    addNotification,
    clearAll,
    markAllAsRead,
    unreadCount
});
</script>

<style scoped>
.notifications-center {
    position: fixed;
    top: 80px;
    right: 24px;
    width: 420px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
    z-index: 1000;
    pointer-events: none;
}

.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: auto;
}

.notification-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    display: flex;
    gap: 14px;
    cursor: pointer;
    transition: all 0.3s;
    border-left: 4px solid #3b82f6;
}

.notification-card:hover {
    transform: translateX(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.notification-unread {
    background: linear-gradient(90deg, #eff6ff 0%, #fff 100%);
    font-weight: 600;
}

.notification-success {
    border-left-color: #10b981;
}

.notification-error,
.notification-danger {
    border-left-color: #ef4444;
}

.notification-warning {
    border-left-color: #f59e0b;
}

.notification-info {
    border-left-color: #3b82f6;
}

.notification-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.notification-success .notification-icon {
    background: #d1fae5;
    color: #065f46;
}

.notification-error .notification-icon,
.notification-danger .notification-icon {
    background: #fee2e2;
    color: #991b1b;
}

.notification-warning .notification-icon {
    background: #fef3c7;
    color: #92400e;
}

.notification-info .notification-icon {
    background: #dbeafe;
    color: #1e40af;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.notification-message {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 6px;
    line-height: 1.4;
}

.notification-time {
    font-size: 12px;
    color: #9ca3af;
}

.notification-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex-shrink: 0;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 6px;
    border: none;
    background: #3b82f6;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-action:hover {
    background: #2563eb;
    transform: scale(1.05);
}

.btn-dismiss {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 16px;
}

.btn-dismiss:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.notifications-empty {
    text-align: center;
    padding: 40px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    color: #9ca3af;
}

.notifications-empty i {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
}

.notifications-empty p {
    margin: 0;
    font-size: 14px;
}

/* Transitions */
.notification-list-enter-active,
.notification-list-leave-active {
    transition: all 0.3s;
}

.notification-list-enter-from {
    opacity: 0;
    transform: translateX(100px);
}

.notification-list-leave-to {
    opacity: 0;
    transform: translateX(-100px);
}

.notification-list-move {
    transition: transform 0.3s;
}

/* Scrollbar */
.notifications-center::-webkit-scrollbar {
    width: 8px;
}

.notifications-center::-webkit-scrollbar-track {
    background: transparent;
}

.notifications-center::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.notifications-center::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Dark Mode */
:global(.dark) .notification-card {
    background: #1f2937;
}

:global(.dark) .notification-unread {
    background: linear-gradient(90deg, #1e3a8a 0%, #1f2937 100%);
}

:global(.dark) .notification-title {
    color: #f3f4f6;
}

:global(.dark) .notification-message {
    color: #d1d5db;
}

:global(.dark) .notification-time {
    color: #9ca3af;
}

:global(.dark) .btn-dismiss {
    background: #374151;
    color: #d1d5db;
}

:global(.dark) .btn-dismiss:hover {
    background: #4b5563;
}

:global(.dark) .notifications-empty {
    background: #1f2937;
}

/* Responsive */
@media (max-width: 768px) {
    .notifications-center {
        right: 12px;
        left: 12px;
        width: auto;
    }

    .notification-card {
        padding: 12px;
    }

    .notification-icon {
        width: 36px;
        height: 36px;
        font-size: 18px;
    }

    .notification-title {
        font-size: 14px;
    }

    .notification-message {
        font-size: 12px;
    }
}
</style>

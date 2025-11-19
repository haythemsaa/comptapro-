<template>
    <div class="activity-timeline">
        <div class="timeline-header">
            <h3>
                <i class="bi bi-clock-history"></i>
                Activité Récente
            </h3>
            <button @click="showAllActivities" class="btn-view-all">
                Tout voir
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>

        <div class="timeline-container">
            <TransitionGroup name="timeline-list" tag="div">
                <div
                    v-for="activity in visibleActivities"
                    :key="activity.id"
                    class="timeline-item"
                >
                    <div :class="['timeline-marker', `marker-${activity.color}`]">
                        <i :class="activity.icon"></i>
                    </div>

                    <div class="timeline-content">
                        <div class="timeline-header-item">
                            <div class="timeline-title">{{ activity.title }}</div>
                            <div class="timeline-time">{{ formatTime(activity.timestamp) }}</div>
                        </div>
                        <div class="timeline-description">{{ activity.description }}</div>
                    </div>
                </div>
            </TransitionGroup>

            <div v-if="activities.length === 0" class="timeline-empty">
                <i class="bi bi-inbox"></i>
                <p>Aucune activité récente</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    activities: {
        type: Array,
        default: () => []
    },
    maxVisible: {
        type: Number,
        default: 5
    }
});

const emit = defineEmits(['view-all']);

const visibleActivities = computed(() => {
    return props.activities.slice(0, props.maxVisible);
});

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

    return new Date(timestamp).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short'
    });
};

const showAllActivities = () => {
    emit('view-all');
};
</script>

<style scoped>
.activity-timeline {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.timeline-header {
    padding: 20px 24px;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.timeline-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
}

.timeline-header h3 i {
    color: #3b82f6;
    font-size: 22px;
}

.btn-view-all {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #3b82f6;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-view-all:hover {
    background: #eff6ff;
    border-color: #3b82f6;
}

.timeline-container {
    padding: 20px 24px;
    max-height: 500px;
    overflow-y: auto;
}

.timeline-item {
    display: flex;
    gap: 16px;
    padding-bottom: 20px;
    margin-bottom: 20px;
    border-left: 2px solid #f3f4f6;
    margin-left: 20px;
    padding-left: 20px;
    position: relative;
}

.timeline-item:last-child {
    margin-bottom: 0;
    border-left-color: transparent;
}

.timeline-marker {
    position: absolute;
    left: -21px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.marker-success {
    background: linear-gradient(135deg, #10b981, #059669);
}

.marker-info {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.marker-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.marker-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.marker-primary {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.timeline-content {
    flex: 1;
    min-width: 0;
}

.timeline-header-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
    gap: 12px;
}

.timeline-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
}

.timeline-time {
    font-size: 12px;
    color: #9ca3af;
    white-space: nowrap;
}

.timeline-description {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}

.timeline-empty {
    text-align: center;
    padding: 60px 24px;
    color: #9ca3af;
}

.timeline-empty i {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
}

.timeline-empty p {
    margin: 0;
    font-size: 14px;
}

/* Transitions */
.timeline-list-enter-active,
.timeline-list-leave-active {
    transition: all 0.3s;
}

.timeline-list-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

.timeline-list-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

.timeline-list-move {
    transition: transform 0.3s;
}

/* Scrollbar */
.timeline-container::-webkit-scrollbar {
    width: 8px;
}

.timeline-container::-webkit-scrollbar-track {
    background: #f3f4f6;
}

.timeline-container::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.timeline-container::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Dark Mode */
:global(.dark) .activity-timeline {
    background: #1f2937;
}

:global(.dark) .timeline-header {
    border-bottom-color: #374151;
}

:global(.dark) .timeline-header h3 {
    color: #f3f4f6;
}

:global(.dark) .btn-view-all {
    background: #374151;
    border-color: #4b5563;
    color: #60a5fa;
}

:global(.dark) .btn-view-all:hover {
    background: #4b5563;
}

:global(.dark) .timeline-item {
    border-left-color: #374151;
}

:global(.dark) .timeline-title {
    color: #f3f4f6;
}

:global(.dark) .timeline-description {
    color: #d1d5db;
}

:global(.dark) .timeline-empty {
    color: #6b7280;
}

/* Responsive */
@media (max-width: 768px) {
    .timeline-header h3 {
        font-size: 16px;
    }

    .timeline-header h3 i {
        font-size: 20px;
    }

    .timeline-marker {
        width: 36px;
        height: 36px;
        font-size: 14px;
        left: -19px;
    }

    .timeline-item {
        margin-left: 18px;
        padding-left: 18px;
    }

    .timeline-title {
        font-size: 14px;
    }

    .timeline-description {
        font-size: 12px;
    }
}
</style>

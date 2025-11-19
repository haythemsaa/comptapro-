<template>
    <div class="tasks-widget">
        <div class="widget-header">
            <h3>
                <i class="bi bi-check2-square"></i>
                Mes Tâches
            </h3>
            <div class="widget-actions">
                <button @click="showCompleted = !showCompleted" class="btn-toggle" :class="{ active: showCompleted }">
                    <i class="bi bi-eye"></i>
                    Terminées
                </button>
                <button @click="showAddTask = true" class="btn-add">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>

        <div class="tasks-list">
            <TransitionGroup name="task-list">
                <div
                    v-for="task in filteredTasks"
                    :key="task.id"
                    :class="['task-item', { 'task-completed': task.completed, 'task-overdue': isOverdue(task) }]"
                >
                    <div class="task-checkbox">
                        <input
                            type="checkbox"
                            :id="`task-${task.id}`"
                            v-model="task.completed"
                            @change="toggleTask(task)"
                        />
                        <label :for="`task-${task.id}`"></label>
                    </div>

                    <div class="task-content">
                        <div class="task-title">{{ task.title }}</div>
                        <div class="task-meta">
                            <span v-if="task.dueDate" class="task-due">
                                <i class="bi bi-calendar"></i>
                                {{ formatDate(task.dueDate) }}
                            </span>
                            <span v-if="task.priority" :class="['task-priority', `priority-${task.priority}`]">
                                {{ getPriorityLabel(task.priority) }}
                            </span>
                            <span v-if="task.category" class="task-category">
                                <i :class="getCategoryIcon(task.category)"></i>
                                {{ task.category }}
                            </span>
                        </div>
                    </div>

                    <div class="task-actions">
                        <button @click="editTask(task)" class="btn-edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button @click="deleteTask(task.id)" class="btn-delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </TransitionGroup>

            <div v-if="filteredTasks.length === 0" class="tasks-empty">
                <i class="bi bi-check-circle"></i>
                <p v-if="showCompleted">Aucune tâche terminée</p>
                <p v-else>Aucune tâche en cours</p>
            </div>
        </div>

        <!-- Add Task Modal -->
        <Transition name="modal">
            <div v-if="showAddTask" class="modal-overlay" @click="showAddTask = false">
                <div class="modal-content" @click.stop>
                    <div class="modal-header">
                        <h4>{{ editingTask ? 'Modifier' : 'Nouvelle' }} Tâche</h4>
                        <button @click="closeModal" class="btn-close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Titre *</label>
                            <input
                                v-model="taskForm.title"
                                type="text"
                                placeholder="Ex: Envoyer devis à Client X"
                                class="form-control"
                            />
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date d'échéance</label>
                                <input v-model="taskForm.dueDate" type="date" class="form-control" />
                            </div>

                            <div class="form-group">
                                <label>Priorité</label>
                                <select v-model="taskForm.priority" class="form-control">
                                    <option value="">Normale</option>
                                    <option value="high">Haute</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Catégorie</label>
                            <select v-model="taskForm.category" class="form-control">
                                <option value="">Sélectionner...</option>
                                <option value="Factures">Factures</option>
                                <option value="Clients">Clients</option>
                                <option value="Comptabilité">Comptabilité</option>
                                <option value="Administratif">Administratif</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea
                                v-model="taskForm.notes"
                                placeholder="Détails supplémentaires..."
                                class="form-control"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button @click="closeModal" class="btn btn-secondary">Annuler</button>
                        <button @click="saveTask" class="btn btn-primary">
                            {{ editingTask ? 'Modifier' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Stats Footer -->
        <div class="widget-footer">
            <div class="stat">
                <span class="stat-value">{{ pendingCount }}</span>
                <span class="stat-label">En cours</span>
            </div>
            <div class="stat">
                <span class="stat-value">{{ completedCount }}</span>
                <span class="stat-label">Terminées</span>
            </div>
            <div class="stat">
                <span class="stat-value">{{ overdueCount }}</span>
                <span class="stat-label">En retard</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const showCompleted = ref(false);
const showAddTask = ref(false);
const editingTask = ref(null);

const taskForm = ref({
    title: '',
    dueDate: '',
    priority: '',
    category: '',
    notes: ''
});

const tasks = ref([
    {
        id: 1,
        title: 'Envoyer facture à Acme Corp',
        dueDate: new Date(Date.now() + 86400000 * 2).toISOString().split('T')[0],
        priority: 'high',
        category: 'Factures',
        completed: false,
        notes: 'Facture FA-2024-125'
    },
    {
        id: 2,
        title: 'Relancer les factures impayées',
        dueDate: new Date(Date.now() - 86400000).toISOString().split('T')[0],
        priority: 'urgent',
        category: 'Factures',
        completed: false,
        notes: '5 factures en retard'
    },
    {
        id: 3,
        title: 'Déclaration TVA du mois',
        dueDate: new Date(Date.now() + 86400000 * 5).toISOString().split('T')[0],
        priority: 'high',
        category: 'Comptabilité',
        completed: false
    },
    {
        id: 4,
        title: 'Mettre à jour le catalogue produits',
        dueDate: new Date(Date.now() + 86400000 * 7).toISOString().split('T')[0],
        priority: '',
        category: 'Administratif',
        completed: false
    },
    {
        id: 5,
        title: 'Appeler nouveau prospect TechStart',
        dueDate: new Date(Date.now() + 86400000 * 3).toISOString().split('T')[0],
        priority: '',
        category: 'Clients',
        completed: true
    }
]);

const filteredTasks = computed(() => {
    return tasks.value
        .filter(task => showCompleted.value ? task.completed : !task.completed)
        .sort((a, b) => {
            // Sort by: completed, priority, due date
            if (a.completed !== b.completed) return a.completed ? 1 : -1;

            const priorityOrder = { urgent: 3, high: 2, '': 1 };
            const aPriority = priorityOrder[a.priority] || 1;
            const bPriority = priorityOrder[b.priority] || 1;

            if (aPriority !== bPriority) return bPriority - aPriority;

            if (a.dueDate && b.dueDate) {
                return new Date(a.dueDate) - new Date(b.dueDate);
            }

            return 0;
        });
});

const pendingCount = computed(() => tasks.value.filter(t => !t.completed).length);
const completedCount = computed(() => tasks.value.filter(t => t.completed).length);
const overdueCount = computed(() => tasks.value.filter(t => !t.completed && isOverdue(t)).length);

const isOverdue = (task) => {
    if (!task.dueDate || task.completed) return false;
    return new Date(task.dueDate) < new Date();
};

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (date.toDateString() === today.toDateString()) {
        return "Aujourd'hui";
    } else if (date.toDateString() === tomorrow.toDateString()) {
        return 'Demain';
    }

    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
};

const getPriorityLabel = (priority) => {
    const labels = {
        urgent: 'Urgent',
        high: 'Haute',
        '': 'Normale'
    };
    return labels[priority] || 'Normale';
};

const getCategoryIcon = (category) => {
    const icons = {
        'Factures': 'bi bi-receipt',
        'Clients': 'bi bi-people',
        'Comptabilité': 'bi bi-calculator',
        'Administratif': 'bi bi-file-text',
        'Autre': 'bi bi-tag'
    };
    return icons[category] || 'bi bi-tag';
};

const toggleTask = (task) => {
    if (task.completed) {
        window.$toast?.success(`Tâche "${task.title}" terminée !`, 'Bravo');
    }
};

const editTask = (task) => {
    editingTask.value = task;
    taskForm.value = { ...task };
    showAddTask.value = true;
};

const deleteTask = (id) => {
    const index = tasks.value.findIndex(t => t.id === id);
    if (index > -1) {
        tasks.value.splice(index, 1);
        window.$toast?.success('Tâche supprimée', 'Succès');
    }
};

const saveTask = () => {
    if (!taskForm.value.title.trim()) {
        window.$toast?.error('Le titre est obligatoire', 'Erreur');
        return;
    }

    if (editingTask.value) {
        Object.assign(editingTask.value, taskForm.value);
        window.$toast?.success('Tâche modifiée', 'Succès');
    } else {
        tasks.value.push({
            id: Date.now(),
            ...taskForm.value,
            completed: false
        });
        window.$toast?.success('Tâche créée', 'Succès');
    }

    closeModal();
};

const closeModal = () => {
    showAddTask.value = false;
    editingTask.value = null;
    taskForm.value = {
        title: '',
        dueDate: '',
        priority: '',
        category: '',
        notes: ''
    };
};
</script>

<style scoped>
.tasks-widget {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.widget-header {
    padding: 20px 24px;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.widget-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
}

.widget-header h3 i {
    color: #3b82f6;
    font-size: 22px;
}

.widget-actions {
    display: flex;
    gap: 8px;
}

.btn-toggle {
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-toggle:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.btn-toggle.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

.btn-add {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    background: #3b82f6;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-add:hover {
    background: #2563eb;
    transform: scale(1.05);
}

.tasks-list {
    max-height: 500px;
    overflow-y: auto;
}

.task-item {
    padding: 16px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.2s;
}

.task-item:hover {
    background: #f9fafb;
}

.task-completed {
    opacity: 0.6;
}

.task-completed .task-title {
    text-decoration: line-through;
    color: #9ca3af;
}

.task-overdue {
    border-left: 3px solid #ef4444;
}

.task-checkbox {
    position: relative;
}

.task-checkbox input[type="checkbox"] {
    width: 24px;
    height: 24px;
    cursor: pointer;
    opacity: 0;
    position: absolute;
}

.task-checkbox label {
    width: 24px;
    height: 24px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    display: block;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
}

.task-checkbox input:checked + label {
    background: #3b82f6;
    border-color: #3b82f6;
}

.task-checkbox input:checked + label::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-size: 14px;
    font-weight: bold;
}

.task-content {
    flex: 1;
    min-width: 0;
}

.task-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 6px;
}

.task-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 12px;
}

.task-due {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #6b7280;
}

.task-priority {
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 11px;
}

.priority-urgent {
    background: #fee2e2;
    color: #991b1b;
}

.priority-high {
    background: #fef3c7;
    color: #92400e;
}

.task-category {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #9ca3af;
}

.task-actions {
    display: flex;
    gap: 6px;
    opacity: 0;
    transition: opacity 0.2s;
}

.task-item:hover .task-actions {
    opacity: 1;
}

.btn-edit,
.btn-delete {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-edit {
    background: #eff6ff;
    color: #1e40af;
}

.btn-edit:hover {
    background: #dbeafe;
}

.btn-delete {
    background: #fee2e2;
    color: #991b1b;
}

.btn-delete:hover {
    background: #fecaca;
}

.tasks-empty {
    text-align: center;
    padding: 60px 24px;
    color: #9ca3af;
}

.tasks-empty i {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
}

.tasks-empty p {
    margin: 0;
    font-size: 14px;
}

.widget-footer {
    padding: 16px 24px;
    border-top: 2px solid #f3f4f6;
    display: flex;
    justify-content: space-around;
    background: #f9fafb;
}

.stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #6b7280;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-content {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 24px;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.btn-close {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #6b7280;
    transition: all 0.2s;
}

.btn-close:hover {
    background: #e5e7eb;
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.modal-footer {
    padding: 20px 24px;
    border-top: 2px solid #f3f4f6;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
}

/* Transitions */
.task-list-enter-active,
.task-list-leave-active {
    transition: all 0.3s;
}

.task-list-enter-from,
.task-list-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.task-list-move {
    transition: transform 0.3s;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
    transition: transform 0.2s, opacity 0.2s;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
    transform: scale(0.95);
    opacity: 0;
}

/* Scrollbar */
.tasks-list::-webkit-scrollbar {
    width: 8px;
}

.tasks-list::-webkit-scrollbar-track {
    background: #f3f4f6;
}

.tasks-list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.tasks-list::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Dark Mode */
:global(.dark) .tasks-widget {
    background: #1f2937;
}

:global(.dark) .widget-header {
    border-bottom-color: #374151;
}

:global(.dark) .widget-header h3 {
    color: #f3f4f6;
}

:global(.dark) .btn-toggle {
    background: #374151;
    border-color: #4b5563;
    color: #d1d5db;
}

:global(.dark) .task-item {
    border-bottom-color: #374151;
}

:global(.dark) .task-item:hover {
    background: #374151;
}

:global(.dark) .task-title {
    color: #f3f4f6;
}

:global(.dark) .widget-footer {
    background: #111827;
    border-top-color: #374151;
}

:global(.dark) .stat-value {
    color: #f3f4f6;
}

:global(.dark) .modal-content {
    background: #1f2937;
}

:global(.dark) .modal-header {
    border-bottom-color: #374151;
}

:global(.dark) .modal-header h4 {
    color: #f3f4f6;
}

:global(.dark) .btn-close {
    background: #374151;
    color: #d1d5db;
}

:global(.dark) .form-group label {
    color: #e5e7eb;
}

:global(.dark) .form-control {
    background: #374151;
    border-color: #4b5563;
    color: #f3f4f6;
}

:global(.dark) .modal-footer {
    border-top-color: #374151;
}
</style>

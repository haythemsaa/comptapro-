<template>
    <div class="draggable-list">
        <div
            v-for="(item, index) in localItems"
            :key="item.id"
            class="draggable-item"
            :class="{
                'dragging': draggingIndex === index,
                'drag-over': dragOverIndex === index
            }"
            draggable="true"
            @dragstart="handleDragStart(index, $event)"
            @dragend="handleDragEnd"
            @dragover.prevent="handleDragOver(index, $event)"
            @drop="handleDrop(index, $event)"
        >
            <div class="drag-handle">
                <i class="bi bi-grip-vertical"></i>
            </div>
            <slot :item="item" :index="index">
                <div class="item-content">
                    {{ item.name || item.title }}
                </div>
            </slot>
        </div>
        <div v-if="localItems.length === 0" class="empty-list">
            <i class="bi bi-inbox"></i>
            <p>Aucun élément à afficher</p>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:items', 'reorder']);

const localItems = ref([...props.items]);
const draggingIndex = ref(null);
const dragOverIndex = ref(null);

watch(() => props.items, (newItems) => {
    localItems.value = [...newItems];
}, { deep: true });

const handleDragStart = (index, event) => {
    draggingIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', event.target.innerHTML);

    // Add visual feedback
    setTimeout(() => {
        event.target.classList.add('dragging');
    }, 0);
};

const handleDragEnd = (event) => {
    event.target.classList.remove('dragging');
    draggingIndex.value = null;
    dragOverIndex.value = null;
};

const handleDragOver = (index, event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';

    if (index !== draggingIndex.value) {
        dragOverIndex.value = index;
    }
};

const handleDrop = (index, event) => {
    event.preventDefault();

    if (draggingIndex.value === null || draggingIndex.value === index) {
        return;
    }

    const newItems = [...localItems.value];
    const draggedItem = newItems[draggingIndex.value];

    // Remove the dragged item from its current position
    newItems.splice(draggingIndex.value, 1);

    // Insert it at the new position
    const insertIndex = draggingIndex.value < index ? index - 1 : index;
    newItems.splice(insertIndex, 0, draggedItem);

    localItems.value = newItems;

    // Emit events
    emit('update:items', newItems);
    emit('reorder', {
        from: draggingIndex.value,
        to: index,
        items: newItems
    });

    dragOverIndex.value = null;
};
</script>

<style scoped>
.draggable-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.draggable-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: move;
    transition: all 0.3s;
    user-select: none;
}

.draggable-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.draggable-item.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.draggable-item.drag-over {
    border-color: #10b981;
    background: #f0fdf4;
    transform: translateY(-4px);
}

.drag-handle {
    color: #9ca3af;
    font-size: 20px;
    cursor: grab;
    display: flex;
    align-items: center;
    transition: color 0.3s;
}

.draggable-item:hover .drag-handle {
    color: #3b82f6;
}

.drag-handle:active {
    cursor: grabbing;
}

.item-content {
    flex: 1;
    font-weight: 500;
    color: #1f2937;
}

.empty-list {
    padding: 60px 24px;
    text-align: center;
    color: #9ca3af;
    background: #f9fafb;
    border-radius: 12px;
}

.empty-list i {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
}

.empty-list p {
    margin: 0;
    font-size: 14px;
}
</style>

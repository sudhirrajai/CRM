<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    stages: { type: Array, required: true },
    modelValue: { type: Array, required: true },
});

const emit = defineEmits(['update:modelValue', 'stage-changed']);

const dragState = ref({
    dragging: false,
    draggedLeadId: null,
    sourceStageId: null,
    overStageId: null,
});

function onDragStart(e, lead, stageId) {
    dragState.value = {
        dragging: true,
        draggedLeadId: lead.id,
        sourceStageId: stageId,
        overStageId: null,
    };
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', lead.id);
    e.target.classList.add('kanban-card--dragging');
}

function onDragEnd(e) {
    e.target.classList.remove('kanban-card--dragging');
    dragState.value = { dragging: false, draggedLeadId: null, sourceStageId: null, overStageId: null };
    document.querySelectorAll('.kanban-column--drag-over').forEach(el => el.classList.remove('kanban-column--drag-over'));
}

function onDragOver(e, stageId) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    dragState.value.overStageId = stageId;
}

function onDragEnter(e, stageId) {
    e.preventDefault();
    dragState.value.overStageId = stageId;
}

function onDragLeave(e, stageId) {
    if (dragState.value.overStageId === stageId) {
        // Only clear if actually leaving the column
        const relatedTarget = e.relatedTarget;
        const column = e.currentTarget;
        if (!column.contains(relatedTarget)) {
            dragState.value.overStageId = null;
        }
    }
}

function onDrop(e, targetStageId) {
    e.preventDefault();
    const leadId = e.dataTransfer.getData('text/plain');
    if (!leadId) return;

    const sourceStageId = dragState.value.sourceStageId;
    if (sourceStageId === targetStageId) {
        dragState.value = { dragging: false, draggedLeadId: null, sourceStageId: null, overStageId: null };
        return;
    }

    // Calculate new position (end of target column)
    const targetStage = props.modelValue.find(s => s.id === targetStageId);
    const newPosition = targetStage ? (targetStage.leads?.length || 0) : 0;

    emit('stage-changed', {
        leadId,
        fromStageId: sourceStageId,
        toStageId: targetStageId,
        position: newPosition,
    });

    dragState.value = { dragging: false, draggedLeadId: null, sourceStageId: null, overStageId: null };
}

function getLeadsForStage(stageId) {
    const stage = props.modelValue.find(s => s.id === stageId);
    return stage?.leads || [];
}

function getPriorityClass(priority) {
    const map = { urgent: 'danger', high: 'warning', medium: 'info', low: 'secondary' };
    return map[priority] || 'secondary';
}

function getSourceIcon(source) {
    const map = {
        website: 'ti-world',
        referral: 'ti-users',
        social_media: 'ti-brand-instagram',
        cold_call: 'ti-phone-call',
        email: 'ti-mail',
        advertisement: 'ti-ad-2',
        other: 'ti-dots',
    };
    return map[source] || 'ti-dots';
}

function formatValue(value, currency) {
    if (!value) return null;
    const sym = currency?.symbol || '$';
    const formatted = parseFloat(value).toLocaleString();
    return currency?.symbol_position === 'suffix' ? `${formatted} ${sym}` : `${sym}${formatted}`;
}

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}
</script>

<template>
    <div class="kanban-board">
        <div
            v-for="stage in stages"
            :key="stage.id"
            class="kanban-column"
            :class="{ 'kanban-column--drag-over': dragState.overStageId === stage.id }"
            @dragover="onDragOver($event, stage.id)"
            @dragenter="onDragEnter($event, stage.id)"
            @dragleave="onDragLeave($event, stage.id)"
            @drop="onDrop($event, stage.id)"
        >
            <!-- Column Header -->
            <div class="kanban-column__header">
                <div class="d-flex align-items-center gap-2">
                    <span class="kanban-column__dot" :style="{ backgroundColor: stage.color }"></span>
                    <h6 class="kanban-column__title mb-0">{{ stage.name }}</h6>
                    <span class="badge bg-light text-dark rounded-pill ms-auto">{{ getLeadsForStage(stage.id).length }}</span>
                </div>
            </div>

            <!-- Column Body -->
            <div class="kanban-column__body">
                <div
                    v-for="lead in getLeadsForStage(stage.id)"
                    :key="lead.id"
                    class="kanban-card"
                    :class="{ 'kanban-card--dragging': dragState.draggedLeadId === lead.id }"
                    draggable="true"
                    @dragstart="onDragStart($event, lead, stage.id)"
                    @dragend="onDragEnd"
                >
                    <!-- Card Top -->
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <a :href="'/leads/' + lead.id" class="kanban-card__title text-reset">{{ lead.title }}</a>
                        <span class="badge" :class="'bg-' + getPriorityClass(lead.priority) + '-subtle text-' + getPriorityClass(lead.priority)" style="font-size: 10px;">
                            {{ lead.priority }}
                        </span>
                    </div>

                    <!-- Company -->
                    <div v-if="lead.company" class="kanban-card__company text-muted mb-2">
                        <i class="ti ti-building fs-12 me-1"></i>{{ lead.company }}
                    </div>

                    <!-- Value -->
                    <div v-if="lead.value" class="kanban-card__value mb-2">
                        <span class="fw-semibold text-success">{{ formatValue(lead.value, lead.currency) }}</span>
                    </div>

                    <!-- Footer -->
                    <div class="kanban-card__footer d-flex align-items-center justify-content-between mt-2">
                        <div class="d-flex align-items-center gap-1">
                            <i :class="getSourceIcon(lead.source)" class="fs-14 text-muted" :title="lead.source"></i>
                            <span v-if="lead.expected_close_date" class="text-muted fs-11">
                                <i class="ti ti-calendar-event me-1"></i>{{ new Date(lead.expected_close_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
                            </span>
                        </div>
                        <div v-if="lead.assigned_user" class="kanban-card__avatar" :title="(lead.assigned_user)?.name">
                            {{ getInitials((lead.assigned_user)?.name) }}
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="getLeadsForStage(stage.id).length === 0" class="kanban-column__empty">
                    <i class="ti ti-inbox fs-24 mb-1 d-block"></i>
                    <span class="fs-11">No leads</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.kanban-board {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding-bottom: 16px;
    min-height: 500px;
}

.kanban-column {
    min-width: 280px;
    max-width: 320px;
    flex: 0 0 280px;
    background: var(--bs-tertiary-bg, #f8f9fa);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
    border: 2px solid transparent;
}

.kanban-column--drag-over {
    border-color: var(--bs-primary, #6ac75a);
    box-shadow: 0 0 0 3px rgba(106, 199, 90, 0.15);
}

.kanban-column__header {
    padding: 14px 16px 10px;
    border-bottom: 1px solid var(--bs-border-color, #eee);
}

.kanban-column__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.kanban-column__title {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kanban-column__body {
    flex: 1;
    padding: 12px;
    overflow-y: auto;
    max-height: calc(100vh - 320px);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.kanban-card {
    background: var(--bs-body-bg, #fff);
    border-radius: 10px;
    padding: 14px;
    cursor: grab;
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    border: 1px solid var(--bs-border-color, #eee);
}

.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.kanban-card:active {
    cursor: grabbing;
}

.kanban-card--dragging {
    opacity: 0.4;
    transform: rotate(2deg);
}

.kanban-card__title {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    text-decoration: none;
    word-break: break-word;
}

.kanban-card__title:hover {
    color: var(--bs-primary) !important;
}

.kanban-card__company {
    font-size: 12px;
}

.kanban-card__value {
    font-size: 13px;
}

.kanban-card__footer {
    border-top: 1px solid var(--bs-border-color, #eee);
    padding-top: 8px;
}

.kanban-card__avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--bs-primary, #6ac75a);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}

.kanban-column__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 10px;
    color: var(--bs-secondary-color, #adb5bd);
    text-align: center;
}
</style>

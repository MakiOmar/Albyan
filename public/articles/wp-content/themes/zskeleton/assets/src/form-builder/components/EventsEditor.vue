<template>
	<div class="zs-fb-events">
		<p class="zs-fb-events__intro">
			{{ strings.eventsOrderHint || 'Actions run top to bottom. Drag to reorder — e.g. save first, email next, redirect last.' }}
		</p>
		<p v-if="!events.length" class="zs-fb-events__empty">
			{{ strings.eventsEmpty || 'No actions yet. Add one to save submissions, send email, or redirect visitors.' }}
		</p>
		<draggable
			:model-value="events"
			item-key="_uid"
			tag="div"
			v-bind="eventDragProps"
			class="zs-fb-events__list"
			@update:model-value="onReorder"
		>
			<template #item="{ element, index }">
				<div class="zs-fb-events__item">
					<EventRow
						:index="index"
						:event="element"
						:event-types="eventTypes"
						:strings="strings"
						@update="( ev ) => $emit( 'update', index, ev )"
						@remove="onRemove( index )"
					/>
				</div>
			</template>
		</draggable>
		<div class="zs-fb-events__foot">
			<button type="button" class="zs-fb-btn zs-fb-btn--primary zs-fb-btn--sm" @click="$emit( 'add' )">
				<span aria-hidden="true">+</span>
				{{ strings.addAction || 'Add action' }}
			</button>
		</div>
	</div>
</template>

<script setup>
import draggable from 'vuedraggable';
import EventRow from './EventRow.vue';
import { FORM_EVENT_DRAG_PROPS } from '../composables/eventDragOptions.js';

defineProps( {
	events: { type: Array, default: () => [] },
	eventTypes: { type: Array, default: () => [] },
	strings: { type: Object, default: () => ( {} ) },
} );

const emit = defineEmits( [ 'add', 'update', 'remove', 'reorder' ] );

const eventDragProps = FORM_EVENT_DRAG_PROPS;

function onReorder( next ) {
	emit( 'reorder', next );
}

function onRemove( index ) {
	if ( window.confirm( 'Remove this action?' ) ) {
		emit( 'remove', index );
	}
}
</script>

<template>
	<div
		class="zs-fb-field-card"
		:class="{ 'is-selected': selected }"
	>
		<div class="zs-fb-field-card__surface">
			<div class="zs-fb-field-card__head">
				<div
					class="zs-fb-field-card__lead zs-fb-drag-handle"
					:title="strings.dragToReorder || 'Drag to reorder'"
					aria-label="Drag to reorder"
				>
					<span class="zs-fb-grip" aria-hidden="true">
						<svg class="zs-fb-grip__icon" viewBox="0 0 20 20" fill="currentColor">
							<circle cx="7" cy="5" r="1.35" /><circle cx="13" cy="5" r="1.35" />
							<circle cx="7" cy="10" r="1.35" /><circle cx="13" cy="10" r="1.35" />
							<circle cx="7" cy="15" r="1.35" /><circle cx="13" cy="15" r="1.35" />
						</svg>
					</span>
					<span class="zs-fb-field-card__icon" :class="meta.iconClass" aria-hidden="true">{{ meta.icon }}</span>
					<div class="zs-fb-field-card__meta" role="button" tabindex="0" @click="onSelect" @keydown.enter.prevent="onSelect">
						<strong class="zs-fb-field-card__label">{{ field.label || field.name }}</strong>
						<code class="zs-fb-field-card__slug">{{ field.name }}</code>
					</div>
				</div>
				<span v-if="field.required" class="zs-fb-field-card__req" :title="strings.required || 'Required'">*</span>
				<div class="zs-fb-field-card__actions">
					<IconButton
						icon="edit"
						variant="edit"
						:title="strings.edit || 'Edit'"
						:aria-label="strings.edit || 'Edit'"
						@click.stop="onSelect"
					/>
					<IconButton
						icon="delete"
						variant="danger"
						:title="strings.remove || 'Remove'"
						:aria-label="strings.remove || 'Remove'"
						@click.stop="onRemove"
					/>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue';
import IconButton from './IconButton.vue';
import { fieldTypeMeta } from '../composables/fieldTypeMeta.js';

const props = defineProps( {
	field: { type: Object, required: true },
	selected: { type: Boolean, default: false },
	strings: { type: Object, default: () => ( {} ) },
} );

const emit = defineEmits( [ 'select', 'remove' ] );

const meta = computed( () => fieldTypeMeta( props.field.type || 'text' ) );

function onSelect() {
	emit( 'select' );
}

function onRemove() {
	if ( window.confirm( props.strings.confirmDelete || 'Remove this item?' ) ) {
		emit( 'remove' );
	}
}
</script>

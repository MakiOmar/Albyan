<template>
	<div class="zs-fb-panel" :style="{ '--zs-builder-cols': row.columns }">
		<div class="zs-fb-row__head">
			<span class="row-handle zs-fb-handle zs-fb-drag-handle" title="Drag row" aria-label="Drag row">⋮⋮</span>
			<strong>{{ strings.rowLabel || 'Row' }}</strong>
			<span class="zs-fb-badge">
				{{ row.columns }} {{ strings.cols || 'cols' }}
			</span>
			<div class="zs-fb-toggle-row zs-fb-toggle-row--inline">
				<span class="zs-fb-toggle-row__label">{{ strings.stackMobile || 'Stack on mobile' }}</span>
				<label class="zs-fb-switch">
					<input v-model="row.mobile_stack" type="checkbox" @change="$emit( 'change' )" />
					<span class="zs-fb-switch__track" aria-hidden="true" />
				</label>
			</div>
			<IconButton
				icon="delete"
				variant="danger"
				:title="strings.remove || 'Remove row'"
				:aria-label="strings.remove || 'Remove row'"
				@click="onRemove"
			/>
		</div>
		<div
			class="zs-fb-row__cols zs-form-builder__row-cols"
			:class="{ 'zs-form-builder__row-cols--stack': row.mobile_stack }"
		>
			<div
				v-for="( col, colIndex ) in row.children"
				:key="colIndex"
				class="zs-fb-col"
			>
				<draggable
					v-model="row.children[ colIndex ].fields"
					v-bind="fieldDragProps"
					item-key="name"
					class="zs-fb-col__fields"
					:move="onColumnMove"
					@end="( evt ) => onColumnDragEnd( evt, colIndex )"
					@change="( evt ) => onColumnChange( evt, colIndex )"
				>
					<template #item="{ element, index: fieldIndex }">
						<div class="zs-fb-col__item">
							<FieldCard
								:field="element"
								:selected="isSelected( fieldIndex, colIndex )"
								:strings="strings"
								@select="selectColumnField( fieldIndex, colIndex )"
								@remove="$emit( 'remove-field', colIndex, fieldIndex )"
							/>
						</div>
					</template>
				</draggable>
				<p v-if="!col.fields.length" class="zs-fb-col__hint">
					{{ strings.dropField || 'Drop field here' }}
				</p>
			</div>
		</div>
	</div>
</template>

<script setup>
import draggable from 'vuedraggable';
import FieldCard from './FieldCard.vue';
import IconButton from './IconButton.vue';
import { FORM_FIELD_DRAG_PROPS } from '../composables/fieldDragOptions.js';

const fieldDragProps = FORM_FIELD_DRAG_PROPS;

const props = defineProps( {
	row: { type: Object, required: true },
	rowIndex: { type: Number, required: true },
	selectedPath: { type: Object, default: null },
	strings: { type: Object, default: () => ( {} ) },
} );

const emit = defineEmits( [ 'change', 'remove', 'select-field', 'remove-field' ] );

function isSelected( fieldIndex, colIndex ) {
	const p = props.selectedPath;
	return (
		p?.kind === 'column' &&
		p.rowIndex === props.rowIndex &&
		p.colIndex === colIndex &&
		p.fieldIndex === fieldIndex
	);
}

function selectColumnField( fieldIndex, colIndex ) {
	emit( 'select-field', {
		kind: 'column',
		rowIndex: props.rowIndex,
		colIndex,
		fieldIndex,
	} );
}

function onRemove() {
	if ( window.confirm( props.strings.confirmDelete || 'Remove this item?' ) ) {
		emit( 'remove' );
	}
}

function onColumnMove( evt ) {
	const el = evt.draggedContext?.element;
	return ! ( el && el.type === 'row' );
}

function onColumnChange( evt, colIndex ) {
	if ( evt.added ) {
		const fields = props.row.children[ colIndex ].fields;
		const item = fields[ evt.added.newIndex ];
		if ( item?.type === 'field' && item.field ) {
			fields[ evt.added.newIndex ] = { ...item.field };
		}
	}
}

function onColumnDragEnd( evt, colIndex ) {
	if ( evt.oldIndex === evt.newIndex ) {
		return;
	}
	emit( 'change', {
		moved: {
			scope: 'column',
			rowIndex: props.rowIndex,
			colIndex,
			oldIndex: evt.oldIndex,
			newIndex: evt.newIndex,
		},
	} );
}
</script>

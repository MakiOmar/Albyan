<template>
	<div class="zs-fb-layout">
		<FieldPalettePanel
			:field-types="fieldTypes"
			:strings="strings"
			@add-field="$emit( 'add-field', $event )"
		/>

		<section class="zs-fb-panel zs-fb-canvas-panel">
			<h3 class="zs-fb-panel__head">{{ strings.formLayout || 'Form layout' }}</h3>
			<div class="zs-fb-panel__body zs-fb-canvas-panel__body">
				<div
					class="zs-fb-canvas"
					:class="{ 'zs-fb-canvas--has-fields': localTree.length }"
				>
					<draggable
						v-model="localTree"
						v-bind="fieldDragProps"
						item-key="_uid"
						:move="onRootMove"
						class="zs-fb-canvas__list"
						@change="onRootChange"
					>
						<template #item="{ element, index }">
							<div v-if="element.type === 'field'" :key="element._uid" class="zs-fb-canvas__item">
								<FieldCard
									:field="element.field"
									:selected="isRootSelected( index )"
									:strings="strings"
									@select="$emit( 'select-field', { kind: 'root', index } )"
									@remove="$emit( 'remove-root', index )"
								/>
							</div>
							<RowBlock
								v-else-if="element.type === 'row'"
								:key="element._uid"
								class="zs-fb-canvas__item"
								:row="element"
								:row-index="index"
								:selected-path="selectedPath"
								:strings="strings"
								@change="onRowChange"
								@remove="$emit( 'remove-root', index )"
								@select-field="$emit( 'select-field', $event )"
								@remove-field="( col, fi ) => $emit( 'remove-column-field', index, col, fi )"
							/>
						</template>
					</draggable>
					<div v-if="!localTree.length" class="zs-fb-empty">
						<div class="zs-fb-empty__icon" aria-hidden="true">+</div>
						<p class="zs-fb-text-strong">
							{{ strings.canvasEmpty || strings.dropField || 'Add fields from the palette or add a row.' }}
						</p>
						<p class="zs-fb-text-hint">
							{{ strings.dropFieldHint || 'Drag the grip to reorder · Click a field to edit' }}
						</p>
					</div>
				</div>
			</div>
		</section>
	</div>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue';
import draggable from 'vuedraggable';
import FieldPalettePanel from './FieldPalettePanel.vue';
import FieldCard from './FieldCard.vue';
import RowBlock from './RowBlock.vue';
import { FORM_FIELD_DRAG_PROPS } from '../composables/fieldDragOptions.js';

const fieldDragProps = FORM_FIELD_DRAG_PROPS;

const props = defineProps( {
	layoutTree: { type: Array, required: true },
	fieldTypes: { type: Array, default: () => [] },
	selectedPath: { type: Object, default: null },
	strings: { type: Object, default: () => ( {} ) },
} );

const emit = defineEmits( [
	'update:layoutTree',
	'add-field',
	'select-field',
	'remove-root',
	'remove-column-field',
	'change',
	'reorder-selection',
] );

const localTree = ref( [] );
let syncingFromProps = false;

/**
 * Stable drag key for vuedraggable (must not change when index changes).
 *
 * @param {object} node Layout node.
 * @param {number} index Fallback index.
 * @return {string}
 */
function nodeUid( node, index ) {
	if ( node.type === 'row' ) {
		return node.id || `row-${ index }`;
	}
	if ( node.type === 'field' && node.field?.name ) {
		return `field-${ node.field.name }`;
	}
	return `node-${ index }`;
}

function attachUids( tree ) {
	return ( tree || [] ).map( ( node, index ) => ( {
		...node,
		_uid: nodeUid( node, index ),
	} ) );
}

function stripUids( tree ) {
	return tree.map( ( { _uid, ...rest } ) => rest );
}

watch(
	() => props.layoutTree,
	( tree ) => {
		if ( syncingFromProps ) {
			return;
		}
		localTree.value = attachUids( tree );
	},
	{ immediate: true, deep: true }
);

function pushTreeToStore( moved ) {
	syncingFromProps = true;
	emit( 'update:layoutTree', stripUids( localTree.value ) );
	if ( moved ) {
		emit( 'reorder-selection', moved );
	}
	emit( 'change' );
	nextTick( () => {
		syncingFromProps = false;
	} );
}

function isRootSelected( index ) {
	return props.selectedPath?.kind === 'root' && props.selectedPath.index === index;
}

function isBareField( item ) {
	return item && item.type !== 'field' && item.type !== 'row' && typeof item.name === 'string';
}

function onRootMove( evt ) {
	const el = evt.draggedContext?.element;
	if ( evt.to?.classList?.contains( 'zs-fb-col__fields' ) ) {
		return el?.type === 'field' && el.field;
	}
	return true;
}

function onRootChange( evt ) {
	if ( evt.added ) {
		const item = localTree.value[ evt.added.newIndex ];
		if ( isBareField( item ) ) {
			localTree.value[ evt.added.newIndex ] = {
				type: 'field',
				field: { ...item },
				_uid: nodeUid( { type: 'field', field: item }, evt.added.newIndex ),
			};
		}
	}

	if ( evt.moved ) {
		pushTreeToStore( {
			scope: 'root',
			oldIndex: evt.moved.oldIndex,
			newIndex: evt.moved.newIndex,
		} );
	} else if ( evt.added || evt.removed ) {
		pushTreeToStore();
	}
}

function onRowChange( payload ) {
	if ( payload?.moved ) {
		emit( 'reorder-selection', payload.moved );
		pushTreeToStore( payload.moved );
		return;
	}
	emit( 'change' );
}
</script>

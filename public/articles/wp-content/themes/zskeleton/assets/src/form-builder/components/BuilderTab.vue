<template>
	<div>
		<div class="zs-fb-toolbar">
			<div v-if="shortcode" class="zs-fb-toolbar__shortcode">
				<span class="zs-fb-kicker">
					{{ strings.shortcode || 'Shortcode' }}
				</span>
				<code class="zs-fb-chip">{{ shortcode }}</code>
				<button
					type="button"
					class="zs-fb-btn zs-fb-btn--ghost zs-fb-btn--sm"
					:class="{ 'is-copied': copied }"
					@click="copyShortcode"
				>
					{{ copied ? ( strings.copied || 'Copied!' ) : ( strings.copy || 'Copy' ) }}
				</button>
			</div>
			<div class="zs-fb-toolbar__actions">
				<div class="zs-fb-toolbar__group" role="group" :aria-label="strings.layoutGroup || 'Layout'">
					<button type="button" class="zs-fb-btn zs-fb-btn--sm" @click="$emit( 'add-row', 2 )">
						<span aria-hidden="true">⊞</span>
						{{ strings.addRow2 || '2 columns' }}
					</button>
					<button type="button" class="zs-fb-btn zs-fb-btn--sm" @click="$emit( 'add-row', 3 )">
						<span aria-hidden="true">⊞</span>
						{{ strings.addRow3 || '3 columns' }}
					</button>
				</div>
				<button type="button" class="zs-fb-btn zs-fb-btn--primary zs-fb-btn--sm" @click="openPreview">
					<span aria-hidden="true">◫</span>
					{{ strings.openPreview || 'Open preview' }}
				</button>
			</div>
		</div>

		<BuilderCanvas
			:layout-tree="layoutTree"
			:field-types="fieldTypes"
			:selected-path="selectedPath"
			:strings="strings"
			@update:layout-tree="$emit( 'update:layoutTree', $event )"
			@add-field="$emit( 'add-field', $event )"
			@select-field="$emit( 'select-field', $event )"
			@remove-root="$emit( 'remove-root', $event )"
			@remove-column-field="( r, c, f ) => $emit( 'remove-column-field', r, c, f )"
			@change="$emit( 'change' )"
			@reorder-selection="$emit( 'reorder-selection', $event )"
		/>

		<FieldInspector
			:open="!!selectedField"
			:field="selectedField"
			:field-types="fieldTypes"
			:strings="strings"
			@update="$emit( 'update-field', $event )"
			@close="$emit( 'clear-selection' )"
		/>

		<FormPreview
			:open="previewOpen"
			:html="previewHtml"
			:loading="previewLoading"
			:error="previewError"
			:strings="strings"
			@close="previewOpen = false"
			@refresh="$emit( 'refresh-preview' )"
		/>
	</div>
</template>

<script setup>
import { ref } from 'vue';
import BuilderCanvas from './BuilderCanvas.vue';
import FormPreview from './FormPreview.vue';
import FieldInspector from './FieldInspector.vue';
import { copyToClipboard } from '../composables/useCopyToClipboard.js';

const props = defineProps( {
	layoutTree: { type: Array, required: true },
	fieldTypes: { type: Array, default: () => [] },
	selectedPath: { type: Object, default: null },
	selectedField: { type: Object, default: null },
	shortcode: { type: String, default: '' },
	strings: { type: Object, default: () => ( {} ) },
	previewHtml: { type: String, default: '' },
	previewLoading: { type: Boolean, default: false },
	previewError: { type: String, default: '' },
} );

const emit = defineEmits( [
	'update:layoutTree',
	'add-field',
	'add-row',
	'select-field',
	'clear-selection',
	'remove-root',
	'remove-column-field',
	'update-field',
	'change',
	'refresh-preview',
	'reorder-selection',
] );

const copied = ref( false );
const previewOpen = ref( false );
let copyTimer = null;

function openPreview() {
	previewOpen.value = true;
	emit( 'refresh-preview' );
}

async function copyShortcode() {
	const ok = await copyToClipboard( props.shortcode );
	if ( ! ok ) {
		return;
	}
	copied.value = true;
	if ( copyTimer ) {
		clearTimeout( copyTimer );
	}
	copyTimer = setTimeout( () => {
		copied.value = false;
	}, 2000 );
}
</script>

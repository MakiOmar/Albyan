<template>
	<div class="zs-form-kit-app">
		<nav class="zs-fb-tabs" role="tablist">
			<button
				v-for="tab in tabs"
				:key="tab.id"
				type="button"
				class="zs-fb-tab"
				:class="{ 'is-active': activeTab === tab.id }"
				role="tab"
				:aria-selected="activeTab === tab.id"
				@click="activeTab = tab.id"
			>
				<span class="zs-fb-tab__icon" aria-hidden="true">{{ tab.icon }}</span>
				{{ tab.label }}
			</button>
		</nav>

		<div v-show="activeTab === 'builder'" data-tab-panel>
			<BuilderTab
				:layout-tree="store.layoutTree"
				:field-types="store.fieldTypes"
				:selected-path="store.selectedPath"
				:selected-field="store.selectedField"
				:shortcode="store.shortcode"
				:strings="store.strings"
				:preview-html="previewHtml"
				:preview-loading="previewLoading"
				:preview-error="previewError"
				@update:layout-tree="onLayoutChange"
				@add-field="store.addField"
				@add-row="store.addRow"
				@select-field="store.selectField"
				@clear-selection="store.selectField( null )"
				@remove-root="store.removeRootNode"
				@remove-column-field="( r, c, f ) => store.removeColumnField( r, c, f )"
				@update-field="onFieldUpdate"
				@change="schedulePreview"
				@reorder-selection="onReorderSelection"
				@refresh-preview="loadPreview"
			/>
		</div>

		<div v-show="activeTab === 'settings'" data-tab-panel>
			<FormSettings
				:settings="store.settings"
				:strings="store.strings"
				@update="onSettingsUpdate"
			/>
		</div>

		<div v-show="activeTab === 'events'" data-tab-panel>
			<div class="zs-fb-settings-panel">
				<div class="zs-fb-panel">
					<h3 class="zs-fb-panel__head">{{ store.strings.tabEvents || 'After submit' }}</h3>
					<div class="zs-fb-panel__body">
						<EventsEditor
							:events="store.events"
							:event-types="store.eventTypes"
							:strings="store.strings"
							@add="store.addEvent( 'save_submission' )"
							@update="( i, ev ) => store.updateEvent( i, ev )"
							@remove="store.removeEvent"
							@reorder="store.setEvents"
						/>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="zskeleton_form_layout_tree_json" :value="layoutJson" />
		<input type="hidden" name="zskeleton_form_schema_json" :value="schemaJson" />
		<input type="hidden" name="zskeleton_form_events_json" :value="eventsJson" />

		<FormBuilderDebug :asset-debug="store.assetDebug" />
	</div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useFormKitStore } from './store/formKit.js';
import { useWpSave } from './composables/useWpSave.js';
import { fetchPreview } from './composables/usePreview.js';
import BuilderTab from './components/BuilderTab.vue';
import FormSettings from './components/FormSettings.vue';
import EventsEditor from './components/EventsEditor.vue';
import FormBuilderDebug from './components/FormBuilderDebug.vue';

const props = defineProps( {
	bootstrap: { type: Object, required: true },
} );

const store = useFormKitStore();
store.hydrate( props.bootstrap );

const activeTab = ref( 'builder' );
const previewHtml = ref( '' );
const previewLoading = ref( false );
const previewError = ref( '' );
let previewTimer = null;

const tabs = computed( () => [
	{ id: 'builder', label: store.strings.tabBuilder || 'Builder', icon: '▦' },
	{ id: 'settings', label: store.strings.tabSettings || 'Settings', icon: '⚙' },
	{ id: 'events', label: store.strings.tabEvents || 'After submit', icon: '⚡' },
] );

const layoutJson = computed( () => JSON.stringify( store.layoutTree ) );
const schemaJson = computed( () => JSON.stringify( store.schemaJson ) );
const eventsJson = computed( () => JSON.stringify( store.events ) );

function syncHidden() {
	const layoutEl = document.querySelector( 'input[name="zskeleton_form_layout_tree_json"]' );
	const schemaEl = document.querySelector( 'input[name="zskeleton_form_schema_json"]' );
	const eventsEl = document.querySelector( 'input[name="zskeleton_form_events_json"]' );
	if ( layoutEl ) {
		layoutEl.value = layoutJson.value;
	}
	if ( schemaEl ) {
		schemaEl.value = schemaJson.value;
	}
	if ( eventsEl ) {
		eventsEl.value = eventsJson.value;
	}
}

async function loadPreview() {
	previewLoading.value = true;
	previewError.value = '';
	try {
		previewHtml.value = await fetchPreview( {
			ajaxUrl: store.ajaxUrl,
			nonce: store.nonce,
			postId: store.postId,
			layoutTree: store.layoutTree,
			fallbackMessage: store.strings.previewFailed,
		} );
	} catch ( err ) {
		previewHtml.value = '';
		previewError.value = err.message || store.strings.previewFailed || 'Preview failed.';
	} finally {
		previewLoading.value = false;
	}
}

function schedulePreview() {
	if ( previewTimer ) {
		clearTimeout( previewTimer );
	}
	previewTimer = setTimeout( loadPreview, 500 );
}

function onLayoutChange( tree ) {
	store.layoutTree = tree;
	schedulePreview();
}

function onReorderSelection( payload ) {
	store.adjustSelectionAfterReorder( payload );
}

function onFieldUpdate( patch ) {
	store.updateSelectedField( patch );
	schedulePreview();
}

function onSettingsUpdate( settings ) {
	store.settings = settings;
}

onMounted( () => {
	useWpSave( syncHidden );
	loadPreview();
	document.getElementById( 'zs-form-kit-app' )?.classList.add( 'is-ready' );
} );

watch( [ layoutJson, schemaJson, eventsJson ], syncHidden );
</script>

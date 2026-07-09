<template>
	<aside class="zs-fb-panel" :aria-label="strings.fieldsPalette || 'Field types'">
		<h3 class="zs-fb-panel__head">{{ strings.fields || 'Fields' }}</h3>
		<div class="zs-fb-search-wrap">
			<input
				v-model="query"
				type="search"
				class="zs-fb-search"
				:placeholder="strings.searchFields || 'Search field types…'"
				autocomplete="off"
			/>
		</div>
		<ul v-if="filteredTypes.length" class="zs-fb-palette-list zs-fb-palette-list--grid">
			<FieldPalette
				v-for="type in filteredTypes"
				:key="type"
				:type="type"
				:label="typeLabel( type )"
				@add="( t ) => $emit( 'add-field', t )"
			/>
		</ul>
		<p v-else class="zs-fb-palette-empty">{{ strings.noFieldMatch || 'No matching field types.' }}</p>
	</aside>
</template>

<script setup>
import { computed, ref } from 'vue';
import FieldPalette from './FieldPalette.vue';
import { fieldTypeLabel } from '../composables/fieldTypeMeta.js';

const props = defineProps( {
	fieldTypes: { type: Array, default: () => [] },
	strings: { type: Object, default: () => ( {} ) },
} );
defineEmits( [ 'add-field' ] );

const query = ref( '' );

const filteredTypes = computed( () => {
	const q = query.value.trim().toLowerCase();
	if ( ! q ) {
		return props.fieldTypes;
	}
	return props.fieldTypes.filter( ( type ) => {
		const label = fieldTypeLabel( type ).toLowerCase();
		return type.includes( q ) || label.includes( q );
	} );
} );

function typeLabel( type ) {
	return fieldTypeLabel( type );
}
</script>

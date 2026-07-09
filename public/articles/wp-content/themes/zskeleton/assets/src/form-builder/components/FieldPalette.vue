<template>
	<li>
		<button type="button" class="zs-fb-palette-btn" @click="$emit('add', type)">
			<span class="zs-fb-palette-icon" :class="meta.iconClass" aria-hidden="true">{{ meta.icon }}</span>
			<span>{{ label }}</span>
		</button>
	</li>
</template>

<script setup>
import { computed } from 'vue';
import { fieldTypeMeta } from '../composables/fieldTypeMeta.js';

const props = defineProps( {
	type: { type: String, required: true },
	label: { type: String, required: true },
} );

defineEmits( [ 'add' ] );

const meta = computed( () => fieldTypeMeta( props.type ) );
</script>

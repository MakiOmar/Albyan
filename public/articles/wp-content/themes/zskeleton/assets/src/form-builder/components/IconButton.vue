<template>
	<button
		type="button"
		class="zs-fb-icon-btn"
		:class="variantClass"
		:title="title"
		:aria-label="ariaLabel || title"
		@click="$emit( 'click', $event )"
	>
		<svg
			v-if="icon === 'edit'"
			class="zs-fb-icon-btn__svg"
			viewBox="0 0 20 20"
			fill="currentColor"
			aria-hidden="true"
		>
			<path d="M13.586 3.586a2 2 0 0 1 2.828 2.828l-8.5 8.5a1 1 0 0 1-.39.242l-3.5 1.25a.75.75 0 0 1-.962-.962l1.25-3.5a1 1 0 0 1 .242-.39l8.5-8.5zM12.172 5l-8.5 8.5-.589 1.648L5.73 14.5 14.23 6l-2.058-2z" />
		</svg>
		<svg
			v-else-if="icon === 'delete'"
			class="zs-fb-icon-btn__svg"
			viewBox="0 0 20 20"
			fill="currentColor"
			aria-hidden="true"
		>
			<path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 9.24A2.75 2.75 0 0 0 7.596 17h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-9.24.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5zM8 3.75A1.25 1.25 0 0 1 9.25 2.5h1.5A1.25 1.25 0 0 1 12 3.75v.443a43.076 43.076 0 0 0-4-.003V3.75z" clip-rule="evenodd" />
		</svg>
		<svg
			v-else-if="icon === 'close'"
			class="zs-fb-icon-btn__svg"
			viewBox="0 0 20 20"
			fill="currentColor"
			aria-hidden="true"
		>
			<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414z" clip-rule="evenodd" />
		</svg>
		<slot v-else />
	</button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps( {
	icon: { type: String, default: '' },
	variant: { type: String, default: 'default' },
	title: { type: String, default: '' },
	ariaLabel: { type: String, default: '' },
} );

defineEmits( [ 'click' ] );

const variantClass = computed( () => {
	if ( props.variant === 'edit' ) {
		return 'zs-fb-icon-btn--edit';
	}
	if ( props.variant === 'danger' ) {
		return 'zs-fb-icon-btn--danger';
	}
	if ( props.variant === 'close' ) {
		return 'zs-fb-icon-btn--close';
	}
	return '';
} );
</script>

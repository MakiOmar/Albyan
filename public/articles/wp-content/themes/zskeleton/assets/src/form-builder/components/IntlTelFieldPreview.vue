<script setup>
import { computed } from 'vue';
import IntlTelInput from '@intl-tel-input/vue';
import 'intl-tel-input/styles';

const props = defineProps( {
	initialCountry: { type: String, default: 'auto' },
	placeholder: { type: String, default: '' },
} );

const loadUtils = () => import( 'intl-tel-input/utils' );

const inputProps = computed( () => ( {
	class: 'zs-fb-input',
	placeholder: props.placeholder || '',
	autocomplete: 'tel',
} ) );
</script>

<template>
	<div class="zs-fb-intl-tel-preview">
		<IntlTelInput
			:initial-country="initialCountry || 'auto'"
			separate-dial-code
			:load-utils="loadUtils"
			:input-props="inputProps"
		/>
	</div>
</template>

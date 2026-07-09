<script setup>
import { computed } from 'vue';
import IntlTelInput from '@intl-tel-input/vue';
import { ar } from 'intl-tel-input/locale';
import 'intl-tel-input/styles';

const props = defineProps( {
	initialCountry: { type: String, default: '' },
	placeholder: { type: String, default: '' },
} );

const loadUtils = () => import( 'intl-tel-input/utils' );

const intlConfig = computed( () => {
	if ( typeof window !== 'undefined' && window.zskeletonIntlTelConfig ) {
		return window.zskeletonIntlTelConfig;
	}
	return {};
} );

const isArabic = computed( () => !! intlConfig.value.isArabic );
const isRtl = computed( () => !! intlConfig.value.isRtl );

const initialCountryLookup = async () => {
	const config = intlConfig.value;
	const geoUrl = config.geoUrl || 'https://ipapi.co/json/';
	try {
		const response = await fetch( geoUrl, { credentials: 'omit' } );
		const data = await response.json();
		const code = String( data.country_code || data.country || '' ).toLowerCase();
		if ( code ) {
			return code;
		}
	} catch ( error ) {
		// Use locale fallback.
	}
	return config.fallbackCountry || ( isArabic.value ? 'ae' : 'us' );
};

const inputProps = computed( () => ( {
	class: 'zs-fb-input',
	placeholder: props.placeholder || '',
	autocomplete: 'tel',
} ) );

const useAutoDetect = computed( () => ! props.initialCountry || props.initialCountry === 'auto' );
</script>

<template>
	<div class="zs-fb-intl-tel-preview" :dir="isRtl ? 'rtl' : undefined">
		<IntlTelInput
			v-if="useAutoDetect"
			separate-dial-code
			:initial-country-lookup="initialCountryLookup"
			:ui-translations="isArabic ? ar : undefined"
			:country-name-locale="isArabic ? 'ar' : undefined"
			:load-utils="loadUtils"
			:input-props="inputProps"
		/>
		<IntlTelInput
			v-else
			:initial-country="initialCountry"
			separate-dial-code
			:ui-translations="isArabic ? ar : undefined"
			:country-name-locale="isArabic ? 'ar' : undefined"
			:load-utils="loadUtils"
			:input-props="inputProps"
		/>
	</div>
</template>

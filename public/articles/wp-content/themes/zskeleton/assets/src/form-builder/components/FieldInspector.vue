<template>
	<Teleport to="body">
		<div v-if="open && field" id="zs-form-kit-portal">
			<div
				class="zs-fb-modal zs-fb-modal--inspector"
				@click.self="onClose"
			>
				<div
					class="zs-fb-modal__dialog zs-fb-modal__dialog--inspector"
					role="dialog"
					aria-modal="true"
					:aria-labelledby="titleId"
					@click.stop
				>
				<header class="zs-fb-modal__head zs-fb-inspector__head">
					<div class="zs-fb-inspector__hero">
						<span class="zs-fb-inspector__icon" :class="meta.iconClass" aria-hidden="true">{{ meta.icon }}</span>
						<div class="zs-fb-inspector__intro">
							<p class="zs-fb-inspector__eyebrow">{{ strings.inspectorTitle || 'Field settings' }}</p>
							<h2 :id="titleId" class="zs-fb-inspector__title">{{ field.label || field.name }}</h2>
							<span class="zs-fb-inspector__type-badge">{{ typeLabel( local.type ) }}</span>
						</div>
					</div>
					<IconButton
						icon="close"
						variant="close"
						class="zs-fb-modal__close"
						:aria-label="strings.close || 'Close'"
						@click="onClose"
					/>
				</header>

				<div class="zs-fb-modal__body zs-fb-inspector__body">
					<div class="zs-fb-inspector__sections">
						<section class="zs-fb-inspector__section">
							<h3 class="zs-fb-inspector__section-title">{{ strings.sectionGeneral || 'General' }}</h3>
							<div class="zs-fb-inspector__fields">
								<div class="zs-fb-control">
									<label :for="idPrefix + '-label'">{{ strings.label || 'Label' }}</label>
									<input
										:id="idPrefix + '-label'"
										v-model="local.label"
										type="text"
										class="zs-fb-input"
										@input="emitUpdate"
									/>
								</div>
								<div class="zs-fb-control">
									<label :for="idPrefix + '-name'">{{ strings.name || 'Field name' }}</label>
									<input
										:id="idPrefix + '-name'"
										v-model="local.name"
										type="text"
										class="zs-fb-input"
										pattern="[a-z0-9_]+"
										spellcheck="false"
										autocomplete="off"
										@change="onNameChange"
									/>
									<p v-if="strings.nameHelp" class="zs-fb-control__hint">{{ strings.nameHelp }}</p>
								</div>
								<div class="zs-fb-control">
									<label :for="idPrefix + '-type'">{{ strings.type || 'Type' }}</label>
									<select :id="idPrefix + '-type'" v-model="local.type" class="zs-fb-select" @change="emitUpdate">
										<option v-for="t in fieldTypes" :key="t" :value="t">{{ typeLabel( t ) }}</option>
									</select>
								</div>
							</div>
						</section>

						<section class="zs-fb-inspector__section">
							<h3 class="zs-fb-inspector__section-title">{{ strings.sectionValidation || 'Validation' }}</h3>
							<div class="zs-fb-inspector__fields">
								<div class="zs-fb-toggle-row zs-fb-toggle-row--card">
									<div class="zs-fb-toggle-row__copy">
										<span class="zs-fb-toggle-row__label">{{ strings.required || 'Required field' }}</span>
										<p class="zs-fb-control__hint">{{ strings.requiredHelp || 'Visitors must fill this field before submitting.' }}</p>
									</div>
									<label class="zs-fb-switch">
										<input v-model="local.required" type="checkbox" @change="emitUpdate" />
										<span class="zs-fb-switch__track" aria-hidden="true" />
									</label>
								</div>
								<template v-if="supportsPattern">
									<div class="zs-fb-control">
										<label :for="idPrefix + '-pattern'">{{ strings.pattern || 'Regex pattern' }}</label>
										<input
											:id="idPrefix + '-pattern'"
											v-model="local.pattern"
											type="text"
											class="zs-fb-input"
											spellcheck="false"
											autocomplete="off"
											:placeholder="strings.patternPlaceholder || '^[A-Za-z0-9]+$'"
											@input="emitUpdate"
										/>
										<p class="zs-fb-control__hint">{{ strings.patternHelp || 'Optional. Validates the value on submit. Use a JavaScript-style pattern (slashes and flags are optional).' }}</p>
									</div>
									<div v-if="local.pattern.trim()" class="zs-fb-control">
										<label :for="idPrefix + '-pattern-message'">{{ strings.patternMessage || 'Validation message' }}</label>
										<input
											:id="idPrefix + '-pattern-message'"
											v-model="local.patternMessage"
											type="text"
											class="zs-fb-input"
											:placeholder="strings.patternMessagePlaceholder || 'Invalid format.'"
											@input="emitUpdate"
										/>
										<p class="zs-fb-control__hint">{{ strings.patternMessageHelp || 'Shown when the value does not match the pattern.' }}</p>
									</div>
								</template>
							</div>
						</section>

						<section v-if="isTelField" class="zs-fb-inspector__section">
							<h3 class="zs-fb-inspector__section-title">{{ strings.sectionPhone || 'Phone input' }}</h3>
							<div class="zs-fb-inspector__fields">
								<div class="zs-fb-toggle-row zs-fb-toggle-row--card">
									<div class="zs-fb-toggle-row__copy">
										<span class="zs-fb-toggle-row__label">{{ strings.intlTel || 'Country dial codes' }}</span>
										<p class="zs-fb-control__hint">{{ strings.intlTelHelp || 'Show a country selector with international dial codes (intl-tel-input).' }}</p>
									</div>
									<label class="zs-fb-switch">
										<input v-model="local.intlTel" type="checkbox" @change="emitUpdate" />
										<span class="zs-fb-switch__track" aria-hidden="true" />
									</label>
								</div>
								<div v-if="local.intlTel" class="zs-fb-control">
									<label :for="idPrefix + '-initial-country'">{{ strings.initialCountry || 'Default country' }}</label>
									<select
										:id="idPrefix + '-initial-country'"
										v-model="local.initialCountry"
										class="zs-fb-select"
										@change="emitUpdate"
									>
										<option value="">{{ strings.initialCountryAuto || 'Auto-detect visitor country' }}</option>
										<option v-for="c in initialCountries" :key="c.value" :value="c.value">{{ c.label }}</option>
									</select>
								</div>
								<div v-if="local.intlTel" class="zs-fb-control zs-fb-intl-tel-preview-wrap">
									<p class="zs-fb-control__hint">{{ strings.intlTelPreview || 'Preview' }}</p>
									<IntlTelFieldPreview
										:initial-country="local.initialCountry || 'auto'"
										:placeholder="local.placeholder"
									/>
								</div>
							</div>
						</section>

						<section class="zs-fb-inspector__section">
							<h3 class="zs-fb-inspector__section-title">{{ strings.sectionDisplay || 'Display' }}</h3>
							<div class="zs-fb-inspector__fields">
								<div class="zs-fb-control">
									<label :for="idPrefix + '-placeholder'">{{ strings.placeholder || 'Placeholder' }}</label>
									<input
										:id="idPrefix + '-placeholder'"
										v-model="local.placeholder"
										type="text"
										class="zs-fb-input"
										@input="emitUpdate"
									/>
								</div>
								<div class="zs-fb-control">
									<label :for="idPrefix + '-description'">{{ strings.description || 'Description' }}</label>
									<input
										:id="idPrefix + '-description'"
										v-model="local.description"
										type="text"
										class="zs-fb-input"
										@input="emitUpdate"
									/>
									<p class="zs-fb-control__hint">{{ strings.descriptionHelp || 'Shown below the field on the public form.' }}</p>
								</div>
								<div v-if="hasChoices" class="zs-fb-control">
									<label :for="idPrefix + '-choices'">{{ strings.choices || 'Choices' }}</label>
									<textarea
										:id="idPrefix + '-choices'"
										v-model="choicesText"
										class="zs-fb-textarea"
										rows="4"
										:placeholder="strings.choicesPlaceholder || 'value|Label (one per line)'"
										@change="onChoicesChange"
									/>
									<p class="zs-fb-control__hint">{{ strings.choicesHelp || 'One option per line. Use value|Label or a single label.' }}</p>
								</div>
							</div>
						</section>
					</div>
				</div>

				<footer class="zs-fb-modal__foot zs-fb-inspector__foot">
					<button type="button" class="zs-fb-btn zs-fb-btn--ghost" @click="onClose">
						{{ strings.cancel || 'Cancel' }}
					</button>
					<button type="button" class="zs-fb-btn zs-fb-btn--primary" @click="onClose">
						{{ strings.done || strings.close || 'Done' }}
					</button>
				</footer>
				</div>
			</div>
		</div>
	</Teleport>
</template>

<script setup>
import { computed, onUnmounted, reactive, watch } from 'vue';
import IconButton from './IconButton.vue';
import IntlTelFieldPreview from './IntlTelFieldPreview.vue';
import { slugifyName } from '../composables/useFormBuilder.js';
import { fieldTypeLabel, fieldTypeMeta } from '../composables/fieldTypeMeta.js';

const INITIAL_COUNTRIES = [
	{ value: 'sa', label: 'Saudi Arabia (+966)' },
	{ value: 'ae', label: 'United Arab Emirates (+971)' },
	{ value: 'eg', label: 'Egypt (+20)' },
	{ value: 'us', label: 'United States (+1)' },
	{ value: 'gb', label: 'United Kingdom (+44)' },
	{ value: 'de', label: 'Germany (+49)' },
	{ value: 'fr', label: 'France (+33)' },
];

const props = defineProps( {
	open: { type: Boolean, default: false },
	field: { type: Object, default: null },
	fieldTypes: { type: Array, default: () => [] },
	strings: { type: Object, default: () => ( {} ) },
	idPrefix: { type: String, default: 'zs-inspector' },
} );

const emit = defineEmits( [ 'update', 'close' ] );

const titleId = `${ props.idPrefix }-modal-title`;

const PATTERN_FIELD_TYPES = [ 'text', 'email', 'tel', 'url', 'textarea', 'number' ];

const local = reactive( {
	label: '',
	name: '',
	type: 'text',
	required: false,
	placeholder: '',
	description: '',
	pattern: '',
	patternMessage: '',
	intlTel: false,
	initialCountry: '',
} );

const meta = computed( () => fieldTypeMeta( props.field?.type || 'text' ) );

const hasChoices = computed( () =>
	[ 'select', 'radio', 'checkbox' ].includes( local.type )
);

const supportsPattern = computed( () => PATTERN_FIELD_TYPES.includes( local.type ) );

const isTelField = computed( () => local.type === 'tel' );

const initialCountries = INITIAL_COUNTRIES;

const choicesText = computed( {
	get() {
		if ( ! props.field?.choices ) {
			return '';
		}
		return Object.entries( props.field.choices )
			.map( ( [ k, v ] ) => ( k === v ? k : `${ k }|${ v }` ) )
			.join( '\n' );
	},
	set() {},
} );

function onKeydown( e ) {
	if ( e.key === 'Escape' && props.open ) {
		onClose();
	}
}

watch(
	() => props.open,
	( isOpen ) => {
		if ( isOpen ) {
			document.body.classList.add( 'zs-fb-modal-open' );
			document.addEventListener( 'keydown', onKeydown );
		} else {
			document.body.classList.remove( 'zs-fb-modal-open' );
			document.removeEventListener( 'keydown', onKeydown );
		}
	},
	{ immediate: true }
);

onUnmounted( () => {
	document.body.classList.remove( 'zs-fb-modal-open' );
	document.removeEventListener( 'keydown', onKeydown );
} );

watch(
	() => props.field,
	( f ) => {
		if ( ! f ) {
			return;
		}
		local.label = f.label || '';
		local.name = f.name || '';
		local.type = f.type || 'text';
		local.required = !! f.required;
		local.placeholder = f.placeholder || '';
		local.description = f.description || '';
		local.pattern = displayPattern( f.rules?.pattern || '' );
		local.patternMessage = f.rules?.pattern_message || '';
		local.intlTel = !! f.intl_tel;
		local.initialCountry = f.initial_country || '';
	},
	{ immediate: true, deep: true }
);

function typeLabel( type ) {
	return fieldTypeLabel( type );
}

function onClose() {
	emit( 'close' );
}

function displayPattern( pattern ) {
	if ( ! pattern ) {
		return '';
	}
	const match = String( pattern ).match( /^\/(.+)\/[gimsuy]*$/ );
	return match ? match[ 1 ] : String( pattern );
}

function buildRulesPatch() {
	const rules = { ...( props.field?.rules || {} ) };
	const pattern = local.pattern.trim();
	const patternMessage = local.patternMessage.trim();

	if ( pattern ) {
		rules.pattern = pattern;
	} else {
		delete rules.pattern;
	}

	if ( patternMessage ) {
		rules.pattern_message = patternMessage;
	} else {
		delete rules.pattern_message;
	}

	return Object.keys( rules ).length ? rules : undefined;
}

function emitUpdate() {
	const patch = {
		label: local.label,
		name: local.name,
		type: local.type,
		required: local.required,
		placeholder: local.placeholder,
		description: local.description,
		choices: props.field?.choices,
		rules: buildRulesPatch(),
	};

	if ( local.type === 'tel' ) {
		patch.intl_tel = !! local.intlTel;
		if ( local.intlTel && local.initialCountry ) {
			patch.initial_country = local.initialCountry;
		} else {
			patch.initial_country = undefined;
		}
	}

	emit( 'update', patch );
}

function onNameChange() {
	local.name = slugifyName( local.name );
	emitUpdate();
}

function onChoicesChange( e ) {
	const lines = String( e.target.value || '' )
		.split( '\n' )
		.map( ( l ) => l.trim() )
		.filter( Boolean );
	const choices = {};
	lines.forEach( ( line ) => {
		const parts = line.split( '|' );
		const key = slugifyName( parts[ 0 ] );
		choices[ key ] = parts[ 1 ] ? parts[ 1 ].trim() : parts[ 0 ].trim();
	} );
	emit( 'update', {
		label: local.label,
		name: local.name,
		type: local.type,
		required: local.required,
		placeholder: local.placeholder,
		description: local.description,
		choices: Object.keys( choices ).length ? choices : undefined,
		rules: buildRulesPatch(),
		intl_tel: local.type === 'tel' ? !! local.intlTel : undefined,
		initial_country: local.type === 'tel' && local.intlTel && local.initialCountry ? local.initialCountry : undefined,
	} );
}
</script>

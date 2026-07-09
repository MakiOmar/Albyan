<template>
	<article class="zs-fb-event-card">
		<header class="zs-fb-event-card__head">
			<span
				class="zs-fb-event-card__drag zs-fb-drag-handle"
				:aria-label="strings.dragAction || 'Drag to reorder action'"
				title="Drag to reorder"
				role="button"
				tabindex="0"
			>
				<svg class="zs-fb-grip__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
					<circle cx="7" cy="5" r="1.35" /><circle cx="13" cy="5" r="1.35" />
					<circle cx="7" cy="10" r="1.35" /><circle cx="13" cy="10" r="1.35" />
					<circle cx="7" cy="15" r="1.35" /><circle cx="13" cy="15" r="1.35" />
				</svg>
			</span>
			<div class="zs-fb-control zs-fb-control--compact">
				<label class="zs-fb-control__label" :for="fieldId( 'type' )">{{ strings.actionType || 'Action' }}</label>
				<select :id="fieldId( 'type' )" :value="event.type" class="zs-fb-select" @change="onTypeChange">
					<option v-for="t in eventTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
				</select>
			</div>
			<div class="zs-fb-toggle-row zs-fb-toggle-row--card zs-fb-event-card__toggle">
				<span class="zs-fb-toggle-row__label">{{ strings.enabled || 'Enabled' }}</span>
				<label class="zs-fb-switch">
					<input :checked="event.enabled" type="checkbox" @change="onEnabledChange" />
					<span class="zs-fb-switch__track" aria-hidden="true" />
				</label>
			</div>
		</header>

		<div v-if="hasExtraFields" class="zs-fb-event-card__body">
			<template v-if="event.type === 'email_admin'">
				<div class="zs-fb-control">
					<label class="zs-fb-control__label" :for="fieldId( 'to' )">{{ strings.adminEmail || 'Admin email' }}</label>
					<input
						:id="fieldId( 'to' )"
						:value="event.to || ''"
						type="email"
						class="zs-fb-input"
						:placeholder="strings.adminEmailPlaceholder || 'admin@example.com'"
						@input="patch( { to: $event.target.value } )"
					/>
				</div>
				<div class="zs-fb-control">
					<label class="zs-fb-control__label" :for="fieldId( 'subject' )">{{ strings.subject || 'Subject' }}</label>
					<input
						:id="fieldId( 'subject' )"
						:value="event.subject || ''"
						type="text"
						class="zs-fb-input"
						:placeholder="strings.defaultAdminSubject || 'New form submission'"
						@input="patch( { subject: $event.target.value } )"
					/>
				</div>
			</template>

			<template v-else-if="event.type === 'email_user'">
				<div class="zs-fb-control">
					<label class="zs-fb-control__label" :for="fieldId( 'to_field' )">{{ strings.toField || 'Email field name' }}</label>
					<input
						:id="fieldId( 'to_field' )"
						:value="event.to_field || 'email'"
						type="text"
						class="zs-fb-input"
						@input="patch( { to_field: $event.target.value } )"
					/>
				</div>
				<div class="zs-fb-control">
					<label class="zs-fb-control__label" :for="fieldId( 'subject-user' )">{{ strings.subject || 'Subject' }}</label>
					<input
						:id="fieldId( 'subject-user' )"
						:value="event.subject || ''"
						type="text"
						class="zs-fb-input"
						@input="patch( { subject: $event.target.value } )"
					/>
				</div>
				<div class="zs-fb-control zs-fb-control--wide">
					<label class="zs-fb-control__label" :for="fieldId( 'body' )">{{ strings.body || 'Email body' }}</label>
					<textarea
						:id="fieldId( 'body' )"
						:value="event.body || ''"
						class="zs-fb-textarea"
						rows="3"
						@input="patch( { body: $event.target.value } )"
					/>
				</div>
			</template>

			<template v-else-if="event.type === 'mailerlite_subscribe'">
				<div class="zs-fb-control">
					<label class="zs-fb-control__label" :for="fieldId( 'email_field' )">{{ strings.emailField || 'Email field name' }}</label>
					<input
						:id="fieldId( 'email_field' )"
						:value="event.email_field || 'email'"
						type="text"
						class="zs-fb-input"
						@input="patch( { email_field: $event.target.value } )"
					/>
				</div>
				<div class="zs-fb-control">
					<label class="zs-fb-control__label" :for="fieldId( 'group_key' )">{{ strings.groupKey || 'MailerLite group key' }}</label>
					<input
						:id="fieldId( 'group_key' )"
						:value="event.group_key || ''"
						type="text"
						class="zs-fb-input"
						@input="patch( { group_key: $event.target.value } )"
					/>
				</div>
			</template>

			<template v-else-if="event.type === 'redirect'">
				<div class="zs-fb-control zs-fb-control--wide">
					<label class="zs-fb-control__label" :for="fieldId( 'url' )">{{ strings.redirectUrl || 'Redirect URL' }}</label>
					<p class="zs-fb-control__hint">{{ strings.redirectUrlHint || 'Absolute URL or site path (e.g. /thank-you). Use {field_name} tokens from submitted values.' }}</p>
					<input
						:id="fieldId( 'url' )"
						:value="event.url || ''"
						type="text"
						class="zs-fb-input"
						placeholder="https://example.com/thank-you"
						@input="patch( { url: $event.target.value } )"
					/>
				</div>
			</template>
		</div>

		<footer class="zs-fb-event-card__foot">
			<button type="button" class="zs-fb-btn zs-fb-btn--ghost zs-fb-btn--sm" @click="$emit( 'remove' )">
				{{ strings.remove || 'Remove' }}
			</button>
		</footer>
	</article>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps( {
	event: { type: Object, required: true },
	eventTypes: { type: Array, default: () => [] },
	strings: { type: Object, default: () => ( {} ) },
	index: { type: Number, default: 0 },
} );

const emit = defineEmits( [ 'update', 'remove' ] );

const hasExtraFields = computed( () => {
	return [ 'email_admin', 'email_user', 'mailerlite_subscribe', 'redirect' ].includes( props.event.type );
} );

function fieldId( suffix ) {
	return `zs-fb-event-${ props.index }-${ suffix }`;
}

function patch( data ) {
	emit( 'update', { ...props.event, ...data } );
}

function onTypeChange( e ) {
	const type = e.target.value;
	const next = { type, enabled: props.event.enabled, _uid: props.event._uid };
	if ( type === 'email_admin' && ! next.subject ) {
		next.subject = props.strings.defaultAdminSubject || 'New form submission';
	}
	if ( type === 'redirect' && props.event.url ) {
		next.url = props.event.url;
	}
	emit( 'update', next );
}

function onEnabledChange( e ) {
	patch( { enabled: e.target.checked } );
}
</script>

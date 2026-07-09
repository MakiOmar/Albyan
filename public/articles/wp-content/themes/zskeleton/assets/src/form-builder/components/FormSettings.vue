<template>
	<div class="zs-fb-settings-panel">
		<div class="zs-fb-panel">
			<h3 class="zs-fb-panel__head">{{ strings.tabSettings || 'Settings' }}</h3>
			<div class="zs-fb-panel__body zs-fb-settings">
				<div class="zs-fb-settings__grid">
					<section class="zs-fb-settings-card">
						<h4 class="zs-fb-settings-card__title">{{ strings.sectionSubmission || 'Submission' }}</h4>
						<div class="zs-fb-settings-card__fields">
							<div class="zs-fb-toggle-row zs-fb-toggle-row--card">
								<div class="zs-fb-toggle-row__copy">
									<span class="zs-fb-toggle-row__label">{{ strings.allowPublic || 'Allow public submissions' }}</span>
									<p class="zs-fb-control__hint">{{ strings.allowPublicHint || 'Visitors can submit without logging in.' }}</p>
								</div>
								<label class="zs-fb-switch">
									<input v-model="local.allowPublic" type="checkbox" @change="emitUpdate" />
									<span class="zs-fb-switch__track" aria-hidden="true" />
								</label>
							</div>
							<div class="zs-fb-control">
								<label class="zs-fb-control__label" :for="id + '-submit-text'">{{ strings.submitButtonText || 'Submit button text' }}</label>
								<p class="zs-fb-control__hint">{{ strings.submitButtonTextHint || 'Label on the primary submit button. Leave empty for the default “Submit”.' }}</p>
								<input
									:id="id + '-submit-text'"
									v-model="local.submitButtonText"
									type="text"
									class="zs-fb-input"
									:placeholder="strings.submitButtonTextPlaceholder || 'Submit'"
									@input="emitUpdate"
								/>
							</div>
						</div>
					</section>

					<section class="zs-fb-settings-card zs-fb-settings-card--wide">
						<h4 class="zs-fb-settings-card__title">{{ strings.sectionSubmissionsManager || 'Submissions manager' }}</h4>
						<div class="zs-fb-settings-card__fields">
							<div class="zs-fb-control">
								<label class="zs-fb-control__label" :for="id + '-manager-roles'">{{ strings.submissionsManagerRoles || 'Who can manage submissions (roles)' }}</label>
								<p class="zs-fb-control__hint">{{ strings.submissionsManagerRolesHint || 'Comma-separated WordPress role slugs. When set here, shortcode roles/users attributes are ignored.' }}</p>
								<input
									:id="id + '-manager-roles'"
									v-model="local.submissionsManagerRoles"
									type="text"
									class="zs-fb-input"
									placeholder="editor, administrator"
									@input="emitUpdate"
								/>
							</div>
							<div class="zs-fb-control">
								<label class="zs-fb-control__label" :for="id + '-manager-users'">{{ strings.submissionsManagerUsers || 'Who can manage submissions (users)' }}</label>
								<p class="zs-fb-control__hint">{{ strings.submissionsManagerUsersHint || 'Comma-separated user IDs. Either a listed role or user ID grants access.' }}</p>
								<input
									:id="id + '-manager-users'"
									v-model="local.submissionsManagerUsers"
									type="text"
									class="zs-fb-input"
									placeholder="12, 34"
									@input="emitUpdate"
								/>
							</div>
						</div>
					</section>

					<section class="zs-fb-settings-card">
						<h4 class="zs-fb-settings-card__title">{{ strings.sectionLayout || 'Layout' }}</h4>
						<div class="zs-fb-toggle-row zs-fb-toggle-row--card">
							<div class="zs-fb-toggle-row__copy">
								<span class="zs-fb-toggle-row__label">{{ strings.mobileStackRows || 'Stack columns on mobile' }}</span>
								<p class="zs-fb-control__hint">{{ strings.mobileStackRowsHint || 'Force multi-column rows to stack into a single column on small screens.' }}</p>
							</div>
							<label class="zs-fb-switch">
								<input v-model="local.mobileStackRows" type="checkbox" @change="emitUpdate" />
								<span class="zs-fb-switch__track" aria-hidden="true" />
							</label>
						</div>
					</section>

					<section class="zs-fb-settings-card">
						<h4 class="zs-fb-settings-card__title">{{ strings.sectionSecurity || 'Security' }}</h4>
						<div class="zs-fb-control">
							<label class="zs-fb-control__label" :for="id + '-honeypot'">{{ strings.honeypot || 'Honeypot field name' }}</label>
							<p class="zs-fb-control__hint">{{ strings.honeypotHint || 'Hidden field name used to catch bots. Leave as default unless you know what you are doing.' }}</p>
							<input
								:id="id + '-honeypot'"
								v-model="local.honeypot"
								type="text"
								class="zs-fb-input"
								autocomplete="off"
								@input="emitUpdate"
							/>
						</div>
					</section>

					<section class="zs-fb-settings-card zs-fb-settings-card--wide">
						<h4 class="zs-fb-settings-card__title">{{ strings.sectionAfterSubmit || 'After submit' }}</h4>
						<div class="zs-fb-settings-card__fields">
							<div class="zs-fb-control">
								<label class="zs-fb-control__label" :for="id + '-success'">{{ strings.successMessage || 'Success message' }}</label>
								<p class="zs-fb-control__hint">{{ strings.successMessageHint || 'Shown after submit when no redirect action runs, or while redirect loads.' }}</p>
								<textarea
									:id="id + '-success'"
									v-model="local.successMessage"
									class="zs-fb-textarea"
									rows="3"
									:placeholder="strings.successMessagePlaceholder || 'Thank you — we received your submission.'"
									@input="emitUpdate"
								/>
							</div>
							<p class="zs-fb-control__hint zs-fb-settings__redirect-note">
								{{ strings.redirectMovedHint || 'To redirect visitors after submit, add a Redirect action on the After submit tab and drag it to the desired position in the list.' }}
							</p>
						</div>
					</section>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps( {
	settings: { type: Object, required: true },
	strings: { type: Object, default: () => ( {} ) },
	id: { type: String, default: 'zs-form-settings' },
} );

const emit = defineEmits( [ 'update' ] );

const local = reactive( {
	allowPublic: true,
	honeypot: '',
	successMessage: '',
	submitButtonText: '',
	submissionsManagerRoles: '',
	submissionsManagerUsers: '',
	mobileStackRows: true,
} );

watch(
	() => props.settings,
	( s ) => {
		local.allowPublic = s.allowPublic !== false;
		local.honeypot = s.honeypot || '';
		local.successMessage = s.successMessage || '';
		local.submitButtonText = s.submitButtonText || '';
		local.submissionsManagerRoles = s.submissionsManagerRoles || '';
		local.submissionsManagerUsers = s.submissionsManagerUsers || '';
		local.mobileStackRows = s.mobileStackRows !== false;
	},
	{ immediate: true, deep: true }
);

function emitUpdate() {
	emit( 'update', { ...local } );
}
</script>

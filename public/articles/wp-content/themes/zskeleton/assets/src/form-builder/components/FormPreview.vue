<template>
	<Teleport to="body">
		<div v-if="open" id="zs-form-kit-portal">
			<div
				class="zs-fb-modal"
				@click.self="onClose"
			>
				<div
					class="zs-fb-modal__dialog zs-fb-modal__dialog--preview"
					role="dialog"
					aria-modal="true"
					:aria-labelledby="titleId"
					@click.stop
				>
				<header class="zs-fb-modal__head">
					<div class="zs-fb-modal__title-wrap">
						<div>
							<h2 :id="titleId" class="zs-fb-modal__title">{{ strings.preview || 'Live preview' }}</h2>
							<p v-if="!loading && !error" class="zs-fb-modal__subtitle">
								<span class="zs-fb-live-badge">
									<span class="zs-fb-live-badge__dot" aria-hidden="true" />
									{{ strings.live || 'Live' }}
								</span>
							</p>
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

				<div class="zs-fb-modal__body zs-fb-preview__body zs-form-builder__preview">
					<div v-if="loading" class="zs-fb-loading">
						<span class="zs-fb-spinner" aria-hidden="true" />
						{{ strings.loading || 'Loading…' }}
					</div>
					<p v-else-if="error" class="zs-fb-alert">
						{{ error }}
					</p>
					<div v-else class="zs-fb-preview__frame" v-html="html" />
				</div>

				<footer class="zs-fb-modal__foot">
					<button type="button" class="zs-fb-btn" @click="$emit( 'refresh' )">
						<span aria-hidden="true">↻</span>
						{{ strings.refreshPreview || 'Refresh preview' }}
					</button>
					<button type="button" class="zs-fb-btn zs-fb-btn--primary" @click="onClose">
						{{ strings.close || 'Close' }}
					</button>
				</footer>
				</div>
			</div>
		</div>
	</Teleport>
</template>

<script setup>
import { nextTick, onUnmounted, watch } from 'vue';
import IconButton from './IconButton.vue';

const props = defineProps( {
	open: { type: Boolean, default: false },
	html: { type: String, default: '' },
	loading: { type: Boolean, default: false },
	error: { type: String, default: '' },
	strings: { type: Object, default: () => ( {} ) },
} );

const emit = defineEmits( [ 'close', 'refresh' ] );

const titleId = 'zs-fb-preview-modal-title';

function onKeydown( e ) {
	if ( e.key === 'Escape' && props.open ) {
		onClose();
	}
}

watch(
	() => props.open,
	( isOpen ) => {
		if ( isOpen ) {
			document.addEventListener( 'keydown', onKeydown );
		} else {
			document.removeEventListener( 'keydown', onKeydown );
		}
	},
	{ immediate: true }
);

onUnmounted( () => {
	document.removeEventListener( 'keydown', onKeydown );
} );

function initPreviewIntlTel() {
	const frame = document.querySelector( '#zs-form-kit-portal .zs-fb-preview__frame' );
	if ( ! frame || typeof window.zskeletonIntlTel === 'undefined' ) {
		return;
	}
	window.zskeletonIntlTel.destroy( frame );
	window.zskeletonIntlTel.init( frame );
}

watch(
	() => [ props.html, props.loading, props.open ],
	() => {
		if ( ! props.open || props.loading || ! props.html ) {
			return;
		}
		nextTick( () => {
			initPreviewIntlTel();
		} );
	}
);

function onClose() {
	emit( 'close' );
}
</script>

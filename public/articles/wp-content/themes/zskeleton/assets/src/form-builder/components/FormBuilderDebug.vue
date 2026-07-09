<template>
	<details v-if="enabled" class="zs-fb-debug" open>
		<summary>Form builder debug</summary>
		<ul>
			<li>
				Vue mounted:
				<span :class="checks.mounted ? 'is-ok' : 'is-bad'">{{ checks.mounted ? 'yes' : 'no' }}</span>
			</li>
			<li>
				Builder CSS linked:
				<span :class="checks.cssLinked ? 'is-ok' : 'is-bad'">{{ checks.cssLinked ? 'yes' : 'no' }}</span>
			</li>
			<li>
				CSS on server:
				<span :class="checks.cssBytesOk ? 'is-ok' : 'is-bad'">
					{{ assetDebug.cssBytes || 0 }} bytes
				</span>
				(ver {{ assetDebug.cssVer || '?' }})
			</li>
			<li>
				Builder styles applied:
				<span :class="checks.semanticProbe ? 'is-ok' : 'is-bad'">{{ checks.semanticProbe ? 'yes' : 'no' }}</span>
				(tabs display: {{ checks.tabsDisplay || '?' }})
			</li>
			<li>
				Layout grid:
				<span :class="checks.layoutGrid ? 'is-ok' : 'is-bad'">{{ checks.layoutGrid ? 'active' : 'stacked' }}</span>
				(viewport {{ checks.viewport }}px)
			</li>
			<li>
				Tailwind utilities:
				<span :class="checks.tailwindProbe ? 'is-ok' : ''">{{ checks.tailwindProbe ? 'detected' : 'not used (optional)' }}</span>
			</li>
			<li v-if="checks.cssHref" class="fb:break-all">CSS: {{ checks.cssHref }}</li>
			<li>JS ver: {{ assetDebug.jsVer || '?' }}</li>
		</ul>
	</details>
</template>

<script setup>
import { onMounted, reactive, computed } from 'vue';

const props = defineProps( {
	assetDebug: { type: Object, default: () => ( {} ) },
} );

const enabled = computed( () => !! props.assetDebug?.enabled );

const checks = reactive( {
	mounted: false,
	cssLinked: false,
	cssHref: '',
	cssBytesOk: false,
	semanticProbe: false,
	tailwindProbe: false,
	tabsDisplay: '',
	layoutGrid: false,
	viewport: 0,
} );

onMounted( () => {
	checks.mounted = true;
	checks.viewport = window.innerWidth;
	checks.cssBytesOk = ( props.assetDebug?.cssBytes || 0 ) >= 8000;

	const links = Array.from( document.querySelectorAll( 'link[rel="stylesheet"]' ) );
	const builderLink = links.find( ( l ) => /form-builder-admin\.css/i.test( l.href ) );
	checks.cssLinked = !! builderLink;
	checks.cssHref = builderLink?.href || '';

	const tabs = document.querySelector( '#zs-form-kit-app .zs-fb-tabs' );
	if ( tabs ) {
		checks.tabsDisplay = window.getComputedStyle( tabs ).display;
		checks.semanticProbe = checks.tabsDisplay === 'flex' || checks.tabsDisplay === 'inline-flex';
	}

	const twProbe = document.createElement( 'div' );
	twProbe.className = 'fb:flex';
	twProbe.setAttribute( 'aria-hidden', 'true' );
	twProbe.style.position = 'absolute';
	twProbe.style.left = '-9999px';
	document.getElementById( 'zs-form-kit-app' )?.appendChild( twProbe );
	checks.tailwindProbe = window.getComputedStyle( twProbe ).display === 'flex';
	twProbe.remove();

	const layout = document.querySelector( '#zs-form-kit-app .zs-fb-layout' );
	if ( layout ) {
		const cols = window.getComputedStyle( layout ).gridTemplateColumns;
		checks.layoutGrid = cols.includes( ' ' ) || window.innerWidth < 900;
	}

	const root = document.getElementById( 'zs-form-kit-app' );
	if ( root ) {
		root.classList.add( 'is-ready' );
	}

	const failed = ! checks.cssLinked || ! checks.cssBytesOk || ! checks.semanticProbe;
	if ( failed ) {
		// eslint-disable-next-line no-console
		console.warn( '[zs-form-kit] Asset check failed', {
			cssLinked: checks.cssLinked,
			cssBytes: props.assetDebug?.cssBytes,
			semanticProbe: checks.semanticProbe,
			tabsDisplay: checks.tabsDisplay,
			cssHref: checks.cssHref,
		} );
	}
} );
</script>

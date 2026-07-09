<?php
/**
 * General legal policy body (Privacy, Refund, Shipping) for theme-assigned pages.
 *
 * @package ZSkeleton_Theme
 *
 * @var array<string, mixed> $args {
 *     @type string $kind One of privacy, refund, shipping.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kind = 'privacy';
if ( isset( $args['kind'] ) && is_string( $args['kind'] ) ) {
	$kind = sanitize_key( $args['kind'] );
}
if ( ! in_array( $kind, array( 'privacy', 'refund', 'shipping' ), true ) ) {
	$kind = 'privacy';
}

$sections = array(
	'privacy'  => array(
		'lead' => __( 'This page explains how we collect, use, and protect information when you browse our site or use our services. Replace this text with your organization’s policy.', 'zskeleton' ),
		'items' => array(
			__( 'We collect only the information needed to operate the site, respond to inquiries, and improve our content. Analytics may use cookies or similar technologies in line with your browser settings.', 'zskeleton' ),
			__( 'We do not sell personal data. Data may be processed by trusted hosting, email, or analytics providers under appropriate agreements.', 'zskeleton' ),
			__( 'You may request access, correction, or deletion of your personal data where applicable law provides such rights.', 'zskeleton' ),
		),
	),
	'refund'   => array(
		'lead' => __( 'This page summarizes how refunds and billing adjustments work for purchases made through this site. Update amounts, windows, and exceptions to match your terms.', 'zskeleton' ),
		'items' => array(
			__( 'Eligible refunds are returned to the original payment method unless otherwise required by law or agreed in writing.', 'zskeleton' ),
			__( 'Digital goods and completed services may be non-refundable after delivery; subscription cancellations typically stop renewal rather than refund past periods.', 'zskeleton' ),
			__( 'To request a review, contact us with your order reference and a short description of the issue.', 'zskeleton' ),
		),
	),
	'shipping' => array(
		'lead' => __( 'This page describes how physical or digital goods are fulfilled. Adjust regions, carriers, and timelines to match your operations.', 'zskeleton' ),
		'items' => array(
			__( 'Digital products are usually delivered by email or secure download link after payment confirmation.', 'zskeleton' ),
			__( 'Physical shipments are processed within the timeframe shown at checkout; tracking is provided when available.', 'zskeleton' ),
			__( 'International orders may be subject to customs, duties, or delays outside our control.', 'zskeleton' ),
		),
	),
);

$block = $sections[ $kind ];
?>
<div class="legal-policy-general">
	<article class="page-content legal-policy-general__article">
		<div class="entry-content academic-content">
			<p class="legal-policy-general__lead"><?php echo esc_html( $block['lead'] ); ?></p>
			<ul class="legal-policy-general__list">
				<?php foreach ( $block['items'] as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</article>
</div>

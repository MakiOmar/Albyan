<?php
/**
 * Arabic SEO homepage section (static markup; later dynamic).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$zskeleton_seo_ar_container_class = isset( $args['container_class'] ) ? (string) $args['container_class'] : '';
$assets_base                        = isset( $args['assets_base'] ) ? (string) $args['assets_base'] : '';
$ref_base                           = isset( $args['ref_base'] ) ? (string) $args['ref_base'] : '';
?>
	<section class="seo-ar-ai-lead" id="ai-lead" aria-labelledby="seo-ar-ai-lead-heading">
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<?php
			$lead_status = isset($_GET['lead']) ? sanitize_text_field(wp_unslash($_GET['lead'])) : '';
			if ('sent' === $lead_status) {
				echo '<p class="seo-ar-lead-notice seo-ar-lead-notice--success" role="status">' . esc_html( 'شكراً لك — تم استلام رسالتك.' ) . '</p>';
			} elseif ('error' === $lead_status) {
				echo '<p class="seo-ar-lead-notice seo-ar-lead-notice--error" role="alert">' . esc_html( 'يرجى تعبئة جميع الحقول المطلوبة بشكل صحيح.' ) . '</p>';
			}
			?>
			<div class="seo-ar-ai-lead-grid">
				<div class="seo-ar-ai-lead-copy">
					<h2 id="seo-ar-ai-lead-heading"><?php echo esc_html( 'شركة خدمات سيو لعصر الذكاء الاصطناعي' ); ?></h2>
					<p class="seo-ar-lead-text">
						<?php
						echo wp_kses_post(
							sprintf(
								'استراتيجيات <span class="seo-ar-text-highlight">%1$s</span> و<span class="seo-ar-text-highlight">%2$s</span> و<span class="seo-ar-text-highlight">%3$s</span> و<span class="seo-ar-text-highlight">%4$s</span> و<span class="seo-ar-text-highlight">%5$s</span> لدينا مبنية على بيانات صلبة وتقنيات مختبرة علمياً. ونحن أيضاً في طليعة <span class="seo-ar-text-highlight">%6$s</span>، لتحسين زيارات الموقع من نماذج لغوية كبيرة مثل ChatGPT وClaude وPerplexity وGemini.',
								esc_html( 'سيو' ),
								esc_html( 'تصميم المواقع' ),
								esc_html( 'PPC' ),
								esc_html( 'وسائل التواصل' ),
								esc_html( 'البريد الإلكتروني' ),
								esc_html( 'سيو الذكاء الاصطناعي' )
							)
						);
						?>
					</p>

					<h3 class="seo-ar-ai-lead-subhead"><?php echo esc_html( 'احذر المدّعين' ); ?></h3>
					<p class="seo-ar-lead-text">
						<?php
						$case_url = esc_url( apply_filters( 'zskeleton_seo_ar_case_study_portfolio_url', home_url( '/' ) ) );
						echo wp_kses_post(
							sprintf(
								'بعض الشركات وما يُسمّى «خبراء» حضروا دورة أو قرأوا مدونات ويستخدمون مصطلحات مثل «SEO» و«PPC» ويظنون أنهم يقدمون الخدمات. احذر أي وكالة لا تملك أمثلة عمل قوية مثل <a class="seo-ar-text-link" href="%s">معرض دراسات الحالة</a> لدينا.',
								$case_url
							)
						);
						?>
					</p>

					<h3 class="seo-ar-ai-lead-subhead"><?php echo esc_html( sprintf( 'لماذا %s؟', get_bloginfo( 'name' ) ) ); ?></h3>
					<p class="seo-ar-lead-text">
						<?php
						echo wp_kses_post(
							sprintf(
								'أعضاء فريقنا لديهم مجموعاً يزيد عن ثمانية عشر قرناً من خبرة وكالات التسويق الرقمي. لا توجد شركة سيو أخرى بفريق مؤهل مثل %s.',
								esc_html( get_bloginfo( 'name' ) )
							)
						);
						?>
					</p>
					<p class="seo-ar-lead-text">
						<?php
						echo wp_kses_post(
							sprintf(
								'نوظّف فقط أفضل 1%% من المتقدمين الذين اختبرنا مهاراتهم، وتدريبهم الصارم يشمل دوراتنا على مستوى جامعي. لدينا أنظمة متقدمة وأدوات مدعومة بالذكاء الاصطناعي لضمان أعلى جودة في عملنا—بما في ذلك التميز في <span class="seo-ar-text-highlight">%s</span>.',
								esc_html( 'التسويق الرقمي' )
							)
						);
						?>
					</p>
				</div>
				<div class="seo-ar-ai-lead-form-col">
					<div class="seo-ar-custom-form-box">
						<div class="seo-ar-custom-form-box__inner">
							<h3 class="seo-ar-custom-form-box__heading"><?php echo esc_html( 'احصل على حساب مجاني ومراجعة استراتيجية للمنافسين الآن' ); ?></h3>
							<div class="seo-ar-custom-form-box__form">
								<?php zskeleton_seo_ar_render_lead_form_column(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

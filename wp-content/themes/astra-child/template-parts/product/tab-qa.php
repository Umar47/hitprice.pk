<?php
/**
 * Q&A tab panel — stub.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="hp-qa">
	<div class="hp-qa__empty">
		<svg class="hp-qa__icon" width="48" height="48" viewBox="0 0 24 24" fill="none"
		     stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
		     stroke-linejoin="round" aria-hidden="true">
			<circle cx="12" cy="12" r="10"/>
			<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
			<line x1="12" y1="17" x2="12.01" y2="17"/>
		</svg>
		<p class="hp-qa__message">
			<?php esc_html_e( 'Q&A is coming soon. Have a question? Contact us.', 'hitprice' ); ?>
		</p>
		<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ?: home_url( '/contact' ) ); ?>"
		   class="hp-qa__cta">
			<?php esc_html_e( 'Contact us', 'hitprice' ); ?>
		</a>
	</div>
</div>

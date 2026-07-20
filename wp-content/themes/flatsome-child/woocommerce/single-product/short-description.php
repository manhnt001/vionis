<?php
/**
 * Single product short description for child theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

if ( ! $short_description ) {
	return;
}
?>
<div class="product-short-description">
	<?php echo $short_description; // WPCS: XSS ok. ?>
</div>
<div class="short-desc-toggle" aria-hidden="true">
	<button type="button" class="short-desc-toggle__button" aria-expanded="false" aria-label="Read more">
		<span class="short-desc-toggle__text short-desc-toggle__text--collapsed">Read more</span>
		<span class="short-desc-toggle__text short-desc-toggle__text--expanded">Show less</span>
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<polyline points="6 9 12 15 18 9"></polyline>
		</svg>
	</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const desc = document.querySelector('.product-short-description');
	const toggle = document.querySelector('.short-desc-toggle');
	const button = toggle ? toggle.querySelector('button') : null;

	if (!desc || !toggle || !button) {
		return;
	}

	const collapsedHeight = 280;
	const threshold = 40; // Ngưỡng hiển thị (chỉ hiện nút nếu dài hơn collapsedHeight + 40px)
	if (desc.scrollHeight <= collapsedHeight + threshold) {
		toggle.style.display = 'none';
		desc.classList.add('expanded'); // Mở rộng hoàn toàn và ẩn gradient mờ ở dưới
		return;
	}

	button.addEventListener('click', function () {
		const expanded = desc.classList.toggle('expanded');
		toggle.classList.toggle('is-expanded', expanded);
		button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

		const topBlock = desc.closest('.main-info-tour');
		if (topBlock) {
			const headerOffset = 100;
			window.scrollTo({
				top: topBlock.getBoundingClientRect().top + window.pageYOffset - headerOffset,
				behavior: 'smooth'
			});
		}
	});
});
</script>

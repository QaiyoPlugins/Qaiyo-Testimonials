/* Qaiyo Testimonials — Display Builder admin UX */
(function ($) {
	'use strict';

	$(function () {
		var $picker = $('.qt-version-picker');
		if (!$picker.length) return;

		function syncVisibility() {
			var v = $picker.find('input[name="qtd_version"]:checked').val() || 'v1';
			$picker.find('.qt-version-card').each(function () {
				$(this).toggleClass('is-active', $(this).data('version') === v);
			});

			var $behavior = $('.qt-behavior');
			$behavior.find('.qt-only-v1, .qt-only-v2, .qt-only-v3, .qt-only-v4, .qt-only-v5, .qt-only-v6, .qt-only-v7, .qt-only-v8').hide();
			$behavior.find('.qt-only-' + v).show();

			// Direction X vs Y swap.
			var isVertical = (v === 'v2');
			$behavior.find('.qt-dir-x').toggle(!isVertical);
			$behavior.find('.qt-dir-y').toggle(isVertical);
			// V3 only supports left.
			$behavior.find('.qt-dir-y, .qt-dir-x').toggle(v !== 'v3' && (isVertical ? true : !isVertical));
			if (v === 'v3') {
				$behavior.find('.qt-dir-x, .qt-dir-y').hide();
			}
		}

		$picker.on('change', 'input[name="qtd_version"]', syncVisibility);
		syncVisibility();

		// Quote style picker visual state.
		var $qp = $('.qt-quote-picker');
		$qp.on('change', 'input[name="qtd_quote_style"]', function () {
			var val = $(this).val();
			$qp.find('.qt-quote-card').each(function () {
				$(this).toggleClass('is-active', String($(this).data('quote')) === val);
			});
		});
	});
})(jQuery);

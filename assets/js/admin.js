/* Qaiyo Testimonials - admin (Media Library picker) */
(function ($) {
	'use strict';

	$(function () {
		var frame;
		var $wrap = $('.qt-photo-picker');
		if (!$wrap.length) return;

		var $input = $wrap.find('#qt_photo_id');
		var $preview = $wrap.find('.qt-photo-preview');
		var $select = $wrap.find('.qt-photo-select');
		var $remove = $wrap.find('.qt-photo-remove');

		$select.on('click', function (e) {
			e.preventDefault();
			if (frame) {
				frame.open();
				return;
			}
			frame = wp.media({
				title: qtAdmin.i18n.choose,
				button: { text: qtAdmin.i18n.use },
				library: { type: ['image/jpeg', 'image/png', 'image/webp'] },
				multiple: false
			});

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				if (att.filesizeInBytes && att.filesizeInBytes > qtAdmin.maxBytes) {
					alert(qtAdmin.i18n.tooLarge);
					return;
				}
				if (att.mime && ['image/jpeg', 'image/png', 'image/webp'].indexOf(att.mime) === -1) {
					alert(qtAdmin.i18n.invalidType);
					return;
				}
				$input.val(att.id);
				var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
				$preview.html('<img src="' + src + '" alt="" />').removeClass('qt-hidden').show();
				$remove.removeClass('qt-hidden').show();
			});

			frame.open();
		});

		$remove.on('click', function (e) {
			e.preventDefault();
			$input.val('');
			$preview.empty().addClass('qt-hidden').hide();
			$(this).addClass('qt-hidden').hide();
		});
	});
})(jQuery);

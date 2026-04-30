/**
 * Media picker for service icon (attachment ID).
 */
(function ($) {
	'use strict';

	$(function () {
		var $id = $('#zs-service-icon-id');
		var $preview = $('#zs-service-icon-preview');
		var $remove = $('#zs-service-icon-remove');
		var frame;

		if (!$id.length) {
			return;
		}

		$('#zs-service-icon-select').on('click', function (e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: window.zsSkeletonServiceIcon.frameTitle,
				button: { text: window.zsSkeletonServiceIcon.frameButton },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$id.val(String(att.id));
				var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
				$preview.html('<img src="' + url + '" alt="" style="max-width:80px;height:auto;display:block;" />');
				$remove.show();
			});

			frame.open();
		});

		$remove.on('click', function (e) {
			e.preventDefault();
			$id.val('');
			$preview.empty();
			$remove.hide();
		});
	});
})(jQuery);

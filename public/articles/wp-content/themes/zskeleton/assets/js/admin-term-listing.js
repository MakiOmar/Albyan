/**
 * Media picker for taxonomy term icon / listing image fields.
 *
 * @package ZSkeleton_Theme
 */
(function ($) {
	'use strict';

	function setPreview($input, attachment) {
		var id = $input.attr('id');
		if (!id) {
			return;
		}
		var $wrap = $('[data-preview-for="' + id + '"]');
		if (!$wrap.length) {
			return;
		}
		if (!attachment || !attachment.url) {
			$wrap.empty().attr('hidden', 'hidden');
			return;
		}
		var url =
			attachment.sizes && attachment.sizes.thumbnail
				? attachment.sizes.thumbnail.url
				: attachment.url;
		var maxW = $input.attr('id') && $input.attr('id').indexOf('image') !== -1 ? '160px' : '80px';
		$wrap.removeAttr('hidden').empty().append(
			$('<img/>', {
				src: url,
				alt: '',
				css: {
					maxWidth: maxW,
					height: 'auto',
					display: 'block',
					margin: '0 0 8px'
				}
			})
		);
	}

	$(document).on('click', '.zskeleton-term-media-select', function (e) {
		e.preventDefault();
		var targetName = $(this).data('target');
		if (!targetName) {
			return;
		}
		var $input = $('input[name="' + targetName + '"]');
		if (!$input.length) {
			return;
		}

		var frame = wp.media({
			title: $(this).text(),
			button: { text: wp.media.view.l10n.insertIntoPost },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$input.val(attachment.id ? String(attachment.id) : '');
			setPreview($input, attachment);
		});

		frame.open();
	});

	$(document).on('click', '.zskeleton-term-media-clear', function (e) {
		e.preventDefault();
		var targetName = $(this).data('target');
		if (!targetName) {
			return;
		}
		var $input = $('input[name="' + targetName + '"]');
		$input.val('');
		setPreview($input, null);
	});
})(jQuery);

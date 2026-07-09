/**
 * Add/remove rows for ZSkeleton repeater meta boxes + TinyMCE for textarea fields.
 */
(function ($) {
	'use strict';

	/**
	 * Settings passed from PHP (wp_localize_script).
	 *
	 * @type {{editor: Object}}
	 */
	var initArgs =
		typeof window.zskeletonRepeaterWysiwyg === 'object' &&
		window.zskeletonRepeaterWysiwyg !== null
			? window.zskeletonRepeaterWysiwyg
			: { editor: {} };

	function editorDefaults() {
		var d = initArgs.editor && typeof initArgs.editor === 'object' ? initArgs.editor : {};
		return {
			tinymce: d.tinymce !== undefined ? d.tinymce : true,
			quicktags: d.quicktags !== undefined ? d.quicktags : true,
			mediaButtons: false,
		};
	}

	function stripEditorWrap($row) {
		$row.find('.wp-editor-wrap').each(function () {
			var $w = $(this);
			var $ta = $w.find('textarea');
			if ($ta.length) {
				$w.replaceWith($ta);
			}
		});
	}

	function removeRowEditors($tbody) {
		if (typeof wp === 'undefined' || !wp.editor || !wp.editor.remove) {
			return;
		}
		$tbody.find('textarea.zskeleton-repeater-wysiwyg-field').each(function () {
			var id = this.id;
			if (id) {
				wp.editor.remove(id);
			}
		});
	}

	function syncWysiwygTextareaIds($tbody) {
		var $wrap = $tbody.closest('.zskeleton-repeater');
		var gid = $wrap.data('group-id');
		if (!gid) {
			return;
		}
		removeRowEditors($tbody);
		$tbody.find('textarea.zskeleton-repeater-wysiwyg-field').each(function () {
			var $ta = $(this);
			var name = $ta.attr('name') || '';
			var m = name.match(/\]\[(\d+)\]\[([^\]]+)\]$/);
			if (!m) {
				return;
			}
			var idx = m[1];
			var fname = m[2].replace(/[^a-z0-9_-]/gi, '_');
			var newId = 'zskeleton_rep_' + String(gid).replace(/[^a-z0-9_-]/gi, '_') + '_' + idx + '_' + fname;
			$ta.attr('id', newId);
		});
	}

	function initRowEditors($tbody) {
		if (typeof wp === 'undefined' || !wp.editor || !wp.editor.initialize) {
			return;
		}
		$tbody.find('textarea.zskeleton-repeater-wysiwyg-field').each(function () {
			var id = this.id;
			if (!id || $(this).closest('.wp-editor-wrap').length) {
				return;
			}
			wp.editor.initialize(id, editorDefaults());
		});
	}

	function refreshWysiwygEditors($tbody) {
		syncWysiwygTextareaIds($tbody);
		initRowEditors($tbody);
	}

	function reindexRows($tbody) {
		var $rows = $tbody.find('tr.zskeleton-repeater__row');
		$rows.each(function (idx) {
			$(this)
				.find('input, textarea')
				.each(function () {
					var name = $(this).attr('name');
					if (!name) {
						return;
					}
					var updated = name.replace(
						/(zskeleton_repeater\[[^\]]+]\[)(\d+)(])/,
						'$1' + idx + '$3'
					);
					$(this).attr('name', updated);
				});
		});
		refreshWysiwygEditors($tbody);
	}

	$(function () {
		$('.zskeleton-repeater__rows').each(function () {
			refreshWysiwygEditors($(this));
		});
	});

	$(document).on('click', '.zskeleton-repeater__add', function () {
		var $wrap = $(this).closest('.zskeleton-repeater');
		var $tbody = $wrap.find('.zskeleton-repeater__rows');
		var $rows = $tbody.find('tr.zskeleton-repeater__row');
		var $first = $rows.first();
		if (!$first.length) {
			return;
		}
		var newIdx = $rows.length;
		var $clone = $first.clone(false, false);
		stripEditorWrap($clone);
		$clone.find('input, textarea').each(function () {
			var $el = $(this);
			var name = $el.attr('name');
			if (name) {
				name = name.replace(
					/(zskeleton_repeater\[[^\]]+]\[)(\d+)(])/,
					'$1' + newIdx + '$3'
				);
				$el.attr('name', name);
			}
			if ($el.is('textarea')) {
				$el.val('');
			} else {
				$el.val('');
			}
		});
		$tbody.append($clone);
		reindexRows($tbody);
	});

	$(document).on('click', '.zskeleton-repeater__remove', function () {
		var $tbody = $(this).closest('tbody');
		if ($tbody.find('tr.zskeleton-repeater__row').length < 2) {
			removeRowEditors($tbody);
			$tbody.find('input, textarea').val('');
			refreshWysiwygEditors($tbody);
			return;
		}
		removeRowEditors($(this).closest('tr'));
		$(this).closest('tr').remove();
		reindexRows($tbody);
	});
})(jQuery);

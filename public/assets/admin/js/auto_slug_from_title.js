/**
 * Admin helper: fill empty slug fields from title on focus out.
 * Supports Arabic/Unicode titles (keeps letters & numbers).
 */
(function ($) {
    'use strict';

    /**
     * Convert a title into a URL slug (unicode-safe).
     */
    function slugifyFromTitle(title) {
        if (!title) {
            return '';
        }

        var slug = String(title).trim();

        // Normalize whitespace / underscores to hyphens.
        slug = slug.replace(/[\s_]+/g, '-');

        // Keep letters & numbers from any language; drop other punctuation.
        try {
            slug = slug.replace(/[^\p{L}\p{N}\-]+/gu, '');
        } catch (e) {
            // Older browsers without Unicode property escapes.
            slug = slug.replace(/[^a-zA-Z0-9\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\-]+/g, '');
        }

        slug = slug.replace(/-+/g, '-').replace(/^-+|-+$/g, '');

        // Lowercase Latin parts without breaking Arabic letters.
        slug = slug.toLowerCase();

        return slug;
    }

    /**
     * Fill target slug input if it is currently empty.
     */
    function fillSlugIfEmpty($titleInput, $slugInput) {
        if (!$slugInput || !$slugInput.length) {
            return;
        }

        var currentSlug = $.trim($slugInput.val() || '');
        if (currentSlug !== '') {
            return;
        }

        var generated = slugifyFromTitle($titleInput.val() || '');
        if (generated !== '') {
            $slugInput.val(generated).trigger('input').trigger('change');
        }
    }

    $(function () {
        // Main form pairs: title[name=title] + slug[name=slug]
        $(document).on('focusout', 'input.js-auto-slug-title[name="title"]', function () {
            var $title = $(this);
            var $form = $title.closest('form');
            var $slug = $form.find('input.js-auto-slug-target[name="slug"]').first();
            fillSlugIfEmpty($title, $slug);
        });

        // Sub-category (or nested) pairs inside a shared container.
        $(document).on('focusout', 'input.js-auto-slug-title', function () {
            var $title = $(this);
            if ($title.attr('name') === 'title') {
                return; // handled above
            }

            var $pair = $title.closest('.js-auto-slug-pair');
            if (!$pair.length) {
                return;
            }

            var $slug = $pair.find('input.js-auto-slug-target').first();
            fillSlugIfEmpty($title, $slug);
        });
    });

    // Expose for reuse / tests.
    window.AlbyanSlugifyFromTitle = slugifyFromTitle;
})(jQuery);

/**
 * Shared vuedraggable / Sortable options for form field lists.
 */
export const FORM_FIELD_GROUP = { name: 'form-fields', pull: true, put: true };

export const FORM_FIELD_DRAG_PROPS = {
	group: FORM_FIELD_GROUP,
	ghostClass: 'zs-form-builder__sortable-ghost',
	chosenClass: 'zs-form-builder__sortable-chosen',
	dragClass: 'zs-form-builder__sortable-drag',
	animation: 200,
	filter: '.zs-fb-field-card__actions, .zs-fb-icon-btn, .zs-fb-switch, .zs-fb-row__head .zs-fb-icon-btn, button, input, select, textarea, label',
	preventOnFilter: true,
};

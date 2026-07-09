/**
 * vuedraggable options for after-submit action rows.
 *
 * Use handle-only dragging so selects, inputs, and toggles stay interactive.
 */
export const FORM_EVENT_DRAG_PROPS = {
	group: { name: 'form-events', pull: false, put: false },
	handle: '.zs-fb-event-card__drag',
	ghostClass: 'zs-form-builder__sortable-ghost',
	chosenClass: 'zs-form-builder__sortable-chosen',
	dragClass: 'zs-form-builder__sortable-drag',
	animation: 200,
};

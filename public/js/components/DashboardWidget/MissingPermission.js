import AbstractWidget from './Abstract.js';

/**
 * Fallback screen rendered in place of a widget when the current user is missing
 * the permission (berechtigung_kurzbz) linked to that widget. Used for widgets
 * that are already on a user's dashboard but whose required permission the user
 * no longer holds.
 */
export default {
	mixins: [
		AbstractWidget
	],
	created() {
		this.$emit('setConfig', false);
	},
	template: /*html*/`
	<div class="dashboard-widget-missing-permission d-flex flex-column justify-content-center align-items-center text-center h-100 p-3 text-body-secondary">
		<i class="fa-solid fa-lock fa-2x mb-2" aria-hidden="true"></i>
		<p class="mb-0">{{ $p.t('dashboard/widget_missing_permission') }}</p>
	</div>`
}

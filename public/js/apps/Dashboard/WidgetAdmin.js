import { CoreNavigationCmpt } from '../../components/navigation/Navigation.js';
import WidgetAdmin from '../../components/Dashboard/WidgetAdmin.js';

import PluginsPhrasen from '../../plugins/Phrasen.js';

const app = Vue.createApp({
	name: 'WidgetsAdminApp',
	data: () => ({
		appSideMenuEntries: {}
	}),
	components: {
		CoreNavigationCmpt,
		WidgetAdmin
	},
	provide() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone
		};
	},
	template: /* html */`
	<div class="widgets-admin-app fhc-page-grid">
		<core-navigation-cmpt :add-side-menu-entries="appSideMenuEntries" />

		<div class="content d-flex flex-column gap-3 w-100 h-100 overflow-hidden">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3">
				<h1 class="h2 pb-3 border-bottom mb-0 w-100">Widgets</h1>
			</div>
			<widget-admin class="h-100 overflow-hidden" />
		</div>
	</div>
	`
});
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.use(PluginsPhrasen);
app.directive('tooltip', primevue.tooltip);
app.mount('#main');
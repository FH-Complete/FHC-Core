import { CoreNavigationCmpt } from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';

import PluginsPhrasen from '../../plugins/Phrasen.js';

const app = Vue.createApp({
	name: 'DashboardAdminApp',
	data: () => ({
		appSideMenuEntries: {}
	}),
	components: {
		CoreNavigationCmpt,
		DashboardAdmin
	},
	provide() {
		return {
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone
		};
	}
});
app.use(PluginsPhrasen);
app.directive('tooltip', primevue.tooltip);
app.mount('#main');
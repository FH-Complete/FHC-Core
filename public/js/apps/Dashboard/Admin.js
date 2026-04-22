import { CoreNavigationCmpt } from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';

import PluginsPhrasen from '../../plugins/Phrasen.js';

import ApiRenderers from '../../api/factory/renderers.js';

const app = Vue.createApp({
	name: 'DashboardAdminApp',
	data: () => ({
		appSideMenuEntries: {},
		renderers: null
	}),
	components: {
		CoreNavigationCmpt,
		DashboardAdmin
	},
	provide() {
		return {
			// TODO(chris): move those two into the components that need it
			renderers: Vue.computed(() => this.renderers),
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone
		};
	},
	created() {
		this.$api
			.call(ApiRenderers.loadRenderers())
			.then(res => {
				for (let rendertype of Object.keys(res.data)) {
					let modalTitle = null;
					let modalContent = null;
					let calendarEvent = null;
					if (res.data[rendertype].modalTitle)
						modalTitle = Vue.markRaw(Vue.defineAsyncComponent(() => import(res.data[rendertype].modalTitle)));
					if (res.data[rendertype].modalContent) 	
						modalContent = Vue.markRaw(Vue.defineAsyncComponent(() => import(res.data[rendertype].modalContent)));
					if (res.data[rendertype].calendarEvent) 	
						calendarEvent = Vue.markRaw(Vue.defineAsyncComponent(() => import(res.data[rendertype].calendarEvent)));

					if (res.data[rendertype].calendarEventStyles) {
						var head = document.head;
						if (!head.querySelector(`link[href="${res.data[rendertype].calendarEventStyles}"]`)) {
							var link = document.createElement("link");
							link.type = "text/css";
							link.rel = "stylesheet";
							link.href = res.data[rendertype].calendarEventStyles;
							head.appendChild(link);
						}
					}

					if (this.renderers === null) {
						this.renderers = {};
					}
					if (!this.renderers[rendertype]) {
						this.renderers[rendertype] = {}
					}
					this.renderers[rendertype].modalTitle = modalTitle;
					this.renderers[rendertype].modalContent = modalContent;
					this.renderers[rendertype].calendarEvent = calendarEvent;
				}
			})
			.catch(this.$fhcAlert.handleSystemErrors);
	}
});
app.use(PluginsPhrasen);
app.directive('tooltip', primevue.tooltip);
app.mount('#main');
import {CoreFilterCmpt} from '../components/Filter.js';
import {CoreNavigationCmpt} from '../components/Navigation.js';

const logsViewerApp = Vue.createApp({
	data() {
		return {
			appSideMenuEntries: {}
		};
	},
	components: {
		CoreNavigationCmpt,
		CoreFilterCmpt
	},
	methods: {
		newSideMenuEntryHandler(payload) {
			this.appSideMenuEntries = payload;
		}
	}
});

logsViewerApp.mount('#main');


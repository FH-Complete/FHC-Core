const logsViewerApp = Vue.createApp({
	data() {
		return {
			appSideMenuEntries: {}
		};
	},
	components: {
		Navigation,
		Filter
	},
	methods: {
		newSideMenuEntryHandler(payload) {
			this.appSideMenuEntries = payload;
		}
	}
});

logsViewerApp.mount('#main');


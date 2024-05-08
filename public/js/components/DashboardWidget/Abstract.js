export default {
	props: [
		"config",
		"width",
		"height",
		"configMode"
	],
	emits: [
		"setConfig",
		"change" // TODO(chris): do we need this?
	],
	computed: {
		apiurl() {
			const app_root = FHC_JS_DATA_STORAGE_OBJECT.app_root;
			const ci_router = FHC_JS_DATA_STORAGE_OBJECT.ci_router;
			return app_root + ci_router;
		}
	},
	methods: {
		formatDateTime: function(dateTime) {
			const dt = new Date(dateTime);
			return dt.getDate() + '.' + dt.getMonth() + '.' + dt.getFullYear() + ' | '
				+ dt.getHours() + ':' + dt.getMinutes();
		},
		getDate: function(dateTime){
			const dt = new Date(dateTime);
			return dt.getDate() + '.' + dt.getMonth() + '.' + dt.getFullYear();
		}
	}
}

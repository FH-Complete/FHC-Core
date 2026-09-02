import { formatDate, formatDateTime } from "../../helpers/DateHelpers.js";

export default {
	props: [
		"config",
		"width",
		"height",
		"configMode",
		"sharedData",
		"widgetInfo",
		"widgetId",
		"item_data"
	],
	emits: [
		"setConfig",
		"change", // TODO(chris): do we need this?
		"update:sharedData"
	],
	computed: {
		apiurl() {
			const app_root = FHC_JS_DATA_STORAGE_OBJECT.app_root;
			const ci_router = FHC_JS_DATA_STORAGE_OBJECT.ci_router;
			return app_root + ci_router;
		},
		shared: {
			get() {
				return this.sharedData;
			},
			set(value) {
				this.$emit('update:sharedData', value);
			}
		},
	},
	methods: {
		formatDateTime,
		getDate: formatDate
	}
}

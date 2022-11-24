let __widgets = {};
let __widgetsStarted = {};
let __path = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/dashboard/Widget';

export default {
	getWidget(id) {
		return __widgets[id];
	},
	loadWidget(id) {
		if (__widgets[id])
			return Promise.resolve(__widgets[id]);
		if (__widgetsStarted[id])
			return __widgetsStarted[id];
		if (!__path)
			return Promise.reject('Widget could not be loaded because there is no path yet!');

		__widgetsStarted[id] = new Promise((resolve, reject) => {
			axios.get(__path, {params:{id}}).then(res => {
				res.data.retval.arguments = JSON.parse(res.data.retval.arguments);
				res.data.retval.setup = JSON.parse(res.data.retval.setup);
				__widgets[id] = res.data.retval;
				__widgetsStarted[id] = undefined;
				resolve(__widgets[id]);
			}).catch(error => reject(error.response.data.retval.error));
		});
		return __widgetsStarted[id];
	},
	setPath(path) {
		__path = path;
	}
}
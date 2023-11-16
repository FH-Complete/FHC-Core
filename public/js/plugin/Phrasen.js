const categories = Vue.reactive({});
const loadingModules = {};

function extractCategory(obj, category) {
	return obj.filter(e => e.category == category).reduce((res, elem) => {
		if (!res[elem.phrase])
			res[elem.phrase] = elem.text;
		return res;
	}, {});
}
function getValueForLoadedPhrase(category, phrase, params) {
	let result = categories[category][phrase];
	if (!result)
		return '<< PHRASE ' + phrase + '>>';
	if (params)
		result = result.replace(/\{([^}]*)\}/g, (match, p1) => params[p1] === undefined ? match : params[p1]);
	return result;
}


const phrasen = {
	loadCategory(category) {
		if (Array.isArray(category))
			return Promise.all(category.map(cat => this.loadCategory(cat)));
		if (!loadingModules[category])
			loadingModules[category] = axios
				.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Phrasen/loadModule/' + category)
				.then(res => {
					if (res.data.retval)
						categories[category] = extractCategory(res.data.retval, category);
					else
						categories[category] = {};
				});
		return loadingModules[category];
	},
	t_ref(category, phrase, params) {
		console.warn('depricated');
		return Vue.computed(() => this.t(category, phrase, params));
	},
	t(category, phrase, params) {
		if (params === undefined && (
			(Array.isArray(category) && category.length == 2) ||
			(category.split && category.split('/').length == 2))
			) {
			params = phrase;
			[category, phrase] = category.split ? category.split('/') : category;
		}
		if (phrase === undefined) {
			console.error('invalid input', category, phrase, params);
			return '';
		}
		let val = Vue.computed(() => {
			if (!categories[category])
				return '';
			return getValueForLoadedPhrase(category, phrase, params);
		});
		if (!categories[category])
			this.loadCategory(category);
		return val.value;
	}
};

export default {
	install(app, options) {
		app.config.globalProperties.$p = phrasen;
	}
}

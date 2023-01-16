const categories = {};
const loadingModules = {};

function extractCategory(obj, category) {
	return obj.filter(e => e.category == category).reduce((res, elem) => {
		if (!res[elem.phrase])
			res[elem.phrase] = elem.text;
		return res;
	}, {});
}
function loadLazy(category, val) {
	// NOTE(chris): load module if it's not loaded yet
	if (loadingModules[category]) {
		loadingModules[category].push(val);
		if (categories[category]) // NOTE(chris): this is for safety in case the loading finished the moment before the val was pushed into the array
			while (loadingModules[category].length)
				Vue.triggerRef(loadingModules[category].pop());
		return Vue.unref(val);
	}
	loadingModules[category] = [val];

	axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Phrasen/LoadModule/' + category).then(res => {
		if (res.data.retval)
			categories[category] = extractCategory(res.data.retval, category);
		else
			categories[category] = {};

		while (loadingModules[category].length)
			Vue.triggerRef(loadingModules[category].pop());
	}).catch(err => console.error(err));
}

export default {
	data: () => {
		return {
			p: {
				t(category, phrase, params) {
					if (params === undefined && (
						(Array.isArray(category) && category.length == 2) || 
						(category.split && category.split('/').length == 2))
						) {
						params = phrase;
						[category, phrase] = category.split ? category.split('/') : category;
					}
					if (phrase === undefined) {
						console.error('invalid input');
						return '';
					}
					if (!categories[category]) {
						if (window.FHC_JS_PHRASES_STORAGE_OBJECT !== undefined)
							categories[category] = extractCategory(FHC_JS_PHRASES_STORAGE_OBJECT, category);
						
						if (!categories[category] || Object.keys(categories[category]).length === 0) {
							let val = Vue.ref('');
							loadLazy(category, val);
							return Vue.unref(val);
						}
					}
					let result = categories[category][phrase];
					if (!result)
						return '<< PHRASE ' + phrase + '>>';
					if (params)
						return result.replace(/\{([^}]*)\}/g, (match, p1) => params[p1] || match);
					return result;
				}
			}
		}
	}
}

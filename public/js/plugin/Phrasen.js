const categories = {};
const loadingModules = {};

function extractCategory(obj, category) {
	return obj.filter(e => e.category == category).reduce((res, elem) => {
		if (!res[elem.phrase])
			res[elem.phrase] = elem.text;
		return res;
	}, {});
}
function reloadRefs(category) {
	while (loadingModules[category].length) {
		var v = loadingModules[category].pop();
		if (v[0].nodeType && v[0].nodeType == Node.TEXT_NODE) {
			v[0].textContent = getValueForLoadedPhrase(category, v[1], v[2]);
		} else if (Vue.isRef(v[0])) {
			v[0].value = getValueForLoadedPhrase(category, v[1], v[2]);
			Vue.triggerRef(v[0]);
			/*Vue.unref(v);*/
		}
	}
}
function loadLazy(category, val, phrase, params) {
	// NOTE(chris): load module if it's not loaded yet
	if (loadingModules[category]) {
		loadingModules[category].push([val, phrase, params]);
		if (categories[category]) // NOTE(chris): this is for safety in case the loading finished the moment before the val was pushed into the array
			reloadRefs(category);
		return;
	}
	loadingModules[category] = [[val, phrase, params]];

	axios.get(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/components/Phrasen/loadModule/' + category).then(res => {
		if (res.data.retval)
			categories[category] = extractCategory(res.data.retval, category);
		else
			categories[category] = {};

		reloadRefs(category);
	}).catch(err => console.error(err));
}
function getValueForLoadedPhrase(category, phrase, params) {
	let result = categories[category][phrase];
	if (!result)
		return '<< PHRASE ' + phrase + '>>';
	if (params)
		result = result.replace(/\{([^}]*)\}/g, (match, p1) => params[p1] === undefined ? match : params[p1]);
	return result;
}

function t_intern(val, category, phrase, params) {
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
	if (categories[category])
		return getValueForLoadedPhrase(category, phrase, params);
	
	loadLazy(category, val, phrase, params);
	return '';
}

const phrasen = {
	t_ref(category, phrase, params) {
		let val = Vue.ref('');
		val.value = t_intern(val, category, phrase, params);
		return val;
	},
	t_node(category, phrase, params) {
		let val = document.createTextNode('');
		val.textContent = t_intern(val, category, phrase, params);
		return val;
	},
	t(category, phrase, params) {
		return Vue.unref(this.t_ref(category, phrase, params));
	}
};

export default {
	install(app, options) {
		app.config.globalProperties.$p = phrasen;
	}
}

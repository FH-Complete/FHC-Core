import ApiPhrasen from '../../api/factory/phrasen.js';

const categories = Vue.reactive({});
const loadingModules = {};
let user_language = Vue.ref(FHC_JS_DATA_STORAGE_OBJECT.user_language);

export const user_locale = Vue.computed(() => {
	if (!user_language.value) return null;
	return FHC_JS_DATA_STORAGE_OBJECT.server_languages.find(l => l.sprache == user_language.value).LC_Time;
});

function extractCategory(obj, category) {
	return obj.filter(e => e.category == category).reduce((res, elem) => {
		if (!res[elem.phrase]) res[elem.phrase] = elem.text;
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

export default {
	init(app) {
		// Create a controller to resolve the promise later
		let resolveReady;
		const readyPromise = new Promise(resolve => { resolveReady = resolve; });
		
		const $p = {
			deps: {},
			ready: readyPromise,
			setDeps(deps) { 
				Object.assign(this.deps, deps);
				// Once we have the API, we are ready to load data
				if (this.deps.$api) resolveReady();
			},
			user_language,
			user_locale,

			async loadCategory(category) {
				if (Array.isArray(category))
					return Promise.all(category.map(cat => this.loadCategory(cat)));

				// 2. SAFETY: Check if API is available via deps
				await this.ready;
				
				if (!loadingModules[category])
					loadingModules[category] = this.deps.$api
						.call(ApiPhrasen.loadCategory(category))
						.then(res => res?.data ? extractCategory(res.data, category) : {})
						.then(res => {
							categories[category] = res;
						});
				return loadingModules[category];
			},
			t_ref(category, phrase, params) {
				console.warn('deprecated');
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
			},

			async setLanguage(language) {
				await this.ready;
				const catArray = Object.keys(categories);
				return this.deps.$api.call(ApiPhrasen.setLanguage(catArray, language)).then(res => {
					res.data.forEach(row => { categories[row.category][row.phrase] = row.text; });
					user_language.value = language;
					return res;
				});
			}
		};
		return $p;
	}
};
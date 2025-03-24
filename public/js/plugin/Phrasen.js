console.warn('plugin/Phrasen.js is DEPRECATED! Use plugins/Phrasen.js instead.');
import FhcApi from './FhcApi.js';
import PluginsApi from '../plugins/Api.js';
import ApiPhrasen from '../api/factory/phrasen.js';

const categories = Vue.reactive({});
const loadingModules = {};
let user_language = Vue.ref(FHC_JS_DATA_STORAGE_OBJECT.user_language);
export let user_locale = Vue.computed(()=>{
	if(!user_language.value) return null;
	return FHC_JS_DATA_STORAGE_OBJECT.server_languages.find(language => language.sprache == user_language.value).LC_Time;
});

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
	user_language,
	user_locale,
	setLanguage(language) {
		const catArray = Object.keys(categories)
		return this.config.globalProperties.$api
			.call(ApiPhrasen.setLanguage(catArray, language))
			.then(res => {
				res.data.forEach(row => {
					categories[row.category][row.phrase] = row.text
				})

				// update the reactive data that holds the current active user_language
				user_language.value = language;

				return res
			})
	},
	loadCategory(category) {
		if (Array.isArray(category))
			return Promise.all(category.map(this.config.globalProperties
				.$p.loadCategory));
		const $fhcApi = this.config.globalProperties.$fhcApi;
		const $fhcApiFactory = this.config.globalProperties.$fhcApiFactory;
		if (!loadingModules[category])
			loadingModules[category] = this.config.globalProperties.$api
				.call(
					ApiPhrasen.loadCategory(category)
				)
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
	}
};

export default {
	install(app, options) {
		app.use(FhcApi, options?.fhcApi || undefined);
		app.use(PluginsApi);
		app.config.globalProperties.$p = {
			t: phrasen.t,
			loadCategory: cat => phrasen.loadCategory.call(app, cat),
			setLanguage: lang => phrasen.setLanguage.call(app, lang),
			user_language: user_language,
			user_locale,
			t_ref: phrasen.t_ref
		};
		app.provide('$p', app.config.globalProperties.$p);
	}
}
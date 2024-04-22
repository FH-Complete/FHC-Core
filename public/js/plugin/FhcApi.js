import FhcAlert from './FhcAlert.js';
import FhcApiFactory from '../apps/api/fhcapifactory.js';


export default {
	install: (app, options) => {
		app.use(FhcAlert);

		function _get_config(form, uri, data, config) {
			if (typeof form == 'string' && config === undefined) {
				[uri, data, config] = [form, uri, data];
				form = undefined;
			} else if (form) {
				if (typeof form != 'object')
					throw new TypeError('Parameter 1 of _get_config must be an object or a string');
				if (uri === undefined && data === undefined && config === undefined) {
					config = form;
					form = undefined;
				}
			}
			if (form) {
				// NOTE(chris): check if form is fhc-form
				if (!form.clearValidation || !form.setFeedback)
					throw new TypeError("'form' is not a Form Component");

				form = {
					clearValidation: form.clearValidation,
					setFeedback: form.setFeedback
				};

				if (config)
					config.form = form;
				else
					config = {form};
			}

			return [uri, data, config];
		}

		function _clean_return_value(response) {
			const result = response.data;
			delete response.data;
			if (!result.meta)
				result.meta = {response};
			else
				result.meta.response = response;
			return result;
		}

		const fhcApiAxios = axios.create({
			timeout: 5000,
			baseURL: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/"
		});

		fhcApiAxios.interceptors.request.use(config => {
			if (config.method != 'post' || !config.data)
				return config;

			if (config.data instanceof FormData)
				return config;
			
			if (!Object.values(config.data).every(item => {
				if (item instanceof FileList)
					return false;
				if (Array.isArray(item))
					return item.every(i => !(i instanceof File));
				return true;
			})) {
				const newData = Object.entries(config.data).reduce((nd, [key, item]) => {
					if (item instanceof FileList) {
						for (const file of item)
							nd.FormData.append(key + (item.length > 1 ? '[]' : ''), file);
					} else if (Array.isArray(item)) {
						if (item.every(i => !(i instanceof File))) {
							nd.jsondata[key] = item;
						} else {
							item.forEach(file => nd.FormData.append(key + (item.length > 1 ? '[]' : ''), file));
						}
					} else {
						nd.jsondata[key] = item;
					}
					return nd;
				}, {
					FormData: new FormData(),
					jsondata: {}
				});
				newData.FormData.append('_jsondata', JSON.stringify(newData.jsondata));
				config.data = newData.FormData;
			}

			return config;
		});

		fhcApiAxios.interceptors.response.use(response => {
			if (response.config?.errorHandling == 'off'
				|| response.config?.errorHandling === false
				|| response.config?.errorHandling == 'fail')
				return _clean_return_value(response);
			
			// NOTE(chris): loop through errors
			if (response.data.errors)
				response.data.errors = response.data.errors.filter(
					err => (response.config[err.type + 'ErrorHandler'] || app.config.globalProperties.$fhcApi._defaultErrorHandlers[err.type])(err, response.config.form)
				);

			return _clean_return_value(response);
		}, error => {
			if (error.code == 'ERR_CANCELED')
				return new Promise(() => {});
			
			if (error.config?.errorHandling == 'off'
				|| error.config?.errorHandling === false
				|| error.config?.errorHandling == 'success')
				return Promise.reject(error);

			if (error.response) {
				if (error.response.status == 404) {
					app.config.globalProperties.$fhcAlert.alertDefault('error', error.message, error.request.responseURL, true);
					return new Promise(() => {});
				}
				
				// NOTE(chris): loop through errors
				error.response.data.errors = error.response.data.errors.filter(
					err => (error.config[err.type + 'ErrorHandler'] || app.config.globalProperties.$fhcApi._defaultErrorHandlers[err.type])(err, error.config.form)
				);
				if (!error.response.data.errors.length)
					return new Promise(() => {});
			} else if (error.request) {
				app.config.globalProperties.$fhcAlert.alertDefault('error', error.message, error.request.responseURL);
				return new Promise(() => {});
			} else {
				app.config.globalProperties.$fhcAlert.alertError(error.message);
				return new Promise(() => {});
			}
			
			return Promise.reject(error);
		});

		app.config.globalProperties.$fhcApi = {
			get(form, uri, params, config) {
				[uri, params, config] = _get_config(form, uri, params, config);
				if (params) {
					if (config)
						config.params = params;
					else
						config = {params};
				}
				return fhcApiAxios.get(uri, config);
			},
			post(form, uri, data, config) {
				[uri, data, config] = _get_config(form, uri, data, config);
				return fhcApiAxios.post(uri, data, config);
			},
			_defaultErrorHandlers: {
				validation(error, form) {
					const $fhcAlert = app.config.globalProperties.$fhcAlert;

					if (form) {
						form.clearValidation();
						form.setFeedback(false, error.messages);
						return false;
					}
					if (Array.isArray(error.messages)) {
						error.messages.forEach($fhcAlert.alertError);
						return false;
					} else if (typeof error.messages == 'object') {
						Object.entries(error.messages).forEach(
							([key, value]) => $fhcAlert.alertDefault('error', key, value, true)
						);
						return false;
					}
					return true;
				},
				general(error, form) {
					const $fhcAlert = app.config.globalProperties.$fhcAlert;

					if (form)
						form.setFeedback(false, error.message);
					else
						$fhcAlert.alertError(error.message);
				},
				php(error) {
					const $fhcAlert = app.config.globalProperties.$fhcAlert;

					var message = '';
					message += 'Message: ' + error.message + '\n\n';
					message += 'Filename: ' + error.filename + '\n';
					message += 'Line Number: ' + error.line + '\n';
					if (error.backtrace && error.backtrace.length) {
						message += '\nBacktrace: ';
						error.backtrace.forEach(err => {
							message += '\n\tFile: ' + err.file + '\n';
							message += '\tLine: ' + err.line + '\n';
							message += '\tFunction: ' + err.function + '\n';
						});
					}
					switch (error.severity) {
						case 'Warning':
						case 'Core Warning':
						case 'Compile Warning':
						case 'User Warning':
							$fhcAlert.alertDefault('warn', 'PHP ' + error.severity, message, true);
							break;
						case 'Notice':
						case 'User Notice':
						case 'Runtime Notice':
							$fhcAlert.alertDefault('info', 'PHP ' + error.severity, message, true);
							break;
						default:
							message = 'Type: PHP ' + error.severity + '\n\n' + message;
							$fhcAlert.alertSystemError(message);
							break;
					}
				},
				exception(error) {
					const $fhcAlert = app.config.globalProperties.$fhcAlert;

					var message = '';
					message += 'Type: ' + error.class + '\n\n';
					message += 'Message: ' + error.message + '\n\n';
					message += 'Filename: ' + error.filename + '\n';
					message += 'Line Number: ' + error.line + '\n';
					if (error.backtrace && error.backtrace.length) {
						message += '\nBacktrace: ';
						error.backtrace.forEach(err => {
							message += '\n\tFile: ' + err.file + '\n';
							message += '\tLine: ' + err.line + '\n';
							message += '\tFunction: ' + err.function + '\n';
						});
					}
					$fhcAlert.alertSystemError(message);
				},
				db(error) {
					const $fhcAlert = app.config.globalProperties.$fhcAlert;

					var message = '';
					if (error.heading !== undefined)
						message += error.heading + '\n\n';
					if (error.code !== undefined)
						message += 'Code: ' + error.code + '\n\n';
					if (error.sql !== undefined)
						message += 'SQL: ' + error.sql + '\n\n';
					if (error.message !== undefined)
						message += 'Message: ' + error.message + '\n\n';
					else if (error.messages !== undefined)
						message += 'Messages: ' + error.messages.join('\n\t') + '\n\n';
					if (error.filename !== undefined)
						message += 'Filename: ' + error.filename + '\n';
					if (error.line !== undefined)
						message += 'Line Number: ' + error.line + '\n';

					$fhcAlert.alertSystemError(message);
				}
			}
		};

		class FhcApiFactoryWrapper {
			constructor(factorypart, root) {
				if (root === undefined)
					this.$fhcApi = app.config.globalProperties.$fhcApi;
				else
					Object.defineProperty(this, '$fhcApi', {
						get() {
							return (root || this).$fhcApi;
						}
					})
				Object.keys(factorypart).forEach(key => {
					Object.defineProperty(this, key, {
						get() {
							if (typeof factorypart[key] == 'function')
								return factorypart[key].bind(this);
							return new FhcApiFactoryWrapper(factorypart[key], root || this);
						}
					});
				});
			}
		}

		app.config.globalProperties.$fhcApi.factory = new FhcApiFactoryWrapper(FhcApiFactory);

	}
};
/**
 * BaseApi.js - Contains the original api plugin methods call, get, post, etc.
 * Dependencies like phrasen($p) or alert($fhcAlert) are optional and can be injected at initialization
 * or at a later stage.
 */
export class BaseApi {
	constructor(deps = {}) {
		// optional dependencies like { $fhcAlert: ..., $p: ... }
		this.deps = deps;

		this.resolveReady = null;
		this.ready = new Promise(resolve => { this.resolveReady = resolve; });

		// If deps were passed in constructor, resolve immediately
		if (this.deps.$fhcAlert && this.deps.$p) this.resolveReady();
		
		this.setupDefaultConfig()
		
		this.axiosInstance = axios.create({
			timeout: 500000,
			baseURL: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/"
		});

		this.setupInterceptors();
	}

	// === public functions START ===
	
	// in case of api instantiation before vue app mount and later usage via fhcBase plugin
	setDependencies(deps) {
		Object.assign(this.deps, deps);
		if (this.deps.$fhcAlert && this.deps.$p) this.resolveReady();
	}

	getUri(url) {
		return this.axiosInstance.getUri({ url });
	}

	get(form, uri, params, config) {
		[uri, params, config] = this.get_config(form, uri, params, config);
		if (params) {
			config = config ? { ...config, params } : { params };
		}
		return this.axiosInstance.get(uri, config);
	}

	post(form, uri, data, config) {
		[uri, data, config] = this.get_config(form, uri, data, config);
		return this.axiosInstance.post(uri, data, config);
	}

	getErrorHandler(config) {
		return this.get_error_handler(config);
	}
	
	async call(factory, configoverwrite, form) {
		if (Array.isArray(factory)) {
			return Promise.allSettled(factory.map((config, index) => {
				if (!Array.isArray(config)) config = ['#' + index, config];
				return this.call(config[1], { errorHeader: config[0], errorHandling: false });
			})).then(result => {
				const [ , , config ] = this.get_config(form, undefined, undefined, configoverwrite || {});
				const errorConfig = this.get_error_handler(config);

				if (!errorConfig.success && !errorConfig.fail) {
					return result;
				}

				const typedErrors = {};
				for (let res of result) {
					const [ allowed, item ] = res.status === 'fulfilled'
						? [ errorConfig.success, res.value ]
						: [ errorConfig.fail, res.reason ];
					if (!allowed)
						return;

					const errors = this.popHandleableErrors(errorConfig, this.get_error_list(item));

					for (let type in errors) {
						if (!typedErrors[type])
							typedErrors[type] = {
								[item.config.errorHeader]: errors[type]
							};
						else
							typedErrors[type][item.config.errorHeader] = errors[type];
					}
				};

				for (let errType in typedErrors) {
					errorConfig.handler[errType](typedErrors[errType]);
				}

				return result;
			});
		}

		let { method = 'get', url, params, config } = factory;
		if (configoverwrite !== undefined) config = configoverwrite;
		method = method.toLowerCase();

		return method === 'post' ? this.post(form, url, params, config) : this.get(form, url, params, config);
	}

	// === public functions END ===
	
	// === private functions START ===
	
	setupInterceptors() {

		this.axiosInstance.interceptors.request.use(config => {
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

		this.axiosInstance.interceptors.response.use(
			response => {
				if (response.config?.errorHandling == 'off'
					|| response.config?.errorHandling === false
					|| response.config?.errorHandling == 'fail')
					return this.clean_return_value(response);

				// NOTE(chris): loop through errors
				if (response.data.errors)
					response.data.errors = response.data.errors.filter(
						err => (response.config[err.type + 'ErrorHandler'] || this.DEFAULT_ERROR_CONFIG.handler[err.type])(err, response.config)
					);

				return this.clean_return_value(response);
			},
			error => {
				if (error.code == 'ERR_CANCELED')
					return Promise.reject({ handled: true, ...error });

				const errorConfig = this.get_error_handler(error.config);

				if (!errorConfig.fail)
					return Promise.reject(error);

				const remaining = this.get_error_list(error);

				const errors = this.popHandleableErrors(errorConfig, remaining);

				for (let type in errors) {
					errorConfig.handler[type](errors[type]);
				}

				if (remaining.length)
					return Promise.reject(error);

				return Promise.reject({ handled: true, ...error });
			}
		);
	}

	setupDefaultConfig() {
		
		this.DEFAULT_ERROR_CONFIG = {
			success: true,
			fail: true,
			combine: {
				form: ['validation', 'general'],
				toast: ['validation', 'general', 'not_found', 'site_failed']
			},
			handler: {
				form: (form, errors) => {
					form.clearValidation();
					errors.forEach(err => form.setFeedback(
						false,
						err.messages || err.message
					));
				},
				toast: async (errors) => {
					await this.ready;

					async function _format_toast(errors) {
						errors = errors.reduce((result, err) => {
							switch (err.type) {
								case 'not_found':
								case 'site_failed':
									if (err.message)
										result[err.message] = [err.url];
									else
										result._default = [err.url];
									break;
								case 'general':
									if (!result._default)
										result._default = [];
									result._default.push(err.message);
									break;
								case 'validation':
									Object.entries(err.messages)
										.forEach(([field, msg]) => {
											if (!result[field])
												result[field] = [];
											if (Array.isArray(msg))
												result[field].push(...msg);
											else
												result[field].push(msg);
										});
									break;
							}
							return result;
						}, {});
						let counter = 0;
						const msgs = await Promise.all(Object.entries(errors)
							.sort((a, b) => ['_default'].indexOf(b[0]) - ['_default'].indexOf(a[0])) // sort _default first
							.map(async ([field, msgs]) => {
								if (field == '_default') {
									await this.deps.$p.loadCategory('dashboard');
									const general = this.deps.$p.t('dashboard/general');
									field = '<dt class="d-none">' + general + '</dt>';
								} else {
									field = '<dt>' + field + '</dt>';
								}
								counter += msgs.length;
								return field
									+ '<dd>'
									+ msgs.join('</dd><dd>')
									+ '</dd>';
							}));
						return {
							counter,
							msgs
						}
					}

					let counter, msgs;
					if (Array.isArray(errors)) {
						({ counter, msgs } = await _format_toast(errors));
					} else {
						({ counter, msgs } = await Object.entries(errors)
							.reduce(async (res, [title, errs]) => {
								const result = await res;
								const { counter, msgs } = await _format_toast(errs);
								result.counter += counter;
								result.msgs.push('<dt>'
									+ title
									+ '</dt><dd><dl>'
									+ msgs.join('')
									+ '</dl></dd>');
								return result;
							}, Promise.resolve({ counter: 0, msgs: []})));
					}

					await this.deps.$p.loadCategory('ui');
					const n_errors = this.deps.$p.t('ui/n_errors', { n: counter });

					this.deps.$fhcAlert.alertDefault(
						'error',
						n_errors,
						'<dl>' + msgs.join('') + '</dl>',
						true,
						true
					);
				},
				php: async (errors) => {
					await this.ready;

					this._send_array_or_object(errors, (error, title) => {
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
								if (title)
									title += ': PHP ' + error.severity;
								else
									title = 'PHP ' + error.severity;
								this.deps.$fhcAlert.alertDefault('warn', title, message, true);
								break;
							case 'Notice':
							case 'User Notice':
							case 'Runtime Notice':
								if (title)
									title += ': PHP ' + error.severity;
								else
									title = 'PHP ' + error.severity;
								this.deps.$fhcAlert.alertDefault('info', title, message, true);
								break;
							default:
								message = 'Type: PHP ' + error.severity + '\n\n' + message;
								if (title)
									message = title + '\n\n' + message;
								this.deps.$fhcAlert.alertSystemError(message);
								break;
						}
					});
				},
				exception: async (errors) => {
					await this.ready;

					this._send_array_or_object(errors, (error, title) => {
						var message = '';
						if (title)
							message += title + '\n\n';
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
						this.deps.$fhcAlert.alertSystemError(message);
					});
				},
				db: async (errors) => {
					await this.ready;

					this._send_array_or_object(errors, (error, title) => {
						var message = '';
						if (title)
							message += title + '\n\n';
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

						this.deps.$fhcAlert.alertSystemError(message);
					});
				},
				auth: async (errors) => {
					await this.ready;

					this._send_array_or_object(errors, (error, title) => {
						if (title)
							title += ': ' + error.message;
						else
							title = error.message;

						var message = '';
						message += 'Controller name: ' + error.controller + '\n';
						message += 'Method name: ' + error.method + '\n';
						message += 'Required permissions: ' + error.required_permissions;

						this.deps.$fhcAlert.alertDefault(
							'error',
							title,
							message,
							true
						);
					});
				}
			}
		};
	}


	_send_array_or_object(errors, func) {
		if (!errors) return;
		
		if (Array.isArray(errors)) {
			errors.forEach(error => func(error));
			return;
		}

		// Handle Single Error Object
		if (errors.type) {
			func(errors);
			return;
		}

		// Handle Category Container
		Object.entries(errors).forEach(([title, value]) => {
			const errorList = Array.isArray(value) ? value : [value];

			errorList.forEach(error => {
				if (error && typeof error === 'object') {
					func(error, title);
				}
			});
		});
	}

	get_config(form, uri, data, config) {
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

	clean_return_value(response) {
		if (typeof response.data === 'string' || response.data instanceof String)
			return this.clean_return_value({ data: response });

		const result = response.data;
		delete response.data;
		if (!result)
			return {meta: {response}, data: null};
		if (!result.meta)
			result.meta = { response };
		else
			result.meta.response = response;
		return result;
	}

	merge_error_config(config) {
		if (config === false || config === 'off')
			return { ...this.DEFAULT_ERROR_CONFIG, success: false, fail: false };

		if (!config || config === true)
			return { ...this.DEFAULT_ERROR_CONFIG };

		if (config === 'success')
			return { ...this.DEFAULT_ERROR_CONFIG, fail: false };

		if (config === 'fail')
			return { ...this.DEFAULT_ERROR_CONFIG, success: false };

		const { success, fail, handler, combine } = config;

		config = { ...this.DEFAULT_ERROR_CONFIG };

		Object.entries({ fail, success }).forEach(([key, value]) => {
			if (value !== undefined)
				config[key] = value;
		});
		Object.entries({ handler, combine }).forEach(([key, value]) => {
			if (value !== undefined)
				config[key] = { ...config[key], ...value };
		});

		return config;
	}

	get_error_handler(config) {
		const result = this.merge_error_config(config?.errorHandling);

		if (!config?.form) {
			result.combine = { ...result.combine, form: [] };
		} else {
			const formHandler = result.handler.form;
			result.handler = { ...result.handler, form: errors => formHandler(config.form, errors) };
		}

		return result;
	}
	get_error_list(error) {
		if (error.response) {
			if (error.response.status == 404) {
				return [{
					type: 'not_found',
					message: error.message,
					url: error.request.responseURL
				}];
			} else {
				if (error.response.data.errors == undefined) return [];
				return error.response.data.errors;
			}
		} else if (error.request) {
			return [{
				type: 'site_failed',
				message: error.message,
				url: error.request.responseURL
			}];
		} else {
			return [{
				type: 'script',
				message: error.message
			}];
		}
	}

	popHandleableErrors(errorHandling, errors) {
		const result = {};
		const copy = [];

		if (errors == undefined) return {};

		while (errors.length)
			copy.push(errors.pop());
		for (let error of copy) {
			let type = error.type;
			let newType = null;
			for (let t in errorHandling.combine) {
				let newTypeCombinesType = errorHandling
					.combine[t]
					.includes(type);
				let newTypeHasHandler = errorHandling.handler[t];
				if (newTypeCombinesType && newTypeHasHandler) {
					newType = t;
					if (newType == 'form')
						break;
				}
			}
			if (newType)
				type = newType;
			const handler = errorHandling.handler[type];
			if (handler) {
				if (!result[type])
					result[type] = [];
				if (Array.isArray(error))
					result[type].push(...error);
				else
					result[type].push(error);
				continue;
			}
			errors.push(error);
		}
		return result;
	}

	// === private functions END ===
	
}

export default BaseApi;
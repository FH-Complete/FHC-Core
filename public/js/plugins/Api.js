import FhcAlert from './FhcAlert.js';


export default {
	install: (app, options) => {
		if (app.config.globalProperties.$api) {
			return;
		}

		if (!app.config.globalProperties.$fhcAlert)
			app.use(FhcAlert);

		const $fhcAlert = app.config.globalProperties.$fhcAlert;

		function _send_array_or_object(errors, func) {
			if (Array.isArray(errors))
				errors.forEach(func);
			else
				Object.entries(errors).forEach(
					([title, errs]) => errs.forEach(
						error => func(error, title)
					)
				);
		}
		let DEFAULT_ERROR_CONFIG = {
			success: true,
			fail: true,
			combine: {
				form: ['validation', 'general'],
				toast: ['validation', 'general', 'not_found', 'site_failed']
			},
			handler: {
				form(form, errors) {
					form.clearValidation();
					errors.forEach(err => form.setFeedback(
						false,
						err.messages || err.message
					));
				},
				async toast(errors) {
					const $p = app.config.globalProperties.$p;
					if (!$p)
						return Promise.reject('Phrasen plugin not loaded!');
				
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
									await $p.loadCategory('dashboard');
									const general = $p.t('dashboard/general');
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

					await $p.loadCategory('ui');
					const n_errors = $p.t('ui/n_errors', { n: counter });

					$fhcAlert.alertDefault(
						'error',
						n_errors,
						'<dl>' + msgs.join('') + '</dl>',
						true,
						true
					);
				},
				php(errors) {
					_send_array_or_object(errors, (error, title) => {
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
								$fhcAlert.alertDefault('warn', title, message, true);
								break;
							case 'Notice':
							case 'User Notice':
							case 'Runtime Notice':
								if (title)
									title += ': PHP ' + error.severity;
								else
									title = 'PHP ' + error.severity;
								$fhcAlert.alertDefault('info', title, message, true);
								break;
							default:
								message = 'Type: PHP ' + error.severity + '\n\n' + message;
								if (title)
									message = title + '\n\n' + message;
								$fhcAlert.alertSystemError(message);
								break;
						}
					});
				},
				exception(errors) {
					_send_array_or_object(errors, (error, title) => {
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
						$fhcAlert.alertSystemError(message);
					});
				},
				db(errors) {
					_send_array_or_object(errors, (error, title) => {
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

						$fhcAlert.alertSystemError(message);
					});
				},
				auth(errors) {
					_send_array_or_object(errors, (error, title) => {
						if (title)
							title += ': ' + error.message;
						else
							title = error.message;

						var message = '';
						message += 'Controller name: ' + error.controller + '\n';
						message += 'Method name: ' + error.method + '\n';
						message += 'Required permissions: ' + error.required_permissions;

						$fhcAlert.alertDefault(
							'error',
							title,
							message,
							true
						);
					});
				}
			}
		};

		if (options?.errorHandling !== undefined)
			DEFAULT_ERROR_CONFIG = _merge_error_config(options.errorHandling);
		
		function get_config(form, uri, data, config) {
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
		function clean_return_value(response) {
			if (typeof response.data === 'string' || response.data instanceof String)
				return clean_return_value({ data: response });

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
		function _merge_error_config(config) {
			if (config === false || config === 'off')
				return { ...DEFAULT_ERROR_CONFIG, success: false, fail: false };
			
			if (!config || config === true)
				return { ...DEFAULT_ERROR_CONFIG };

			if (config === 'success')
				return { ...DEFAULT_ERROR_CONFIG, fail: false };
			
			if (config === 'fail')
				return { ...DEFAULT_ERROR_CONFIG, success: false };
			
			const { success, fail, handler, combine } = config;
			
			config = { ...DEFAULT_ERROR_CONFIG };

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
		function get_error_handler(config) {
			const result = _merge_error_config(config?.errorHandling);

			if (!config?.form) {
				result.combine.form = [];
			} else {
				const formHandler = result.handler.form;
				result.handler.form = errors => formHandler(config.form, errors);
			}

			return result;
		}
		function get_error_list(error) {
			if (error.response) {
				if (error.response.status == 404) {
					return [{
						type: 'not_found',
						message: error.message,
						url: error.request.responseURL
					}];
				} else {
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
		function popHandleableErrors(errorHandling, errors) {
			const result = {};
			const copy = [];
			while (errors.length)
				copy.push(errors.pop());
			for (var error of copy) {
				let type = error.type;
				let newType = null;
				for (var t in errorHandling.combine) {
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

		const fhcApiAxios = axios.create({
			timeout: 500000,
			baseURL: FHC_JS_DATA_STORAGE_OBJECT.app_root
				+ FHC_JS_DATA_STORAGE_OBJECT.ci_router
				+ "/"
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

		fhcApiAxios.interceptors.response.use(
			response => {
				if (response.config?.errorHandling == 'off'
					|| response.config?.errorHandling === false
					|| response.config?.errorHandling == 'fail')
					return clean_return_value(response);

				// NOTE(chris): loop through errors
				if (response.data.errors)
					response.data.errors = response.data.errors.filter(
						err => (response.config[err.type + 'ErrorHandler'] || app.config.globalProperties.$api._defaultErrorHandlers[err.type])(err, response.config)
					);

				return clean_return_value(response);
			},
			error => {
				if (error.code == 'ERR_CANCELED')
					return Promise.reject({ handled: true, ...error });

				const errorConfig = get_error_handler(error.config);

				if (!errorConfig.fail)
					return Promise.reject(error);

				const remaining = get_error_list(error);

				const errors = popHandleableErrors(errorConfig, remaining);

				for (var type in errors) {
					errorConfig.handler[type](errors[type]);
				}

				if (remaining.length)
					return Promise.reject(error);
				
				return Promise.reject({ handled: true, ...error });
			}
		);

		app.config.globalProperties.$api = {
			getUri(url) {
				return fhcApiAxios.getUri({url});
			},
			get(form, uri, params, config) {
				[uri, params, config] = get_config(form, uri, params, config);
				if (params) {
					if (config)
						config.params = params;
					else
						config = {params};
				}
				return fhcApiAxios.get(uri, config);
			},
			post(form, uri, data, config) {
				[uri, data, config] = get_config(form, uri, data, config);
				return fhcApiAxios.post(uri, data, config);
			},
			call(factory, configoverwrite, form) {
				if (Array.isArray(factory)) {
					const $api = app.config.globalProperties.$api;

					return Promise
						.allSettled(factory.map((config, index) => {
							if (!Array.isArray(config))
								config = ['#' + index, config];
							return $api.call(config[1], {
								errorHeader: config[0],
								errorHandling: false
							});
						}))
						.then(result => {
							const [ , , config ] = get_config(form, undefined, undefined, configoverwrite || {});
							const errorConfig = get_error_handler(config);

							if (!errorConfig.success && !errorConfig.fail) {
								return result;
							}

							const typedErrors = {};
							for (var res of result) {
								const [ allowed, item ] = res.status === 'fulfilled'
									? [ errorConfig.success, res.value ]
									: [ errorConfig.fail, res.reason ];
								if (!allowed)
									return;

								const errors = popHandleableErrors(errorConfig, get_error_list(item));

								for (var type in errors) {
									if (!typedErrors[type])
										typedErrors[type] = {
											[item.config.errorHeader]: errors[type]
										};
									else
										typedErrors[type][item.config.errorHeader] = errors[type];
								}
							};

							for (var errType in typedErrors) {
								errorConfig.handler[errType](typedErrors[errType]);
							}
							
							return result;
						});
				}
				let { method, url, params, config } = factory;
				if (configoverwrite !== undefined) {
					config = configoverwrite;
				}
				if (!method) {
					method = 'get';
				}
				if (method.toLowerCase)
					method = method.toLowerCase();
				if (method == 'get') {
					return this.get(form, url, params, config);
				} else if (method == 'post') {
					return this.post(form, url, params, config);
				} else {
					console.error("FhcApi: method not allowed:", method);
				}
			}
		};

		app.provide('$api', app.config.globalProperties.$api);
	}
};
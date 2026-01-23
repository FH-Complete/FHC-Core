import PvConfig from "../../../../index.ci.php/public/js/components/primevue/config/config.esm.min.js";
import PvToast from "../../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";
import PvConfirm from "../../../../index.ci.php/public/js/components/primevue/confirmdialog/confirmdialog.esm.min.js";
import PvConfirmationService from "../../../../index.ci.php/public/js/components/primevue/confirmationservice/confirmationservice.esm.min.js";
import {CoreRESTClient} from "../../RESTClient.js";

export default {
	init(app, $p) {
		let resolveReady;
		const readyPromise = new Promise(resolve => { resolveReady = resolve; });
		
		const helperAppContainer = document.createElement('div');
		document.body.appendChild(helperAppContainer);

		const helperApp = Vue.createApp({
			name: "FhcAlertApp",
			components: { PvToast, PvConfirm },
			methods: {
				mailToUrl(slotProps) {
					let mailTo = FHC_JS_DATA_STORAGE_OBJECT.systemerror_mailto;
					let subject = 'Meldung%20Systemfehler';
					let body = `
						Danke, dass Sie uns den Fehler melden. %0D%0A %0D%0A
						Bitte beschreiben Sie uns ausführlich das Problem.%0D%0A
						Bsp: Ich habe X ausgewählt und Y angelegt. Beim Speichern erhielt ich die Fehlermeldung. [Optional: Ich habe den Browser Z verwendet.]%0D%0A
						-----------------------------------------------------------------------------------------------------------------------------------%0D%0A
						PROBLEM: ... %0D%0A %0D%0A %0D%0A
		
						-----------------------------------------------------------------------------------------------------------------------------------%0D%0A
						Fehler URL: ` + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' + FHC_JS_DATA_STORAGE_OBJECT.called_method + `%0D%0A
						Fehler Meldung: ` + slotProps.message.detail + `%0D%0A
						-----------------------------------------------------------------------------------------------------------------------------------%0D%0A %0D%0A
						Wir kümmern uns um eine rasche Behebung des Problems!`

					return "mailto:" + mailTo + "?subject=" + subject + "&body=" + body;
				},
				openMessagecard(e) {
					bootstrap.Collapse.getOrCreateInstance(e.target.getAttribute('href')).toggle();
				}
			},
			computed: {
				showmaillink() { return FHC_JS_DATA_STORAGE_OBJECT.systemerror_mailto !== ''; }
			},
			template: /* html */`
			<pv-toast ref="toast" class="fhc-alert" :base-z-index="99999">
				<template #message="{ message }">
					<!--span :class="slotProps.iconClass"></span-->
					<div class="p-toast-message-text">
						<span class="p-toast-summary">{{ message.summary }}</span>
						<div v-if="message.detail && message.html" class="p-toast-detail" v-html="message.detail"></div>
						<div v-else-if="message.detail" class="p-toast-detail">{{ message.detail }}</div>
					</div>
				</template>
			</pv-toast>
			<pv-toast ref="alert" class="fhc-alert" :base-z-index="99999" position="center">
				<template #message="slotProps">
					<i class="fa fa-circle-exclamation fa-2xl mt-3"></i>
					<div class="p-toast-message-text">
						<span class="p-toast-summary">{{slotProps.message.summary}}</span>
						<div class="p-toast-detail my-3">Sorry! Ein interner technischer Fehler ist aufgetreten.</div>
						<div class="d-flex justify-content-between align-items-center">
							<a
								class="align-bottom flex-fill me-2"
								data-bs-toggle="collapse"
								:href="'#fhcAlertCollapseMessageCard' + slotProps.message.id"
								role="button"
								aria-expanded="false"
								:aria-controls="'fhcAlertCollapseMessageCard' + slotProps.message.id"
								@click="openMessagecard"
								>
								Fehler anzeigen
							</a>
							<a
								v-if="showmaillink"
								class="btn btn-primary flex-fill"
								target="_blank"
								:href="mailToUrl(slotProps)"
								>
								Fehler melden
							</a>
						</div>
						<div ref="messageCard" :id="'fhcAlertCollapseMessageCard' + slotProps.message.id" class="collapse mt-3">
							<div class="card card-body text-body small alertCollapseText">
								{{slotProps.message.detail}}
							</div>
						</div>
					</div>
				</template>
			</pv-toast>
			<pv-confirm group="fhcAlertConfirm"></pv-confirm>`
		});

		// Link the helper app to the main app's phrasen system
		helperApp.config.globalProperties.$p = $p;
		helperApp.use(PvConfig);
		helperApp.use(PvConfirmationService);

		const helperAppInstance = helperApp.mount(helperAppContainer);

		const $fhcAlert = {
			// Internal storage for cross-plugin dependencies
			deps: { $p },
			ready: readyPromise,
			// Method used by FhcBase to inject the API later
			setDeps(deps) {
				Object.assign(this.deps, deps);
				// Alert is ready when both Phrases and API are present
				if (this.deps.$p && this.deps.$api) resolveReady();
			},
			
			alertSuccess(message) {
				if (Array.isArray(message))
					return message.forEach(this.alertSuccess);
				helperAppInstance.$refs.toast.add({ severity: 'success', summary: 'Info', detail: message, life: 1000});
			},
			alertInfo(message) {
				if (Array.isArray(message))
					return message.forEach(this.alertInfo);
				helperAppInstance.$refs.toast.add({ severity: 'info', summary: 'Info', detail: message, life: 3000 });
			},
			alertWarning(message) {
				if (Array.isArray(message))
					return message.forEach(this.alertWarning);
				helperAppInstance.$refs.toast.add({ severity: 'warn', summary: 'Achtung', detail: message});
			},
			alertError(message) {
				if (Array.isArray(message))
					return message.forEach(this.alertError);
				helperAppInstance.$refs.toast.add({ severity: 'error', summary: 'Achtung', detail: message });
			},
			async alertSystemError(message) {
				//TODO(Manu) for translation of content of template: restructure in data
				//and update definitions with  translations
				
				await this.ready;
				
				if (Array.isArray(message))
					return message.forEach(this.alertSystemError);
				helperAppInstance.$refs.alert.add({
					severity: 'error',
					summary: Vue.computed(() => this.deps.$p.t('alert/systemerror')),
					detail: message});
			},
			async confirmDelete() {
				await this.ready;
				
				return new Promise((resolve, reject) => {
					helperAppInstance.$confirm.require({
						group: 'fhcAlertConfirm',
						header: Vue.computed(() => this.deps.$p.t('alert/attention')),
						message: Vue.computed(() => this.deps.$p.t('alert/confirm_delete')),
						acceptLabel: Vue.computed(() => this.deps.$p.t('ui/loeschen')),
						acceptClass: 'p-button-danger',
						rejectLabel: Vue.computed(() => this.deps.$p.t('ui/abbrechen')),
						rejectClass: 'p-button-secondary',
						accept() {
							resolve(true);
						},
						reject() {
							resolve(false);
						},
					});
				});
			},
			confirm(options) {
				return new Promise((resolve, reject) => {
					helperAppInstance.$confirm.require({
						group: options?.group ?? 'fhcAlertConfirm',
						header: options?.header ?? Vue.computed(() => this.deps.$p.t('alert/attention')),
						message: options?.message ?? '',
						acceptLabel: options?.acceptLabel ?? 'Ok',
						acceptClass: options?.acceptClass ?? 'btn btn-primary',
						rejectLabel: options?.rejectLabel ?? Vue.computed(() => this.deps.$p.t('ui/abbrechen')),
						rejectClass: options?.rejectClass ?? 'btn btn-outline-secondary',
						accept() {
							resolve(true);
						},
						reject() {
							resolve(false);
						},
					});
				});
			},
			alertDefault(severity, title, message, sticky = false, html = false) {
				let options = { severity: severity, summary: title, detail: message, html };

				if (!sticky)
					options.life = 3000;

				helperAppInstance.$refs.toast.add(options);
			},
			alertMultiple(messageArray, severity = 'info', title = 'Info', sticky = false, html = false){
				// Check, if array has only string values
				if (messageArray.every(message => typeof message === 'string')) {
					messageArray.forEach(message => this.alertDefault(severity, title, message, sticky, html));
					return true;
				}
				return false;
			},
			handleSystemError(error) {
				// don't show an error message to the user if the error was an aborted request
				if(error.hasOwnProperty('name') && error.name.toLowerCase() === "AbortError".toLowerCase())
					return;

				// Error is string
				if (typeof error === 'string')
					return this.alertSystemError(error);

				// Error is array of strings
				if (Array.isArray(error) && error.every(err => typeof err === 'string'))
					return error.every(this.alertSystemError);

				// Error has been handled already
				if (error.hasOwnProperty('handled') && error.handled)
					return;

				// Error is object
				if (typeof error === 'object' && error !== null) {
					let errMsg = '';


					if (error.hasOwnProperty('response') && error.response?.data?.retval)
						errMsg += 'Error Message: ' + (error.response.data.retval.message || error.response.data.retval) + '\r\n';
					else if (error.hasOwnProperty('message'))
						errMsg += 'Error Message: ' + error.message.toUpperCase() + '\r\n';

					if (error.hasOwnProperty('config') && error.config.hasOwnProperty('url'))
						errMsg += 'Error ConfigURL: ' + error.config.url + '\r\n';

					if (error.hasOwnProperty('stack'))
						errMsg += 'Error Stack: ' + error.stack + '\r\n';

					// Fallback object error message
					if (errMsg == '')
						errMsg = 'Error Message: ' + JSON.stringify(error) + '\r\n';

					errMsg += 'Error Controller Path: ' + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' + FHC_JS_DATA_STORAGE_OBJECT.called_method;

					return this.alertSystemError(errMsg);
				}

				// Fallback
				this.alertSystemError('alertSystemError throws Generic Error\r\nError Controller Path: ' + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' + FHC_JS_DATA_STORAGE_OBJECT.called_method);
			},
			handleSystemMessage(message) {
				// Message is string
				if (typeof message === 'string')
					return this.alertWarning(message);

				// Message is array of strings
				if (Array.isArray(message)) {
					// If Array has only Strings
					if (message.every(msg => typeof msg === 'string'))
						return message.every(this.alertWarning);

					// If Array has only Objects
					if (message.every(msg => typeof msg === 'object') && msg !== null) {
						return message.every(msg => {
							if (msg.hasOwnProperty('data') && msg.data.hasOwnProperty('retval')) {
								this.alertWarning(JSON.stringify(msg.data.retval));
							} else {
								this.alertSystemError(JSON.stringify(msg));
							}
						});
					}
				}

				// Message is Object with data property
				if (typeof message === 'object' && message !== null){
					if (message.hasOwnProperty('data') && message.data.hasOwnProperty('retval')) {
						// NOTE(chris): changed: alertSystemError => alertWarning
						this.alertWarning(JSON.stringify(message.data.retval));
					} else {
						this.alertSystemError(JSON.stringify(message));
					}
					return;
				}

				// Fallback
				this.alertSystemError('alertSystemError throws Generic Error\r\nError Controller Path: ' + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' +  FHC_JS_DATA_STORAGE_OBJECT.called_method);
			},
			resetFormValidation(form) {
				const event = new Event('fhc-form-reset');
				form.querySelectorAll(['[data-fhc-form-validate],[data-fhc-form-error]']).forEach(el => el.dispatchEvent(event));
				/*const alert = form.querySelector('div.alert.alert-danger[role="alert"]');
				if (alert) {
					alert.innerHTML = '';
					alert.classList.add('d-none');
				}
				form.querySelectorAll('.invalid-feedback').forEach(n => n.remove());
				form.querySelectorAll('.is-invalid').forEach(n => n.classList.remove('is-invalid'));
				form.querySelectorAll('.is-valid').forEach(n => n.classList.remove('is-valid'));*/
			},
			handleFormValidation(error, form) {
				if (form === undefined) {
					if (error && error.nodeType === Node.ELEMENT_NODE)
						return err => this.handleFormValidation(err, error);
				} else {
					if (error?.response?.status == 400) {
						let errors = CoreRESTClient.getError(error.response.data);
						if (typeof errors !== "object")
							errors = error.response.data;

						// NOTE(chris): reset form validation
						this.resetFormValidation(form);

						// NOTE(chris): set form input validation
						const notFound = Object.entries(errors).filter(([key, detail]) => {
							const input = form.querySelector('[data-fhc-form-validate="' + key + '"]');
							if (!input)
								return true;

							input.dispatchEvent(new CustomEvent('fhc-form-invalidate', {detail}));

							/*const input = form.querySelector('[name="' + key + '"]');
							if (!input)
								return true;
							input.classList.add('is-invalid');
							const feedback = document.createElement('div');
							feedback.classList.add('invalid-feedback');
							feedback.innerHTML = detail;
							input.after(feedback);*/
							return false;
						}).map(arr => arr[1]);


						//const alert = form.querySelector('div.alert.alert-danger[role="alert"]');
						const alert = form.querySelector('[data-fhc-form-error]');
						if (alert && notFound.length) {
							alert.dispatchEvent(new CustomEvent('fhc-form-error', {detail: notFound}));
							/*notFound.forEach(txt => {
								const p = document.createElement('p');
								p.innerHTML = txt;
								alert.append(p);
							});

							if (notFound.length) {
								alert.lastChild.classList.add('mb-0');
								alert.classList.remove('d-none');
							}*/
						} else {
							notFound.forEach(this.alertError);
						}
						return;
					}
				}

				if (error?.response?.status == 400) {
					let errors = CoreRESTClient.getError(error.response.data);
					this.alertError((typeof errors === 'object') ? Object.values(errors) : errors);
				} else {
					this.handleSystemError(error);
				}
			}
		};

		return $fhcAlert;
	}
};
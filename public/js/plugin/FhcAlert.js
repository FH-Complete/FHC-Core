/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 * 
 * @usage:
 * Preperations:
 * Be sure to have PrimeVue loaded  with the toast and confirmdialog
 * components as primevue variable
 * Install:
 * Import this Plugin and install it with the app.use() function.
 * Use:
 * In your component you can call now the global property $fhcAlert
 * which has the following functions:
 * 
 * alertSuccess
 * ------------
 * Displays a success message
 * @param string	message
 * @return void
 * 
 * alertInfo
 * ---------
 * Displays an info message
 * @param string	message
 * @return void
 * 
 * alertWarning
 * ------------
 * Displays a warning
 * @param string	message
 * @return void
 * 
 * alertError
 * ----------
 * Displays an error
 * @param string	message
 * @return void
 * 
 * alertSystemError
 * ----------------
 * Displays an alert with the error details and a button to mail
 * the error to the Support Team
 * @param string	message
 * @return void
 * 
 * confirmDelete
 * -------------
 * Displays a confirmation dialog and returns a Promise which resolves
 * with true or false depending und the pressed button.
 * @return Promise
 * 
 * alertDefault
 * ------------
 * Displays an alert
 * @param string	severity		can be 'success', 'info', 'warning', 'error'
 * @param string	title
 * @param string	message
 * @param boolean	sticky			(optional) defaults to false
 * @return void
 * 
 * alertMultiple
 * -------------
 * Displays multiple alerts
 * @param array		messageArray
 * @param string	severity		(optional) defaults to 'info'
 * @param string	title			(optional) defaults to 'Info'
 * @param boolean	sticky			(optional) defaults to false
 * @return void
 * 
 * handleSystemError
 * -----------------
 * Automatiticly determine how to display an system error and display it.
 * This would be used in a catch block of an ajax call.
 * @param mixed		error
 * @return void
 * 
 * handleSystemMessage
 * -------------------
 * Automatiticly determine how to display a message and display it.
 * @param mixed		message
 * @return void
 */
import PvConfig from "../../../index.ci.php/public/js/components/primevue/config/config.esm.min.js";
import PvToast from "../../../index.ci.php/public/js/components/primevue/toast/toast.esm.min.js";
import PvConfirm from "../../../index.ci.php/public/js/components/primevue/confirmdialog/confirmdialog.esm.min.js";
import PvConfirmationService from "../../../index.ci.php/public/js/components/primevue/confirmationservice/confirmationservice.esm.min.js";

import {CoreRESTClient} from '../RESTClient.js';

const helperAppContainer = document.createElement('div');

const helperApp = Vue.createApp({
	components: {
		PvToast,
		PvConfirm
	},
	methods: {
		mailToUrl(slotProps) {
			let mailTo = 'noreply@technikum-wien.at'; // TODO domain anpassen
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
	unmounted() {
		helperAppContainer.parentElement.removeChild(helperAppContainer);
	},
	template: `
	<pv-toast ref="toast" class="fhc-alert" :base-z-index="99999"></pv-toast>
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
						class="btn btn-primary flex-fill"
						target="_blank"
						:href="mailToUrl(slotProps)"
						>
						Fehler melden
					</a>
				</div>
				<div ref="messageCard" :id="'fhcAlertCollapseMessageCard' + slotProps.message.id" class="collapse mt-3">
					<div class="card card-body text-body small">
						{{slotProps.message.detail}}
					</div>
				</div>
			</div>
		</template>
	</pv-toast>
	<pv-confirm group="fhcAlertConfirm"></pv-confirm>`
});

helperApp.use(PvConfig);
helperApp.use(PvConfirmationService);

const helperAppInstance = helperApp.mount(helperAppContainer);

document.body.appendChild(helperAppContainer);


export default {
	install: (app, options) => {
		const $fhcAlert = {
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
			alertSystemError(message) {
				if (Array.isArray(message))
					return message.forEach(this.alertSystemError);
				helperAppInstance.$refs.alert.add({ severity: 'error', summary: 'Systemfehler', detail: message});
			},
			confirmDelete() {
				return new Promise((resolve, reject) => {
					helperAppInstance.$confirm.require({
						group: 'fhcAlertConfirm',
						header: 'Achtung',
						message: 'Möchten Sie sicher löschen?',
						acceptLabel: 'Löschen',
						acceptClass: 'btn btn-danger',
						rejectLabel: 'Abbrechen',
						rejectClass: 'btn btn-outline-secondary',
						accept() {
							resolve(true);
						},
						reject() {
							resolve(false);
						},
					});
				});
			},
			alertDefault(severity, title, message, sticky = false) {
				let options = { severity: severity, summary: title, detail: message};
				
				if (!sticky)
					options.life = 3000;

				helperAppInstance.$refs.toast.add(options);
			},
			alertMultiple(messageArray, severity = 'info', title = 'Info', sticky = false){
				// Check, if array has only string values
				if (messageArray.every(message => typeof message === 'string')) {
					messageArray.forEach(message => this.alertDefault(severity, title, message, sticky));
					return true;
				}
				return false;
			},
			handleSystemError(error) {
				// Error is string
				if (typeof error === 'string')
					return $fhcAlert.alertSystemError(error);

				// Error is array of strings
				if (Array.isArray(error) && error.every(err => typeof err === 'string'))
					return error.every($fhcAlert.alertSystemError);

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

					return $fhcAlert.alertSystemError(errMsg);
				}

				// Fallback
				$fhcAlert.alertSystemError('alertSystemError throws Generic Error\r\nError Controller Path: ' + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' + FHC_JS_DATA_STORAGE_OBJECT.called_method);
			},
			handleSystemMessage(message) {
				// Message is string
				if (typeof message === 'string')
					return $fhcAlert.alertWarning(message);

				// Message is array of strings
				if (Array.isArray(message)) {
					// If Array has only Strings
					if (message.every(msg => typeof msg === 'string'))
						return message.every($fhcAlert.alertWarning);

					// If Array has only Objects
					if (message.every(msg => typeof msg === 'object') && msg !== null) {
						return message.every(msg => {
							if (msg.hasOwnProperty('data') && msg.data.hasOwnProperty('retval')) {
								$fhcAlert.alertWarning(JSON.stringify(msg.data.retval));
							} else {
								$fhcAlert.alertSystemError(JSON.stringify(msg));
							}
						});
					}
				}

				// Message is Object with data property
				if (typeof message === 'object' && message !== null){
					if (message.hasOwnProperty('data') && message.data.hasOwnProperty('retval')) {
						// NOTE(chris): changed: alertSystemError => alertWarning
						$fhcAlert.alertWarning(JSON.stringify(message.data.retval));
					} else {
						$fhcAlert.alertSystemError(JSON.stringify(message));
					}
					return;
				}

				// Fallback
				$fhcAlert.alertSystemError('alertSystemError throws Generic Error\r\nError Controller Path: ' + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' +  FHC_JS_DATA_STORAGE_OBJECT.called_method);
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
						return err => $fhcAlert.handleFormValidation(err, error);
				} else {
					if (error?.response?.status == 400) {
						let errors = CoreRESTClient.getError(error.response.data);
						if (typeof errors !== "object")
							errors = error.response.data;

						// NOTE(chris): reset form validation
						$fhcAlert.resetFormValidation(form);
						
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
							notFound.forEach($fhcAlert.alertError);
						}
						return;
					}
				}

				if (error?.response?.status == 400) {
					let errors = CoreRESTClient.getError(error.response.data);
					$fhcAlert.alertError((typeof errors === 'object') ? Object.values(errors) : errors);
				} else {
					$fhcAlert.handleSystemError(error);
				}
			}
		};
		app.config.globalProperties.$fhcAlert = $fhcAlert;
	}
}
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

let toast_component = null;
const toast_queue = [];

function _add_to_toast(...args) {
	toast_component.add.apply(toast_component, args);
}
let toast_add = function _add_to_toast_queue(...args) {
	toast_queue.push(args);
};

let alert_component = null;
const alert_queue = [];

function _add_to_alert(...args) {
	alert_component.add.apply(alert_component, args);
}
let alert_add = function _add_to_alert_queue(...args) {
	alert_queue.push(args);
};

let confirm_component = null;
const confirm_queue = [];

function _require_confirm(...args) {
	confirm_component.confirmListener.apply(confirm_component, args);
}
let confirm_require = function _add_to_confirm_queue(...args) {
	confirm_queue.push(args);
};


const helperAppContainer = document.createElement('div');

const helperApp = Vue.createApp({
	setup() {
		return self => {
			return [
				Vue.h(primevue.toast, {
					ref: 'toast',
					baseZIndex: 99999
				}),
				Vue.h(primevue.toast, {
					ref: 'alert',
					baseZIndex: 99999,
					position: 'center',
				}, {
					message: slotProps => {
						const messageCard = Vue.h('div', {
							ref: 'messageCard',
							id: 'collapseMessageCard',
							class: 'collapse mt-3'
						}, [
							Vue.h('div', {class: 'card card-body text-body small', style: 'white-space: pre-wrap'}, slotProps.message.detail)
						]);
						return [
							Vue.h('i', {class: 'fa fa-circle-exclamation fa-2xl mt-3'}),
							Vue.h('div', {class: 'p-toast-message-text'}, [
								Vue.h('span', {class: 'p-toast-summary'}, slotProps.message.summary),
								Vue.h('div', {class: 'p-toast-detail my-3'}, 'Sorry! Ein interner technischer Fehler ist aufgetreten.'),
								Vue.h('div', {class: 'd-flex justify-content-between align-items-center'}, [
									Vue.h('a', {
										class: 'align-bottom flex-fill me-2',
										dataBsToggle: 'collapse',
										href: '#collapseMessageCard',
										role: 'button',
										ariaExpanded: 'false',
										ariaControls: 'collapseMessageCard',
										onClick: () => { bootstrap.Collapse.getOrCreateInstance(messageCard.el).toggle(); }
									}, 'Fehler anzeigen'),
									Vue.h('a', {
										class: 'btn btn-primary flex-fill',
										target: '_blank',
										href: self.mailToUrl(slotProps),
									}, 'Fehler melden')
								]),
								messageCard,
							]),
						];
					}
				}),
				Vue.h(primevue.confirmdialog, {
					ref: 'confirm'
				})
			];
		};
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
		}
	},
	mounted() {
		toast_component = this.$refs.toast;
		toast_add = _add_to_toast;
		while (toast_queue.length)
			_add_to_toast(toast_queue.unshift());

		alert_component = this.$refs.alert;
		alert_add = _add_to_alert;
		while (alert_queue.length)
			_add_to_alert(alert_queue.unshift());

		confirm_component = this.$refs.confirm;
		confirm_require = _require_confirm;
		while (confirm_queue.length)
			_require_confirm(confirm_queue.unshift());
	},
	beforeUnmount() {
		toast_add = _add_to_toast_queue;
		toast_component = null;

		alert_add = _add_to_alert_queue;
		alert_component = null;

		confirm_require = _add_to_confirm_queue;
		confirm_component = null;
	},
	unmounted() {
		helperAppContainer.parentElement.removeChild(helperAppContainer);
	}
});

helperApp
	.use(primevue.config.default)
	.mount(helperAppContainer);

document.body.appendChild(helperAppContainer);

export default {
	install: (app, options) => {
		const $fhcAlert = {
			alertSuccess(message) {
				toast_add({ severity: 'success', summary: 'Info', detail: message, life: 1000});
			},
			alertInfo(message) {
				toast_add({ severity: 'info', summary: 'Info', detail: message, life: 3000 });
			},
			alertWarning(message) {
				toast_add({ severity: 'warn', summary: 'Achtung', detail: message});
			},
			alertError(message) {
				toast_add({ severity: 'error', summary: 'Achtung', detail: message });
			},
			alertSystemError(message) {
				alert_add({ severity: 'error', summary: 'Systemfehler', detail: message});
			},
			confirmDelete() {
				return new Promise((resolve, reject) => {
					confirm_require({
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

				toast_add(options);
			},
			alertMultiple(messageArray, severity = 'info', title = 'Info', sticky = false){
				// Check, if array has only string values
				if (messageArray.every(message => typeof message === 'string')) {
					messageArray.every(message => this.alertDefault(severity, title, message, sticky));
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
				
				// Error is object
				if (typeof error === 'object' && error !== null) {
					let errMsg = '';

					if (error.hasOwnProperty('message'))
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
					return fhcerror.alertWarning(message);

				// Message is array of strings
				if (Array.isArray(message)) {
					// If Array has only Strings
					if (message.every(msg => typeof msg === 'string'))
						return message.every(fhcerror.alertWarning);

					// If Array has only Objects
					if (message.every(msg => typeof msg === 'object') && msg !== null) {
						return message.every(msg => {
							if (msg.hasOwnProperty('data') && msg.data.hasOwnProperty('retval')) {
								fhcerror.alertWarning(JSON.stringify(msg.data.retval));
							} else {
								fhcerror.alertSystemError(JSON.stringify(msg));
							}
						});
					}
				}

				// Message is Object with data property
				if (typeof message === 'object' && message !== null){
					if (message.hasOwnProperty('data') && message.data.hasOwnProperty('retval')) {
						// NOTE(chris): changed: alertSystemError => alertWarning
						fhcerror.alertWarning(JSON.stringify(message.data.retval));
					} else {
						fhcerror.alertSystemError(JSON.stringify(message));
					}
					return;
				}

				// Fallback
				fhcerror.alertSystemError('alertSystemError throws Generic Error\r\nError Controller Path: ' + FHC_JS_DATA_STORAGE_OBJECT.called_path + '/' +  FHC_JS_DATA_STORAGE_OBJECT.called_method);
			}
		};
		app.config.globalProperties.$fhcAlert = $fhcAlert;
	}
}
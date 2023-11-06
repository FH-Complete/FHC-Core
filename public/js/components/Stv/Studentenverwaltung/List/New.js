import {CoreRESTClient} from '../../../../RESTClient.js';
import BsModal from '../../../Bootstrap/Modal.js';
import FhcFormValidation from '../../../Form/Validation.js';
import VueDatePicker1 from '../../../vueDatepicker.js.php';

var _uuid = 0;

export default {
	components: {
		BsModal,
		FhcFormValidation,
		VueDatePicker1
	},
	props: {
		studiengangKz: Number
	},
	data() {
		return {
			date: null,
			geschlechter: []
		}
	},
	methods: {
		open() {
			this.$refs.modal.show();
		},
		send(e) {
			this.sendForm(e.target, 'components/stv/student/check')
				.then(result => {
					console.log('check: ', result);
				});
		},
		sendForm(form, url) {
			this.resetFormValidation(form);
			const data = new FormData(form);
			return CoreRESTClient
				.post(url, data)
				.then(result => result.data)
				.then(result => {
					if (CoreRESTClient.isError(result))
						throw new Error(CoreRESTClient.getError(result));
					return result;
				})
				.catch(this.handleErrors(form));
		},
		resetForm() {
			const form = this.$refs.form;
			form.reset();
			this.resetFormValidation(form);
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
		handleErrors(error, form) {
			if (form === undefined) {
				if (error && error.nodeType === Node.ELEMENT_NODE)
					return err => this.handleErrors(err, error);
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
						notFound.forEach(this.$fhcAlert.alertError);
					}
					return;
				}
			}
			this.$fhcAlert.handleSystemError(error);
		},test() {console.log('test')}
	},
	created() {
		this.uuid = _uuid++;
		CoreRESTClient
			.get('components/stv/Student/getGeschlechter')
			.then(result => CoreRESTClient.getData(result.data))
			.then(result => {
				this.geschlechter = result.data;
			})
			.catch(err => {
				console.error(CoreRestClient.getError(err.response.data) || err.message);
			});
	},
	template: `
	<form ref="form" class="stv-list-new" @submit.prevent="send">
		<bs-modal ref="modal" dialog-class="modal-fullscreen" @hidden-bs-modal="resetForm">
			<template #title>
				InteressentIn anlegen
			</template>
			<template #default>
				<fhc-form-validation></fhc-form-validation>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-anrede-' + uuid">Anrede</label>
						<fhc-form-validation name="anrede">
							<input :id="'stv-list-new-anrede-' + uuid" type="text" name="anrede" class="form-control">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-titelpre-' + uuid">Titel (Pre)</label>
						<fhc-form-validation name="titelpre">
							<input :id="'stv-list-new-titelpre-' + uuid" type="text" name="titelpre" class="form-control">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-titelpost-' + uuid">Titel (Post)</label>
						<fhc-form-validation name="titelpost">
							<input :id="'stv-list-new-titelpost-' + uuid" type="text" name="titelpost" class="form-control">
						</fhc-form-validation>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-nachname-' + uuid">Nachname*</label>
						<fhc-form-validation name="nachname">
							<input :id="'stv-list-new-nachname-' + uuid" type="text" name="nachname" class="form-control">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-vorname-' + uuid">Vorname</label>
						<fhc-form-validation name="vorname">
							<input :id="'stv-list-new-vorname-' + uuid" type="text" name="vorname" class="form-control">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-vornamen-' + uuid">Weitere Vornamen</label>
						<fhc-form-validation name="vornamen">
							<input :id="'stv-list-new-vornamen-' + uuid" type="text" name="vornamen" class="form-control">
						</fhc-form-validation>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-wahlname-' + uuid">Wahlname</label>
						<fhc-form-validation name="wahlname">
							<input :id="'stv-list-new-wahlname-' + uuid" type="text" name="wahlname" class="form-control">
						</fhc-form-validation>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-geschlecht-' + uuid">Geschlecht*</label>
						<fhc-form-validation name="geschlecht">
							<select :id="'stv-list-new-geschlecht-' + uuid" class="form-control" name="geschlecht">
								<option v-for="geschlecht in geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
							</select>
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'dp-input-stv-list-new-gebdatum-' + uuid">Geburtsdatum</label>
						<fhc-form-validation name="gebdatum">
							<vue-date-picker1 :uid="'stv-list-new-gebdatum-' + uuid" name="gebdatum" text-input auto-apply no-today v-model="date" :enable-time-picker="false" format="dd.MM.yyyy"></vue-date-picker1>
						</fhc-form-validation>
					</div>
				</div>
			</template>
			<template #footer>
				<button type="submit" class="btn btn-primary">Vorschläge laden</button>
			</template>
		</bs-modal>
	</form>`
};
import {CoreRESTClient} from '../../../../RESTClient.js';
import BsModal from '../../../Bootstrap/Modal.js';
import FhcFormValidation from '../../../Form/Validation.js';
import VueDatePicker from '../../../vueDatepicker.js.php';
import accessibility from '../../../../directives/accessibility.js';

var _uuid = 0;

export default {
	components: {
		BsModal,
		FhcFormValidation,
		VueDatePicker
	},
	directives: {
		accessibility
	},
	props: {
		studiengangKz: Number
	},
	data() {
		return {
			geschlechter: [],
			formData: {},
			suggestions: {},
			person: null
		}
	},
	computed: {
		saveTitle() {
			if (this.person === null)
				return 'Bitte auswählen';
			if (this.person === 0)
				return 'Neue Person anlegen';
			return 'zu ' + (this.person.vorname + ' ' + this.person.nachname).trim() + ' hinzufügen';
		},
		formDataPerson() {
			if (this.person)
				return this.person;
			return this.formData;
		}
	},
	methods: {
		open() {
			this.$refs.modal.show();
		},
		reset() {
			this.formData = {};
			this.person = null;
			this.suggestions = [];
			this.$fhcAlert.resetFormValidation(this.$refs.form)
		},
		loadSuggestions() {
			if (this.person)
				return;
			// TODO(chris): load serialized
			CoreRESTClient
				.post('components/stv/student/check', {
					vorname: this.formData.vorname,
					nachname: this.formData.nachname,
					gebdatum: this.formData.gebdatum
				})
				.then(result => CoreRESTClient.getData(result.data) || [])
				.then(result => {
					this.suggestions = result;
				})
				.catch(() => {
					// NOTE(chris): repeat request
					window.setTimeout(this.loadSuggestions, 100);
				});
		},
		send(e) {
			if (e.person === null)
				this.$fhcAlert.alertError('Select a person first'); // TODO(chris): better error handling!

			this.$fhcAlert.resetFormValidation(form);
			const data = {...this.formData, ...(this.person || {})};
			CoreRESTClient
				.post('components/stv/student/add')
				.then(result => result.data)
				.then(result => {
					if (CoreRESTClient.isError(result))
						throw new Error(CoreRESTClient.getError(result));
					return CoreRESTClient.getData(result);
				})
				.then(result => {
					this.$fhcAlert.alertSuccess('Gespeichert');
					console.log('saved');
					// TODO(chris): close
				})
				.catch(this.$fhcAlert.handleFormValidation);
		}
	},
	created() {
		this.uuid = _uuid++;
		// TODO(chris): geschlechter in parent?
		CoreRESTClient
			.get('components/stv/Student/getGeschlechter')
			.then(result => CoreRESTClient.getData(result.data))
			.then(result => {
				this.geschlechter = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<form ref="form" class="stv-list-new" @submit.prevent="send">
		<bs-modal ref="modal" dialog-class="modal-fullscreen" @hidden-bs-modal="reset">
			<template #title>
				InteressentIn anlegen
			</template>
			<template #default>
				<fhc-form-validation></fhc-form-validation>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-anrede-' + uuid">Anrede</label>
						<fhc-form-validation name="anrede">
							<input :id="'stv-list-new-anrede-' + uuid" type="text" name="anrede" v-model="formDataPerson['anrede']" class="form-control" :disabled="person">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-titelpre-' + uuid">Titel (Pre)</label>
						<fhc-form-validation name="titelpre">
							<input :id="'stv-list-new-titelpre-' + uuid" type="text" name="titelpre" v-model="formDataPerson['titelpre']" class="form-control" :disabled="person">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-titelpost-' + uuid">Titel (Post)</label>
						<fhc-form-validation name="titelpost">
							<input :id="'stv-list-new-titelpost-' + uuid" type="text" name="titelpost" v-model="formDataPerson['titelpost']" class="form-control" :disabled="person">
						</fhc-form-validation>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-nachname-' + uuid">Nachname*</label>
						<fhc-form-validation name="nachname">
							<input :id="'stv-list-new-nachname-' + uuid" type="text" name="nachname" v-model="formDataPerson['nachname']" class="form-control" :disabled="person" @input="loadSuggestions">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-vorname-' + uuid">Vorname</label>
						<fhc-form-validation name="vorname">
							<input :id="'stv-list-new-vorname-' + uuid" type="text" name="vorname" v-model="formDataPerson['vorname']" class="form-control" :disabled="person" @input="loadSuggestions">
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-vornamen-' + uuid">Weitere Vornamen</label>
						<fhc-form-validation name="vornamen">
							<input :id="'stv-list-new-vornamen-' + uuid" type="text" name="vornamen" v-model="formDataPerson['vornamen']" class="form-control" :disabled="person">
						</fhc-form-validation>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-wahlname-' + uuid">Wahlname</label>
						<fhc-form-validation name="wahlname">
							<input :id="'stv-list-new-wahlname-' + uuid" type="text" name="wahlname" v-model="formDataPerson['wahlname']" class="form-control" :disabled="person">
						</fhc-form-validation>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4 mb-3">
						<label :for="'stv-list-new-geschlecht-' + uuid">Geschlecht*</label>
						<fhc-form-validation name="geschlecht">
							<select :id="'stv-list-new-geschlecht-' + uuid" class="form-control" :disabled="person" name="geschlecht" v-model="formDataPerson['geschlecht']">
								<option v-for="geschlecht in geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
							</select>
						</fhc-form-validation>
					</div>
					<div class="col-sm-4 mb-3">
						<label :for="'dp-input-stv-list-new-gebdatum-' + uuid">Geburtsdatum</label>
						<fhc-form-validation name="gebdatum">
							<vue-date-picker :uid="'stv-list-new-gebdatum-' + uuid" name="gebdatum" text-input auto-apply no-today v-model="formDataPerson['gebdatum']" :enable-time-picker="false" format="dd.MM.yyyy" @update:model-value="loadSuggestions" :disabled="person"></vue-date-picker>
						</fhc-form-validation>
					</div>
				</div>
			</template>
			<template #footer>
				<div class="btn-group dropup">
					<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" style="border-bottom-right-radius: 0; border-top-right-radius: 0" data-bs-toggle="dropdown" aria-expanded="false">
						<span class="visually-hidden">Choose Person</span>
					</button>
					<ul class="dropdown-menu">
						<!-- TODO(chris): more details -->
						<li v-for="suggestion in suggestions" :key="suggestion.person_id"><a class="dropdown-item" :class="{active: person?.person_id == suggestion.person_id}" href="#" @click.prevent="person=suggestion">{{suggestion.vorname + ' ' + suggestion.nachname}}</a></li>
						<li><a class="dropdown-item" :class="{active: person === 0}" href="#" @click.prevent="person=0">Neue Person anlegen</a></li>
					</ul>
					<button type="submit" class="btn btn-primary" :disabled="person === null">{{saveTitle}}</button>
				</div>
			</template>
		</bs-modal>
	</form>`
};
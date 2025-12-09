import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import NewPerson from "../../List/New.js";
import Contact from "../Kontakt/Contact.js";
import Vertrag from "./Vertrag.js";

import ApiStvProjektbetreuer from '../../../../../api/factory/stv/projektbetreuer.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		PvAutoComplete,
		NewPerson,
		Contact,
		Vertrag
	},
	provide() {
		return {
			configShowVertragsdetails: this.config.showVertragsdetails
		}
	},
	computed: {
		betreuerFormOpened() {
			return this.newMode || this.editMode;
		}
	},
	props: {
		config: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			tabulatorOptions: {
				columns: [
					{title: "Nachname", field: "nachname"},
					{title: "Vorname", field: "vorname"},
					{title: "Note", field: "note"},
					{title: "Punkte", field: "punkte"},
					{title: "Stunden", field: "stunden"},
					{title: "Stundensatz", field: "stundensatz", visible: false},
					{title: "Art", field: "betreuerart_kurzbz", visible: false},
					{title: "Person ID", field: "person_id", visible: false},
					{title: "Vertrag ID", field: "vertrag_id", visible: false},
					{title: "Projektarbeit ID", field: "projektarbeit_id", visible: false},
					{
						title: 'Aktionen',
						field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('ui', 'bearbeiten');
							button.addEventListener('click', (event) => {
								event.stopPropagation();
								event.preventDefault();
								let data = cell.getData();
								this.actionEditProjektbetreuer(data.projektarbeit_id, data.person_id, data.betreuerart_kurzbz);
							});
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', (event) => {
								event.stopPropagation();
								event.preventDefault();
								const data = cell.getData();
								this.actionDeleteProjektbetreuer(data.betreuer_id, data.projektarbeit_id, data.person_id, data.betreuerart_kurzbz)
							});
							container.append(button);

							let data = cell.getData();
							if (data.beurteilungDownloadLink !== null) {
								if (data.beurteilungDownloadLink == '') {
									button = document.createElement('span');
									button.title = this.$p.t('projektarbeit', 'projektarbeitNochNichtBeurteilt')
									button.innerHTML = '<button class="btn btn-outline-secondary btn-action" disabled>'+
										'<i class="fa-regular fa-file-pdf"></i></button>';
									button.addEventListener('click', (event) => {
										event.stopPropagation();
										event.preventDefault();
									});
								}
								else {
									button = document.createElement('a');
									button.setAttribute('href', data.beurteilungDownloadLink);
									button.setAttribute('role', 'button');
									button.innerHTML = '<i class="fa fa-file-pdf"></i>';
									button.title = this.$p.t('projektarbeit', 'projektbeurteilungErstellen');
									button.className = 'btn btn-outline-secondary btn-action';
									button.addEventListener('click', (event) => {
										event.stopPropagation();
										event.preventDefault();
										window.location.href = data.beurteilungDownloadLink;
									});
								}
								container.append(button);
							}

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataStretchFrozen',
				layoutColumnsOnNewData: false,
				height: 'auto',
				minHeight: '100',
				selectable: true,
				selectable: 1,
				index: 'betreuer_id',
				persistence:{
					columns: true, //persist column layout
				},
				persistenceID: 'stv-details-projektbetreuer-2025112401'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'projektarbeit', 'ui']);

						// Force layout recalculation for handling overflow text
						this.$refs.projektbetreuerTable.tabulator.redraw(true);

					}
				},
				{
					event: 'rowSelected',
					handler: row => {
						let data = row.getData();
						this.actionEditProjektbetreuer(data.projektarbeit_id, data.person_id, data.betreuerart_kurzbz);
					}
				}
			],
			formData: {
				betreuerart_kurzbz: null,
				note: null,
				stunden: null,
				stundensatz: null
			},
			newMode: false,
			editMode: false,
			initialFormData: null,
			defaultFormDataValues: {stunden: null, stundensatz: null},
			projektarbeit_id: null,
			editedBetreuerIdx: -1,
			arrBetreuerart: [],
			arrNoten: [],
			filteredBetreuer: [],
			autocompleteSelectedBetreuer: null,
			beurteilungDownloadLink: null,
			vertragFieldsDisabled: false,
			abortController: {
				betreuer: null
			}
		}
	},
	methods: {
		actionNewProjektbetreuer() {
			this.resetForm();
			this.newMode = !this.newMode;
			this.editMode = false;
			this.captureFormData();
		},
		actionEditProjektbetreuer(projektarbeit_id, person_id, betreuerart_kurzbz) {
			this.editMode = true;
			this.newMode = false;
			this.$api
				.call(ApiStvProjektbetreuer.getDefaultStundensaetze(person_id, this.studiensemester_kurzbz))
				.then(result => {
					this.resetForm();

					// get betreuer from tabulator list
					let projektbetreuerListe = this.$refs.projektbetreuerTable.tabulator.getData();
					const idx = projektbetreuerListe.findIndex(
							betr =>
								betr.person_id === person_id &&
								betr.projektarbeit_id === projektarbeit_id &&
								betr.betreuerart_kurzbz === betreuerart_kurzbz
						);

					if (idx >= 0) { // if betreuer found

						// set currently edited betreuer (deep copy)
						this.formData = JSON.parse(JSON.stringify(projektbetreuerListe[idx]));

						// set download link
						if (this.formData.beurteilungDownloadLink !== null) this.beurteilungDownloadLink = this.formData.beurteilungDownloadLink;

						// set betreuer for autocomplete field
						this.autocompleteSelectedBetreuer = {
							person_id: this.formData.person_id,
							name: this.formData.name,
							vorname: this.formData.vorname,
							nachname: this.formData.nachname,
							vertrag_id: this.formData.vertrag_id
						};
					}

					// set default stundensatz (if no other is set yet)
					if (this.formData.stundensatz == null) this.formData.stundensatz = result.data;

					// capture initial form data for detecting changes
					this.captureFormData();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionDeleteProjektbetreuer(betreuer_id, projektarbeit_id, person_id, betreuerart_kurzbz) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? {projektarbeit_id, person_id, betreuerart_kurzbz}
					: Promise.reject({handled: true}))
				.then(result => {
					return this.$api
						.call(ApiStvProjektbetreuer.deleteProjektbetreuer(projektarbeit_id, person_id, betreuerart_kurzbz))
				})
				.then(result => {
					this.$refs.projektbetreuerTable.tabulator.deleteRow(betreuer_id);
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getFormData(projekttyp_kurzbz) {
			// default Stundensätze from config
			this.defaultFormDataValues.stunden = this.getDefaultStunden(projekttyp_kurzbz);
			this.defaultFormDataValues.stundensatz = this.config.defaultProjektbetreuerStundensatz;

			// get other initial data
			this.$api
				.call(ApiStvProjektbetreuer.getBetreuerarten())
				.then(result => {
					this.arrBetreuerart = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);

			this.$api
				.call(ApiStvProjektbetreuer.getNoten())
				.then(result => {
					this.arrNoten = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getProjektbetreuer(projektarbeit_id, studiensemester_kurzbz) {
			if (projektarbeit_id) {
				// if projektarbeit changed, reset the form to hold new data
				if (this.projektarbeit_id != projektarbeit_id) {
					this.resetForm();
					this.resetModes();
				}
				this.projektarbeit_id = projektarbeit_id;
				this.studiensemester_kurzbz = studiensemester_kurzbz;
				this.$api
					.call(ApiStvProjektbetreuer.getProjektbetreuer(this.projektarbeit_id))
					.then(result => {
						this.$refs.projektbetreuerTable.tabulator.replaceData(this.addIds(result.data));
					})
					.catch(this.$fhcAlert.handleSystemError);
			} else {
				this.emptyBetreuer();
			}
		},
		saveProjektbetreuer() {
			this.$refs.formProjektbetreuer.call(
				ApiStvProjektbetreuer.saveProjektbetreuer(this.projektarbeit_id, this.getFormDataWithBetreuer())
			)
			.then(result => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.getProjektbetreuer(this.projektarbeit_id, this.studiensemester_kurzbz);
				this.resetModes();
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
		searchBetreuer(event) {
			if (this.abortController.betreuer) {
				this.abortController.betreuer.abort();
			}
			this.abortController.betreuer = new AbortController();

			return this.$api
				.call(ApiStvProjektbetreuer.getProjektbetreuerBySearchQuery(event.query))
				.then(result => {
					this.filteredBetreuer = result.data;
				});
		},
		emptyBetreuer() {
			this.$refs.projektbetreuerTable.tabulator.clearData();
		},
		resetForm() {
			this.formData = this.getDefaultFormData();
			if (this.beurteilungDownloadLink !== null) this.beurteilungDownloadLink = '';
			this.autocompleteSelectedBetreuer = null;
			this.initialFormData = null;
			if (this.projekttyp_kurzbz) this.setDefaultStunden(this.projekttyp_kurzbz);
			this.disableVertragFields(false);
			this.$refs.formProjektbetreuer.clearValidation();
		},
		resetModes() {
			this.newMode = false;
			this.editMode = false;
		},
		getDefaultFormData() {
			let formData = {betreuerart_kurzbz : null, note: null};

			for (const name in this.defaultFormDataValues) {
				formData[name] = this.defaultFormDataValues[name];
			}

			return formData;
		},
		captureFormData() {
			this.initialFormData = JSON.parse(JSON.stringify(this.formData)); // deep copy
		},
		// add own betreuer ids to betreuer liste
		addIds(betreuerListe) {

			for (const idx in betreuerListe) {
				let betreuer = betreuerListe[idx];

				betreuer.person_id_old = betreuer.person_id;
				betreuer.betreuerart_kurzbz_old = betreuer.betreuerart_kurzbz;
				betreuer.betreuer_id = parseInt(idx);
			}
			return betreuerListe;
		},
		// add the betreuer selected in automomplete to betreuer liste
		getFormDataWithBetreuer() {
			let preparedFormData = this.formData;

			preparedFormData.projektarbeit_id = this.projektarbeit_id;
			if (this.autocompleteSelectedBetreuer) {
				preparedFormData.person_id = this.autocompleteSelectedBetreuer.person_id;
				preparedFormData.name = this.autocompleteSelectedBetreuer.name;
				preparedFormData.vorname = this.autocompleteSelectedBetreuer.vorname;
				preparedFormData.nachname = this.autocompleteSelectedBetreuer.nachname;
			}

			return preparedFormData;
		},
		// get default values for stunden
		getDefaultStunden(projekttyp_kurzbz) {
			let stunden = '0.0';
			if (projekttyp_kurzbz == 'Bachelor') stunden = this.config.defaultProjektbetreuerStunden;
			if (projekttyp_kurzbz == 'Diplom') stunden = this.config.defaultProjektbetreuerStundenDiplom;
			return stunden;
		},
		setDefaultStunden(projekttyp_kurzbz) {
			this.projekttyp_kurzbz = projekttyp_kurzbz;
			// if form data has not already been modified by user, set the default stunden
			if (!this.formDataModified()) {
				let defaultStunden = this.getDefaultStunden(projekttyp_kurzbz);
				// adapt initial form data so it does not count as modified
				if (this.initialFormData) this.initialFormData.stunden = defaultStunden;
				// set default Stunden
				this.formData.stunden = defaultStunden;
			}
		},
		// check if form data has been modified since initial data has been captured
		formDataModified() {
			if (this.autocompleteSelectedBetreuer != null) return true;

			for (const prop in this.initialFormData) {
				if (typeof this.formData[prop] == 'undefined') return true;
				if (this.formData[prop] != this.initialFormData[prop]) return true;
			}

			return false;
		},
		actionNewPerson() {
			this.$refs.newPersonModal.reset();
			this.$refs.newPersonModal.open();
		},
		actionKontaktdatenBearbeiten() {
			if (!this.autocompleteSelectedBetreuer) return;
			this.$refs.kontaktdatenModal.show();
		},
		// stuff to do after new person has been saved
		personSaved(result) {
			this.$api
				.call(ApiStvProjektbetreuer.getPerson(result))
				.then(response => {
					// set the new person in Betreuer autocomplete field
					this.autocompleteSelectedBetreuer = response.data;
				})
				.catch(this.$fhcAlert.handleSystemError)
		},
		// disable fields which are dependent on Vertrag status
		disableVertragFields(statusAkzeptiert) {
			this.vertragFieldsDisabled = statusAkzeptiert;
		}
	},
	template: `
	<div class="stv-details-projektbetreuer h-100 pb-3 row">

		<div :class="this.config.showVertragsdetails ? 'col-9' : 'col-12'">

			<legend>{{this.$p.t('projektarbeit','betreuerGross')}}</legend>

			<core-filter-cmpt
				ref="projektbetreuerTable"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				new-btn-show
				:new-btn-label="this.$p.t('projektarbeit', 'betreuerGross')"
				@click:new="actionNewProjektbetreuer"
				>
			</core-filter-cmpt>

			<form-form ref="formProjektbetreuer" v-show="betreuerFormOpened" @submit.prevent>
				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-betreuer"
						:label="$p.t('projektarbeit', 'betreuer')"
						type="autocomplete"
						optionLabel="name"
						v-model="autocompleteSelectedBetreuer"
						name="person_id"
						:suggestions="filteredBetreuer"
						@complete="searchBetreuer"
						:min-length="3"
						:disabled="vertragFieldsDisabled"
						>
					</form-input>
				</div>

				<div class="row mb-3">
					<div class="col-6">
						<button class="btn btn-outline-secondary" @click="actionNewPerson">{{ $p.t('projektarbeit', 'neuePersonAnlegen') }}</button>
					</div>
					<div class="col-6">
						<button class="btn btn-outline-secondary float-end" @click="actionKontaktdatenBearbeiten">{{ $p.t('projektarbeit', 'kontaktdatenBearbeiten') }}</button>
					</div>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektbetreuer-betreuerart"
						:label="$p.t('projektarbeit', 'betreuerart')"
						type="select"
						v-model="formData.betreuerart_kurzbz"
						name="betreuerart_kurzbz"
						>
						<option
							v-for="art in arrBetreuerart"
							:key="art.betreuerart_kurzbz"
							:value="art.betreuerart_kurzbz"
							>
							{{art.beschreibung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektbetreuer-note"
						:label="$p.t('projektarbeit', 'note')"
						type="select"
						v-model="formData.note"
						name="note"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="note in arrNoten"
							:key="note.note"
							:value="note.note"
							>
							{{note.bezeichnung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<div class="col-6">
						<form-input
							container-class="stv-details-projektarbeit-stunden"
							type="text"
							name="stunden"
							:label="$p.t('projektarbeit', 'stunden')"
							:disabled="vertragFieldsDisabled"
							v-model="formData.stunden"
							>
						</form-input>
					</div>
					<div class="col-6">
						<form-input
							container-class="stv-details-projektarbeit-stundensatz"
							type="text"
							name="stundensatz"
							:label="$p.t('projektarbeit', 'stundensatz')"
							:disabled="vertragFieldsDisabled"
							v-model="formData.stundensatz"
							>
						</form-input>
					</div>
				</div>

			</form-form>

			<button class="btn btn-primary" v-show="betreuerFormOpened" @click="saveProjektbetreuer">
				{{ $p.t('projektarbeit', 'betreuerSpeichern') }}
			</button>
			<!-- <div class = "mt-5" v-if="beurteilungDownloadLink !== null">
				<div class="mb-1">
					<a :href="beurteilungDownloadLink" class="btn btn-primary d-block" :class="{ 'disabled' : beurteilungDownloadLink === ''}">
						{{ $p.t('projektarbeit', 'projektbeurteilungErstellen') }}
					</a>
				</div>
				{{ autocompleteSelectedBetreuer?.person_id && beurteilungDownloadLink === '' ? $p.t('projektarbeit', 'projektarbeitNochNichtBeurteilt') : ''}}
			</div> -->
		</div>

		<div class="col-3">
			<vertrag ref="vertrag"
				:vertrag_id="autocompleteSelectedBetreuer?.vertrag_id"
				:person_id="autocompleteSelectedBetreuer?.person_id"
				:betreuerProjektarbeit="initialFormData"
				@vertragsstatusChanged="disableVertragFields">
			</vertrag>
		</div>

	</div>

	<!--Modal: new Person modal -->
	<new-person ref="newPersonModal" :personOnly="true" @saved="personSaved"></new-person>

	<!--Modal: KontaktdatenModal -->
	<bs-modal
		ref="kontaktdatenModal"
		dialog-class="modal-xl modal-dialog-scrollable"
		v-if="autocompleteSelectedBetreuer && autocompleteSelectedBetreuer.person_id">

		<template #title>
			<p class="fw-bold mt-3">{{$p.t('projektarbeit', 'kontaktdatenBearbeiten')}}</p>
		</template>

		<div class="row">
			<div class="col-12">
				<contact ref="contact" :uid="autocompleteSelectedBetreuer.person_id">
				</contact>
			</div>
		</div>

	</bs-modal>
`
}

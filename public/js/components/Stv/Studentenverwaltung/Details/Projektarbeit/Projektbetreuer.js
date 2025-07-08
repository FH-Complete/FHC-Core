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
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('ui', 'bearbeiten');
							button.addEventListener('click', (event) => {
								let data = cell.getData();
								this.actionEditProjektbetreuer(data.projektarbeit_id, data.person_id, data.betreuerart_kurzbz);
							});
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () => {
								const data = cell.getData();
								this.actionDeleteProjektbetreuer(data.betreuer_id, data.projektarbeit_id, data.person_id, data.betreuerart_kurzbz)
							});
							container.append(button);

							//container.append(cell.getData().actionDiv);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				minHeight: '100',
				selectable: true,
				selectable: 1,
				index: 'betreuer_id',
				persistenceID: 'stv-details-projektbetreuer'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'projektarbeit', 'ui']);
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

					let betreuer = [];
					if (idx >= 0) { // if betreuer found
						betreuer = projektbetreuerListe[idx];

						// set currently edited betreuera
						this.formData = betreuer;

						// set download link
						if (betreuer.beurteilungDownloadLink) this.beurteilungDownloadLink = betreuer.beurteilungDownloadLink

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
		getData(projektarbeit_id, studiensemester_kurzbz, projekttyp_kurzbz) {

			this.studiensemester_kurzbz = studiensemester_kurzbz;

			// default Stundensätze from config
			this.defaultFormDataValues.stunden = this.getDefaultStunden(projekttyp_kurzbz);
			this.defaultFormDataValues.stundensatz = this.config.defaultProjektbetreuerStundensatz;
			this.resetModes();

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

			if (projektarbeit_id) {
				this.projektarbeit_id = projektarbeit_id;
				this.$api
					.call(ApiStvProjektbetreuer.getProjektbetreuer(projektarbeit_id))
					.then(result => {
						this.$refs.projektbetreuerTable.tabulator.setData(this.addIds(result.data));
						this.resetForm();
					})
					.catch(this.$fhcAlert.handleSystemError);
			} else {
				this.$refs.projektbetreuerTable.tabulator.setData([]);
				this.resetForm();
			}
		},
		// confirming Betreuer means adding/updating him in list (but not yet saving in db)
		confirmProjektbetreuer() {
			if (!this.betreuerFormOpened) return;

			if (typeof this.formData.betreuer_id == 'undefined') {
				this.formData.betreuer_id = this.getNewBetreuerId();
				this.$refs.projektbetreuerTable.tabulator.addData(this.addAutoCompleteBetreuerToFormData(this.formData));
			} else {
				this.$refs.projektbetreuerTable.tabulator.updateData([this.formData]);
			}

			this.resetModes();
		},
		confirmProjektbetreuerAfterValidation() {
			//if (!this.formDataModified()) return;

			this.validateProjektbetreuer()
				.then(result => {
					this.confirmProjektbetreuer();
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		saveProjektbetreuer(projektarbeit_id) {
			this.confirmProjektbetreuer();
			return this.$refs.formProjektbetreuer.call(
				ApiStvProjektbetreuer.saveProjektbetreuer(projektarbeit_id, this.$refs.projektbetreuerTable.tabulator.getData())
			);
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
		// validate betreuer for data
		validateProjektbetreuer() {
			let alleBetreuer = this.$refs.projektbetreuerTable.tabulator.getData();

			if (this.betreuerFormOpened) {
				alleBetreuer.push(this.addAutoCompleteBetreuerToFormData(this.formData));
			}

			return this.$api.call(ApiStvProjektbetreuer.validateProjektbetreuer(alleBetreuer));
		},
		resetForm() {
			this.formData = this.getDefaultFormData();
			this.beurteilungDownloadLink = null;
			this.autocompleteSelectedBetreuer = null;
			this.initialFormData = null;
			if (this.projekttyp_kurzbz) this.setDefaultStunden(this.projekttyp_kurzbz);
			this.disableVertragFields(false);
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
		addAutoCompleteBetreuerToFormData() {
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
			if (!this.formDataModified()) this.formData.stunden = this.getDefaultStunden(projekttyp_kurzbz);
		},
		// get a new betreuer id (max + 1) 
		getNewBetreuerId() {
			let max = 0;

			for (const betreuer of this.$refs.projektbetreuerTable.tabulator.getData()) {
				if (betreuer.betreuer_id > max) max = betreuer.betreuer_id;
			}

			return max + 1;
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
		reload() {
			this.$refs.projektbetreuerTable.reloadTable();
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
				.call(ApiStvProjektbetreuer.getPerson(result.person_id))
				.then(response => {
					// set the new person in autocomplete field
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

		<div :class="this.config.showVertragsdetails ? 'col-8' : 'col-12'">

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
						name="betreuer"
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

				<div class="row mb-3">
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

			</form-form>

			<button class="btn btn-primary" v-show="betreuerFormOpened" @click="confirmProjektbetreuerAfterValidation">
				{{ $p.t('projektarbeit', 'betreuerBestaetigen') }}
			</button>
			<div class = "mt-5">
				<div class="mb-1">
					<a :href="beurteilungDownloadLink" class="btn btn-primary d-block" :class="{ 'disabled' : !beurteilungDownloadLink || beurteilungDownloadLink == ''}">
						{{ $p.t('projektarbeit', 'projektbeurteilungErstellen') }}
					</a>
				</div>
				{{ autocompleteSelectedBetreuer?.person_id && (!beurteilungDownloadLink || beurteilungDownloadLink == '') ? $p.t('projektarbeit', 'projektarbeitNochNichtBeurteilt') : ''}}
			</div>
		</div>

		<div class="col-4">
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

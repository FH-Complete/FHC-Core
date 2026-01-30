import {CoreFilterCmpt} from "../filter/Filter.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
	},
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		typeId: String,
		id: {
			type: [Number, String],
			required: true
		},
		uid: {
			type: [Number, String],
			required: true
		},
		/** List of types to allow for creation */
		betriebsmittelTypes: {
			type: Array,
			default: null
		},
		/**
		 * If true: only show the types specified in 'betriebsmittelTypes'.
		 * If false: show all available types.
		 */
		filterByProvidedTypes: Boolean
	},
	data() {
		return {
			listBetriebsmitteltyp: [],
			formData: {
				ausgegebenam : new Date(),
				betriebsmitteltyp: 'Zutrittskarte'
			},
			statusNew: true,
			filteredInventar: [],
			layout: 'fitColumns',
			layoutColumnsOnNewData: false,
			height: '550',
			persistenceID: 'core-betriebsmittel'
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					this.endpoint.getAllBetriebsmittel(this.typeId, this.id, (this.filterByProvidedTypes ? this.betriebsmittelTypes : null))
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Nummer", field: "nummer", width: 150},
					{title: "PersonId", field: "person_id", visible: false},
					{title: "Typ", field: "betriebsmitteltyp", width: 125},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{
						title: "Retourdatum",
						field: "retouram",
						width: 128,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{title: "Beschreibung", field: "beschreibung"},
					{title: "UID", field: "uid", width: 87},
					{title: "Kaution", field: "kaution", visible: false},
					{
						title: "Ausgabedatum",
						field: "ausgegebenam",
						width: 144,
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{title: "Betriebsmittel ID", field: "betriebsmittel_id", visible: false},
					{title: "Betriebsmittelperson ID", field: "betriebsmittelperson_id", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						maxWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-print"></i>';
							button.title = this.$p.t('betriebsmittel', 'btn_printUebernahmebestaetigung');
							let cellData = cell.getData();
							button.addEventListener(
								'click',
								(event) =>
								{
									let linkToPdf = this.cisRoot +
										'/content/pdfExport.php?xml=betriebsmittelperson.rdf.php&xsl=Uebernahme&id=' + cellData.betriebsmittelperson_id + '&output=pdf';

									window.open(linkToPdf, '_blank');
								});
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('betriebsmittel', 'btn_editBetriebsmittel');
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditBetriebsmittel(cell.getData().betriebsmittelperson_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('betriebsmittel', 'btn_deleteBetriebsmittel');
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteBetriebsmittel(cell.getData().betriebsmittelperson_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}
				],
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['wawi', 'global', 'infocenter', 'betriebsmittel', 'person']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('nummer').component.updateDefinition({
							title: this.$p.t('wawi', 'nummer')
						});
						cm.getColumnByField('betriebsmitteltyp').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('retouram').component.updateDefinition({
							title: this.$p.t('wawi', 'retourdatum')
						});
						cm.getColumnByField('beschreibung').component.updateDefinition({
							title: this.$p.t('global', 'beschreibung')
						});
						cm.getColumnByField('kaution').component.updateDefinition({
							title: this.$p.t('infocenter', 'kaution')
						});
						cm.getColumnByField('ausgegebenam').component.updateDefinition({
							title: this.$p.t('wawi', 'ausgabedatum')
						});
						cm.getColumnByField('betriebsmittel_id').component.updateDefinition({
							title: this.$p.t('ui', 'betriebsmittel_id')
						});
						cm.getColumnByField('betriebsmittelperson_id').component.updateDefinition({
							title: this.$p.t('ui', 'betriebsmittelperson_id')
						});
						cm.getColumnByField('person_id').component.updateDefinition({
							title: this.$p.t('person', 'person_id')
						});
						cm.getColumnByField('uid').component.updateDefinition({
							title: this.$p.t('person', 'uid')
						});
					}
				}
			];
			return events;
		}
	},
	watch: {
		id() {
			this.$refs.table.reloadTable();
		}
	},
	methods: {
		actionEditBetriebsmittel(betriebsmittelperson_id) {
			this.statusNew = false;
			this
				.loadBetriebsmittel(betriebsmittelperson_id)
				.then(this.$refs.betriebsmittelModal.show);
		},
		actionNewBetriebsmittel() {
			this.resetModal();
			this.$refs.betriebsmittelModal.show();
		},
		actionDeleteBetriebsmittel(betriebsmittelperson_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? betriebsmittelperson_id
					: Promise.reject({handled: true}))
				.then(betriebsmittelperson_id => this.$api.call(
					this.endpoint.deleteBetriebsmittel(betriebsmittelperson_id))
				)
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					window.scrollTo(0, 0);
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewBetriebsmittel() {
			//just append uid to formdata
			this.formData.uid = this.uid;
			if (this.formData.betriebsmitteltyp == 'Inventar')
				this.formData.betriebsmittel_id = this.formData.inventarData?.betriebsmittel_id;
			return this.$refs.betriebsmittelData
				.call(this.endpoint.addNewBetriebsmittel(this.id, this.formData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.betriebsmittelModal.hide();
					this.resetModal();
					window.scrollTo(0, 0);
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateBetriebsmittel(betriebsmittelperson_id) {
			if (this.formData.betriebsmitteltyp == 'Inventar')
				this.formData.betriebsmittel_id = this.formData.inventarData?.betriebsmittel_id;
			return this.$refs.betriebsmittelData
				.call(this.endpoint.updateBetriebsmittel(
					betriebsmittelperson_id,
					this.formData
				))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.betriebsmittelModal.hide();
					this.resetModal();
					window.scrollTo(0, 0);
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadBetriebsmittel(betriebsmittelperson_id) {
			this.resetModal();
			this.statusNew = false;
			return this.$api
				.call(this.endpoint.loadBetriebsmittel(betriebsmittelperson_id))
				.then(result => {
					this.formData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		searchInventar(event) {
			const encodedQuery = encodeURIComponent(event.query);
			return this.$api
				.call(this.endpoint.loadInventarliste(encodedQuery))
				.then(result => {
					this.filteredInventar = result.data;
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		resetModal() {
			this.formData = {};
			this.formData.ausgegebenam = new Date();
			this.formData.retouram = null;
			this.formData.betriebsmitteltyp = null;
			this.formData.nummer = null;
			this.formData.nummer2 = null;
			this.formData.kaution = null;
			this.formData.anmerkung = null;
			this.formData.beschreibung = null;
			this.statusNew = true;
		}
	},
	created() {
		return this.$api
			.call(this.endpoint.getTypenBetriebsmittel(this.betriebsmittelTypes))
			.then(result => {
				this.listBetriebsmitteltyp = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="core-betriebsmittel h-100 d-flex flex-column">
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('ui', 'betriebsmittel')"
			@click:new="actionNewBetriebsmittel"
			>
		</core-filter-cmpt>		
		
		<!--Modal: betriebsmittelModal-->
		<bs-modal ref="betriebsmittelModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('ui', 'add_betriebsmittel')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('ui', 'edit_betriebsmittel')}}</p>
			</template>
			
			<core-form class="row g-3" ref="betriebsmittelData">		
				<legend>Details</legend>
				<div class="row mb-3">
					<form-input
						type="select"
						:label="$p.t('global/typ') + ' *'"
						name="betriebsmitteltyp"
						v-model="formData.betriebsmitteltyp"
						:disabled="!statusNew"
						>
						<option
							v-for="entry in listBetriebsmitteltyp"
							:key="entry.betriebsmitteltyp"
							:value="entry.betriebsmitteltyp"
							>
							{{entry.beschreibung}}
						</option>
					</form-input>
				</div>
			
				<div v-if="formData.betriebsmitteltyp == 'Inventar'" class="row mb-3">
					<form-input
						type="autocomplete"
						:label="$p.t('wawi/inventarnummer')"
						name="betriebsmittel_id"
						v-model="formData.inventarData"
						option-label="dropdowntext" 
						:suggestions="filteredInventar" 
						@complete="searchInventar" 
						:min-length="3"
						>
					</form-input>
				</div>
				<div v-else-if="formData.inventarnummer" class="row mb-3">
					<form-input
						type="text"
						:label="$p.t('wawi/inventarnummer')"
						name="inventarnummer"
						v-model="formData.inventarnummer"
						:disabled="!statusNew"
						>
					</form-input>
				</div>
				
				<div v-if="formData.betriebsmitteltyp!='Inventar' && !formData.inventarnummer" class="row mb-3">
					<form-input
						type="text"
						:label="$p.t('wawi/nummer')"
						name="nummer"
						v-model="formData.nummer"
						>
					</form-input>
				</div>
				
				<div v-if="formData.betriebsmitteltyp!='Inventar' && !formData.inventarnummer" class="row mb-3">
					<form-input
						type="text"
						:label="$p.t('wawi/nummer') + ' 2'"
						name="nummer2"
						v-model="formData.nummer2"
						>
					</form-input>
				</div>
			
				<div v-if="formData.betriebsmitteltyp!='Inventar'" class="row mb-3">
					<form-input
						type="textarea"
						:label="$p.t('global/beschreibung')"
						name="beschreibung"
						v-model="formData.beschreibung"
						:disabled="formData.inventarnummer"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						type="text"
						:label="$p.t('infocenter/kaution')"
						name="kaution"
						v-model="formData.kaution"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						type="textarea"
						:label="$p.t('global/anmerkung')"
						name="anmerkung"
						v-model="formData.anmerkung"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						type="DatePicker"
						:label="$p.t('wawi/ausgegebenam')"
						name="ausgegebenam"
						v-model="formData.ausgegebenam"
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						type="DatePicker"
						:label="$p.t('wawi/retouram')"
						name="retouram"
						v-model="formData.retouram"
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
				</div>
			</core-form>
			
			<template #footer>
				<button type="button" class="btn btn-primary" @click="statusNew ? addNewBetriebsmittel() : updateBetriebsmittel(formData.betriebsmittelperson_id)">{{$p.t('ui', 'speichern')}}</button>
			</template>
		</bs-modal>
	</div>`
}


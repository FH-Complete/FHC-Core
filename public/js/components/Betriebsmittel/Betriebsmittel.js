import VueDatePicker from '../vueDatepicker.js.php';
import {CoreFilterCmpt} from "../filter/Filter.js";
import PvAutoComplete from "../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

import BsModal from "../Bootstrap/Modal.js";
import FormForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		VueDatePicker,
		BsModal,
		FormForm,
		FormInput,
		PvAutoComplete
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
		}
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.endpoint.getAllBetriebsmittel,
				ajaxParams: {
					type: this.typeId,
					id: this.id
				},
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Nummer", field: "nummer", width: 150},
					{title: "PersonId", field: "person_id", visible: false},
					{title: "Typ", field: "betriebsmitteltyp", width: 125},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "Retourdatum", field: "format_retour", width: 128},
					{title: "Beschreibung", field: "beschreibung"},
					{title: "Uid", field: "uid", width: 87},
					{title: "Kaution", field: "kaution", visible: false},
					{title: "Ausgabedatum", field: "format_ausgabe", width: 144},
					{title: "Betriebsmittel_id", field: "betriebsmittel_id", visible: false},
					{title: "Betriebsmittelperson_id", field: "betriebsmittelperson_id", visible: false},
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
							button.title = 'Übernahmebestätigung drucken';
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
							button.title = 'Betriebsmittel bearbeiten';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditBetriebsmittel(cell.getData().betriebsmittelperson_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Betriebsmittel löschen';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteBetriebsmittel(cell.getData().betriebsmittelperson_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '550',
				selectableRangeMode: 'click',
				selectable: true
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async() => {

						await this.$p.loadCategory(['wawi', 'global', 'infocenter']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('nummer').component.updateDefinition({
							title: this.$p.t('wawi', 'nummer')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('format_retour').component.updateDefinition({
							title: this.$p.t('wawi', 'retourdatum')
						});
						cm.getColumnByField('kaution').component.updateDefinition({
							title: this.$p.t('infocenter', 'kaution')
						});
						cm.getColumnByField('format_ausgabe').component.updateDefinition({
							title: this.$p.t('wawi', 'ausgabedatum')
						});

					}
				}
			],
			betriebsmittelData: {},
			betriebsmittelperson_id : null,
			listBetriebsmitteltyp: [],
			formData: {
				ausgegebenam : this.getDefaultDate(),
				betriebsmitteltyp: 'Zutrittskarte'
			},
			statusNew: true,
			filteredInventar: []
		}
	},
	watch: {
		uid() {
			this.$refs.table.tabulator.setData(this.endpoint.getAllBetriebsmittel + '/' + this.typeId + '/' + this.id);
		}
	},
	methods: {
		actionEditBetriebsmittel(betriebsmittelperson_id) {
			this.statusNew = false;
			this.loadBetriebsmittel(betriebsmittelperson_id);
			this.$refs.betriebsmittelModal.show();
		},
		actionNewBetriebsmittel() {
			this.resetModal();
			this.$refs.betriebsmittelModal.show();
			this.statusNew = true;
			this.formData.ausgegebenam = this.getDefaultDate();
			this.reload();
		},
		actionDeleteBetriebsmittel(betriebsmittelperson_id) {
			this.loadBetriebsmittel(betriebsmittelperson_id).then(() => {
				this.$refs.deleteBetriebsmittelModal.show();
			});
		},
		addNewBetriebsmittel() {
			//just append uid to formdata
			this.formData.uid = this.uid;
			return this.endpoint.addNewBetriebsmittel(this.id, this.formData)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('betriebsmittelModal');
					this.resetModal();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		deleteBetriebsmittel(betriebsmittelperson_id) {
			return this.endpoint.deleteBetriebsmittel(betriebsmittelperson_id)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
						this.hideModal('deleteBetriebsmittelModal');
						this.resetModal();
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		updateBetriebsmittel(betriebsmittelperson_id) {
			this.formData.uid = this.uid;
			return this.endpoint.updateBetriebsmittel(betriebsmittelperson_id, this.formData)
			.then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				this.hideModal('betriebsmittelModal');
				this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		loadBetriebsmittel(betriebsmittelperson_id) {
			this.resetModal();
			this.statusNew = false;
			return this.endpoint.loadBetriebsmittel(betriebsmittelperson_id)
				.then(result => result.data)
				.then(result => {
					this.formData = result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		searchInventar(event) {
			const encodedQuery = encodeURIComponent(event.query);
			return this.endpoint.loadInventarliste(encodedQuery)
				.then(result => {
					this.filteredInventar = result.data.retval;
				});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		hideModal(modalRef) {
			this.$refs[modalRef].hide();
		},
		resetModal() {
			this.formData = {};
			this.formData.ausgegebenam = this.getDefaultDate();
			this.formData.retouram = null;
			this.formData.betriebsmitteltyp = null;
			this.formData.nummer = null;
			this.formData.nummer2 = null;
			this.formData.kaution = null;
			this.formData.anmerkung = null;
			this.formData.beschreibung = null;
			this.betriebsmittelperson_id = {};
			this.statusNew = true;
		},
		getDefaultDate() {
			const today = new Date();
			return today;
		}
	},
	created(){
		return this.endpoint.getTypenBetriebsmittel()
			.then(result => result.data)
			.then(result => {
				this.listBetriebsmitteltyp = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="core-betriebsmittel h-100 d-flex flex-column">
		
		<!--Modal: deleteBetriebsmittelModal-->
		<BsModal ref="deleteBetriebsmittelModal">
			<template #title>{{$p.t('ui', 'betriebsmittel_delete')}}</template>
			<template #default><p>{{$p.t('ui', 'betriebsmittel_confirm_delete')}}</p></template>
			<template #footer>
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteBetriebsmittel(formData.betriebsmittelperson_id)">OK</button>
			</template>
		</BsModal>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Betriebsmittel"
			@click:new="actionNewBetriebsmittel"
			>
		</core-filter-cmpt>		
		
		<!--Modal: betriebsmittelModal-->
		<bs-modal ref="betriebsmittelModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('ui', 'add_betriebsmittel')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('ui', 'edit_betriebsmittel')}}</p>
			</template>
		
			<form-form class="row g-3" ref="betriebsmittelData">		
				<legend>Details</legend>
					
					<div class="row mb-3">
						<form-input
						type="select"
						:label="$p.t('global/typ')"
						name="typ"
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
							name="inventarnummer"
							v-model="formData.inventarData"
							optionLabel="dropdowntext" 
							:suggestions="filteredInventar" 
							@complete="searchInventar" 
							minLength="3"
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
							v-model="formData['nummer']"
						>
						</form-input>
					</div>
					
					<div v-if="formData.betriebsmitteltyp!='Inventar' && !formData.inventarnummer" class="row mb-3">
						<form-input
							type="text"
							:label="$p.t('wawi/nummer') + ' 2'"
							name="nummer2"
							v-model="formData['nummer2']"
						>
						</form-input>
					</div>
				
					<div v-if="formData.betriebsmitteltyp!='Inventar'" class="row mb-3">
						<form-input
							type="textarea"
							:label="$p.t('global/beschreibung')"
							name="beschreibung"
							v-model="formData['beschreibung']"
							:disabled="formData.inventarnummer"
						>
						</form-input>
					</div>
					
					<div class="row mb-3">
						<form-input
							type="text"
							:label="$p.t('infocenter/kaution')"
							name="kaution"
							v-model="formData['kaution']"
						>
						</form-input>
					</div>
					
					<div class="row mb-3">
						<form-input
							type="textarea"
							:label="$p.t('global/anmerkung')"
							name="anmerkung"
							v-model="formData['anmerkung']"
						>
						</form-input>
					</div>
					
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							:label="$p.t('wawi/ausgegebenam')"
							name="datum"
							v-model="formData['ausgegebenam']"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
						></form-input>
					</div>
					
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							:label="$p.t('wawi/retouram')"
							name="datum"
							v-model="formData['retouram']"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
						></form-input>
					</div>
				</form-form>
				
				<template #footer>
					<button v-if="statusNew"  ref="Close" type="button" class="btn btn-primary" @click="addNewBetriebsmittel()">{{$p.t('ui', 'speichern')}}</button>

					<button v-else  ref="Close" type="button" class="btn btn-primary" @click="updateBetriebsmittel(formData.betriebsmittelperson_id)">{{$p.t('ui', 'speichern')}}</button>
				</template>
					
		</bs-modal>
				
	</div>`
}


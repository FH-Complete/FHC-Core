import VueDatePicker from '../vueDatepicker.js.php';
import {CoreFilterCmpt} from "../filter/Filter.js";
import BsModal from "../Bootstrap/Modal";
import FormForm from '../Form/Form.js';
import FormInput from '../Form/Input.js';

export default {
	components: {
		CoreFilterCmpt,
		VueDatePicker,
		BsModal,
		FormForm,
		FormInput,
	},
	props: [
		'person_id',
		'uid'
		],
	data(){
		return {
			tabulatorOptions: {
			//	ajaxURL: CoreRESTClient._generateRouterURI('components/stv/Betriebsmittel/getBetriebsmittel/' + this.id + '/' + this.typeId),
				ajaxURL: 'api/frontend/v1/stv/Betriebsmittel/getAllBetriebsmittel/' + this.uid + '/' + this.person_id,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Nummer", field: "nummer"},
					{title:  "PersonId", field: "person_id"},
					{title: "Typ", field: "betriebsmitteltyp"},
					{title:  "Retourdatum", field: "retouram"},
					{title:  "Beschreibung", field: "beschreibung"},
					{title:  "Uid", field: "uid"},
					{title:  "Anmerkung", field: "anmerkung", visible: false},
					{title:  "Kaution", field: "kaution", visible: false},
					{title:  "Ausgabedatum", field: "ausgegebenam", visible: false},
					{title: "Betriebsmittel_id", field: "betriebsmittel_id", visible: false},
					{title: "Betriebsmittelperson_id", field: "betriebsmittelperson_id", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-print"></i>';
							button.title = 'Übernahmebestätigung drucken';
							button.addEventListener(
								'click',
								(event) =>
									this.actionPrintConfirmation(cell.getData().betriebsmittel_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Betriebsmittel bearbeiten';
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditBetriebsmittel(cell.getData().betriebsmittel_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Betriebsmittel löschen';
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteBetriebsmittel(cell.getData().betriebsmittel_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}],
				layout: 'fitColumns',
				layoutColumnsOnNewData: false,
				height: '150',
				selectableRangeMode: 'click',
				selectable: true,
				//index: 'betriebsmittel_id'
			},
			tabulatorEvents: [],
			betriebsmittelData: {},
			betriebsmittel_id: {},
			listBetriebsmitteltyp: []
		};
	},
	methods: {
		actionEditBetriebsmittel(betriebsmittel_id){
			console.log("id: " + betriebsmittel_id);
			this.loadBetriebsmittel(betriebsmittel_id).then(() => {
				if(this.betriebsmittelData)
					this.$refs.editBetriebsmittelModal.show();
			});
		},
		actionPrintConfirmation(betriebsmittel_id){
			console.log("actionPrintConfirmation of id: " + betriebsmittel_id);
/*			this.loadBetriebsmittel(betriebsmittel_id).then(() => {

				if(this.betriebsmittelData)
					this.$refs.editBetriebsmittelModal.show();
			});*/
		},
		loadBetriebsmittel(betriebsmittel_id){
			//console.log("2 " + betriebsmittel_id);
			return this.$fhcApi.post('api/frontend/v1/stv/betriebsmittel/loadBetriebsmittel/',
				betriebsmittel_id)
				.then(result => {
					//console.log("in load"  + result);
					this.betriebsmittelData = result.data;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},

	created(){
		this.$fhcApi
			.get('api/frontend/v1/stv/betriebsmittel/getTypenBetriebsmittel')
			.then(result => result.data)
			.then(result => {
				this.listBetriebsmitteltyp = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
/*	async mounted() {
		if(this.showTinyMCE){
			this.initTinyMCE();
		}

		await this.$p.loadCategory(['notiz','global']);

		let cm = this.$refs.table.tabulator.columnManager;

		cm.getColumnByField('verfasser_uid').component.updateDefinition({
			title: this.$p.t('notiz', 'verfasser')
		});
		cm.getColumnByField('titel').component.updateDefinition({
			title: this.$p.t('global', 'titel')
		});
		cm.getColumnByField('text_stripped').component.updateDefinition({
			title: this.$p.t('global', 'text')
		});
		cm.getColumnByField('bearbeiter_uid').component.updateDefinition({
			title: this.$p.t('notiz', 'bearbeiter')
		});
		cm.getColumnByField('start').component.updateDefinition({
			title: this.$p.t('global', 'gueltigVon')
		});
		cm.getColumnByField('ende').component.updateDefinition({
			title: this.$p.t('global', 'gueltigBis')
		});
		cm.getColumnByField('countdoc').component.updateDefinition({
			title: this.$p.t('notiz', 'document')
		});
		cm.getColumnByField('erledigt').component.updateDefinition({
			title: this.$p.t('notiz', 'erledigt')
		});
		cm.getColumnByField('lastupdate').component.updateDefinition({
			title: this.$p.t('notiz', 'letzte_aenderung')
		});
	},*/
	template: `
	<div class="betriebsmittel-betriebsmittel">
	
			<!--Modal: editBetriebsmittelModal-->
		<BsModal ref="editBetriebsmittelModal">
		
			<template #title>Details</template>
			
		
			<form-form class="row g-3" ref="betriebsmittelData">
				
						<div class="row mb-3">								   
							<label for="typ" class="form-label col-sm-4">Typ</label>
							<div class="col-sm-6">
								<form-input
								type="select"
								name="typ"
								v-model="betriebsmittelData['typ']"
								>
								<option v-for="entry in listBetriebsmitteltyp" :key="entry.betriebsmitteltyp" :value="entry.betriebsmitteltyp">{{entry.betriebsmitteltyp}}</option>
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="nummer" class="form-label col-sm-4">Nummer </label>
							<div class="col-sm-6">
								<form-input
									type="text"
									name="nummer"
									v-model="betriebsmittelData['nummer2']"
								>						
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="nummer2" class="form-label col-sm-4">Nummer2</label>
							<div class="col-sm-6">
								<form-input
									type="text"
									name="nummer"
									v-model="betriebsmittelData['nummer2']"
								>						
								</form-input>
							</div>
						</div>
					
						<div class="row mb-3">
							<label for="bechreibung" class="form-label col-sm-4">Beschreibung</label>
							<div class="col-sm-6">
								<form-input
									type="textarea"
									name="beschreibung"
									v-model="betriebsmittelData['beschreibung']"
								>						
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="kaution" class="form-label col-sm-4">Kaution</label>
							<div class="col-sm-6">
								<form-input
									type="text"
									name="kaution"
									v-model="betriebsmittelData['kaution']"
								>						
								</form-input>
							</div>
						</div>
						
				<div class="row mb-3">
							<label for="anmerkung" class="form-label col-sm-4">Anmerkung</label>
							<div class="col-sm-6">
								<form-input
									type="textarea"
									name="anmerkung"
									v-model="betriebsmittelData['anmerkung']"
								>						
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="ausgegebenam" class="form-label col-sm-4">Ausgegeben am</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="betriebsmittelData['ausgegebenam']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="retouram" class="form-label col-sm-4">Retour am</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="betriebsmittelData['retouram']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
				
			</form-form>
			
			<template #footer>
				<button ref="Close" type="button" class="btn btn-primary" @click="editBetriebsmittel(betriebsmittelData.betriebsmittel_id)">Speichern</button>
			</template>
		</BsModal>

		<!--Modal: deleteBetriebsmittelModal-->
<!--		<BsModal ref="deleteBetriebsmittelModal">
			<template #title>Betriebsmittel löschen</template>
			<template #default>
				<p>Betriebsmittel wirklich löschen?</p>
			</template>
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">Abbrechen</button>
				<button ref="Close" type="button" class="btn btn-primary" @click="deleteBetriebsmittel(notizen.notiz_id)">OK</button>
			</template>
		</BsModal>-->

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
		<br>
		
	</div>`
}


import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from "../../../../Form/Form.js";
import FormInput from "../../../../Form/Input.js";
import CoreNotiz from "../../../../Notiz/Notiz.js";

import ApiStvExemptions from "../../../../../api/factory/stv/exemptions.js";
import ApiNotizPerson from '../../../../../api/factory/notiz/person.js';

export default {
	name: "ExemptionComponent",
	components: {
		CoreFilterCmpt,
		BsModal,
		CoreForm,
		FormInput,
		CoreNotiz
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		config: {
			from: 'config',
			required: true
		},
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvExemptions.getAnrechnungen(this.student.prestudent_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "anrechnung_id", field: "anrechnung_id", visible: false},
					{title: "lehrveranstaltung_id", field: "lehrveranstaltung_id", visible: false},
					{title: "Lehrveranstaltung", field: "bez_lehrveranstaltung"},
					{title: "Begründung", field: "begruendung"},
					{title: "lehrveranstaltung_id_kompatibel", field: "lehrveranstaltung_id_kompatibel", visible: false},
					{title: "lehrveranstaltung_bez_kompatibel", field: "lehrveranstaltung_bez_kompatibel"},
					{title: "status", field: "status"},
					{title: "genehmigt_von", field: "genehmigt_von"},
					{title: "notizen_anzahl", field: "notizen_anzahl", visible: false},
					{title: "Datum", field: "insertamum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						}
					},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							if(this.config.editableAnrechnungen){
								let buttonEdit = document.createElement('button');
								buttonEdit.className = 'btn btn-outline-secondary btn-action';
								buttonEdit.innerHTML = '<i class="fa fa-edit"></i>';
								buttonEdit.title = this.$p.t('ui', 'bearbeiten');
								buttonEdit.addEventListener('click', (event) =>
									this.actionEditAnrechnung(cell.getData().anrechnung_id)
								);
								container.append(buttonEdit);
							}

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteAnrechnung(cell.getData().anrechnung_id)
							);
							container.append(button);

							let countNotizen = cell.getData().notizen_anzahl;
							let buttonNotes = document.createElement('button');
							buttonNotes.className = 'btn btn-outline-secondary btn-action';
							if (countNotizen > 0){
								buttonNotes.innerHTML = countNotizen + this.$p.t('anrechnung', 'existingNotes');
							}
							else
								buttonNotes.innerHTML = '+' + this.$p.t('global', 'notiz');

							buttonNotes.addEventListener('click', (event) =>
								this.addNote(cell.getData().anrechnung_id)
							);
							container.append(buttonNotes);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				height: '500',
				index: 'anrechnung_id',
				persistenceID: 'stv-details-anrechnungen'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {

						await this.$p.loadCategory(['anrechnungen', 'global', 'ui', 'lehre']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('anrechnung_id').component.updateDefinition({
							title: this.$p.t('ui', 'anrechnung_id'),
						});
						cm.getColumnByField('lehrveranstaltung_id').component.updateDefinition({
							title: this.$p.t('lehre', 'lehrveranstaltung_id'),
						});
						cm.getColumnByField('bez_lehrveranstaltung').component.updateDefinition({
							title: this.$p.t('lehre', 'lehrveranstaltung'),
						});
						cm.getColumnByField('begruendung').component.updateDefinition({
							title: this.$p.t('global', 'begruendung'),
						});
						cm.getColumnByField('lehrveranstaltung_id_kompatibel').component.updateDefinition({
							title: this.$p.t('anrechnung', 'lehrveranstaltung_id_kompatibel'),
						});
						cm.getColumnByField('lehrveranstaltung_bez_kompatibel').component.updateDefinition({
							title: this.$p.t('anrechnung', 'lehrveranstaltung_bez_kompatibel'),
						});
						cm.getColumnByField('genehmigt_von').component.updateDefinition({
							title: this.$p.t('anrechnung', 'genehmigtVon'),
						});
						cm.getColumnByField('notizen_anzahl').component.updateDefinition({
							title: this.$p.t('anrechnung', 'existingNotes'),
						});
						cm.getColumnByField('insertamum').component.updateDefinition({
							title: this.$p.t('global', 'datum'),
						});

					}
				}
			],
			formData: {},
			listBegruendungen: [],
			listNewLehrveranstaltungen: [],
			listLektoren: [],
			listKompatibleLehrveranstaltungen: [],
			statusNew: true,
			showNotizen: false,
			currentAnrechnung_id: null,
			endpoint: ApiNotizPerson
		}
	},
	watch: {
		student(){
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
		},
	},
	methods: {
		actionNewAnrechnung(){
			this.statusNew = true;
			this.$refs.anrechnungsModal.show();
		},
		actionEditAnrechnung(anrechnung_id){
			this.resetForm();
			this.statusNew = false;
			this.loadAnrechnung(anrechnung_id);
			this.$refs.anrechnungsModal.show();
		},
		addNote(anrechnung_id){
			this.currentAnrechnung_id = anrechnung_id;
			this.showNotizen = true;
		},
		addNewAnrechnung(){
			const dataToSend = {
				prestudent_id: this.student.prestudent_id,
				formData: this.formData
			};

			return this.$refs.formExemptions
				.call(ApiStvExemptions.addNewAnrechnung(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.anrechnungsModal.hide();
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		editAnrechnung(anrechnung_id){
			const dataToSend = {
				anrechnung_id: anrechnung_id,
				formData: this.formData
			};
			return this.$refs.formExemptions
				.call(ApiStvExemptions.editAnrechnung(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$refs.anrechnungsModal.hide();
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		actionDeleteAnrechnung(anrechnung_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? anrechnung_id
					: Promise.reject({handled: true}))
				.then(this.deleteAnrechnung)
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteAnrechnung(anrechnung_id){
			return this.$api
				.call(ApiStvExemptions.deleteAnrechnung(anrechnung_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		getLvsKompatibel(){
			if(this.formData.lehrveranstaltung_id) {
				this.$api
					.call(ApiStvExemptions.getLvsKompatibel(this.formData.lehrveranstaltung_id))
					.then(result => {
						this.listKompatibleLehrveranstaltungen = result.data;
					})
					.catch(this.$fhcAlert.handleSystemError);
			}
		},
		handleInput(){
			if(this.formData.begruendung == 2) {
				this.getLvsKompatibel();
			}
		},
		loadAnrechnung(anrechnung_id){
			return this.$api
				.call(ApiStvExemptions.loadAnrechnung(anrechnung_id))
				.then(result => {
					this.formData = result.data;
					this.getLvsKompatibel();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		resetForm(){
			this.formData = {};
			this.statusNew = true;
		},
		resetLvKompatibel(){
			this.formData.lehrveranstaltung_id_kompatibel = null;
			this.handleInput();
		}
	},
	created() {
		this.$api
			.call(ApiStvExemptions.getLehrveranstaltungen(this.student.prestudent_id))
			.then(result => {
				this.listNewLehrveranstaltungen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvExemptions.getBegruendungen())
			.then(result => {
				this.listBegruendungen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvExemptions.getLektoren(this.student.studiengang_kz))
			.then(result => {
				this.listLektoren = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

	},
	template: `
					<div class="stv-details-tab_exemptions h-100 pb-3">
					<h5>{{$p.t('lehre', 'anrechnungen')}}</h5>
					
					<div v-if="showNotizen" class="border p-3 overflow-auto" style="height: 200px;">
							<div class="justify-content-end pb-3">
								<form-input
									container-class="form-switch"
									type="checkbox"
									label="Notizen"
									v-model="showNotizen"
									@change="onSwitchHide"
									>
								</form-input>
							</div>
						<core-notiz
							:endpoint="endpoint"
							ref="formNotes"
							notiz-layout="popupModal"
							typeId="anrechnung_id"
							:id="currentAnrechnung_id"
							show-document
							show-tiny-mce
							:visibleColumns="['titel','text','verfasser','bearbeiter','dokumente']"
							>
						</core-notiz>
					
					</div>
					
					<template v-if="config.editableAnrechnungen" >
						<core-filter-cmpt
							ref="table"
							:tabulator-options="tabulatorOptions"
							:tabulator-events="tabulatorEvents"
							table-only
							:side-menu="false"
							reload
							new-btn-show
							:new-btn-label="this.$p.t('lehre', 'anrechnung')"
							@click:new="actionNewAnrechnung"
						>
						</core-filter-cmpt>
					</template>
					<template v-else>
						<core-filter-cmpt
							ref="table"
							:tabulator-options="tabulatorOptions"
							:tabulator-events="tabulatorEvents"
							table-only
							:side-menu="false"
							reload
						>
						</core-filter-cmpt>
					</template>
				
					<bs-modal ref="anrechnungsModal" dialog-class="modal-dialog-scrollable" >
						<template #title>
							<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('anrechnung', 'neueAnrechnung')}}</p>							
							<p v-else class="fw-bold mt-3">{{$p.t('anrechnung', 'editAnrechnung')}}</p>
						</template>
						
						<core-form ref="formExemptions">
							<div class="row mb-3">
								<form-input
									type="select"
									:label="$p.t('lehre/lehrveranstaltung')"
									name="lehrveranstaltung"
									v-model="formData.lehrveranstaltung_id"
									@change="resetLvKompatibel"
									>
									<option
										v-for="entry in listNewLehrveranstaltungen"
										:key="entry.lehrveranstaltung_id"
										:value="entry.lehrveranstaltung_id"
										>
										{{entry.bezeichnung}} Semester {{entry.semester}} {{entry.lehrform_kurzbz}}
									</option>
								</form-input>
							</div>
							
							<div class="row mb-3">
								<form-input
									type="select"
									:label="$p.t('anrechnung/begruendung')"
									name="begruendung"
									v-model="formData.begruendung_id"
									@change="handleInput"
									>
									<option
										v-for="entry in listBegruendungen"
										:key="entry.begruendung_id"
										:value="entry.begruendung_id"
										>
										{{entry.bezeichnung}}
									</option>
								</form-input>
							</div>
							
							<!--is shown when typ == kompatible LV-->
							<div v-if="formData.begruendung_id == '2'" class="row mb-3">
								<form-input
									type="select"
									label="Lehrveranstaltung Kompatibel"
									name="lehrveranstaltung_id_kompatibel"
									v-model="formData.lehrveranstaltung_id_kompatibel"
									>
									<option
										v-for="entry in listKompatibleLehrveranstaltungen"
										:key="entry.lehrveranstaltung_id"
										:value="entry.lehrveranstaltung_id"
										>
										{{entry.bezeichnung}} Semester {{entry.semester}} {{entry.lehrform_kurzbz}}
									</option>
								</form-input>
							</div>						
							
							<div class="row mb-3">
								<form-input
									type="select"
									:label="$p.t('anrechnung/genehmigtVon')"
									name="genehmigtVon"
									v-model="formData.genehmigt_von"
									>
									<option
										v-for="entry in listLektoren"
										:key="entry.uid"
										:value="entry.uid"
										>
										{{entry.nachname}} {{entry.vorname}}
									</option>
								</form-input>
							</div>						
						</core-form>
						
						<template #footer>
							<button v-if="statusNew" type="button" class="btn btn-primary" @click="addNewAnrechnung">{{$p.t('ui', 'speichern')}}</button>
							<button v-else type="button" class="btn btn-primary" @click="editAnrechnung(formData.anrechnung_id)">{{$p.t('ui', 'speichern')}}</button>
						</template>
								
					</bs-modal>
	`
}
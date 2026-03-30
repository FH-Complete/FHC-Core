import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

import ApiStvProjektarbeit from '../../../../../api/factory/stv/projektarbeit.js';
import ProjektarbeitDetails from "./Details.js";
import Projektbetreuer from "./Projektbetreuer.js";

export default {
	name: 'Projektarbeit',
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		PvAutoComplete,
		ProjektarbeitDetails,
		Projektbetreuer
	},
	inject: {
		cisRoot: {
			from: 'cisRoot'
		},
		config: {
			from: 'config',
			required: true
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorEvents: [
				{
					event: 'dataLoaded',
					handler: data => this.tabulatorData = data.map(item => {
						item.actionDiv = document.createElement('div');
						return item;
					}),
				},
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'lehre', 'stv', 'ui', 'projektarbeit']);

						const setHeader = (field, text) => {
							const col = this.$refs.table.tabulator.getColumn(field);
							if (!col) return;

							const el = col.getElement();
							if (!el || !el.querySelector) return;

							const titleEl = el.querySelector('.tabulator-col-title');
							if (titleEl) {
								titleEl.textContent = text;
							}
						};

						setHeader('projekttyp_kurzbz', this.$p.t('projektarbeit', 'typ_kurzbz'));
						setHeader('bezeichnung', this.$p.t('projektarbeit', 'typ'));
						setHeader('studiensemester_kurzbz', this.$p.t('lehre', 'studiensemester'));
						setHeader('titel', this.$p.t('projektarbeit', 'titel'));
						setHeader('note', this.$p.t('projektarbeit', 'gesamtnote'));
						setHeader('beginn', this.$p.t('projektarbeit', 'beginn'));
						setHeader('ende', this.$p.t('projektarbeit', 'ende'));
						setHeader('freigegeben', this.$p.t('projektarbeit', 'freigegeben'));
						setHeader('gesperrtbis', this.$p.t('projektarbeit', 'gesperrtBis'));
						setHeader('themenbereich', this.$p.t('projektarbeit', 'themenbereich'));
						setHeader('anmerkung', this.$p.t('projektarbeit', 'anmerkung'));
						setHeader('firma_id', this.$p.t('projektarbeit', 'firmaId'));
						setHeader('abgabedatum', this.$p.t('projektarbeit', 'abgabeEndupload'));
						setHeader('actions', this.$p.t('global', 'aktionen'));
					}
				},
			],
			tabulatorData: [],
			editedProjektarbeit: null,
			statusNew: true,
			studiensemester_kurzbz: null,
			lehrveranstaltung_id: null,
			activeTab: 'details'
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvProjektarbeit.getProjektarbeit(this.student.uid)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Projektarbeit ID", field: "projektarbeit_id", visible: false},
					{title: "Typ", field: "bezeichnung"},
					{title: "Typ Kurzbz", field: "projekttyp_kurzbz", visible: false},
					{title: "Studiensemester", field: "studiensemester_kurzbz"},
					{title: "Titel", field: "titel"},
					{title: "Gesamtnote", field: "note"},
					{
						title: "Abgabe Endupload",
						field: "abgabedatum",
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
					{
						title: "Beginn",
						field: "beginn",
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
						},
						visible: false
					},
					{
						title: "Ende",
						field: "ende",
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
						},
						visible: false
					},
					{
						title:"Freigegeben",
						field:"freigegeben",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						},
						visible: false
					},
					{
						title: "Gesperrt bis",
						field: "gesperrtbis",
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
						},
						visible: false
					},
					{title: "Themenbereich", field: "themenbereich", visible: false},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "Lehreinheit ID", field: "lehreinheit_id", visible: false},
					{title: "Student UID", field: "student_uid", visible: false},
					{title: "Projektbetreuer", field: "projektbetreuer"},
					{
						title:"Final",
						field:"final",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						},
						visible: false
					},
					{title: "Firma ID", field: "firma_id", visible: false},
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
								this.editedProjektarbeit = data;
								this.actionEditProjektarbeit();
							});
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteProjektarbeit(cell.getData().projektarbeit_id)
							);
							container.append(button);

							container.append(cell.getData().actionDiv);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataStretchFrozen',
				height: 'auto',
				minHeight: '200',
				selectableRows: 1,
				index: 'projektarbeit_id',
				persistence:{
					columns: true, //persist column layout
				},
				persistenceID: 'stv-details-projektarbeit-20260217'
			}
			return options;
		}
	},
	methods: {
		actionNewProjektarbeit() {
			this.statusNew = true;
			this.editedProjektarbeit = null;
			this.toggleMenu('details');
			this.$refs.projektarbeitDetails.getFormData(this.statusNew, null, null);
			this.$refs.projektarbeitModal.show();
		},
		actionEditProjektarbeit() {
			this.statusNew = false;
			this.toggleMenu('details');
			this.$refs.projektbetreuer.getFormData(
				this.editedProjektarbeit ? this.editedProjektarbeit.projekttyp_kurzbz : null
			);
			this.$refs.projektbetreuer.getProjektbetreuer(this.editedProjektarbeit?.projektarbeit_id, this.editedProjektarbeit?.studiensemester_kurzbz);
			this.$refs.projektarbeitModal.show();
		},
		actionEditBetreuer() {
			this.statusNew = false;
			this.toggleMenu('betreuer');
			this.$refs.projektarbeitModal.show();
		},
		actionDeleteProjektarbeit(projektarbeit_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? projektarbeit_id
					: Promise.reject({handled: true}))
				.then(this.deleteProjektarbeit)
				.catch(this.$fhcAlert.handleSystemError);
		},
		saveProjektarbeit() {
			if(this.statusNew) this.addNewProjektarbeit()
			else this.updateProjektarbeit()
		},
		addNewProjektarbeit() {
			this.$refs.projektarbeitDetails.addNewProjektarbeit()
				.then((result) => {
					if(result?.data?.length) {
						this.editedProjektarbeit = result.data[0]
						this.$refs.projektarbeitDetails.setFormData(this.editedProjektarbeit)
						this.toggleMenu('betreuer');
					}
					this.projektarbeitSaved();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateProjektarbeit() {
			this.$refs.projektarbeitDetails.updateProjektarbeit()
				.then((result) => {
					console.log('res update', result)
					this.projektarbeitSaved();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteProjektarbeit(projektarbeit_id) {
			return this.$api
				.call(ApiStvProjektarbeit.deleteProjektarbeit(projektarbeit_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		projektarbeitSaved() {
			this.reload();
			this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
			if(!this.statusNew) this.hideModal('projektarbeitModal');
			else this.statusNew = false
		},
		setDefaultStunden(projekttyp_kurzbz) {
			this.$refs.projektbetreuer.setDefaultStunden(projekttyp_kurzbz);
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		toggleMenu(tabId) {
			this.activeTab = tabId;
			if (this.statusNew == false && tabId == 'details') {

				this.$refs.projektarbeitDetails.getFormData(
					this.statusNew, this.editedProjektarbeit?.studiensemester_kurzbz, this.editedProjektarbeit?.lehrveranstaltung_id
				);
				this.$refs.projektarbeitDetails.loadProjektarbeit(this.editedProjektarbeit?.projektarbeit_id);
			} else if (tabId == 'betreuer') {
				this.$refs.projektbetreuer.getFormData(
					this.editedProjektarbeit ? this.editedProjektarbeit.projekttyp_kurzbz : null
				);
				this.$refs.projektbetreuer.getProjektbetreuer(this.editedProjektarbeit?.projektarbeit_id, this.editedProjektarbeit?.studiensemester_kurzbz);
			}
		},
		resetFormData() {
			this.$refs.projektarbeitDetails.resetForm()
			this.$refs.projektbetreuer.resetForm()
		}
	},
	template: `
	<div class="stv-details-projektarbeit h-100 pb-3">
		<h4>{{this.$p.t('stv','tab_projektarbeit')}}</h4>

		<core-filter-cmpt
			v-if="!this.student.length"
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('stv', 'tab_projektarbeit')"
			@click:new="actionNewProjektarbeit"
			>
		</core-filter-cmpt>

		<!--Modal: projektarbeitModal-->
		<bs-modal ref="projektarbeitModal" :dialog-class="(statusNew ? 'modal-xl ' : 'fhc-xxl-modal ' ) + 'modal-dialog-scrollable'" 
			header-class="flex-wrap pb-0"
			@hideBsModal="resetFormData">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('projektarbeit', 'projektarbeitAnlegen')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('projektarbeit', 'projektarbeitBearbeiten')}}</p>
			</template>

			<div class="row" >
				<div :class="statusNew ? 'col-12' : 'col-6'">
					<projektarbeit-details ref="projektarbeitDetails" :student="student" @projekttyp-changed="setDefaultStunden">
					</projektarbeit-details>
				</div>

				<div :class="statusNew ? '' : 'col-6'" :style="statusNew ? 'display: none' : ''">
					<projektbetreuer ref="projektbetreuer" :config="config" @betreuer-saved="reload"></projektbetreuer>
				</div>
			</div>

			<template #footer>
				<button type="button" class="btn btn-secondary" @click="resetFormData" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button class="btn btn-primary" @click="saveProjektarbeit()"> {{$p.t('ui', 'speichern')}}</button>
			</template>

		</bs-modal>
	</div>
`
}


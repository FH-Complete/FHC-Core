import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

import ApiStvProjektarbeit from '../../../../../api/factory/stv/projektarbeit.js';
import ProjektarbeitDetails from "./Details.js";
import Projektbetreuer from "./Projektbetreuer.js";

export default {
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

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('projekttyp_kurzbz').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'typ_kurzbz')
						});
						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'typ')
						});
						cm.getColumnByField('studiensemester_kurzbz').component.updateDefinition({
							title: this.$p.t('lehre', 'studiensemester')
						});
						cm.getColumnByField('titel').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'titel')
						});
						cm.getColumnByField('note').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'gesamtnote')
						});
						cm.getColumnByField('beginn').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'beginn')
						});
						cm.getColumnByField('ende').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'ende')
						});
						cm.getColumnByField('freigegeben').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'freigegeben')
						});
						cm.getColumnByField('gesperrtbis').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'gesperrtBis')
						});
						cm.getColumnByField('themenbereich').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'themenbereich')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'anmerkung')
						});
						cm.getColumnByField('firma_id').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'firmaId')
						});
						cm.getColumnByField('abgabedatum').component.updateDefinition({
							title: this.$p.t('projektarbeit', 'abgabeEndupload')
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
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
						title: "Abgabe Enduplad",
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
							button.innerHTML = '<i class="fa fa-users"></i>';
							button.title = this.$p.t('projektarbeit', 'betreuerBearbeiten');
							button.addEventListener('click', (event) => {
								let data = cell.getData();
								this.editedProjektarbeit = data;
								this.actionEditBetreuer();
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
				persistenceID: 'stv-details-projektarbeit-2025112401'
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
		addNewProjektarbeit() {
			this.$refs.projektarbeitDetails.addNewProjektarbeit()
				.then((result) => {
					this.projektarbeitSaved();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateProjektarbeit() {
			this.$refs.projektarbeitDetails.updateProjektarbeit()
				.then((result) => {
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
			this.hideModal('projektarbeitModal');
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
			if (this.statusNew == false) {
				switch(tabId) {
					case 'details':
						this.$refs.projektarbeitDetails.getFormData(
							this.statusNew, this.editedProjektarbeit?.studiensemester_kurzbz, this.editedProjektarbeit?.lehrveranstaltung_id
						);
						this.$refs.projektarbeitDetails.loadProjektarbeit(this.editedProjektarbeit?.projektarbeit_id);
						break;
					case 'betreuer':
						this.$refs.projektbetreuer.getFormData(
							this.editedProjektarbeit ? this.editedProjektarbeit.projekttyp_kurzbz : null
						);
						this.$refs.projektbetreuer.getProjektbetreuer(this.editedProjektarbeit?.projektarbeit_id, this.editedProjektarbeit?.studiensemester_kurzbz);
						break;
				}
			}
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
		<bs-modal ref="projektarbeitModal" dialog-class="modal-xl modal-dialog-scrollable" header-class="flex-wrap pb-0">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('projektarbeit', 'projektarbeitAnlegen')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('projektarbeit', 'projektarbeitBearbeiten')}}</p>
			</template>

			<template #modal-header-content v-if="!statusNew">
				<ul class="nav nav-tabs w-100 mt-3 msg_preview" id="pa_tabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link" :class="activeTab == 'details' ? 'active' : ''" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true" @click="toggleMenu('details')">Details</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" :class="activeTab == 'betreuer' ? 'active' : ''" id="betreuer-tab" data-bs-toggle="tab" data-bs-target="#betreuer" type="button" role="tab" aria-controls="betreuer" aria-selected="false" @click="toggleMenu('betreuer')">{{$p.t('projektarbeit', 'betreuerGross')}}</button>
					</li>
				</ul>
			</template>

			<div class="tab-content" id="pa_content">
				<div class="tab-pane fade show" :class="activeTab == 'details' ? 'active' : ''" id="details" role="tabpanel" aria-labelledby="details-tab">
					<div class="row">
						<div class="col-12">
							<projektarbeit-details ref="projektarbeitDetails" :student="student" @projekttyp-changed="setDefaultStunden">
							</projektarbeit-details>
						</div>
					</div>
				</div>

				<div class="tab-pane fade show" :class="activeTab == 'betreuer' ? 'active' : ''" id="betreuer" role="tabpanel" aria-labelledby="betreuer-tab">
					<div class="row">
						<div class="col-12">
							<projektbetreuer ref="projektbetreuer" :config="config" @betreuer-saved="reload"></projektbetreuer>
						</div>
					</div>
				</div>
			</div>

			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button v-if="statusNew" class="btn btn-primary" @click="addNewProjektarbeit()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-if="!statusNew && activeTab == 'details'" class="btn btn-primary" @click="updateProjektarbeit()"> {{$p.t('ui', 'speichern')}}</button>
			</template>

		</bs-modal>
	</div>
`
}


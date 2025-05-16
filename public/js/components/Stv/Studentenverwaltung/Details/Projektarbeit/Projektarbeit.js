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
		},
		isBerechtigtDocAndOdt: {
			from: 'hasPermissionOutputformat',
			default: false
		}
	},
	computed: {
		//~ studentUids() {
			//~ if (this.student.uid)
			//~ {
				//~ return [this.student.uid];
			//~ }
			//~ return this.student.map(e => e.uid);
		//~ },
		studentKzs(){
			if (this.student.uid)
			{
				return [this.student.studiengang_kz];
			}
			return this.student.map(e => e.studiengang_kz);
		},
		stg_kz(){
			return this.studentKzs.length > 0 ? this.studentKzs.length[0] : null;
		}
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvProjektarbeit.getProjektarbeit(this.student.uid)),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Projektarbeit ID", field: "projektarbeit_id", visible: false},
					{title: "Typ", field: "projekttyp_kurzbz"},
					{title: "Studiensemester", field: "studiensemester_kurzbz"},
					{title: "Titel", field: "titel"},
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
								this.actionEditProjektarbeit(
									data.projektarbeit_id, data.studiensemester_kurzbz, data.lehrveranstaltung_id, data.projekttyp_kurzbz
								);
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
				layout: 'fitDataFill',
				height: 'auto',
				minHeight: '200',
				selectable: 1,
				index: 'projektarbeit_id',
				persistenceID: 'stv-details-projektarbeit'
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
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
						await this.$p.loadCategory(['global', 'person', 'stv', 'ui']);


						let cm = this.$refs.table.tabulator.columnManager;

						//~ cm.getColumnByField('vorsitz_nachname').component.updateDefinition({
							//~ title: this.$p.t('abschlusspruefung', 'vorsitz_header')
						//~ });
						/*
						cm.getColumnByField('actions').component.updateDefinition({
						title: this.$p.t('global', 'aktionen')
												});
						*/
					}
				},
			],
			tabulatorData: [],
			lastSelected: null,
			statusNew: true,
			studiensemester_kurzbz: null,
			lehrveranstaltung_id: null
		}
	},
	methods: {
		actionNewProjektarbeit() {
			this.statusNew = true;
			this.$refs.projektarbeitDetails.resetForm();
			this.$refs.projektarbeitDetails.getFormData();
			this.$refs.projektbetreuer.getData();
			this.$refs.projektarbeitModal.show();
		},
		actionEditProjektarbeit(projektarbeit_id, studiensemester_kurzbz, lehrveranstaltung_id, projekttyp_kurzbz) {
			this.statusNew = false;
			this.$refs.projektarbeitDetails.getFormData(this.statusNew, studiensemester_kurzbz, lehrveranstaltung_id);
			// TODO: maybe preload projektarbeit? not just on edit?
			this.$refs.projektarbeitDetails.loadProjektarbeit(projektarbeit_id);
			this.$refs.projektbetreuer.getData(projektarbeit_id, studiensemester_kurzbz, projekttyp_kurzbz);
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
			this.$refs.projektbetreuer.validateProjektbetreuer()
				.then(() => {
					return this.$refs.projektarbeitDetails.addNewProjektarbeit();
				})
				.then((result) => {
					const projektarbeit_id = result.data;
					console.log(projektarbeit_id);

					if (!isNaN(projektarbeit_id)) {
						return this.$refs.projektbetreuer.saveProjektbetreuer(projektarbeit_id);
					}
				})
				.then((result) => {
					console.log(result);
					this.projektarbeitSaved();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateProjektarbeit() {
			this.$refs.projektbetreuer.validateProjektbetreuer()
				.then(() => {
					return this.$refs.projektarbeitDetails.updateProjektarbeit();
				})
				.then((result) => {
					const projektarbeit_id = result.data;
					console.log(projektarbeit_id);

					if (!isNaN(projektarbeit_id)) {
						return this.$refs.projektbetreuer.saveProjektbetreuer(projektarbeit_id);
					}
				})
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
			this.$refs.projektarbeitDetails.resetForm();
		},
		rowSelectionChanged(data) {
			this.lastSelected = data.length > 0 ? data[0] : null;
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload() {
			this.$refs.table.reloadTable();
		}
	},
	created() {
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
		<bs-modal ref="projektarbeitModal" dialog-class="modal-xl modal-dialog-scrollable">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('projektarbeit', 'projektarbeitAnlegen')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('projektarbeit', 'projektarbeitBearbeiten')}}</p>
			</template>

			<div class="row">
				<div class="col-6">
					<projektarbeit-details ref="projektarbeitDetails" :student="student"></projektarbeit-details>
				</div>
				<div class="col-6">
					<projektbetreuer ref="projektbetreuer" :config="config"></projektbetreuer>
				</div>
			</div>

			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
					<button v-if="statusNew" class="btn btn-primary" @click="addNewProjektarbeit()"> {{$p.t('ui', 'speichern')}}</button>
					<button v-else class="btn btn-primary" @click="updateProjektarbeit()"> {{$p.t('ui', 'speichern')}}</button>
			</template>

		</bs-modal>
	</div>
`
}


import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

import ApiStvAdmissionDates from '../../../../../api/factory/stv/admissionDates';

export default {
	name: 'ListAdmissionDates',
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
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
					ApiStvAdmissionDates.getAufnahmetermine(this.student.person_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "rt_id", field: "rt_id"},
					{title: "rt_person_id", field: "rt_person_id"},
					{title: "person_id", field: "person_id"},
					{title: "datum", field: "datum",
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
					{title: "stufe", field: "stufe"},
					{title: "studiensemester", field: "studiensemester_kurzbz"},
					{title: "anmerkung", field: "anmerkung", visible: false},
					{title: "anmeldedatum", field: "anmeldedatum", visible: false,
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
					{title: "punkte", field: "punkte"},
					{title: "teilgenommen", field: "teilgenommen"},
					{title: "ort", field: "ort", visible: false},
					{title: "studienplan", field: "studienplan", visible: false},
					{title: "studienplan_id", field: "studienplan_id", visible: false},
					{title: "stg_kuerzel", field: "stg_kuerzel"},
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
							button.addEventListener('click', (event) =>
								this.actionEditPlacementTest(cell.getData().rt_person_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeletePlacementTest(cell.getData().rt_person_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					}
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				minHeight: 200,
				index: 'aufnahmetermin_id',
				persistenceID: 'stv-details-table_admission-dates'
			},
			tabulatorEvents: [],
			formData: {},
			statusNew: true,
			listPlacementTests: [],
			listStudyPlans: []
		}
	},
	methods: {
		actionNewPlacementTest() {
			this.resetForm();
			this.statusNew = true;
			this.$refs.placementTestModal.show();
		},
		actionEditPlacementTest(rt_person_id) {
			console.log("edit Test " + rt_person_id);
			this.resetForm();
			this.statusNew = false;
			this.loadPlacementTest(rt_person_id);
			this.$refs.placementTestModal.show();
		},
		actionDeletePlacementTest(rt_person_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? rt_person_id
					: Promise.reject({handled: true}))
				.then(this.deletePlacementTest)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewPlacementTest() {
			const dataToSend = {
				person_id: this.student.person_id,
				formData: this.formData
			};
			return this.$refs.formPlacementTest
				.call(ApiStvAdmissionDates.addNewPlacementTest(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("placementTestModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadPlacementTest(rt_person_id) {
			console.log("load Test " + rt_person_id);
			return this.$api
				.call(ApiStvAdmissionDates.loadPlacementTest(rt_person_id))
				.then(result => {
					this.formData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updatePlacementTest(rt_person_id) {
			const dataToSend = {
				formData: this.formData,
				rt_person_id: rt_person_id,
			};
			this.$refs.formPlacementTest
				.call(ApiStvAdmissionDates.updatePlacementTest(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("placementTestModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deletePlacementTest(rt_person_id) {
			console.log("delete Test" + rt_person_id);
			return this.$api
				.call(ApiStvAdmissionDates.deletePlacementTest(rt_person_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		resetForm() {
			console.log("resetForm");
			this.formData = {};
			/*

			this.formData.von = new Date();
			this.formData.bis = new Date();
			this.formData.mobilitaetsprogramm_code = 7;
			this.formData.nation_code = 'A';
			this.formData.herkunftsland_code = 'A';
			this.formData.rt_id = null;
			this.formData.localPurposes = [];
			this.formData.localSupports = [];
			this.formData.lehrveranstaltung_id = '',
				this.formData.lehreinheit_id = '',
				this.statusNew = true;
			this.listLes = [];*/
		},
	},
	created() {
		this.$api
			.call(ApiStvAdmissionDates.getListPlacementTests())
			.then(result => {
				this.listPlacementTests = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAdmissionDates.getListStudyPlans(this.student.person_id))
			.then(result => {
				this.listStudyPlans = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-admission-table h-100 pb-3">
		<h4>Allgemein</h4>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('lehre', 'reihungstest')"
			@click:new="actionNewPlacementTest"
			>
		</core-filter-cmpt>
		
		{{formData}}
		
<!--		{{student}}-->
		
<!--		{{listPlacementTests}}-->
		
		<!--Modal: placementTestModal-->
		<bs-modal ref="placementTestModal" dialog-class="modal-dialog-scrollable">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('admission', 'admission_new')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('admission', 'admission_edit')}}</p>
			</template>


			<form-form ref="formPlacementTest" @submit.prevent>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-admission-placementtest"
						:label="$p.t('lehre', 'reihungstest')"
						type="select"
						v-model="formData.rt_id"
						name="reihungstest_id"
						>
						<option value=""> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
						<option
							v-for="test in listPlacementTests"
							:key="test.reihungstest_id"
							:value="test.reihungstest_id"
							>
							{{test.reihungstest_id}} {{test.studiensemester_kurzbz}} St.{{test.stufe}} {{test.kurzbzlang}} {{test.datum}} {{test.uhrzeit}}
							{{test.anmerkung}} (x/{{test.max_teilnehmer}}) {{test.rt_id}}
						</option>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="stv-details-admission-anmeldedatum"
						:label="$p.t('admission', 'anmeldundRtAm')"
						type="DatePicker"
						v-model="formData.anmeldedatum"
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						name="anmeldedatum"
						:teleport="true"
						>
					</form-input>
				</div>				
				
				<div class="row mb-3">
						<form-input
							container-class="stv-details-admission-teilgenommen"
							type="checkbox"
							name="teilgenommen"
							:label="$p.t('admission/rtAngetreten')"
							v-model="formData.teilgenommen"
						>
						</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="stv-details-admission-studyplan"
						:label="$p.t('lehre', 'studienplan')"
						type="select"
						v-model="formData.studienplan_id"
						name="studienplan_id"
						>
						<option value=""> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
						<option
							v-for="stplan in listStudyPlans"
							:key="stplan.studienplan_id"
							:value="stplan.studienplan_id"
							>
							{{stplan.bezeichnung}}
						</option>
					</form-input>
				</div>				
				
				<div class="row mb-3">
					<form-input
						container-class="stv-details-admission-points"
						:label="$p.t('exam', 'punkte')"
						type="text"
						v-model="formData.punkte"
						name="punkte"
						>
					</form-input>	
				</div>
				
				<!--TODO(Manu) Reihungstestergebnis holen-->
				
				
		</form-form>
		
		<template #footer>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
			<button v-if="statusNew" class="btn btn-primary" @click="addNewPlacementTest()"> {{$p.t('ui', 'speichern')}}</button>
			<button v-else class="btn btn-primary" @click="updatePlacementTest(formData.rt_id)"> {{$p.t('ui', 'speichern')}}</button>
		</template>
		
	</div>
	`
}

import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from "../../../../Form/Form.js";
import FormInput from "../../../../Form/Input.js";

import ApiStvJointstudies from '../../../../../api/factory/stv/jointstudies.js';
import ApiStvMobility from "../../../../../api/factory/stv/mobility.js";

export default {
	name: 'Tbl_JointStudies',
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
	},
	inject: {
		currentSemester: {
			from: 'currentSemester',
		},
	},
	props: {
		student: Object
	},
	data(){
		return {
			formData: {
				mobilitaetstyp_kurzbz: "GS",
				studiensemester_kurzbz: this.currentSemester,
				status_kurzbz: 'Student',
				mobilitaet_id: null
			},
			statusNew: true,
			listTypenMobility: [],
			listStudiensemester: [],
			programsMobility: [],
			listStudienprogramme: [],
			listPartner: [],
			statiPrestudent: [],
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvJointstudies.getStudies(this.student.prestudent_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				persistenceID: 'stv-details-jointstudies',
				columns: [
					{title: "mobilitaet_id", field: "mobilitaet_id", visible: false},
					{title: "StSem", field: "studiensemester_kurzbz"},
					//	{title: "mobilitaetstyp_kurzbz", field: "mobilitaetstyp_kurzbz"},
					{title: "kurzbz", field: "kurzbz"},
					{title: "prestudent_id", field: "prestudent_id", visible: false},
					{title: "Status", field: "status_kurzbz"},
					{title: "Partner", field: "partner", visible: false},
					{title: "Sem", field: "ausbildungssemester"},
					{title: "gsprogrammtyp_kurzbz", field: "gsprogrammtyp_kurzbz"},
					{title: "studienprogramm", field: "studienprogramm", visible: false},
					{title: "insertvon", field: "insertvon", visible: false},
					{
						title: "insertamum", field: "insertamum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						},
						visible: false
					},
					{title: "updatevon", field: "updatevon", visible: false},
					{
						title: "updateamum", field: "updateamum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						},
						visible: false
					},
					{
						title: 'Aktionen',
						field: 'actions',
						minWidth: 150,
						maxWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-copy"></i>';
							button.title = this.$p.t('exam', 'newFromOld_studies');
							button.addEventListener(
								'click',
								(event) =>
									this.actionNewFromOldJointStudy(cell.getData().mobilitaet_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('exam', 'edit_studies');
							button.addEventListener(
								'click',
								(event) =>
									this.actionEditJointStudy(cell.getData().mobilitaet_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('exam', 'delete_studies');
							button.addEventListener(
								'click',
								() =>
									this.actionDeleteJointStudy(cell.getData().mobilitaet_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'jointstudies', 'ui', 'lehre', 'mobility']);

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

						setHeader('mobilitaet_id', this.$p.t('global', 'mobilitaet_id'));
						setHeader('gsprogrammtyp_kurzbz', this.$p.t('global', 'typ'));
						setHeader('prestudent_id', this.$p.t('global', 'prestudentID'));
						setHeader('kurzbz', this.$p.t('mobility', 'mobilitaetsprogramm'));
						setHeader('studienprogramm', this.$p.t('jointstudies', 'studienprogramm'));
						setHeader('insertamum', this.$p.t('global', 'insertamum'));
						setHeader('insertvon', this.$p.t('global', 'insertvon'));
						setHeader('updateamum', this.$p.t('global', 'updateamum'));
						setHeader('updatevon', this.$p.t('global', 'updatevon'));
					}
				}
			];
			return events;
		},
	},
	methods: {
		actionDeleteJointStudy(mobilitaet_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? mobilitaet_id
					: Promise.reject({handled: true}))
				.then(this.deleteJointStudy)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionEditJointStudy(mobilitaet_id) {
			this.resetForm();
			this.statusNew = false;
			this.loadJointStudy(mobilitaet_id);
			this.$refs.jointstudyModal.show();
		},
		actionNewFromOldJointStudy(mobilitaet_id) {
			this.resetForm();
			this.loadJointStudy(mobilitaet_id);
			this.statusNew = true;
			this.$refs.jointstudyModal.show();
		},
		actionNewJointStudy() {
			this.resetForm();
			this.statusNew = true;
			this.$refs.jointstudyModal.show();
		},
		addNewJointStudy() {
			const data = {
				prestudent_id: this.student.prestudent_id,
				formData: this.formData
			};
			return this.$refs.formJointStudies
				.call(ApiStvJointstudies.insertStudy(data))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("jointstudyModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		updateJointStudy() {
			const data = {
				prestudent_id: this.student.prestudent_id,
				formData: this.formData
			};
			return this.$refs.formJointStudies
				.call(ApiStvJointstudies.updateStudy(data))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("jointstudyModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		loadJointStudy(mobilitaet_id) {
			return this.$api
				.call(ApiStvJointstudies.loadStudy(mobilitaet_id))
				.then(result => {
					this.formData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteJointStudy(mobilitaet_id) {
			return this.$api
				.call(ApiStvJointstudies.deleteStudy(mobilitaet_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
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
		resetForm() {
			this.formData = {};
			this.formData.mobilitaetstyp_kurzbz = "GS";
			this.formData.status_kurzbz = 'Student';
			this.formData.studiensemester_kurzbz = this.currentSemester;
			this.formData.prestudent_id = this.student.prestudent_id;
		}
	},
	created(){
		this.$api
			.call(ApiStvJointstudies.getTypenMobility())
			.then(result => {
				this.listTypenMobility = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvJointstudies.getStudiensemester())
			.then(result => {
				this.listStudiensemester = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvMobility.getProgramsMobility())
			.then(result => {
				this.programsMobility = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvJointstudies.getStudyprograms())
			.then(result => {
				this.listStudienprogramme = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvJointstudies.getListPartner())
			.then(result => {
				this.listPartner = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvJointstudies.getStatiPrestudent())
			.then(result => {
				this.statiPrestudent = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

	},
	template: `
	<div class="stv-details-jointstudies-table h-100 pb-3">
		<h4>{{$p.t('jointstudies', 'gemeinsameStudien')}}</h4>

		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('jointstudies', 'gemeinsamesStudium')"
			@click:new="actionNewJointStudy"
			>
		</core-filter-cmpt>

		<!--Modal: jointstudyModal-->
		<bs-modal ref="jointstudyModal" dialog-class="modal-dialog-scrollable">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('jointstudies', 'neuAnlegen')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('jointstudies', 'edit')}}</p>
			</template>

			<form-form ref="formJointStudies">
				<div class="row mb-3">
					<form-input
						container-class="stv-details-jointstudy-mobilitaetstyp"
						:label="$p.t('global', 'typ')"
						type="select"
						v-model="formData.mobilitaetstyp_kurzbz"
						name="mobilitaetstyp_kurzbz"
						disabled
						>
						<option
							v-for="mob in listTypenMobility"
							:key="mob.mobilitaetstyp_kurzbz"
							:value="mob.mobilitaetstyp_kurzbz"
							>
							 {{mob.bezeichnung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-jointstudies-studiensemester"
						:label="$p.t('lehre', 'studiensemester')"
						type="select"
						v-model="formData.studiensemester_kurzbz"
						name="studiensemester_kurzbz"
						>
						<option
							v-for="sem in listStudiensemester"
							:key="sem.studiensemester_kurzbz"
							:value="sem.studiensemester_kurzbz"
							>
							 {{sem.studiensemester_kurzbz}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-jointstudies-partner"
						:label="$p.t('mobility', 'mobilitaetsprogramm')"
						type="select"
						v-model="formData.mobilitaetsprogramm_code"
						name="mobilitaetsprogramm_code"
						>
						<option
							v-for="mob in programsMobility"
							:key="mob.mobilitaetsprogramm_code"
							:value="mob.mobilitaetsprogramm_code"
							>
							 {{mob.beschreibung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-jointstudies-studienprogramm"
						:label="$p.t('jointstudies', 'studienprogramm')"
						type="select"
						v-model="formData.gsprogramm_id"
						name="gsprogramm_id"
						>
						<option
							v-for="prog in listStudienprogramme"
							:key="prog.gsprogramm_id"
							:value="prog.gsprogramm_id"
							>
							 {{prog.bezeichnung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-jointstudies-partner"
						label="Partner"
						type="select"
						v-model="formData.firma_id"
						name="firma_id"
						>
						<option
							v-for="partner in listPartner"
							:key="partner.firma_id"
							:value="partner.firma_id"
							>
							 {{partner.name}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-jointstudies-status"
						label="Status"
						type="select"
						v-model="formData.status_kurzbz"
						name="status_kurzbz"
						>
						<option
							v-for="status in statiPrestudent"
							:key="status.status_kurzbz"
							:value="status.status_kurzbz"
							>
							 {{status.status_kurzbz}}
						</option>
					</form-input>
				</div>
				<div class="col-2 row mb-3">
					<form-input
						container-class="stv-details-jointstudies-ausbildungssemester"
						:label="$p.t('lehre', 'ausbildungssemester')"
						type="text"
						v-model="formData.ausbildungssemester"
						name="ausbildungssemester"
						>
					</form-input>
				</div>

			</form-form>

			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button v-if="statusNew" class="btn btn-primary" @click="addNewJointStudy()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-else class="btn btn-primary" @click="updateJointStudy(formData.mobilitaet_id)"> {{$p.t('ui', 'speichern')}}</button>
			</template>

		</bs-modal>

	</div>
	`
}

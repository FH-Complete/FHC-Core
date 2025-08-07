import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import MobilityPurpose from './List/Purpose.js';
import MobilitySupport from './List/Support.js';

import ApiStvMobility from '../../../../../api/factory/stv/mobility.js';

export default {
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		MobilityPurpose,
		MobilitySupport
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		lists: {
			from: 'lists'
		},
		currentSemester: {
			from: 'currentSemester',
		},
		hasAssistenzPermissionForStgs: {
			from: 'hasAssistenzPermissionForStgs',
			default: false
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
					ApiStvMobility.getMobilitaeten(this.student.uid)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Kurzbz", field: "kurzbz"},
					{title: "Nation", field: "nation_code"},
					{
						title: "Von",
						field: "von",
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
						title: "Bis",
						 field: "bis",
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
					{title: "bisio_id", field: "bisio_id"},
					{title: "lehrveranstaltung_id", field: "lehrveranstaltung_id"},
					{title: "lehreinheit_id", field: "lehreinheit_id"},
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
								this.actionEditMobility(cell.getData().bisio_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', () =>
								this.actionDeleteMobility(cell.getData().bisio_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				minHeight: 200,
				index: 'bisio_id',
				persistenceID: 'stv-details-table_mobiliy'
			},
			tabulatorEvents: [
				{
					event: 'dataLoaded',
					handler: data => this.tabulatorData = data.map(item => {
					//	item.actionDiv = document.createElement('div');
						return item;
					}),
				},
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'person', 'stv', 'mobility', 'ui']);


						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('kurzbz').component.updateDefinition({
							title: this.$p.t('mobility', 'kurzbz_program')
						});
						cm.getColumnByField('nation_code').component.updateDefinition({
							title: this.$p.t('mobility', 'gastnation')
						});
						cm.getColumnByField('von').component.updateDefinition({
							title: this.$p.t('ui', 'von')
						});
						cm.getColumnByField('bis').component.updateDefinition({
							title: this.$p.t('global', 'bis')
						});
						cm.getColumnByField('bisio_id').component.updateDefinition({
							title: this.$p.t('mobility', 'bisio_id')
						});

/*						cm.getColumnByField('actions').component.updateDefinition({
						title: this.$p.t('global', 'aktionen')
						});*/
					}
				}
			],
			formData: {
				von: new Date(),
				bis: new Date(),
				mobilitaetsprogramm_code: 7,
				nation_code: 'A',
				herkunftsland_code: 'A',
				bisio_id: null,
				localPurposes: [],
				localSupports: [],
				lehrveranstaltung_id: null,
				lehreinheit_id: null
			},
			statusNew: true,
			programsMobility: [],
			listLvs: [],
			listLes: [],
			listLvsAndLes: [],
			listPurposes: [],
			listSupports: [],
			tabulatorData: []
		}
	},
	watch: {
		student(){
			if (this.$refs.table) {
				this.$refs.table.reloadTable();
			}
		},
	},
	computed:{
		lv_teile(){
			return this.listLvsAndLes.filter(lv => lv.lehreinheit_id == this.formData.lehreinheit_id);
		},
		isBerechtigtForStudiengang(){
			const currentKz = this.student.studiengang_kz.toString();
			return this.hasAssistenzPermissionForStgs.includes(currentKz);
		}
	},
	methods: {
		actionNewMobility() {
			this.resetForm();
			this.statusNew = true;
			this.$refs.mobilityModal.show();
		},
		actionEditMobility(bisio_id) {
			this.resetForm();
			this.statusNew = false;
			this.loadMobility(bisio_id);
			this.$refs.mobilityModal.show();
		},
		actionDeleteMobility(bisio_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? bisio_id
					: Promise.reject({handled: true}))
				.then(this.deleteMobility)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewMobility() {
			const dataToSend = {
				uid: this.student.uid,
				formData: this.formData
			};
			return this.$refs.formMobility
				.call(ApiStvMobility.addNewMobility(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("mobilityModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
					this.$refs.purposes.resetLocalData();
					this.$refs.supports.resetLocalData();
				});
		},
		handleLVchanged: function() {
			this.loadItems();
			this.resetLehreinheit();
		},
		loadItems(){
			if(this.formData.lehrveranstaltung_id) {
				this.getLehreinheiten(this.formData.lehrveranstaltung_id, this.currentSemester);
			}
		},
		changeItems(){
			this.resetLehreinheit();
			this.loadItems();
		},
		resetLehreinheit(){
			this.formData.lehreinheit_id = null;
		},
		getLehreinheiten(lv_id, studiensemester_kurzbz) {
			const data = {
				lv_id: lv_id,
				studiensemester_kurzbz: studiensemester_kurzbz
			};
			return this.$api
				.call(ApiStvMobility.getAllLehreinheiten(data))
				.then(response => {
					this.listLes = response.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadMobility(bisio_id) {
			return this.$api
				.call(ApiStvMobility.loadMobility(bisio_id))
				.then(result => {
					this.formData = result.data;

					if(this.formData.lehrveranstaltung_id > 0 ) {
						this.loadItems();
					}
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateMobility(bisio_id) {
			const dataToSend = {
				formData: this.formData,
				uid: this.student.uid,
			};
			this.$refs.formMobility
				.call(ApiStvMobility.updateMobility(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal("mobilityModal");
					this.resetForm();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
					this.$refs.purposes.resetLocalData();
					this.$refs.supports.resetLocalData();
				});
		},
		deleteMobility(bisio_id) {
			return this.$api
				.call(ApiStvMobility.deleteMobility(bisio_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		resetForm() {
			this.formData = {};
			this.formData.von = new Date();
			this.formData.bis = new Date();
			this.formData.mobilitaetsprogramm_code = 7;
			this.formData.nation_code = 'A';
			this.formData.herkunftsland_code = 'A';
			this.formData.bisio_id = null;
			this.formData.localPurposes = [];
			this.formData.localSupports = [];
			this.formData.lehrveranstaltung_id = null,
			this.formData.lehreinheit_id = null,
			this.statusNew = true;
			this.listLes = [];
		},
		// ----------------------------------- methods purposes -----------------------------------
		addMobilityPurpose({zweck_code, bisio_id}){
			let params = {
				bisio_id : bisio_id,
				zweck_code: zweck_code
			};
			return this.$api
				.call(ApiStvMobility.addMobilityPurpose(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					this.$refs.purposes.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteMobilityPurpose({zweck_code, bisio_id}){
			let params = {
				bisio_id : bisio_id,
				zweck_code: zweck_code
			};
			return this.$api
				.call(ApiStvMobility.deleteMobilityPurpose(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					this.$refs.purposes.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addPurposeToMobility({zweck_code}){
			this.formData.localPurposes.push(zweck_code);
		},
		// ----------------------------------- methods supports -----------------------------------
		addMobilitySupport({aufenthaltfoerderung_code, bisio_id}){
			let params = {
				bisio_id : bisio_id,
				aufenthaltfoerderung_code: aufenthaltfoerderung_code
			};
			return this.$api
				.call(ApiStvMobility.addMobilitySupport(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));

					this.$refs.supports.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteMobilitySupport({aufenthaltfoerderung_code, bisio_id}){
			let params = {
				bisio_id : bisio_id,
				aufenthaltfoerderung_code: aufenthaltfoerderung_code
			};
			return this.$api
				.call(ApiStvMobility.deleteMobilitySupport(params))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));

					this.$refs.supports.reload();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addSupportToMobility({aufenthaltfoerderung_code}){
			this.formData.localSupports.push(aufenthaltfoerderung_code);
		},
	},
	created() {
		this.$api
			.call(ApiStvMobility.getProgramsMobility())
			.then(result => {
				this.programsMobility = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvMobility.getLVList(this.student.studiengang_kz))
			.then(result => {
				this.listLvs = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvMobility.getListPurposes())
			.then(result => {
				this.listPurposes = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvMobility.getListSupports())
			.then(result => {
				this.listSupports = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvMobility.getLvsandLesByStudent(this.student.uid))
			.then(result => {
				this.listLvsAndLes = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-mobility h-100 pb-3">
		<h4>In / Out</h4>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('stv', 'tab_mobility')"
			@click:new="actionNewMobility"
			>
		</core-filter-cmpt>
		
		<!--Modal: mobilityModal-->
		<bs-modal ref="mobilityModal" dialog-class="modal-xl modal-dialog-scrollable">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('mobility', 'mobility_anlegen')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('mobility', 'mobility_bearbeiten')}}</p>
			</template>

			<form-form v-if="!this.student.length" ref="formMobility" @submit.prevent>

				<div class="row my-3">
					<legend class="col-6">BIS</legend>
					<legend class="col-6">Outgoing</legend>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-mobility-von"
						:label="$p.t('ui', 'von')"
						type="DatePicker"
						v-model="formData.von"
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						name="von"
						:teleport="true"
						>
					</form-input>
					
					<form-input
						container-class="col-6 stv-details-mobility-typ"
						:label="$p.t('lehre', 'lehrveranstaltung')"
						type="select"
						v-model="formData.lehrveranstaltung_id"
						name="lehrveranstaltung_id"
						@change="handleLVchanged"
						>
						<option value=null> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
						<option
							v-for="lv in listLvs"
							:key="lv.lehrveranstaltung_id"
							:value="lv.lehrveranstaltung_id"
							>
							{{lv.bezeichnung}} - Semester {{lv.semester}}
						</option>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-mobility-bis"
						:label="$p.t('global', 'bis')"
						type="DatePicker"
						v-model="formData.bis"
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						name="bis"
						:teleport="true"
						>
					</form-input>

					<form-input
						type="select"
						container-class="col-6 stv-details-mobility-typ"
						:label="$p.t('lehre', 'lehreinheit')"
						v-model="formData.lehreinheit_id"
						name="lehreinheit_id"
						:disabled="listLes.length > 0 ? false : true"
						>
						<option value=null> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
						<option
							v-for="le in listLes"
							:key="le.lehreinheit_id"
							:value="le.lehreinheit_id"
							>
							{{ le.kurzbz }}-{{ le.lehrform_kurzbz }} {{ le.bezeichnung }} {{ le.gruppe }} ({{ le.kuerzel }})
						</option>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-mobility-mobilitaetsprogramm"
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
							{{mob.kurzbz}} - {{mob.beschreibung}}
						</option>
					</form-input>
					<form-input
						container-class="col-6 stv-details-mobility-ort"
						:label="$p.t('person', 'ort')"
						type="text"
						v-model="formData.ort"
						name="ort"
						>

					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-mobility-gastnation"
						:label="$p.t('mobility', 'gastnation')"
						type="select"
						v-model="formData.nation_code"
						name="nation_code"
						>
						<option 
						v-for="nation in lists.nations" 
						:key="nation.nation_code" 
						:value="nation.nation_code" 
						:disabled="nation.sperre"
						>
						{{nation.kurztext}}
						</option>
					</form-input>
					<form-input
						container-class="col-6 stv-details-mobility-universitaet"
						:label="$p.t('mobility', 'universitaet')"
						type="text"
						v-model="formData.universitaet"
						name="universitaet"
						>

					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-mobility-herkunftsland"
						:label="$p.t('mobility', 'herkunftsland')"
						type="select"
						v-model="formData.herkunftsland_code"
						name="herkunftsland_code"
						>
						<option 
						v-for="nation in lists.nations" 
						:key="nation.nation_code" 
						:value="nation.nation_code" 
						:disabled="nation.sperre"
						>
						{{nation.kurztext}}
						</option>
					</form-input>
					<form-input
						container-class="col-3 stv-details-mobility-ects_erworben"
						:label="$p.t('mobility', 'ects_erworben')"
						type="text"
						v-model="formData.ects_erworben"
						name="ects_erworben"
						>
					</form-input>				
					<form-input
						container-class="col-3 stv-details-mobility-ects_angerechnet"
						:label="$p.t('mobility', 'ects_angerechnet')"
						type="text"
						v-model="formData.ects_angerechnet"
						name="ects_angerechnet"
						>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<div class="col-6 stv-details-mobility-zweck">
						<mobility-purpose 
							:bisio_id="formData.bisio_id" 
							:listPurposes="listPurposes"
							@deleteMobilityPurpose="deleteMobilityPurpose"
							@setMobilityPurpose="addMobilityPurpose"
							@setMobilityPurposeToNewMobility="addPurposeToMobility"
							ref="purposes"
							></mobility-purpose>
					</div>
					
					<div class="col-6 stv-details-mobility-aufenthaltfoerderung">
						<mobility-support
							:bisio_id="formData.bisio_id"
							:listSupports="listSupports"
							@deleteMobilitySupport="deleteMobilitySupport"
							@setMobilitySupport="addMobilitySupport"
							@setMobilitySupportToNewMobility="addSupportToMobility"
							ref="supports"
							></mobility-support>
					</div>
				</div>

			</form-form>

			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button v-if="statusNew" class="btn btn-primary" @click="addNewMobility()"> {{$p.t('ui', 'speichern')}}</button>
				<button v-else class="btn btn-primary" @click="updateMobility(formData.bisio_id)"> {{$p.t('ui', 'speichern')}}</button>
			</template>

		</bs-modal>

				
	</div>
`
}


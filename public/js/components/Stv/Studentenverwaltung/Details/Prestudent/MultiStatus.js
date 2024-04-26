import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import {CoreRESTClient} from "../../../../../RESTClient";

export default{
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
		hasPermissionToSkipStatusCheck: {
			from: 'hasPermissionToSkipStatusCheck',
			default: false
		},
		hasPrestudentPermission: {
			from: 'hasPrestudentPermission',
			default: false
		},
		hasAssistenzPermission: {
			from: 'hasAssistenzPermission',
			default: false
		},
		hasAdminPermission: {
			from: 'hasAdminPermission',
			default: false
		},
		hasAssistenzPermissionForStgs: {
			from: 'hasAssistenzPermissionForStgs',
			default: false
		},
		hasSchreibrechtAss: {
			from: 'hasSchreibrechtAss',
			default: false
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	computed: {
/*		personIds(){
			if (this.modelValue.person_id)
				return [this.modelValue.person_id];
			return this.modelValue.map(e => e.person_id);
		},*/
		prestudentIds(){
			if (this.modelValue.prestudent_id)
			{
				return [this.modelValue.prestudent_id];
			}
			return this.modelValue.map(e => e.prestudent_id);
		},
		updateData(){
			const dataArray = [];
			if (this.modelValue.prestudent_id) {
				const newObj = {
					prestudent_id : this.modelValue.prestudent_id,
					studiensemester_kurzbz : this.defaultSemester,
					ausbildungssemester : this.modelValue.semester
				};
				dataArray.push(newObj);
				//console.log(dataArray);
				return dataArray;
			}
			else
			{
				for (const item of this.modelValue) {
					const newObj = {
						prestudent_id: item.prestudent_id,
						ausbildungssemester: item.semester,
						studiensemester_kurzbz: this.defaultSemester
					};
					dataArray.push(newObj);
				}

				return dataArray;
			}

		},
		gruende() {
			return this.listStatusgruende.filter(grund => grund.status_kurzbz == this.statusData.status_kurzbz);
		},
		arrayStg(){
			let stgInteger = this.hasAssistenzPermissionForStgs.map(item => {
				return parseInt(item); // Wandelt jeden String in eine ganze Zahl um
			});
			return stgInteger;
		},
		hasPermissionCurrentStg(){
			return this.arrayStg.includes(this.studiengang_kz);
		}
	},
	props: {
		modelValue: Object,
	},
	data() {

		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Kurzbz", field: "status_kurzbz", tooltip: true},
					{title: "StSem", field: "studiensemester_kurzbz"},
					{title: "Sem", field: "ausbildungssemester"},
					{title: "Lehrverband", field: "lehrverband", width: 72},
					{title: "Datum", field: "format_datum"},
					{title: "Studienplan", field: "bezeichnung"},
					{title: "BestätigtAm", field: "format_bestaetigtam"},
					{title: "AbgeschicktAm", field: "format_bewerbung_abgeschicktamum", visible:false},
					{title: "Statusgrund", field: "statusgrund_kurzbz"},
					{title: "Organisationsform", field: "orgform_kurzbz", visible: false},
					{title: "PrestudentInId", field: "prestudent_id", visible: false},
					{title: "StudienplanId", field: "studienplan_id", visible: false},
					{title: "Anmerkung", field: "anmerkung", visible: false},
					{title: "BestätigtVon", field: "bestaetigtvon", visible: false},
					{title: "InsertAmUm", field: "format_insertamum", visible: false},
					{title: "InsertVon", field: "insertvon", visible: false},
					{title: "UpdateAmUm", field: "format_updateamum", visible: false},
					{title: "UpdateVon", field: "updatevon", visible: false},
					{
						title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {


							//let disableButton = false;
							//const rowData = this.row.getData();


							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-forward"></i>';
							button.title = 'Status vorrücken';
							button.addEventListener(
								'click',
								() =>
								this.actionAdvanceStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-check"></i>';
							button.title = 'Status bestätigen';
							button.addEventListener('click', () =>
								this.actionConfirmStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = 'Status bearbeiten';
							button.addEventListener('click', (event) =>
								this.actionEditStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							button = document.createElement('button');
							if (this.dataMeldestichtag && this.dataMeldestichtag > cell.getData().datum && !this.hasPermissionToSkipStatusCheck)
								button.className = 'btn btn-outline-secondary btn-action disabled';
							else
								button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = 'Status löschen';
							button.addEventListener('click', () =>
								this.actionDeleteStatus(cell.getData().status_kurzbz, cell.getData().studiensemester_kurzbz, cell.getData().ausbildungssemester)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				rowFormatter: (row) => {
					const rowData = row.getData();
					if (this.dataMeldestichtag && this.dataMeldestichtag > rowData.datum)
					{
						row.getElement().classList.add('disabled');

					}
				},
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				selectable: false,
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['lehre','global','person']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('lehrverband').component.updateDefinition({
									title: this.$p.t('lehre', 'lehrverband')
								});

						cm.getColumnByField('format_bestaetigtam').component.updateDefinition({
							title: this.$p.t('lehre', 'bestaetigt_am')
						});

						cm.getColumnByField('format_bewerbung_abgeschicktamum').component.updateDefinition({
							title: this.$p.t('lehre', 'bewerbung_abgeschickt_am')
						});

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'studienplan')
						});

						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});

						cm.getColumnByField('format_datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});

						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});

						cm.getColumnByField('bestaetigtvon').component.updateDefinition({
							title: this.$p.t('lehre', 'bestaetigt_von')
						});

						cm.getColumnByField('format_insertamum').component.updateDefinition({
							title: this.$p.t('lehre', 'insert_am')
						});

						cm.getColumnByField('insertvon').component.updateDefinition({
							title: this.$p.t('lehre', 'insert_von')
						});
					}
				}
			],
//		}

			statusData: {},
			listStudiensemester: [],
			maxSem:  Array.from({ length: 11 }, (_, index) => index),
			listStudienplaene: [],
			aufnahmestufen: {'': '-- keine Auswahl --', 1: 1, 2: 2, 3: 3},
			listStatusgruende: [],
			statusId: {},
			gruendeLength: {},
			dataMeldestichtag: null,
			stichtag: {},
			isLastStatus: {},
			hasPermissionThisStg: {},
			actionButton: {},
			actionStatusText: {},
			actionSem: null,
			newArray: {}
		}
	},
	watch: {
		data: {
			handler(n) {
				const start = this.status_kurzbz;
			},
			deep: true
		},
		modelValue(){
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id);
		}
	},
	methods: {
		actionNewStatus() {
			this.statusData.status_kurzbz = 'Interessent';
			this.statusData.studiensemester_kurzbz = this.defaultSemester;
			this.statusData.ausbildungssemester = 1;
			this.statusData.datum = this.getDefaultDate();
			this.statusData.bestaetigtam = this.getDefaultDate();
			this.$refs.newStatusModal.show();
		},
		actionEditStatus(status, stdsem, ausbildungssemester){
			this.statusId = {
				'prestudent_id': this.modelValue.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.$refs.editStatusModal.show();
			});
		},
		actionDeleteStatus(status, stdsem, ausbildungssemester){
			this.statusId = {
				'prestudent_id': this.modelValue.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};

			this.checkIfLastStatus();

			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.$refs.deleteStatusModal.show();
			});
		},
		actionAdvanceStatus(status, stdsem, ausbildungssemester){
			this.statusId = {
				'prestudent_id': this.modelValue.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.advanceStatus(this.statusId);
			});
		},
		actionConfirmStatus(status, stdsem, ausbildungssemester){
			this.statusId = {
				'prestudent_id': this.modelValue.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.confirmStatus(this.statusId);
			});
		},
		actionConfirmDialogue(data, statusgrund, statusText){
			//this.hideModal('addMultiStatus2');
			this.actionButton = statusgrund;
			this.actionStatusText = statusText;

			console.log("statusgrund: " + this.actionButton + ' , statusText:  ' + this.actionStatusText + ' sem: ' + this.actionSem);
			if(this.actionStatusText != "Student" && this.actionStatusText != "Wiederholer")
				this.$refs.confirmStatusAction.show();
			else
				this.$refs.askForAusbildungssemester.show();
		},
		changeStatusToAbbrecherStgl(prestudentIds){
			this.hideModal('confirmStatusAction2');
			let abbruchData =
				{
					status_kurzbz: 'Abbrecher',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate(),
					statusgrund_id: 17
				};
			console.log(this.updateData);
			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...abbruchData }));
			console.log(this.newArray);

			console.log("in changeStatusToAbbrecher" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		changeStatusToAbbrecherStud(prestudentIds){
			this.hideModal('confirmStatusAction');
			let deltaData =
				{
					status_kurzbz: 'Abbrecher',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate(),
					statusgrund_id: 18
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData }));

			console.log("in changeStatusToAbbrecher" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		changeStatusToUnterbrecher(prestudentIds){
			this.hideModal('confirmStatusAction');
			let deltaData =
				{
					status_kurzbz: 'Unterbrecher',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate()
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData }));

			console.log("in changeStatusToUnterbrecher" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		changeStatusToStudent(prestudentIds){
			this.hideModal('askForAusbildungssemester');
			let deltaData =
				{
					status_kurzbz: 'Student',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate()
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData, ausbildungssemester: this.actionSem}));

			console.log("in changeStatusToStudent" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		changeStatusToWiederholer(prestudentIds){
			this.hideModal('askForAusbildungssemester');
			let deltaData =
				{
					status_kurzbz: 'Student',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate(),
					statusgrund_id: 16
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData, ausbildungssemester: this.actionSem}));

			console.log("in changeStatusToWiederholer" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		changeStatusToDiplomand(prestudentIds){

			let deltaData =
				{
					status_kurzbz: 'Diplomand',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate(),
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData}));

			console.log("in changeStatusToDiplomand" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		changeStatusToAbsolvent(prestudentIds){

			let deltaData =
				{
					status_kurzbz: 'Absolvent',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate(),
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData}));

			console.log("in changeStatusToAbsolvent" + prestudentIds);
			this.addNewStatus(prestudentIds);
		},
		addNewStatus(prestudentIds){
			//Array.isArray(prestudentIds) ? this.modelValue.prestudent_id : [prestudentIds];
			let changeData = {};

			if(!prestudentIds)
				prestudentIds = [this.modelValue.prestudent_id];

			const promises = prestudentIds.map(prestudentId => {
				//TODO(manu) besserer check
				//if(!this.newArray)
				if(this.statusData.status_kurzbz)
				{
					changeData = this.statusData; //this.statusData = this.updateData.find(item => item.prestudent_id === prestudentId);
				}
				else
				{
					changeData = this.newArray.find(item => item.prestudent_id === prestudentId);
				}

				console.log("---");
				console.log(changeData);

				return this.$fhcApi.post('api/frontend/v1/stv/status/addNewStatus/' + prestudentId,
					//this.statusData
					//this.updateData.find(item => item.prestudent_id == prestudentId)
					changeData
				).then(response => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
						console.log(response);
						return response;
						}).catch(this.$fhcAlert.handleSystemError)
					/*.finally(() => {
					window.scrollTo(0, 0);
				})*/;
			});

			Promise
				.allSettled(promises)
				.then(values => {
					if (this.modelValue.prestudent_id) {
						this.reload();
					}
					else {
						this.$reloadList();
					}
					this.hideModal('newStatusModal');
					this.resetModal();
				});
		},
		advanceStatus(statusId){
			return this.$fhcApi.post('api/frontend/v1/stv/status/advanceStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successAdvance'));
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		confirmStatus(statusId){
			return this.$fhcApi.post('api/frontend/v1/stv/status/confirmStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successConfirm'));
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
/*					window.scrollTo(0, 0);*/
					this.reload();
				});
		},
		deleteStatus(status_id){
			return this.$fhcApi.post('api/frontend/v1/stv/status/deleteStatus/',
				status_id)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
							this.hideModal('deleteStatusModal');
							this.resetModal();
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		editStatus(){
			return this.$fhcApi.post('api/frontend/v1/stv/status/updateStatus/' +
				this.statusId.prestudent_id + '/' +
				this.statusId.status_kurzbz + '/' +
				this.statusId.studiensemester_kurzbz + '/' +
				this.statusId.ausbildungssemester,
				this.statusData)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
						this.hideModal('editStatusModal');
						this.resetModal();
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		checkIfLastStatus(){
			return this.$fhcApi
				.get('api/frontend/v1/stv/status/isLastStatus/' + this.modelValue.prestudent_id)
				.then(
					result => {
						if(result.data){
							this.isLastStatus = result.data;
						} else {
							this.isLastStatus = {};
						}
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadStatus(status_id){
			return this.$fhcApi.post('api/frontend/v1/stv/status/loadStatus/',
				status_id)
					.then(result => {
							this.statusData = result.data;
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);
		},
		reload(){
			this.$refs.table.reloadTable(); //bei multiactions not working
		},
		reloadLast(prestudent_id){
			console.log("prestudent_id: " + prestudent_id);
		//	this.$refs.table.tabulator.setData('api/frontend/v1/stv/Status/getHistoryPrestudent/' + prestudent_id);

		//	window.location.href = "https://c3p0.ma0068.technikum-wien.at/fhcomplete/index.ci.php/studentenverwaltung/prestudent/" + prestudent_id + "/multistatus";
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
		resetModal(){
			this.statusData = {};
			this.statusId = {};
			this.actionButton = {};
			this.actionStatusText = {};
			this.actionSem = null;
		},
		getDefaultDate() {
			const today = new Date();
			return today;
		}
	},
	created(){
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getStudiensemester')
			.then(result => result.data)
			.then(result => {
				this.listStudiensemester = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
/*		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getStudienplaene/' + this.modelValue.prestudent_id)
			.then(result => result.data)
			.then(result => {
				this.listStudienplaene = result;
			})
			.catch(this.$fhcAlert.handleSystemError);*/
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusgruende/')
			.then(result => result.data)
			.then(result => {
				this.listStatusgruende = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getLastBismeldestichtag/')
			.then(result => result.data)
			.then(result => {
				this.dataMeldestichtag = result.retval[0].meldestichtag;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted(){},
	template: `
		<div class="stv-list h-100 pt-3">
		
		PersonId(s): {{personIds}}
		||
		PrestudentId(s): {{prestudentIds}}
		
		<hr>
		
<!--		{{statusData}}
		
		<hr>
		
		{{modelValue}}
		
		<hr>
		
		{{updateData}}-->
				
			<!--Modal: Add New Status-->
			<BsModal ref="newStatusModal">
				<template #title>{{$p.t('lehre', 'status_new')}}</template>
							
					<form-form class="row g-3" ref="statusData">
					
						<div class="row mb-3">
							<label for="status_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'status_rolle')}}</label>
							<div class="col-sm-6">
							<!--<form-input type="text" :readonly="readonly" class="form-control" id="status_kurzbz" v-model="statusData['status_kurzbz']">-->
								<form-input
									required
									v-model="statusData['status_kurzbz']"
									name="status_kurzbz"
									type="select"
									>
									<option  value="Interessent">InteressentIn</option>
									<option  value="Bewerber">BewerberIn</option>
									<option  value="Aufgenommener">Aufgenommene/r</option>
									<option  value="Student">StudentIn</option>
									<option  value="Unterbrecher">UnterbrecherIn</option>
									<option  value="Diplomand">DiplomandIn</option>
									<option  value="Incoming">Incoming</option>
									<option  value="Absolvent">AbsolventIn</option>
									<option  value="Abbrecher">AbbrecherIn</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">		   
							<label for="studiensemester_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
							<div class="col-sm-6">
								<form-input
									:readonly="readonly"
									type="select"
									name="studiensemester_kurzbz"
									v-model="statusData['studiensemester_kurzbz']"
								>
									<option v-for="sem in listStudiensemester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz"  :selected="sem.studiensemester_kurzbz === defaultSemester">{{sem.studiensemester_kurzbz}}</option>
								</form-input>
							</div>
						</div>
						<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false) 100 Semester-->
						<div class="row mb-3">
							<label for="ausbildungssemester" class="form-label col-sm-4">{{$p.t('lehre', 'ausbildungssemester')}}</label>
							<div class="col-sm-6">
								<form-input
									type="select"
									:readonly="readonly"
									name="ausbildungssemester"
									v-model="statusData.ausbildungssemester"
								>
								 <option v-for="number in maxSem" :key="number" :value="number">{{ number }}</option>
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="datum" class="form-label col-sm-4">{{$p.t('global', 'datum')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="statusData.datum"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bestaetigtam" class="form-label col-sm-4">{{$p.t('lehre', 'bestaetigt_am')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="statusData.bestaetigtam"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bewerbung_abgeschicktamum" class="form-label col-sm-4">{{$p.t('lehre', 'bewerbung_abgeschickt_am')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="statusData['bewerbung_abgeschicktamum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bezeichnung" class="form-label col-sm-4">{{$p.t('lehre', 'studienplan')}}</label>
							<div class="col-sm-6">
								<form-input
									:readonly="readonly"
									type="select"
									name="studienplan"
									v-model="statusData['studienplan_id']"
								>		
									<option v-for="sp in listStudienplaene" :key="sp.studienplan_id" :value="sp.studienplan_id">{{sp.bezeichnung}}</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="anmerkung" class="form-label col-sm-4">{{$p.t('global', 'anmerkung')}}</label>
							<div class="col-sm-6">
								<form-input
									type="text"
									name="anmerkung"
									v-model="statusData['anmerkung']"
								>						
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">								   
							<label for="aufnahmestufe" class="form-label col-sm-4">{{$p.t('lehre', 'aufnahmestufe')}}</label>
							<div class="col-sm-6">
								<form-input
								type="select"
								:readonly="readonly"
								name="aufnahmestufe"
								v-model="statusData['rt_stufe']"
								>
								<option v-for="entry in aufnahmestufen" :key="entry" :value="entry">{{entry}}</option>
								</form-input>
							</div>
						</div>
										
						<div v-if="gruende.length > 0" class="row mb-3">
							<label for="grund" class="form-label col-sm-4">{{$p.t('studierendenantrag', 'antrag_grund')}}</label>
							<div class="col-sm-6">
								<form-input
									type="select"
									:readonly="readonly"
									name="statusgrund"
									v-model="statusData['statusgrund_id']"
								>
								<option v-for="grund in gruende" :key="grund.statusgrund_id" :value="grund.statusgrund_id">{{grund.beschreibung[0]}}</option>
								</form-input>
							</div>
						</div>
					
					</form-form>
					
				<template #footer>
						<button type="button" class="btn btn-primary" @click="addNewStatus()">OK</button>
				</template>
								
			</BsModal>
			
			<!--Modal: Edit Status-->
			<BsModal ref="editStatusModal">
				<template #title>{{$p.t('lehre', 'status_edit')}}</template>
					<form-form class="row g-3" ref="statusData">
					
					<div v-if="statusData.datum < dataMeldestichtag">
						<b>{{$p.t('bismeldestichtag', 'meldestichtag_erreicht')}}</b>
					</div>
					
					 <input type="hidden" id="statusId" name="statusId" value="statusData.statusId">
					
						<div class="row mb-3">
							<label for="status_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'status_rolle')}}</label>
							<div class="col-sm-6">
<!--								<form-input type="text" :readonly="readonly" class="form-control" id="status_kurzbz" v-model="statusData['status_kurzbz']">-->
								<form-input
									required
									v-model="statusData['status_kurzbz']"
									name="status_kurzbz"
									type="select"
									disabled
									>
									<option  value="Interessent">InteressentIn</option>
									<option  value="Bewerber">BewerberIn</option>
									<option  value="Aufgenommener">Aufgenommene/r</option>
									<option  value="Student">StudentIn</option>
									<option  value="Unterbrecher">UnterbrecherIn</option>
									<option  value="Diplomand">DiplomandIn</option>
									<option  value="Incoming">Incoming</option>
									<option  value="Absolvent">AbsolventIn</option>
									<option  value="Abbrecher">AbbrecherIn</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">							   
							<label for="studiensemester_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
							<div class="col-sm-6">
								<form-input
									:readonly="readonly"
									type="select"
									name="studiensemester_kurzbz"
									v-model="statusData['studiensemester_kurzbz']"
								>
									<option v-for="sem in listStudiensemester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">{{sem.studiensemester_kurzbz}}</option>
								</form-input>
							</div>
						</div>
						<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false)-->
						<div class="row mb-3">
							<label for="ausbildungssemester" class="form-label col-sm-4">{{$p.t('lehre', 'ausbildungssemester')}}</label>
							<div class="col-sm-6">
								<form-input
									type="select"
									:readonly="readonly"
									name="ausbildungssemester"
									v-model="statusData['ausbildungssemester']"
								>
								 <option v-for="number in maxSem" :key="number" :value="number">{{ number }}</option>
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="datum" class="form-label col-sm-4">{{$p.t('global', 'datum')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="statusData['datum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">					   
							<label for="bestaetigtam" class="form-label col-sm-4">{{$p.t('lehre', 'bestaetigt_am')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="statusData['bestaetigtam']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bewerbung_abgeschicktamum" class="form-label col-sm-4">{{$p.t('lehre', 'bewerbung_abgeschickt_am')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									:readonly="readonly"
									name="datum"
									v-model="statusData['bewerbung_abgeschicktamum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bezeichnung" class="form-label col-sm-4">{{$p.t('lehre', 'studienplan')}}</label>
							<div class="col-sm-6">
								<form-input
									:readonly="readonly"
									type="select"
									name="studienplan"
									v-model="statusData['studienplan_id']"
								>
									<option v-for="sp in listStudienplaene" :key="sp.studienplan_id" :value="sp.studienplan_id">{{sp.bezeichnung}}</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="anmerkung" class="form-label col-sm-4">{{$p.t('global', 'anmerkung')}}</label>
							<div class="col-sm-6">
								<form-input
									type="text"
									name="anmerkung"
									v-model="statusData['anmerkung']"
								>				
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">				   
							<label for="aufnahmestufe" class="form-label col-sm-4">{{$p.t('lehre', 'aufnahmestufe')}}</label>
							<div class="col-sm-6">
								<form-input
								type="select"
								:readonly="readonly"
								name="aufnahmestufe"
								v-model="statusData['rt_stufe']"
								>
								<option v-for="entry in aufnahmestufen" :key="entry" :value="entry">{{entry}}</option>
								</form-input>
							</div>
						</div>
						
						<div v-if="gruende.length > 0" class="row mb-3">
							<label for="grund" class="form-label col-sm-4">{{$p.t('studierendenantrag', 'antrag_grund')}}</label>
							<div class="col-sm-6">
								<form-input
									type="select"
									:readonly="readonly"
									name="statusgrund"
									v-model="statusData['statusgrund_id']"
								>
								<option v-for="grund in gruende" :key="grund.statusgrund_id" :value="grund.statusgrund_id">{{grund.beschreibung[0]}}</option>
								</form-input>
							</div>
						</div>
					
					</form-form>
					
				<template #footer>
						<button type="button" class="btn btn-primary" @click="editStatus()">OK</button>
				</template>
								
			</BsModal>
		
			<!--Modal: Delete Status-->
			<BsModal ref="deleteStatusModal">
				<template #title>{{$p.t('lehre', 'status_edit')}}</template>
				<template #default>
				<div v-if="isLastStatus == 1">
					<p>{{$p.t('lehre', 'last_status_confirm_delete')}}</p>
				</div>
				<div v-else>
					<p>{{$p.t('lehre', 'status_confirm_delete')}}</p>
				</div>
					
				</template>
				<template #footer>
					<button ref="Close" type="button" class="btn btn-primary" @click="deleteStatus(statusId)">OK</button>
				</template>
			</BsModal>
			
			<!--Modal: Confirm Abbruch-->
			<BsModal ref="confirmStatusAction">
				<template #title>{{$p.t('lehre', 'status_edit')}}</template>
				<template #default>
					<div v-if="prestudentIds.length == 1">
						<p>Diese Person wirklich zum {{actionStatusText}} machen?</p>
					</div>
					<div v-else>
						<p>Diese {{prestudentIds.length}} Personen wirklich zum {{actionStatusText}} machen?</p>
					</div>
					
				</template>
				<template #footer>
					<div v-if="actionButton=='abbrecherStgl'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToAbbrecherStgl(prestudentIds)">OK</button>
					</div>
					<div v-if="actionButton=='abbrecherStud'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToAbbrecherStud(prestudentIds)">OK</button>
					</div>
					<div v-if="actionButton=='unterbrecher'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToUnterbrecher(prestudentIds)">OK</button>
					</div>					
				</template>
			</BsModal>
			
			<!--Modal: askForAusbildungssemester-->
			<BsModal ref="askForAusbildungssemester">
				<template #title>{{$p.t('lehre', 'status_edit')}}</template>
				<template #default>
				<div v-if="prestudentIds.length == 1">
					<p>In welches Semester soll dieser {{actionStatusText}} verschoben werden?</p>
				</div>
				<div v-else>
					<p>In welches Semester sollen diese {{prestudentIds.length}} {{actionStatusText}}en verschoben werden?</p>
				</div>
				
				<div class="row mb-3">
					<label for="studiensemester" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
					<div class="col-sm-6">
						<form-input
							type="text"
							name="studiensemester"
							v-model="actionSem"
						>				
						</form-input>
					</div>
				</div>
					
				</template>
				<template #footer>

				<div v-if="actionStatusText=='Student'">
					<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToStudent(prestudentIds)">OK</button>
				</div>
				<div v-if="actionStatusText=='Wiederholer'">
					<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToWiederholer(prestudentIds)">OK</button>
				</div>
					
				</template>
			</BsModal>
			
			<!--Modal: addMultiStatus--> <!--TODO(MANU) use bs template-->
<!--			<BsModal ref="addMultiStatus">				
			<template #title>{{$p.t('lehre', 'status_edit')}}</template>
				<template #default>
				<div v-if="prestudentIds.length == 1">
					<p>In welches Semester soll dieser {{actionStatusText}} verschoben werden?</p>
				</div>
				<div v-else>
					<p>In welches Semester sollen diese {{prestudentIds.length}} {{actionStatusText}}en verschoben werden?</p>
				</div>
				
				<div class="row mb-3">
					<label for="studiensemester" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
					<div class="col-sm-6">
						<form-input
							type="text"
							name="studiensemester"
							v-model="actionSem"
						>				
						</form-input>
					</div>
				</div>
					
				</template>
				<template #footer>

				<div v-if="actionStatusText=='Student'">
					<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToStudent(prestudentIds)">OK</button>
				</div>
				<div v-if="actionStatusText=='Wiederholer'">
					<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToWiederholer(prestudentIds)">OK</button>
				</div>
					
				</template>
			</BsModal>-->
				
			<core-filter-cmpt
				v-if="!this.modelValue.length"
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				new-btn-show
				new-btn-label="Status"
				@click:new="actionNewStatus"
				>
				
				<template #actions="{updateData2}">

<!-- Version1 -->
<!--    <div class="btn-group">
        &lt;!&ndash; Hauptbutton &ndash;&gt;
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            Status Ändern
        </button>
        &lt;!&ndash; Dropdown-Menü &ndash;&gt;
        <ul class="dropdown-menu">
            &lt;!&ndash; Schleife für fünf Unterbuttons &ndash;&gt;
            &lt;!&ndash; Jeder Unterbutton hat zwei weitere Unterbuttons &ndash;&gt;
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Abbrecher</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Unterbutton 1-1</a></li>
                    <li><a class="dropdown-item" href="#">Unterbutton 1-2</a></li>
                </ul>
            </li>
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Unterbrecher</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Unterbutton 2-1</a></li>
                    <li><a class="dropdown-item" href="#">Unterbutton 2-2</a></li>
                </ul>
            </li>
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Student</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Unterbutton 3-1</a></li>
                    <li><a class="dropdown-item" href="#">Unterbutton 3-2</a></li>
                </ul>
            </li>
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Diplomand</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Unterbutton 4-1</a></li>
                    <li><a class="dropdown-item" href="#">Unterbutton 4-2</a></li>
                </ul>
            </li>
            <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Absolvent</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Unterbutton 5-1</a></li>
                    <li><a class="dropdown-item" href="#">Unterbutton 5-2</a></li>
                </ul>
            </li>
        </ul>
    </div>-->

<!-- Version2 -->
<!--    <div class="btn-group">
        &lt;!&ndash; Hauptbutton &ndash;&gt;
        <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
            Version 2
        </button>
    </div>-->

    <!-- Offcanvas-Menü -->
<!--    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuLabel">Untermenü</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            &lt;!&ndash; Untermenü-Buttons &ndash;&gt;
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 1-1</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 1-2</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 2-1</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 2-2</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 3-1</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 3-2</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 4-1</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 4-2</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 5-1</button>
            <button type="button" class="btn btn-secondary d-block mb-2">Unterbutton 5-2</button>
        </div>
    </div>-->


<!-- Hauptbutton zum Öffnen des Modals -->
<!--TODO(MANU) use bs template addMultiStatus-->
<!--<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mainModal">
    Status Ändern(3)
</button>-->

<!-- Modal für Hauptbuttons und Untermenü-Buttons -->
<!--<div class="modal fade" id="mainModal" tabindex="-1" aria-labelledby="mainModalLabel" aria-hidden="true" ref="addMultiStatus2">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mainModalLabel">Status ändern zu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                &lt;!&ndash; Liste der Hauptbuttons &ndash;&gt;
                <button type="button" class="btn btn-primary d-block mb-2" data-bs-toggle="collapse" data-bs-target="#submenu1">
                    Abbrecher
                </button>
                <div class="collapse" id="submenu1">
                    <button type="button" class="btn btn-light d-block mb-2" @click="actionConfirmDialogue(updateData, 'abbrecherStgl', 'Abbrecher')">durch Stgl</button>
                    <button type="button" class="btn btn-light d-block mb-2" @click="actionConfirmDialogue(updateData, 'abbrecherStud','Abbrecher')">durch Student</button>
                </div>

                <button type="button" class="btn btn-primary d-block mb-2" @click="actionConfirmDialogue(updateData, 'unterbrecher','Unterbrecher')">
                    Unterbrecher
                </button>
                
                <button type="button" class="btn btn-primary d-block mb-2" data-bs-toggle="collapse" data-bs-target="#submenu2">
                    Student
                </button>
                <div class="collapse" id="submenu2">
                    <button type="button" class="btn btn-light d-block mb-2" @click="actionConfirmDialogue(updateData, 'student','Student')">Student</button>
                    <button type="button" class="btn btn-light d-block mb-2" @click="actionConfirmDialogue(updateData, 'student','Wiederholer')">Wiederholer</button>
                </div>
               
                <button type="button" class="btn btn-primary d-block mb-2" @click="changeStatusToDiplomand(prestudentIds)">
                    Diplomand
                </button>

                
                <button type="button" class="btn btn-primary d-block mb-2" @click="changeStatusToAbsolvent(prestudentIds)">
                    Absolvent
                </button>

            </div>
        </div>
    </div>
</div>-->





					<button
						class="btn btn-outline-primary"
						@click="actionChangeStatus(selected)"
						> Status Ändern
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'abbrecherStgl', 'Abbrecher')"
						>
						Abbrecher Stgl
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'abbrecherStud','Abbrecher')"
						>
						Abbrecher Stud
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'unterbrecher','Unterbrecher')"
	
						>
						Unterbrecher
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'student','Student')"
		
						>
						Student
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'student','Wiederholer')"
		
						>
						Wiederholer
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="changeStatusToDiplomand(prestudentIds)"
		
						>
						Diplomand
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="changeStatusToAbsolvent(prestudentIds)"
		
						>
						Absolvent
					</button>
				</template>
			</core-filter-cmpt>
			
			<div 
			v-if="this.modelValue.length"
			ref="buttonsStatusMulti"
			>	
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'abbrecherStgl', 'Abbrecher')"
						>
						Abbrecher Stgl
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'abbrecherStud','Abbrecher')"
						>
						Abbrecher Stud
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'unterbrecher','Unterbrecher')"
	
						>
						Unterbrecher
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData, 'student','Student')"
		
						>
						Student
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="changeStatusToDiplomand(prestudentIds)"
		
						>
						Diplomand
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="changeStatusToAbsolvent(prestudentIds)"
		
						>
						Absolvent
					</button>	
<!--				<template #actions="{updateData}">
					<button
						class="btn btn-outline-primary"
						@click="actionChangeStatus(selected)"
						> Status Ändern
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(updateData)"
		
						>
						Abbrecher Stgl
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(selected)"
		
						>
						Abbrecher Stud
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(selected)"
		
						>
						Unterbrecher
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(selected)"
		
						>
						Student
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(selected)"
		
						>
						Wiederholer
					</button>
					<button
						class="btn btn-outline-secondary"
						@click="actionConfirmDialogue(selected)"
		
						>
						Diplomand
					</button>
				</template>-->
			</div>
		
		</div>`

};
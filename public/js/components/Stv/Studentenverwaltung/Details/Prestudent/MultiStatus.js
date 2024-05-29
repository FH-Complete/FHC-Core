import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import {CoreRESTClient} from "../../../../../RESTClient.js";

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
		hasPermissionRtAufsicht: {
			from: 'hasPermissionRtAufsicht',
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
					ausbildungssemester : this.modelValue.semester,
					name: `${this.modelValue.vorname} ${this.modelValue.nachname}`
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
						studiensemester_kurzbz: this.defaultSemester,
						name: `${item.vorname} ${item.nachname}`
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
		},
		isStatusBeforeStudent(){
			let isStatusStudent = ['Student', 'Absolvent', 'Diplomand'];
			return !isStatusStudent.includes(this.statusData.status_kurzbz);
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
								button.className = 'btn btn-outline-secondary btn-action';
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
								button.className = 'btn btn-outline-secondary btn-action';
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
								button.className = 'btn btn-outline-secondary btn-action';
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
								button.className = 'btn btn-outline-secondary btn-action';
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
			newArray: {},
			abbruchData: {},
			newStatus: ''
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
			this.statusData.name = this.modelValue.vorname + ' ' + this.modelValue.nachname;
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
			this.hideModal('addMultiStatus');
			this.actionButton = statusgrund;
			this.actionStatusText = statusText;

			console.log("statusgrund: " + this.actionButton + ' , statusText:  ' + this.actionStatusText + ' sem: ' + this.actionSem);
			if(this.actionStatusText != "Student" && this.actionStatusText != "Wiederholer")
				this.$refs.confirmStatusAction.show();
			else
				this.$refs.askForAusbildungssemester.show();
		},
		changeStatusToAbbrecherStgl(prestudentIds){
			this.hideModal('confirmStatusAction');
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
			console.log("count: " + prestudentIds.length);

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
			this.hideModal('addMultiStatus');
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
			this.hideModal('addMultiStatus');
			this.addNewStatus(prestudentIds);
		},
		addNewStatus(prestudentIds){
			//Array.isArray(prestudentIds) ? this.modelValue.prestudent_id : [prestudentIds];
			let changeData = {};

			//for Feedback Sucess, Error
			let countSuccess = 0;
			let countError = 0;

			if(!prestudentIds)
				prestudentIds = [this.modelValue.prestudent_id];

			const promises = prestudentIds.map(prestudentId => {
				//TODO(manu) besserer check
				changeData = this.statusData.status_kurzbz ? this.statusData : this.newArray.find(item => item.prestudent_id === prestudentId);

				return this.$fhcApi.post('api/frontend/v1/stv/status/addNewStatus/' + prestudentId,
					changeData
				).then(response => {
						countSuccess++;
						return response;
						})
					//.catch(this.$fhcAlert.handleSystemError)
					.catch(error => {
						countError++;
						//For each Prestudent show Error in Alert
						this.$fhcAlert.handleSystemError(error);
					});
			});

			Promise
				.allSettled(promises)
				.then(values => {
					if (this.abbruchData.length < 1) {
						console.log("singleNew");
					}
					else{
						if(this.newArray.length > 0) {
							this.newStatus = this.newArray[0].status_kurzbz;
							console.log(`Successful: ${countSuccess}, Errors: ${countError}`);
						}
						else {
							this.newStatus = this.statusData.status_kurzbz;
						}
					}



					//Feedback Success als infoalert
					 if (countSuccess > 0) {
						 this.$fhcAlert.alertInfo(this.$p.t('ui', 'successNewStatus', {
							 'countSuccess': countSuccess,
							 'status': this.newStatus,
							 'countError': countError
						 }));
					 }

					if (this.modelValue.prestudent_id) {
						this.reload();
						//TODO(manu) reload Detailtab after Abbrecher to see current status activ, verband and gruppe
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
		
					
			<!--Modal: Add New Status-->
			<BsModal ref="newStatusModal">
				<template #title>{{$p.t('lehre', 'status_new')}}</template>
							
					<form-form class="row g-3" ref="statusData">
						<div class="row mb-3">	
							<p class="py-2 fw-bold">Details {{modelValue.nachname}} {{modelValue.vorname}}</p>
							<label for="status_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'status_rolle')}}</label>
							<div class="col-sm-6">

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
<!--									TODO(Manu) handle Unterbrecher from here
									<option  value="Unterbrecher">UnterbrecherIn</option>-->
									<option  value="Diplomand">DiplomandIn</option>
									<option  value="Incoming">Incoming</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">		   
							<label for="studiensemester_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
							<div class="col-sm-6">
								<form-input
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
						<div class="row mb-3">
							<p class="py-2 fw-bold">Details {{modelValue.nachname}} {{modelValue.vorname}}</p>
							
							<div v-if="statusData.datum < dataMeldestichtag && !isStatusBeforeStudent" class="mb-1">
								<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrund')}}</b>
							</div>
							<div v-if="statusData.datum < dataMeldestichtag && isStatusBeforeStudent">
								<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrundSemester')}}</b>
							</div>
						
							<input type="hidden" id="statusId" name="statusId" value="statusData.statusId">
					
							<label for="status_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'status_rolle')}}</label>
							<div class="col-sm-6">
<!--								<form-input type="text" class="form-control" id="status_kurzbz" v-model="statusData['status_kurzbz']">-->
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
<!--									TODO(Manu) check: is handle Unterbrecher from here necessary?
									<option  value="Unterbrecher">UnterbrecherIn</option>-->
									<option  value="Diplomand">DiplomandIn</option>
									<option  value="Incoming">Incoming</option>
								</form-input>
							</div>
						</div>
						<div class="row mb-3">							   
							<label for="studiensemester_kurzbz" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
							<div class="col-sm-6">
								<form-input
									type="select"
									name="studiensemester_kurzbz"
									v-model="statusData['studiensemester_kurzbz']"
									:disabled="statusData.datum < dataMeldestichtag"
								>
									<option value="null"></option>
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
									name="ausbildungssemester"
									v-model="statusData['ausbildungssemester']"
									:disabled="statusData.datum < dataMeldestichtag && !isStatusBeforeStudent"
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
									name="datum"
									v-model="statusData['datum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									:disabled="statusData.datum < dataMeldestichtag"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">					   
							<label for="bestaetigtam" class="form-label col-sm-4">{{$p.t('lehre', 'bestaetigt_am')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									name="datum"
									v-model="statusData['bestaetigtam']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									:disabled="statusData.datum < dataMeldestichtag"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bewerbung_abgeschicktamum" class="form-label col-sm-4">{{$p.t('lehre', 'bewerbung_abgeschickt_am')}}</label>
							<div class="col-sm-6">
								<form-input
									type="DatePicker"
									name="datum"
									v-model="statusData['bewerbung_abgeschicktamum']"
									auto-apply
									:enable-time-picker="false"
									format="dd.MM.yyyy"
									preview-format="dd.MM.yyyy"
									:teleport="true"
									:disabled="statusData.datum < dataMeldestichtag"
								></form-input>
							</div>
						</div>
						<div class="row mb-3">
							<label for="bezeichnung" class="form-label col-sm-4">{{$p.t('lehre', 'studienplan')}}</label>
							<div class="col-sm-6">
								<form-input
									type="select"
									name="studienplan"
									v-model="statusData['studienplan_id']"
									:disabled="statusData.datum < dataMeldestichtag"
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
									:disabled="statusData.datum < dataMeldestichtag"
								>				
								</form-input>
							</div>
						</div>
						
						<div class="row mb-3">				   
							<label for="aufnahmestufe" class="form-label col-sm-4">{{$p.t('lehre', 'aufnahmestufe')}}</label>
							<div class="col-sm-6">
								<form-input
								type="select"
								name="aufnahmestufe"
								v-model="statusData['rt_stufe']"
								:disabled="statusData.datum < dataMeldestichtag"
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
									name="statusgrund"
									v-model="statusData['statusgrund_id']"
								>
								<option :value="NULL"></option>
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
			
			<BsModal ref="addMultiStatus" id="addMultiStatus">
				<template #title>Status ändern zu</template>
				<template #default>
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
			
			<ul class="dropdown-menu" ref="addMultiStatus3" id="addMultiStatus3">
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'abbrecherStgl', 'Abbrecher')">Abbrecher durch Stgl</a>
				</li>
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'abbrecherStud','Abbrecher')">Abbrecher durch Student</a>
				</li>
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'unterbrecher','Unterbrecher')">Unterbrecher</a>
				</li>
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'student','Student')">Student</a>
				</li>
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'student','Wiederholer')">Wiederholer</a>
				</li>
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="changeStatusToDiplomand(prestudentIds)">Diplomand</a>
				</li>
				<li class="dropdown-submenu">
					<a class="dropdown-item" @click="changeStatusToAbsolvent(prestudentIds)">Absolvent</a>
				</li>							
			</ul>
			
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
<!--					<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMultiStatus">
						Status Ändern
					</button>-->
					
					<div class="btn-group">						
						<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
							Status Ändern
						</button>
					
						<ul class="dropdown-menu">
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'abbrecherStgl', 'Abbrecher')">Abbrecher durch Stgl</a>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'abbrecherStud','Abbrecher')">Abbrecher durch Student</a>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'unterbrecher','Unterbrecher')">Unterbrecher</a>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'student','Student')">Student</a>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'student','Wiederholer')">Wiederholer</a>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="changeStatusToDiplomand(prestudentIds)">Diplomand</a>
							</li>
							<li class="dropdown-submenu">
								<a class="dropdown-item" @click="changeStatusToAbsolvent(prestudentIds)">Absolvent</a>
							</li>							
						</ul>
					</div>
					
			</core-filter-cmpt>
			
			<div 
				v-if="this.modelValue.length"
				ref="buttonsStatusMulti"
			>	
<!--			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMultiStatus">
				Status Ändern
			</button>-->
			
			<div class="btn-group">						
				<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
					Status Ändern
				</button>
			
				<ul class="dropdown-menu">
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'abbrecherStgl', 'Abbrecher')">Abbrecher durch Stgl</a>
					</li>
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'abbrecherStud','Abbrecher')">Abbrecher durch Student</a>
					</li>
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'unterbrecher','Unterbrecher')">Unterbrecher</a>
					</li>
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'student','Student')">Student</a>
					</li>
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="actionConfirmDialogue(updateData, 'student','Wiederholer')">Wiederholer</a>
					</li>
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="changeStatusToDiplomand(prestudentIds)">Diplomand</a>
					</li>
					<li class="dropdown-submenu">
						<a class="dropdown-item" @click="changeStatusToAbsolvent(prestudentIds)">Absolvent</a>
					</li>							
				</ul>
			</div>
			
			</div>
		
		</div>`

};
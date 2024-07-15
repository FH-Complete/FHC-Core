import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

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
		lists: {
			from: 'lists'
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	computed: {
		prestudentIds(){
			if (this.modelValue.prestudent_id)
			{
				return [this.modelValue.prestudent_id];
			}
			return this.modelValue.map(e => e.prestudent_id);
		},
		paramIds(){
			return  Array.isArray(this.prestudentIds) ? this.prestudentIds.join(',') : this.prestudentIds;
		},
		updateData(){
			const dataArray = [];
			if (this.modelValue.prestudent_id) {
				const newObj = {
					prestudent_id : this.modelValue.prestudent_id,
					studiensemester_kurzbz : this.defaultSemester,
					ausbildungssemester : this.modelValue.semester,
					orgform_kurzbz: this.modelValue.orgform_kurzbz,
					name: `${this.modelValue.vorname} ${this.modelValue.nachname}`
				};
				dataArray.push(newObj);
				return dataArray;
			}
			else
			{
				for (const item of this.modelValue) {
					const newObj = {
						prestudent_id: item.prestudent_id,
						ausbildungssemester: item.semester,
						studiensemester_kurzbz: this.defaultSemester,
						orgform_kurzbz: item.modelValue.orgform_kurzbz,
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
				return parseInt(item);
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
					{title: "Statusgrund", field: "statusgrund_beschreibung"},
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
			statusData: {},
			maxSem:  Array.from({ length: 11 }, (_, index) => index),
			listStudienplaene: [],
			aufnahmestufen: {1: 1, 2: 2, 3: 3},
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
			newStatus: '',
			statusNew: true,
			isErsterStudent: false,
			isBewerber: true
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
			this.statusNew = true;
			this.resetModal();
			this.statusData.status_kurzbz = 'Interessent';
			this.statusData.studiensemester_kurzbz = this.defaultSemester;
			this.statusData.ausbildungssemester = 1;
			this.statusData.datum = this.getDefaultDate();
			this.statusData.bestaetigtam = this.getDefaultDate();
			this.statusData.name = this.modelValue.vorname + ' ' + this.modelValue.nachname;
			this.$refs.statusModal.show();
		},
		actionEditStatus(status, stdsem, ausbildungssemester){
			this.statusNew = false;
			this.statusId = {
				'prestudent_id': this.modelValue.prestudent_id,
				'status_kurzbz': status,
				'studiensemester_kurzbz': stdsem,
				'ausbildungssemester': ausbildungssemester
			};
			this.loadStatus(this.statusId).then(() => {
				if(this.statusData)
					this.$refs.statusModal.show();
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
				{
					this.$fhcAlert
						.confirmDelete()
						.then(result => result
							? this.statusId
							: Promise.reject({handled: true}))
						.then(this.deleteStatus)
						.catch(this.$fhcAlert.handleSystemError);
				}
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
			this.actionButton = statusgrund;
			this.actionStatusText = statusText;

			if(this.actionStatusText != "Student" && this.actionStatusText != "Wiederholer")
				this.$refs.confirmStatusAction.show();
			else {
				//haut einzeln hin
/*				console.log("check if Bewerberstatus");
				this.checkIfBewerber(this.prestudentIds);
				console.log(this.isBewerber);*/

				this.$refs.askForAusbildungssemester.show();
			}
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
			this.addNewStatus(prestudentIds);
		},
		changeStatusToStudent(prestudentIds){
			//TODO Manu validation if Bewerber already before asking for ausbildungssemester
			//this.checkIfBewerber();
			this.hideModal('askForAusbildungssemester');
			let deltaData =
				{
					status_kurzbz: 'Student',
					datum: this.getDefaultDate(),
					bestaetigtam: this.getDefaultDate()
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData, ausbildungssemester: this.actionSem}));
			//BewerberZuStudent
			this.addStudent(prestudentIds);
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
			this.addNewStatus(prestudentIds);
		},
		turnIntoStudent(prestudentIds){

			//erste Voraussetzung: kein check auf checkIfExistingStudent
			// macht aus einem Bewerber einen Studenten
			// Voraussetzungen:
			// - ZGV muss ausgefuellt sein (bei Master beide)
			// - Kaution muss bezahlt sein
			// - Rolle Bewerber muss existieren
			// Wenn die Voraussetzungen erfuellt sind, dann wird die Matrikelnr
			// und UID generiert und der Studentendatensatz angelegt.
			let changeData = {};

			//for Feedback Sucess, Error
			let countSuccess = 0;
			let countError = 0;

			const promises = prestudentIds.map(prestudentId => {

				this.checkIfErsterStudent(prestudentId).then(() => {

					if(this.isErsterStudent)
					{
						console.log(prestudentId + ": isersterStudent: " + this.isErsterStudent + " Logik turnIntoStudent");

						changeData = this.newArray.find(item => item.prestudent_id === prestudentId);

						return this.$fhcApi.post('api/frontend/v1/stv/status/turnIntoStudent/' + prestudentId,
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
					}
					else
					{
						console.log(prestudentId + ": isersterStudent: " + this.isErsterStudent + " Add New Status");
						changeData = this.newArray.find(item => item.prestudent_id === prestudentId);

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
					}
				});
			});

			Promise
				.allSettled(promises)
				.then(values => {

					//Feedback Success als infoalert
					if (countSuccess > 0) {
						this.$fhcAlert.alertInfo(this.$p.t('ui', 'successNewStatus', {
							'countSuccess': countSuccess,
							'status': this.newStatus,
							'countError': countError
						}));
					}

/*					if (this.modelValue.prestudent_id) {
						this.reload();
						//TODO(manu) reload Detailtab after Abbrecher to see current status activ, verband and gruppe
					}*/
					if(this.isErsterStudent) {
						this.reload();
						this.isErsterStudent = false;
					}
					else {
						this.$reloadList();
					}
					this.hideModal('statusModal');
					this.resetModal();
				});


		},
		addStudent(prestudentIds){
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

				return this.$fhcApi.post('api/frontend/v1/stv/status/addStudent/' + prestudentId,
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

					this.newStatus = 'Student';

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
					}
					else {
						this.$reloadList();
					}
					this.hideModal('statusModal');
					this.resetModal();
				});
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
					}
					else{
						if(this.newArray.length > 0) {
							this.newStatus = this.newArray[0].status_kurzbz;
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
					}
					else {
						this.$reloadList();
					}
					this.hideModal('statusModal');
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
					this.reload();
				});
		},
		deleteStatus(status_id){
			return this.$fhcApi.post('api/frontend/v1/stv/status/deleteStatus/',
				status_id)
				.then(
					result => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
							this.resetModal();
					})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
					this.reload();
					this.$reloadList();
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
						this.hideModal('statusModal');
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
		checkIfErsterStudent(prestudent_id){
			return this.$fhcApi
				.get('api/frontend/v1/stv/status/isErsterStudent/' + prestudent_id)
				.then(
					result => {
						this.isErsterStudent = result.data.retval == 0 ? 1 : 0;
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);
		},
/*		checkIfBewerber(prestudentIds){

			if(!prestudentIds)
				prestudentIds = [this.modelValue.prestudent_id];

			const promises = prestudentIds.map(prestudentId => {

				return this.$fhcApi.post('api/frontend/v1/stv/status/hasStatusBewerber/' + prestudentId,
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

/!*			return this.$fhcApi
				.get('api/frontend/v1/stv/status/hasStatusBewerber/' + prestudent_id)
				.then(
					result => {
						this.isBewerber = result.data;
						console.log(result);
						return result;
					})
				.catch(this.$fhcAlert.handleSystemError);*!/
			//}
		},*/
		loadStatus(status_id){
			this.statusNew = false;
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
			this.statusNew = true;
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
			.get('api/frontend/v1/stv/prestudent/getStudienplaene/' + encodeURIComponent(this.paramIds))
			.then(result => result.data)
			.then(result => {
				this.listStudienplaene = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusgruende/')
			.then(result => result.data)
			.then(result => {
				this.listStatusgruende = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getLastBismeldestichtag/')
			.then(result => {
				this.dataMeldestichtag = result.data[0].meldestichtag;
				//TODO(Manu) wirft plötzlich fehler bei multiselect status
/*				if (this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.redraw(true);*/
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<div class="stv-list h-100 pt-3">
			
			<!--Modal: statusModal-->
			<BsModal ref="statusModal">
				<template #title>
					<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('lehre', 'status_new')}}</p>
					<p v-else class="fw-bold mt-3">{{$p.t('lehre', 'status_edit')}}</p>
				</template>

				<form-form class="row g-3" ref="statusData">
					<p class="pt-2 fw-bold">Details {{modelValue.nachname}} {{modelValue.vorname}} </p>
					<p v-if="statusData.datum < dataMeldestichtag && !isStatusBeforeStudent">
						<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrund')}}</b>
					</p>
					<p v-if="statusData.datum < dataMeldestichtag && isStatusBeforeStudent">
						<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrundSemester')}}</b>
					</p>
					<input type="hidden" id="statusId" name="statusId" value="statusData.statusId">
					<div class="row mb-3">	
						<form-input
							required
							v-model="statusData['status_kurzbz']"
							name="status_kurzbz"
							:label="$p.t('lehre/status_rolle')"
							type="select"
							:disabled="!statusNew"
							>
							<option  value="Interessent">InteressentIn</option>
							<option  value="Bewerber">BewerberIn</option>
							<option  value="Aufgenommener">Aufgenommene/r</option>
							<option  value="Student">StudentIn</option>
							<option  value="Unterbrecher">UnterbrecherIn</option>
							<option  value="Diplomand">DiplomandIn</option>
							<option  value="Incoming">Incoming</option>
							<option v-if="!statusNew" value="Absolvent">Absolvent</option>
							<option v-if="!statusNew" value="Abbrecher">Abbrecher</option>
						</form-input>
					</div>
					<div class="row mb-3">		   
						<form-input
							type="select"
							name="studiensemester_kurzbz"
							:label="$p.t('lehre/studiensemester')"
							v-model="statusData['studiensemester_kurzbz']"
							:disabled="statusData.datum < dataMeldestichtag"
						>
							<option v-for="sem in lists.studiensemester_desc" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz"  :selected="sem.studiensemester_kurzbz === defaultSemester">{{sem.studiensemester_kurzbz}}</option>
						</form-input>
					</div>
					<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false) 100 Semester-->
					<div class="row mb-3">
						<form-input
							type="select"
							name="ausbildungssemester"
							:label="$p.t('lehre/ausbildungssemester')"
							v-model="statusData.ausbildungssemester"
							:disabled="statusData.datum < dataMeldestichtag && !isStatusBeforeStudent"
						>
						 <option v-for="number in maxSem" :key="number" :value="number">{{ number }}</option>
						</form-input>
					</div>
					
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							name="datum"
							:label="$p.t('global/datum')"
							v-model="statusData.datum"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
							:disabled="statusData.datum < dataMeldestichtag"
						></form-input>
					</div>
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							name="datum"
							:label="$p.t('lehre/bestaetigt_am')"
							v-model="statusData.bestaetigtam"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
							:disabled="statusData.datum < dataMeldestichtag"
						></form-input>
					</div>
					<div class="row mb-3">
						<form-input
							type="DatePicker"
							name="datum"
							:label="$p.t('lehre/bewerbung_abgeschickt_am')"
							v-model="statusData['bewerbung_abgeschicktamum']"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
							:disabled="statusData.datum < dataMeldestichtag"
						></form-input>
					</div>
					<div class="row mb-3">
						<form-input
							type="select"
							name="studienplan"
							:label="$p.t('lehre/studienplan')"
							v-model="statusData['studienplan_id']"
							:disabled="statusData.datum < dataMeldestichtag"
						>		
							<option v-for="sp in listStudienplaene" :key="sp.studienplan_id" :value="sp.studienplan_id">{{sp.bezeichnung}}</option>
						</form-input>
					</div>
					<div class="row mb-3">
						<form-input
							type="text"
							name="anmerkung"
							:label="$p.t('global/anmerkung')"
							v-model="statusData['anmerkung']"
							:disabled="statusData.datum < dataMeldestichtag"
						>						
						</form-input>
					</div>
					
					<div class="row mb-3">								   
						<form-input
						type="select"
						name="aufnahmestufe"
						:label="$p.t('lehre/aufnahmestufe')"
						v-model="statusData['rt_stufe']"
						:disabled="statusData.datum < dataMeldestichtag"
						>
						<option :value="NULL">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
						<option v-for="entry in aufnahmestufen" :key="entry" :value="entry">{{entry}}</option>
						</form-input>
					</div>
									
					<div v-if="gruende.length > 0" class="row mb-3">
						<form-input
							type="select"
							name="statusgrund"
							:label="$p.t('studierendenantrag/antrag_grund')"
							v-model="statusData['statusgrund_id']"
							>
							<option :value="NULL"></option>
							<option v-for="grund in gruende" :key="grund.statusgrund_id" :value="grund.statusgrund_id">{{grund.beschreibung[0]}}</option>
						</form-input>
					</div>
				
				</form-form>
				
				<template #footer>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
					<button v-if="statusNew" type="button" class="btn btn-primary" @click="addNewStatus()">OK</button>
					<button v-else type="button" class="btn btn-primary" @click="editStatus()">OK</button>
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
					<p>
					{{$p.t('lehre', 'modal_askAusbildungssem', { status: actionStatusText })}}</p>
				</div>
				<div v-else>
					<p>
					{{$p.t('lehre', 'modal_askAusbildungssemPlural', { count: prestudentIds.length,
					status: actionStatusText
						})}}</p>
				</div>
				
				<div class="row mb-3">
					<label for="studiensemester" class="form-label col-sm-4">{{$p.t('lehre', 'studiensemester')}}</label>
					<div class="col-sm-6">
						<form-input
							type="text"
							name="studiensemester"
							v-model="actionSem"
							maxlength="2"
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
					<!-- SingleSelectButton-->
					<div class="btn-group">						
						<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
							{{$p.t('lehre', 'btn_statusAendern')}}
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
			<!--MultiSelectButton-->
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
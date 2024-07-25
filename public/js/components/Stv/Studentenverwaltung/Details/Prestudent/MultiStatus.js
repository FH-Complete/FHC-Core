import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import FormValidation from '../../../../Form/Validation.js';

export default{
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput,
		FormValidation,
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
		hasPrestudentstatusPermission: {
			from: 'hasPrestudentstatusPermission',
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
						orgform_kurzbz: item.orgform_kurzbz,
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
		},
		showToolbarStudent() {
			if (Array.isArray(this.modelValue)) {
				if (!this.modelValue.length)
					return false;
				return this.modelValue.every(item => item.uid);
			}
			return !!this.modelValue.uid;
		},
		showToolbar() {
			return this.showToolbarStudent || this.showToolbarInteressent;
		},
		showToolbarInteressent() {
			if (Array.isArray(this.modelValue)) {
				if (!this.modelValue.length)
					return false;
				return !this.modelValue.some(item => item.uid);
			}
			return !this.modelValue.uid;
		},
		toolbarInteressent() {
			return this.listDataToolbar.filter(item => this.statiInteressent.includes(item.status_kurzbz));
		},
		toolbarStudent() {
			return this.listDataToolbar.filter(item => this.statiStudent.includes(item.status_kurzbz));
		},
		sortedGruende() {
			return this.listDataToolbar.reduce((result,current) => {
				if (!result[current.status_kurzbz])
					result[current.status_kurzbz] = [];
				result[current.status_kurzbz].push(current);
				return result;
			}, {});
		},
		resultInteressentArray() {
			const result = [];
			this.statiInteressent.forEach(status => {
				const defaultObject = {
					status_kurzbz: status,
					statusgrund_id: null,
					link: `changeStatusTo${status}`,
					children: []
				};

				if (status === "Student") {
					defaultObject.link = 'changeInteressentToStudent';

				}
				result.push(defaultObject);
				if(this.sortedGruende[status]) {
					this.sortedGruende[status].forEach(item => {
						const itemObject = {
							status_kurzbz: item.status_kurzbz,
							statusgrund_id: item.statusgrund_id,
							beschreibung: item.beschreibung,
							link: `changeStatusTo${item.status_kurzbz}(${item.statusgrund_id})`,
						};

						if (item.status_kurzbz === "Student") {
							itemObject.link = `changeInteressentTo${item.status_kurzbz}(${item.statusgrund_id})`;
						}
						defaultObject.children.push(itemObject);
					});
					//push one item object if student is in the array
					const hasStudentChild = defaultObject.children.some(child => child.status_kurzbz === "Student");

					if (hasStudentChild) {
						defaultObject.children.push({
							status_kurzbz: 'Student',
							statusgrund_id: null,
							beschreibung: 'Student',
							link: 'changeInteressentToStudent'
						});
					}
				}
			});
			return result;
		},
		resultStudentArray() {
			const result = [];
			this.statiStudent.forEach(status => {
				const defaultObject = {
					status_kurzbz: status,
					statusgrund_id: null,
					link: `changeStatusTo${status}`,
					dropEntry: null,
					children: []
				};
				result.push(defaultObject);
				if(this.sortedGruende[status]) {
					this.sortedGruende[status].forEach(item => {
							const itemObject = {
								status_kurzbz: item.status_kurzbz,
								statusgrund_id: item.statusgrund_id,
								beschreibung: item.beschreibung,
								link: `changeStatusTo${item.status_kurzbz}(${item.statusgrund_id})`,
								dropEntry: `[${item.beschreibung}]`,
							};
						defaultObject.children.push(itemObject);
					});
				}
				//push one item object if student is in the array
				const hasStudentChild = defaultObject.children.some(child => child.status_kurzbz === "Student");

				if (hasStudentChild) {
					defaultObject.children.push({
						status_kurzbz: 'Student',
						statusgrund_id: null,
						beschreibung: 'Student',
						link: 'changeStatusToStudent'
					});
				}
			});
			return result;
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
			isBewerber: true,
			listDataToolbar: [],
			//TODO(Manu) get from config
			statiInteressent: ["Bewerber", "Aufgenommener", "Student" , "Wartender", "Abgewiesener"],
			statiStudent: ["Abbrecher", "Unterbrecher", "Student" , "Diplomand", "Absolvent"],
			selectedStatus: 'default'
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
			if (this.$refs.table) {
				if (this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.setData('api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id);
				else
					this.data.tabulatorOptions.ajaxURL = 'api/frontend/v1/stv/Status/getHistoryPrestudent/' + this.modelValue.prestudent_id;
			}
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
			this.$refs.statusData.clearValidation();
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
				if(this.statusData) {
					this.$refs.statusData.clearValidation();
					this.$refs.statusModal.show();
				}
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
			this.$refs.confirmStatusAction.show();
		},
		changeStatusToAbbrecher(statusgrund_id){
			this.resetChangeModals();
			let def_date = this.getDefaultDate();
			let abbruchData =
				{
					status_kurzbz: 'Abbrecher',
					datum: def_date,
					bestaetigtam: def_date,
					statusgrund_id: statusgrund_id
				};
			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...abbruchData }));

			this.actionConfirmDialogue(this.updateData, 'studenten','Abbrecher');
			//this.changeStatus(prestudentIds);
		},
		changeStatusToUnterbrecher(){
			this.resetChangeModals();
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Unterbrecher',
					datum: def_date,
					bestaetigtam: def_date
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData }));
			this.actionConfirmDialogue(this.updateData, 'studenten','Unterbrecher');
		},
		changeStatusToStudent(statusgrund_id){
			this.resetChangeModals();
			console.log("in function changeStatusToStudent: ", statusgrund_id);
			let def_date = this.getDefaultDate();
			//TODO Manu validation if Bewerber already before asking for ausbildungssemester
			//this.checkIfBewerber();

			this.actionButton = 'student';

			if(statusgrund_id == 19){
				this.actionStatusText = 'Pre-Abbrecher';
			}
			if(statusgrund_id == 15){
				this.actionStatusText = 'Pre-Wiederholer';
			}
			if(statusgrund_id == 16){
				this.actionStatusText = 'Wiederholer';
			}
			if(!statusgrund_id){
				this.actionStatusText = 'Student';
			}
			let deltaData =
				{
					status_kurzbz: 'Student',
					datum: def_date,
					bestaetigtam: def_date,
					statusgrund_id: statusgrund_id
				};
			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData}));

			if(statusgrund_id == 16 || !statusgrund_id){
				this.$refs.askForAusbildungssemester.show();
			}
			else
			{
				this.actionConfirmDialogue(this.updateData, this.actionButton, this.actionStatusText);
			}
		},
		changeStatusToDiplomand(){
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Diplomand',
					datum: def_date,
					bestaetigtam: def_date,
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData}));
			this.changeStatus(this.prestudentIds);
		},
		changeStatusToAbsolvent(){
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Absolvent',
					datum: def_date,
					bestaetigtam: def_date,
				};

			this.newArray = this.updateData.map(objekt => ({ ...objekt, ...deltaData}));
			this.changeStatus(this.prestudentIds);
		},
		saveNewAusbildungssemester(){
			this.newArray = this.newArray.map(objekt => ({ ...objekt, ausbildungssemester: this.actionSem}));
			this.changeStatus(this.prestudentIds);
		},
		changeStatusToBewerber(){
			this.resetChangeModals();
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Bewerber',
					datum: def_date,
					bestaetigtam: def_date,
					ausbildungssemester: 1
				};

			this.newArray = this.updateData.map(objekt => ({
				...objekt,
				...deltaData}));
			this.changeStatus(this.prestudentIds);
		},
		changeStatusToAufgenommener(){
			this.resetChangeModals();
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Aufgenommener',
					datum: def_date,
					bestaetigtam: def_date
				};

			this.newArray = this.updateData.map(objekt => ({
				...objekt,
				...deltaData,
			}));

			this.actionConfirmDialogue(this.updateData, 'aufgenommener','Aufgenommenen');

		},
		changeInteressentToStudent(statusgrund_id){
			this.resetChangeModals();
			//TODO(Manu) test statusgrund_id
			console.log("in function changeInteressentToStudent mit statusgrund_id", statusgrund_id);
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Student',
					datum: def_date,
					bestaetigtam: def_date,
					statusgrund_id: statusgrund_id
				};

			this.newArray = this.updateData.map(objekt => ({
				...objekt,
				...deltaData,
			}));

			this.addStudent(this.prestudentIds);

		},
		changeStatusToAbgewiesener(statusgrund_id){
			this.resetChangeModals();
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Abgewiesener',
					datum: def_date,
					bestaetigtam: def_date,
					statusgrund_id: statusgrund_id
				};

			this.newArray = this.updateData.map(objekt => ({
				...objekt,
				...deltaData,
			}));

			this.actionConfirmDialogue(this.updateData, 'abgewiesener','Abgewiesenen');
		},
		changeStatusToWartender(){
			this.resetChangeModals();
			let def_date = this.getDefaultDate();
			let deltaData =
				{
					status_kurzbz: 'Wartender',
					datum: def_date,
					bestaetigtam: def_date
				};
			this.newArray = this.updateData.map(objekt => ({
				...objekt,
				...deltaData,
			}));

			this.actionConfirmDialogue(this.updateData, 'wartender','Wartenden');
		},
		addStudent(prestudentIds){
			//this.hideModal('confirmStatusAction');
			let changeData = {};

			//for Feedback Sucess, Error
			let countSuccess = 0;
			let countError = 0;

			if(!prestudentIds)
				prestudentIds = [this.modelValue.prestudent_id];

			const promises = prestudentIds.map(prestudentId => {

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
		// Manually inserts a new Status with use of the statusModal
		insertStatus() {
			this.$refs.statusData
				.post(
					'api/frontend/v1/stv/status/insertStatus/' + this.modelValue.prestudent_id,
					this.statusData
				)
				.then(result => {
					this.reload();
					this.$reloadList();
					this.$refs.statusModal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeStatus(prestudentIds){
			this.resetChangeModals();
			//Array.isArray(prestudentIds) ? this.modelValue.prestudent_id : [prestudentIds];
			let changeData = {};

			//for Feedback Sucess, Error
			let countSuccess = 0;
			let countError = 0;

			if(!prestudentIds)
				prestudentIds = [this.modelValue.prestudent_id];

			// Check if ausbildungssemester is already in this.newArray
			const existingEntry = this.newArray.find(
				(entry) => entry.ausbildungssemester === this.actionSem
			);

			// If the entry doesn't exist, add a new object with ausbildungssemester
			if (!existingEntry) {
				this.newArray.push({ ausbildungssemester: this.actionSem });
			}

			const promises = prestudentIds.map(prestudentId => {
				//TODO(manu) besserer check
				changeData = this.statusData.status_kurzbz ? this.statusData : this.newArray.find(item => item.prestudent_id === prestudentId);

				return this.$fhcApi.post('api/frontend/v1/stv/status/changeStatus/' + prestudentId,
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
/*					if (this.abbruchData.length < 1) {
					}
					else{*/
					if(this.newArray.length > 0) {
						this.newStatus = this.newArray[0].status_kurzbz;
					}
					else {
						this.newStatus = this.statusData.status_kurzbz;
					}
				//	}

					//Feedback Success als infoalert
					 if (countSuccess > 0) {
						 this.$fhcAlert.alertInfo(this.$p.t('ui', 'successNewStatus', {
							 'countSuccess': countSuccess,
							 'status': this.newStatus,
							 'countError': countError
						 }));
					 }

					 //TODO(Manu) bei status Interessent, Bewerber, aufgenommener, reload nicht working

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
		resetChangeModals(){
			this.hideModal('confirmStatusAction');
			this.hideModal('askForAusbildungssemester');
		},
		getDefaultDate() {
			const today = new Date();
			return today;
		},
		executeLink(link) {
			// Split the link string to extract the function name and arguments
			const match = link.match(/(\w+)\(([^)]*)\)/);
			const functionName = match ? match[1] : link;
			const args = match ? match[2].split(',').map(arg => arg.trim()) : [];
			
			if (typeof this[functionName] === 'function') {
				this[functionName](...args);
			} else {
				console.error(`Method ${functionName} not found`);
			}
		},
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
				if (this.$refs.table && this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.redraw(true);
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusarray/')
			.then(result => result.data)
			.then(result => {
				this.listDataToolbar = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<div class="stv-list h-100 pt-3">
			
			<!--Modal: statusModal-->
			<bs-modal ref="statusModal">
				<template #title>
					{{ $p.t('lehre', statusNew ? 'status_new' : 'status_edit', modelValue) }}
				</template>

				<form-form ref="statusData">
					<form-validation></form-validation>
					<p v-if="statusData.datum < dataMeldestichtag && !isStatusBeforeStudent">
						<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrund')}}</b>
					</p>
					<p v-if="statusData.datum < dataMeldestichtag && isStatusBeforeStudent">
						<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrundSemester')}}</b>
					</p>
					<input type="hidden" id="statusId" name="statusId" value="statusData.statusId">
					<form-input
						container-class="mb-3"
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
						<option v-if="!statusNew" value="Abgewiesener">Abgewiesener</option>
					</form-input>
					<form-input
						container-class="mb-3"
						type="select"
						name="studiensemester_kurzbz"
						:label="$p.t('lehre/studiensemester')"
						v-model="statusData['studiensemester_kurzbz']"
						:disabled="statusData.datum < dataMeldestichtag"
						>
						<option v-for="sem in lists.studiensemester_desc" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz"  :selected="sem.studiensemester_kurzbz === defaultSemester">{{sem.studiensemester_kurzbz}}</option>
					</form-input>
					<!-- TODO(manu) if(defined('VORRUECKUNG_STATUS_MAX_SEMESTER') && VORRUECKUNG_STATUS_MAX_SEMESTER==false) 100 Semester-->
					<form-input
						container-class="mb-3"
						type="select"
						name="ausbildungssemester"
						:label="$p.t('lehre/ausbildungssemester')"
						v-model="statusData.ausbildungssemester"
						:disabled="statusData.datum < dataMeldestichtag && !isStatusBeforeStudent"
						>
					 <option v-for="number in maxSem" :key="number" :value="number">{{ number }}</option>
					</form-input>
					<form-input
						container-class="mb-3"
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
						>
					</form-input>
					<form-input
						container-class="mb-3"
						type="DatePicker"
						name="bestaetigtam"
						:label="$p.t('lehre/bestaetigt_am')"
						v-model="statusData.bestaetigtam"
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						:disabled="statusData.datum < dataMeldestichtag"
						>
					</form-input>
					<form-input
						container-class="mb-3"
						type="DatePicker"
						name="bewerbung_abgeschicktamum"
						:label="$p.t('lehre/bewerbung_abgeschickt_am')"
						v-model="statusData['bewerbung_abgeschicktamum']"
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						:disabled="statusData.datum < dataMeldestichtag || !hasPrestudentstatusPermission"
						>
					</form-input>
					<form-input
						container-class="mb-3"
						type="select"
						name="studienplan"
						:label="$p.t('lehre/studienplan')"
						v-model="statusData['studienplan_id']"
						:disabled="statusData.datum < dataMeldestichtag"
						>		
						<option v-for="sp in listStudienplaene" :key="sp.studienplan_id" :value="sp.studienplan_id">{{sp.bezeichnung}}</option>
					</form-input>
					<form-input
						container-class="mb-3"
						type="text"
						name="anmerkung"
						:label="$p.t('global/anmerkung')"
						v-model="statusData['anmerkung']"
						:disabled="statusData.datum < dataMeldestichtag"
						>						
					</form-input>
					<form-input
						container-class="mb-3"
						type="select"
						name="aufnahmestufe"
						:label="$p.t('lehre/aufnahmestufe')"
						v-model="statusData['rt_stufe']"
						:disabled="statusData.datum < dataMeldestichtag"
						>
						<option value="">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
						<option v-for="entry in aufnahmestufen" :key="entry" :value="entry">{{entry}}</option>
					</form-input>
									
					<form-input
					 	v-if="gruende.length > 0"
					 	container-class="mb-3"
						type="select"
						name="statusgrund"
						:label="$p.t('studierendenantrag/antrag_grund')"
						v-model="statusData['statusgrund_id']"
						>
						<option value="">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
						<option v-for="grund in gruende" :key="grund.statusgrund_id" :value="grund.statusgrund_id">{{grund.bezeichnung}}</option>
					</form-input>
				
				</form-form>
				
				<template #footer>
					<button v-if="statusNew" type="button" class="btn btn-primary" @click="insertStatus">OK</button>
					<button v-else type="button" class="btn btn-primary" @click="editStatus()">OK</button>
				</template>
								
			</bs-modal>
				
			<!--Modal: Confirm Abbruch-->
			<BsModal ref="confirmStatusAction">
				<template #title>{{$p.t('lehre', 'status_edit', modelValue)}}</template>
				<template #default>
					<div v-if="prestudentIds.length == 1">
						<p>Diese Person wirklich zum {{actionStatusText}} machen?</p>
					</div>
					<div v-else>
						<p>Diese {{prestudentIds.length}} Personen wirklich zum {{actionStatusText}} machen?</p>
					</div>
					
				</template>
				<template #footer>
<!--					<div v-if="actionButton=='abbrecherStgl'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToAbbrecher(17)">OK</button>
					</div>
					<div v-if="actionButton=='abbrecherStud'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToAbbrecher(18)">OK</button>
					</div>
					<div v-if="actionButton=='unterbrecher'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatusToUnterbrecher()">OK</button>
					</div>
					<div v-if="actionButton=='aufgenommener'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatus(prestudentIds)">OK</button>
					</div>-->
					
<!--					<div v-if="actionButton=='student'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeInteressentToStudent(prestudentIds)">OK</button>
					</div>
					<div v-if="actionButton=='wartender'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatus(prestudentIds)">OK</button>
					</div>
					<div v-if="actionButton=='abgewiesener'">
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatus(prestudentIds)">OK</button>
					</div>-->		
					
					<!--Action changeStatus-->
					<div>
						<button  ref="Close" type="button" class="btn btn-primary" @click="changeStatus(prestudentIds)">OK</button>
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
					<div>
						<button  ref="Close" type="button" class="btn btn-primary" @click="saveNewAusbildungssemester()">OK</button>
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
						<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
							{{$p.t('lehre', 'btn_statusAendern')}}
						</button>
															
						    <ul v-if="showToolbarInteressent" class="dropdown-menu">
							  <li v-for="item in resultInteressentArray" :key="item.status_kurzbz" class="w-100">

								<div v-if="item.children.length > 0" class="btn-group dropend w-100">
								  <a
									class="dropdown-item dropdown-toggle"
									data-bs-toggle="dropdown"
									aria-expanded="false"
									href="#"
								  >
									{{ item.status_kurzbz }}
								  </a>
								  <ul class="dropdown-menu dropdown-menu-right">
									<li v-for="child in item.children" :key="child.statusgrund_id">
									  <a class="dropdown-item" @click="executeLink(child.link)">{{ child.beschreibung }}</a>
									</li>
								  </ul>
								</div>
						
								<div v-else>
								  <a
									class="dropdown-item"
									@click="executeLink(item.link)"
								  >
									{{ item.status_kurzbz }}
								  </a>
								</div>
							  </li>
							</ul>	
					
							<!--toolbar Student-->
							<ul v-if="showToolbarStudent" class="dropdown-menu">
							  <li v-for="item in resultStudentArray" :key="item.status_kurzbz" class="w-100">

								<div v-if="item.children.length > 0" class="btn-group dropend w-100">
								  <a
									class="dropdown-item dropdown-toggle"
									data-bs-toggle="dropdown"
									aria-expanded="false"
									href="#"
								  >
									{{ item.status_kurzbz }}
								  </a>
								  <ul class="dropdown-menu dropdown-menu-right">
									<li v-for="child in item.children" :key="child.statusgrund_id">
									  <a class="dropdown-item" @click="executeLink(child.link)">{{ child.beschreibung }}</a>
									</li>
								  </ul>
								</div>
						
								<div v-else>
								  <a
									class="dropdown-item"
									@click="executeLink(item.link)"
								  >
									{{ item.status_kurzbz }}
								  </a>
								</div>
							  </li>
							</ul>	
					</div>
				</template>

			</core-filter-cmpt>
			
			<div 
				v-if="this.modelValue.length"
				ref="buttonsStatusMulti"
			>	
			<!--MultiSelectButton-->
			<div v-if="showToolbar"  class="btn-group">						
				<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
					{{$p.t('lehre', 'btn_statusAendern')}}
				</button>
													
					<ul v-if="showToolbarInteressent" class="dropdown-menu">
					  <li v-for="item in resultInteressentArray" :key="item.status_kurzbz" class="w-100">

						<div v-if="item.children.length > 0" class="btn-group dropend w-100">
						  <a
							class="dropdown-item dropdown-toggle"
							data-bs-toggle="dropdown"
							aria-expanded="false"
							href="#"
						  >
							{{ item.status_kurzbz }}
						  </a>
						  <ul class="dropdown-menu dropdown-menu-right">
							<li v-for="child in item.children" :key="child.statusgrund_id">
							  <a class="dropdown-item" @click="executeLink(child.link)">{{ child.beschreibung }}</a>
							</li>
						  </ul>
						</div>
				
						<div v-else>
						  <a
							class="dropdown-item"
							@click="executeLink(item.link)"
						  >
							{{ item.status_kurzbz }}
						  </a>
						</div>
					  </li>
					</ul>	
							
				<!--toolbar Student-->
					<ul v-if="showToolbarStudent" class="dropdown-menu">
					  <li v-for="item in resultStudentArray" :key="item.status_kurzbz" class="w-100">

						<div v-if="item.children.length > 0" class="btn-group dropend w-100">
						  <a
							class="dropdown-item dropdown-toggle"
							data-bs-toggle="dropdown"
							aria-expanded="false"
							href="#"
						  >
							{{ item.status_kurzbz }}
						  </a>
						  <ul class="dropdown-menu dropdown-menu-right">
							<li v-for="child in item.children" :key="child.statusgrund_id">
							  <a class="dropdown-item" @click="executeLink(child.link)">{{ child.beschreibung }}</a>
							</li>
						  </ul>
						</div>
				
						<div v-else>
						  <a
							class="dropdown-item"
							@click="executeLink(item.link)"
						  >
							{{ item.status_kurzbz }}
						  </a>
						</div>
					  </li>
					</ul>
			</div>			
	 	</div>	
	</div>`
};
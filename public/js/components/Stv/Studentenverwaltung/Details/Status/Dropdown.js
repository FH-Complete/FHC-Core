import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from '../../../../Form/Form.js';
import FormValidation from '../../../../Form/Validation.js';
import FormInput from '../../../../Form/Input.js';

export default {
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		}
	},
	props: {
		showToolbar: {
			type: Boolean,
			required: true
		},
		showToolbarStudent: {
			type: Boolean,
			required: true
		},
		showToolbarInteressent: {
			type: Boolean,
			required: true
		},
		prestudentIds: {
			type: Array,
			required: true,
			default: () => []
		},
		updateData: {
			type: Array,
			required: true,
			default: () => []
		}
	},
	data() {
		return {
			listDataToolbar: [],
			//TODO(Manu) get from config
			statiInteressent: ["Bewerber", "Aufgenommener", "Student" , "Wartender", "Abgewiesener"],
			statiStudent: ["Abbrecher", "Unterbrecher", "Student" , "Diplomand", "Absolvent"],
			selectedStatus: 'default',
			actionButton: {},
			actionStatusText: {},
			actionSem: null
		};
	},
	computed: {
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
		},
		gruende() {
			return this.listStatusgruende.filter(grund => grund.status_kurzbz == this.formData.status_kurzbz);
		}
	},
	methods: {
		executeLink(link) {
			bootstrap.Dropdown.getInstance(this.$refs.toolbarButton).hide();
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
		actionConfirmDialogue(data, statusgrund, statusText){
			this.actionButton = statusgrund;
			this.actionStatusText = statusText;
			this.$refs.confirmStatusAction.show();
		},
		addStudent(prestudentIds){
			this.resetChangeModals();
			let changeData = {};

			//for Feedback Sucess, Error
			let countSuccess = 0;
			let countError = 0;

			const promises = prestudentIds.map(prestudentId => {

				changeData = this.newArray.find(item => item.prestudent_id === prestudentId);

				return this.$fhcApi.post('api/frontend/v1/stv/status/addStudent/' + prestudentId,
					changeData
				).then(response => {
					countSuccess++;
					return response;
				})
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

					if(this.prestudentIds.length == 1){
						this.reload();
						this.$reloadList();
					}
					else {
						this.$reloadList();
					}
					this.resetModal();
				});
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

			this.actionConfirmDialogue(this.updateData, 'aufgenommener','Aufgenommener');

		},
		changeInteressentToStudent(statusgrund_id){
			this.resetChangeModals();
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

			this.actionConfirmDialogue(this.updateData, 'abgewiesener','Abgewiesener');
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

			this.actionConfirmDialogue(this.updateData, 'wartender','Wartender');
		},
		changeStatus(prestudentIds){
			this.resetChangeModals();
			let changeData = {};

			//for Feedback Sucess, Error
			let countSuccess = 0;
			let countError = 0;

			// Check if ausbildungssemester is already in this.newArray
			const existingEntry = this.newArray.find(
				(entry) => entry.ausbildungssemester === this.actionSem
			);

			// If the entry doesn't exist, add a new object with ausbildungssemester
			if (!existingEntry) {
				this.newArray.push({ ausbildungssemester: this.actionSem });
			}

			const promises = prestudentIds.map(prestudentId => {

				changeData = this.newArray.find(item => item.prestudent_id === prestudentId);

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
					if (countSuccess > 0 || countError > 0 ) {
						this.$fhcAlert.alertInfo(this.$p.t('ui', 'successNewStatus', {
							'countSuccess': countSuccess,
							'status': this.newStatus,
							'countError': countError
						}));
					}

					if(this.prestudentIds.length == 1 && countSuccess > 0){
						this.reload();

						//necessary to see new status in List
						this.$reloadList();
						//to change ToolbarInteressent to ToolbarStudent


					}
					else {
						this.$reloadList();
					}
					this.resetModal();
				});
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
		getDefaultDate() {
			const today = new Date();
			return today;
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
			this.statusNew = true;
		},
		reload() {
			this.$emit('reload-table');
		},
		reloadList() {
			this.$emit('reload-list');
		},
		resetChangeModals(){
			this.hideModal('confirmStatusAction');
			this.hideModal('askForAusbildungssemester');
		},
		resetModal() {
			this.statusData = {};
			this.statusId = {};
			this.actionButton = {};
			this.actionStatusText = {};
			this.actionSem = null;
		},
		saveNewAusbildungssemester(){
			this.newArray = this.newArray.map(objekt => ({ ...objekt, ausbildungssemester: this.actionSem}));
			console.log("ausbildungssem" + this.actionSem);
			this.changeStatus(this.prestudentIds);
		},

	},
	created() {
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusarray/')
			.then(result => result.data)
			.then(result => {
				this.listDataToolbar = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		//TODO(manu) check if necessary
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusgruende/')
			.then(result => result.data)
			.then(result => {
				this.listStatusgruende = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-status-dropdown">
		
		<!--Modal: ConfirmStatusAction-->
		<BsModal ref="confirmStatusAction">
			<template #title>{{$p.t('lehre', 'status_edit')}}</template>
			<template #default>
				<div v-if="prestudentIds.length == 1">
					<p>{{$p.t('lehre', 'modal_StatusactionSingle', { status: actionStatusText })}}</p>
				</div>
				<div v-else>
					<p>{{$p.t('lehre', 'modal_StatusactionPlural', { count: prestudentIds.length,
				status: actionStatusText
					})}}</p>
				</div>
				
			</template>
			<template #footer>	
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
	
		<!-- Dropdown -->
		<div v-if="showToolbar"  class="btn-group">						
			<button ref="toolbarButton" type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
				{{$p.t('lehre', 'btn_statusAendern')}}
			</button>

			<ul class="dropdown-menu">
				
				<!--toolbar Interessent-->
				<template v-if="showToolbarInteressent">
					<li v-for="item in resultInteressentArray" :key="item.status_kurzbz" class="w-100">

						<div v-if="item.children.length > 0" class="btn-group dropend w-100">
							<a
								class="dropdown-item dropdown-toggle d-flex justify-content-between align-items-center"
								data-bs-toggle="dropdown"
								aria-expanded="false"
								href="#"
								>
								{{ item.status_kurzbz }}
							</a>
							<ul class="dropdown-menu dropdown-menu-right">
								<li v-for="child in item.children" :key="child.statusgrund_id">
									<a class="dropdown-item" @click.prevent="executeLink(child.link)" href="#">{{ child.beschreibung }}</a>
								</li>
							</ul>
						</div>
						<div v-else>
							<a
								class="dropdown-item"
								@click.prevent="executeLink(item.link)"
								href="#"
								>
								{{ item.status_kurzbz }}
							</a>
						</div>

					</li>
				</template>

				<!--toolbar Student-->
				<template v-if="showToolbarStudent">
					<li v-for="item in resultStudentArray" :key="item.status_kurzbz" class="w-100">

						<div v-if="item.children.length > 0" class="btn-group dropend w-100">
							<a
								class="dropdown-item dropdown-toggle d-flex justify-content-between align-items-center"
								data-bs-toggle="dropdown"
								aria-expanded="false"
								href="#"
								>
								{{ item.status_kurzbz }}
							</a>
							<ul class="dropdown-menu dropdown-menu-right">
								<li v-for="child in item.children" :key="child.statusgrund_id">
									<a class="dropdown-item" @click.prevent="executeLink(child.link)" href="#">{{ child.beschreibung }}</a>
								</li>
							</ul>
						</div>
						<div v-else>
							<a
								class="dropdown-item"
								@click.prevent="executeLink(item.link)"
								href="#"
								>
								{{ item.status_kurzbz }}
							</a>
						</div>

					</li>
				</template>

			</ul>
		</div>
	</div> `
};
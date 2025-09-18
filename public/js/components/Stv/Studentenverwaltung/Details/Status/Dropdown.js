import BsModal from "../../../../Bootstrap/Modal.js";
import BsConfirm from "../../../../Bootstrap/Confirm.js";
import BsPrompt from "../../../../Bootstrap/Prompt.js";
import FormInput from '../../../../Form/Input.js';

import ApiStvStatus from '../../../../../api/factory/stv/status.js';

export default {
	components: {
		BsModal,
		FormInput
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		}
	},
	emits: [
		'reloadTable'
	],
	props: {
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
		maxSem: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
			listDataToolbar: [],
			statiInteressent: ["Bewerber", "Aufgenommener", "Student" , "Wartender", "Abgewiesener"],
			statiStudent: ["Abbrecher", "Unterbrecher", "Student" , "Diplomand", "Absolvent"]
		};
	},
	computed: {
		showToolbar() {
			return this.showToolbarStudent || this.showToolbarInteressent;
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
					link: () => this['changeStatusTo' + status](),
					children: []
				};

				if (status === "Student") {
					defaultObject.link = () => this.changeInteressentToStudent();

				}
				result.push(defaultObject);
				if(this.sortedGruende[status]) {
					this.sortedGruende[status].forEach(item => {
						const itemObject = {
							status_kurzbz: item.status_kurzbz,
							statusgrund_id: item.statusgrund_id,
							beschreibung: item.beschreibung,
							link: () => this['changeStatusTo' + item.status_kurzbz](item.statusgrund_id),
						};

						if (item.status_kurzbz === "Student") {
							itemObject.link = () => this['changeInteressentTo' + item.status_kurzbz](item.statusgrund_id);
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
							link: () => this.changeInteressentToStudent()
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
					link: () => this['changeStatusTo' + status](),
					children: []
				};
				result.push(defaultObject);
				if(this.sortedGruende[status]) {
					this.sortedGruende[status].forEach(item => {
						const itemObject = {
							status_kurzbz: item.status_kurzbz,
							statusgrund_id: item.statusgrund_id,
							beschreibung: item.beschreibung,
							link: () => this['changeStatusTo' + item.status_kurzbz](item.statusgrund_id)
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
						link: () => this.changeStatusToStudent()
					});
				}
			});
			return result;
		}
	},
	methods: {
		changeInteressentToStudent(statusgrund_id) {
			this.addStudent({status_kurzbz: 'student', statusgrund_id});
		},
		addStudent(data) {
			this.$api.call(this.prestudentIds.map(prestudent_id => [
				prestudent_id,
				ApiStvStatus.addStudent(prestudent_id, data),
				{ errorHeader: prestudent_id }
			]))
			.then(result => {
				const messagesSuccessful = result.filter(res => res.status == 'fulfilled');
				if (messagesSuccessful.length) {
					this.$fhcAlert.alertDefault(
						'info',
						'Feedback',
						messagesSuccessful.length + " erfolgreiche Statusänderung(en) durchgeführt", // TODO(chris): translate
						false,
						true
					);
				}
				this.$emit('reloadTable');
				this.$reloadList();
			});
		},
		changeStatusToAbbrecher(statusgrund_id) {
			this
				.confirmStatusChange('Abbrecher', statusgrund_id)
				.then(this.changeStatus)
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeStatusToUnterbrecher(statusgrund_id) {
			this
				.confirmStatusChange('Unterbrecher', statusgrund_id)
				.then(this.changeStatus)
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeStatusToStudent(statusgrund_id) {
			this
				.promtAusbildungssemester('Student', statusgrund_id)
				.then(this.changeStatus)
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeStatusToDiplomand(statusgrund_id) {
			this.changeStatus({status_kurzbz: 'Diplomand', statusgrund_id});
		},
		changeStatusToAbsolvent(statusgrund_id) {
			this.changeStatus({status_kurzbz: 'Absolvent', statusgrund_id});
		},
		changeStatusToBewerber(statusgrund_id) {
			this.changeStatus({status_kurzbz: 'Bewerber', statusgrund_id});
		},
		changeStatusToAufgenommener(statusgrund_id) {
			this
				.confirmStatusChange('Aufgenommener', statusgrund_id)
				.then(this.changeStatus)
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeStatusToAbgewiesener(statusgrund_id) {
			this
				.confirmStatusChange('Abgewiesener', statusgrund_id)
				.then(this.changeStatus)
				.catch(this.$fhcAlert.handleSystemError);
		},
		changeStatusToWartender(statusgrund_id) {
			this
				.confirmStatusChange('Wartender', statusgrund_id)
				.then(this.changeStatus)
				.catch(this.$fhcAlert.handleSystemError);
		},
		confirmStatusChange(status, statusgrund_id) {
			const count = this.prestudentIds.length;
			return BsConfirm
				.popup(this.$p.t(
					'lehre',
					count > 1 ? 'modal_StatusactionPlural' : 'modal_StatusactionSingle',
					{ count, status }
				))
				.then(() => ({
					status_kurzbz: status,
					statusgrund_id
				}));
		},
		promtAusbildungssemester(status, statusgrund_id) {
			const count = this.prestudentIds.length;

			const askForSemester = () => {
				return BsPrompt
					.popup(this.$p.t(
						'lehre',
						count > 1 ? 'modal_askAusbildungssemPlural' : 'modal_askAusbildungssem',
						{ count, status }
					))
					.then(input => {
						const ausbildungssemester = parseInt(input, 10);
						//check if valid number
						if ((!/^\d+$/.test(input) || ausbildungssemester < 0)) {
							this.$fhcAlert.alertError(this.$p.t('ui', 'error_noInteger'));

							return askForSemester();
						}
						if (ausbildungssemester > this.maxSem) {
							this.$fhcAlert.alertError(this.$p.t('ui', 'error_maxSem'));

							return askForSemester();
						}
						return {
							status_kurzbz: status,
							ausbildungssemester,
							statusgrund_id
						};
					});
			};
			return askForSemester();
		},
		changeStatus(data) {
			data.currentSemester = this.currentSemester;
			this.$api.call(this.prestudentIds.map(prestudent_id => [
				prestudent_id,
				ApiStvStatus.changeStatus(prestudent_id, data)
			]))
			.then(() => {
				this.$emit('reloadTable');
				this.$reloadList();
			});
		},
	},
	created() {
		this.$api
			.call(ApiStvStatus.getStatusarray())
			.then(result => result.data)
			.then(result => {
				this.listDataToolbar = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-status-dropdown">
			
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
									<a class="dropdown-item" @click.prevent="child.link" href="#">{{ child.beschreibung }}</a>
								</li>
							</ul>
						</div>
						<div v-else>
							<a
								class="dropdown-item"
								@click.prevent="item.link"
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
									<a class="dropdown-item" @click.prevent="child.link" href="#">{{ child.beschreibung }}</a>
								</li>
							</ul>
						</div>
						<div v-else>
							<a
								class="dropdown-item"
								@click.prevent="item.link"
								href="#"
								>
								{{ item.status_kurzbz }}
							</a>
						</div>

					</li>
				</template>

			</ul>
		</div>
	</div>`
};

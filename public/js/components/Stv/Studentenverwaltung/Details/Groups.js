import GroupsLvb from './Groups/Lvb.js';
import GroupsSpecial from './Groups/Special.js';
import GroupsList from './Groups/List.js';

import ApiStvGroups from '../../../../api/factory/stv/group.js';
import ApiStvDetails from '../../../../api/factory/stv/details.js';

export default {
	name: 'TabGroups',
	components: {
		GroupsLvb,
		GroupsSpecial,
		GroupsList
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		currentSemester: {
			form: 'currentSemester',
			required: true
		}
	},
	props: {
		modelValue: [Object, Array]
	},
	data() {
		return {
			hasOrgforms: false,
			lvbList: [],
			specialGroups: [],
			selectedOrgform: false,
			selectedSemester: false,
			selectedVerband: false,
			selectedGruppe: false,
			multiFormHandler: (form, errors) => {
				function _split_errors(result, [uid, errors]) {
					const gruppe_kurzbz = [];
					const studiensemester_kurzbz = [];
					const others = {};
					errors.forEach(error => {
						_split_messages(error.messages, gruppe_kurzbz, studiensemester_kurzbz, others);
					});
					if (gruppe_kurzbz.length) {
						if (!result.formFeedback.gruppe_kurzbz)
							result.formFeedback.gruppe_kurzbz = [];
						result.formFeedback.gruppe_kurzbz.push(...gruppe_kurzbz);
					}
					if (studiensemester_kurzbz.length) {
						if (!result.formFeedback.studiensemester_kurzbz)
							result.formFeedback.studiensemester_kurzbz = [];
						result.formFeedback.studiensemester_kurzbz.push(...studiensemester_kurzbz);
					}
					if (Object.keys(others).length) {
						result.toast[uid] = [
							{ type: 'validation', messages: others }
						];
					}
					return result;
				}
				function _split_messages(messages, gruppe_kurzbz, studiensemester_kurzbz, others) {
					Object.entries(messages).forEach(([field, msg]) => {
						if (field == 'gruppe_kurzbz') {
							gruppe_kurzbz.push(msg);
						} else if (field == 'studiensemester_kurzbz') {
							studiensemester_kurzbz.push(msg);
						} else {
							if (!others[field])
								others[field] = [];
							others[field].push(msg);
						}
					});
				}
				const { formFeedback, toast } = Object.entries(errors)
					.reduce(_split_errors, { formFeedback: {}, toast: {} });

				if (formFeedback.gruppe_kurzbz)
					formFeedback.gruppe_kurzbz = formFeedback.gruppe_kurzbz
						.filter((v,k,a) => a.indexOf(v) == k);
				if (formFeedback.studiensemester_kurzbz)
					formFeedback.studiensemester_kurzbz = formFeedback.studiensemester_kurzbz
						.filter((v,k,a) => a.indexOf(v) == k);

				form.clearValidation();
				if (Object.keys(formFeedback)) {
					form.setFeedback(false, formFeedback);
				}

				if (Object.keys(toast).length) {
					console.log(toast);
					this.$api.getErrorHandler().handler.toast(toast);
				}
			}
		};
	},
	computed: {
		allAreStudents() {
			if (Array.isArray(this.modelValue))
				return this.modelValue.every(ps => ps.uid);
			return this.modelValue.uid;
		},
		sharedStg() {
			if (Array.isArray(this.modelValue)) {
				const first = this.modelValue.find(Boolean);
				if (this.modelValue.every(ps => ps.studiengang_kz === first.studiengang_kz))
					return first.studiengang_kz;
				return false;
			}
			return this.modelValue.studiengang_kz;
		}
	},
	methods: {
		showNewGroupModal() {
			this.$refs.newGroupModal.show()
		},
		changeLvb(params) {
			let data = { semester: params.semester };
			if (params.verband && params.verband != " ") {
				data.verband = params.verband;
				if (params.gruppe && params.gruppe != " ")
					data.gruppe = params.gruppe;
			}

			let endpoint;

			if (Array.isArray(this.modelValue)) {
				endpoint = this.modelValue.map(student => [
					student.uid + ' (' + student.vorname + ' ' + student.nachname + ')',
					ApiStvDetails.save(
						student.prestudent_id,
						this.currentSemester,
						data
					)
				]);
			} else {
				endpoint = ApiStvDetails.save(
					this.modelValue.prestudent_id,
					this.currentSemester,
					data
				);
			}
			this.$api
				.call(endpoint)
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$reloadList();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addSpecialGroup(params) {
			const gruppe_kurzbz = this.$refs.newGroupModal.value.gruppe_kurzbz || this.$refs.newGroupModal.value;

			if (Array.isArray(this.modelValue)) {
				this.$refs.newGroupModal.$refs.form
					.call(
						this.modelValue.map(student => [
							student.uid + ' (' + student.vorname + ' ' + student.nachname + ')',
							ApiStvGroups.add(
								student.uid,
								gruppe_kurzbz,
								this.currentSemester
							)
						]),
						{
							errorHandling: {
								combine: { form: ['validation'] },
								handler: { form: this.multiFormHandler }
							}
						}
					)
					.then(result => {
						const successes = result.filter(res => res.status == 'fulfilled');
						if (result.length == successes.length) {
							this.$refs.newGroupModal.hide();
						}
						if (successes.length) {
							this.$fhcAlert.alertSuccess(this.$p.t('gruppenmanagement/groups_added', { n: successes.length }));
							this.$refs.list.reload();
						}
					})
					.catch(this.$fhcAlert.handleSystemError);
			} else {
				this.$refs.newGroupModal.$refs.form
					.call(ApiStvGroups.add(this.modelValue.uid, gruppe_kurzbz, this.currentSemester))
					.then(result => {
						this.$refs.newGroupModal.hide();
						this.$fhcAlert.alertSuccess(this.$p.t('gruppenmanagement/groups_added', { n: 1 }));
						this.$refs.list.reload();
					})
					.catch(this.$fhcAlert.handleSystemError);
			}
		}
	},
	template: /* html */`
	<div class="stv-details-groups h-100 d-flex flex-column">
		<h3>{{ $p.t('gruppenmanagement/special_groups') }}</h3>
		<groups-special
			ref="newGroupModal"
			:default-stg="sharedStg"
			@submit.capture.prevent="addSpecialGroup"
		/>
		<groups-list
			ref="list"
			class="mb-3"
			:students="modelValue"
			@new="showNewGroupModal"
		/>

		<h3>{{ $p.t('lehre/lehrverband') }}</h3>
		<groups-lvb
			:students="modelValue"
			@submit="changeLvb"
		/>
	</div>`
};
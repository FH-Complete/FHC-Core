import BsModal from "../../../../Bootstrap/Modal.js";
import CoreForm from '../../../../Form/Form.js';
import FormValidation from '../../../../Form/Validation.js';
import FormInput from '../../../../Form/Input.js';

export default{
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
		hasPermissionToSkipStatusCheck: {
			from: 'hasPermissionToSkipStatusCheck',
			default: false
		},
		hasPrestudentstatusPermission: {
			from: 'hasPrestudentstatusPermission',
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
	emit: [
		'saved'
	],
	props: {
		meldestichtag: {
			type: Date,
			required: true
		},
		maxSem: {
			type: Number,
			required: true
		}
	},
	data() {
		return {
			prestudent: {},
			originalDatum: null,
			statusNew: true,
			mischform: false,
			statusId: {},
			formData: {},
			studienplaene: [],
			statusgruende: [],
			stati: [],
			allowedNewStatus: [
				'Interessent',
				'Bewerber',
				'Aufgenommener',
				'Student',
				'Unterbrecher',
				'Diplomand',
				'Incoming'
			]
		};
	},
	computed: {
		semester() {
			return Array.from({length: this.maxSem}, (_, index) => index);
		},
		bisLocked() {
			if (this.statusNew || this.hasPermissionToSkipStatusCheck)
				return false;

			if (!this.originalDatum || !this.meldestichtag)
				return true;
			
			return this.originalDatum < this.meldestichtag;
		},
		isStatusBeforeStudent() {
			let isStatusStudent = ['Student', 'Absolvent', 'Diplomand'];
			return !isStatusStudent.includes(this.formData.status_kurzbz);
		},
		allowedStati() {
			if (!this.stati)
				return [];
			
			if (this.statusNew)
				return this.stati.filter(status => this.allowedNewStatus.includes(status.status_kurzbz));

			return this.stati.filter(status => this.statusId.status_kurzbz == status.status_kurzbz);
		},
		gruende() {
			return this.statusgruende.filter(grund => grund.status_kurzbz == this.formData.status_kurzbz);
		}
	},
	methods: {
		open(prestudent, status_kurzbz, studiensemester_kurzbz, ausbildungssemester) {
			this.$refs.modal.hide();
			
			if (!status_kurzbz && !studiensemester_kurzbz && !ausbildungssemester) {
				this.statusNew = true;
				this.statusId = prestudent.prestudent_id;
				this.formData = {
					status_kurzbz: 'Interessent',
					studiensemester_kurzbz: this.defaultSemester,
					ausbildungssemester: 1,
					datum: new Date(),
					bestaetigtam: new Date(),
					bewerbung_abgeschicktamum: null,
					studienplan_id: null,
					anmerkung: null,
					rt_stufe: null,
					statusgrund_id: null
				};
				this.originalDatum = null;
				
				this.loadStudienplaeneAndSetPrestudent(prestudent)
					.then(this.$refs.form.clearValidation)
					.then(this.$refs.modal.show)
					.catch(this.$fhcAlert.handleSystemError);
			} else {
				this.statusId = {
					prestudent_id: prestudent.prestudent_id,
					status_kurzbz,
					studiensemester_kurzbz,
					ausbildungssemester
				};
				
				this.$fhcApi
					.post('api/frontend/v1/stv/status/loadStatus/', this.statusId)
					.then(result => {
						this.statusNew = false;
						this.formData = result.data;
						this.originalDatum = new Date(result.data.datum);
						return prestudent;
					})
					.then(this.loadStudienplaeneAndSetPrestudent)
					.then(this.$refs.form.clearValidation)
					.then(this.$refs.modal.show)
					.catch(this.$fhcAlert.handleSystemError);
			}
		},
		insertStatus() {
			this.$refs.form
				.post(
					'api/frontend/v1/stv/status/insertStatus/' + this.statusId,
					this.formData
				)
				.then(result => {
					this.$reloadList();
					this.$emit('saved');
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		editStatus() {
			this.$refs.form
				.post(
					'api/frontend/v1/stv/status/updateStatus/' + Object.values(this.statusId).join('/'),
					this.formData
				)
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$reloadList();
					this.$emit('saved');
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadStudienplaeneAndSetPrestudent(prestudent) {
			const old_id = this.prestudent.prestudent_id;
			this.prestudent = prestudent;
			if (old_id == prestudent.prestudent_id)
				return Promise.resolve();
			
			return this.$fhcApi
				.get('api/frontend/v1/stv/prestudent/getStudienplaene/' + prestudent.prestudent_id)
				.then(result => this.studienplaene = result.data)
				.then(() => this.$fhcApi.get('api/frontend/v1/stv/prestudent/getStudiengang/' + prestudent.prestudent_id))
				.then(result => this.mischform = result.data.mischform);
		}
	},
	created() {
		this.$fhcApi
			.get('api/frontend/v1/stv/status/getStatusgruende')
			.then(result => this.statusgruende = result.data)
			.catch(this.$fhcAlert.handleSystemError);
		/*this.$fhcApi
			.get('api/frontend/v1/stv/lists/getStati')
			.then(result => this.stati = result.data)
			.catch(this.$fhcAlert.handleSystemError);*/
		this.stati = [
			{ status_kurzbz: 'Interessent', bezeichnung: 'Interessent'},
			{ status_kurzbz: 'Bewerber', bezeichnung: 'Bewerber'},
			{ status_kurzbz: 'Aufgenommener', bezeichnung: 'Aufgenommener'},
			{ status_kurzbz: 'Student', bezeichnung: 'Student'},
			{ status_kurzbz: 'Unterbrecher', bezeichnung: 'Unterbrecher'},
			{ status_kurzbz: 'Diplomand', bezeichnung: 'Diplomand'},
			{ status_kurzbz: 'Incoming', bezeichnung: 'Incoming'},
			{ status_kurzbz: 'Absolvent', bezeichnung: 'Absolvent'},
			{ status_kurzbz: 'Abbrecher', bezeichnung: 'Abbrecher'},
			{ status_kurzbz: 'Abgewiesener', bezeichnung: 'Abgewiesener'},
			{ status_kurzbz: 'Wartender', bezeichnung: 'Wartender'}
		];
	},
	template: `
	<bs-modal class="stv-status-modal" ref="modal">
		<template #title>
			{{ $p.t('lehre', statusNew ? 'status_new' : 'status_edit', prestudent) }}
		</template>

		<core-form ref="form">

			<form-validation></form-validation>
			
			<p v-if="bisLocked && !isStatusBeforeStudent">
				<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrund')}}</b>
			</p>
			<p v-if="bisLocked && isStatusBeforeStudent">
				<b>{{$p.t('bismeldestichtag', 'info_MeldestichtagStatusgrundSemester')}}</b>
			</p>
			
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.status_kurzbz"
				name="status_kurzbz"
				:label="$p.t('lehre/status_rolle')"
				required
				:disabled="!statusNew"
				>
				<option
					v-for="status in allowedStati"
					:value="status.status_kurzbz"
					>
					{{ status.bezeichnung }}
				</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.studiensemester_kurzbz"
				name="studiensemester_kurzbz"
				:label="$p.t('lehre/studiensemester')"
				:disabled="bisLocked"
				>
				<option
					v-for="sem in lists.studiensemester_desc"
					:key="sem.studiensemester_kurzbz"
					:value="sem.studiensemester_kurzbz"
					>
					{{ sem.studiensemester_kurzbz }}
				</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.ausbildungssemester"
				name="ausbildungssemester"
				:label="$p.t('lehre/ausbildungssemester')"
				:disabled="bisLocked && !isStatusBeforeStudent"
				>
				<option
					v-for="number in semester"
					:key="number"
					:value="number"
					>
					{{ number }}
				</option>
			</form-input>
			<form-input
				v-if="mischform"
				container-class="mb-3"
				type="select"
				v-model="formData.orgform_kurzbz"
				name="orgform_kurzbz"
				:label="$p.t('lehre/organisationsform')"
				:disabled="bisLocked && !isStatusBeforeStudent"
				>
				<option
					v-for="orgform in lists.orgforms"
					:key="orgform.orgform_kurzbz"
					:value="orgform.orgform_kurzbz"
					>
					{{ orgform.bezeichnung }}
				</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.datum"
				name="datum"
				:label="$p.t('global/datum')"
				auto-apply
				:enable-time-picker="false"
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				:disabled="bisLocked"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.bestaetigtam"
				name="bestaetigtam"
				:label="$p.t('lehre/bestaetigt_am')"
				auto-apply
				:enable-time-picker="false"
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				:disabled="bisLocked"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.bewerbung_abgeschicktamum"
				name="bewerbung_abgeschicktamum"
				:label="$p.t('lehre/bewerbung_abgeschickt_am')"
				auto-apply
				:enable-time-picker="false"
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				:disabled="bisLocked || !hasPrestudentstatusPermission"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.studienplan_id"
				name="studienplan_id"
				:label="$p.t('lehre/studienplan')"
				:disabled="bisLocked"
				>
				<option
					v-for="plan in studienplaene"
					:key="plan.studienplan_id"
					:value="plan.studienplan_id"
					>
					{{ plan.bezeichnung }}
				</option>
			</form-input>
			<form-input
				container-class="mb-3"
				type="text"
				v-model="formData.anmerkung"
				name="anmerkung"
				:label="$p.t('global/anmerkung')"
				:disabled="bisLocked"
				>
			</form-input>
			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.rt_stufe"
				name="rt_stufe"
				:label="$p.t('lehre/aufnahmestufe')"
				:disabled="bisLocked"
				>
				<option :value="null">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
				<option v-for="entry in [1,2,3]" :key="entry" :value="entry">{{entry}}</option>
			</form-input>
			<form-input
			 	v-if="gruende.length"
			 	container-class="mb-3"
				type="select"
				v-model="formData.statusgrund_id"
				name="statusgrund_id"
				:label="$p.t('international/grund')"
				>
				<option :value="null">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
				<option
					v-for="grund in gruende"
					:key="grund.statusgrund_id"
					:value="grund.statusgrund_id"
					>
					{{ grund.bezeichnung }}
				</option>
			</form-input>
		
		</core-form>
		
		<template #footer>
		<button
			type="button"
			class="btn btn-primary"
			@click="statusNew ? insertStatus() : editStatus()"
			>
			{{ $p.t('ui', 'ok') }}
		</button>
		</template>
	</bs-modal>`
};
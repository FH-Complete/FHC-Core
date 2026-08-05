import BsModal from '../Bootstrap/Modal.js';
import CoreForm from '../Form/Form.js';
import FormValidation from '../Form/Validation.js';
import FormInput from '../Form/Input.js';

import ApiTempusSync from '../../api/factory/tempus/sync.js';

export default {
	components: {
		BsModal,
		CoreForm,
		FormValidation,
		FormInput
	},
	props: {
		config: Object,
		stsem_kurzbz: String
	},
	emits: [
		'saved'
	],
	data() {
		return {
			mode: 'new',
			formData: {},
			studienplaene: [],
			max_semester: null,
			syncStati: []
		};
	},
	computed: {
		semester() {
			if (!this.max_semester)
				return [];

			return Array.from({length: this.max_semester}, (_, index) => index);
		},
		studienplanTrigger() {
			return [
				this.formData.oe_kurzbz,
				this.formData.studiensemester_kurzbz,
				this.formData.ausbildungssemester
			].join('|');
		}
	},
	watch: {
		'formData.oe_kurzbz'(newOrganisation)
		{
			if (!newOrganisation) {
				this.max_semester = null;
				this.studienplaene = [];
				return;
			}

			let filtered_org = (this.config.organisationen ?? []).filter(org => org.oe_kurzbz === newOrganisation)[0];

			if (filtered_org && filtered_org.studiengang_kz)
				this.getSemester(filtered_org.studiengang_kz);
			else
				this.max_semester = null;
		},
		studienplanTrigger() {
			this.getStudienplan();
		}
	},
	methods: {

		openNew() {
			this.$refs.modal.hide();

			this.mode = 'new';
			this.max_semester = null;
			this.studienplaene = [];
			this.formData = {
				oe_kurzbz: null,
				studiensemester_kurzbz: this.stsem_kurzbz ?? null,
				datum_bis: null,
				studienplan_id: null,
				ausbildungssemester: null,
				sync_status_kurzbz: null,
				mail: false
			};

			this.$refs.form.clearValidation()
			this.$refs.modal.show()
		},
		openEdit(entry) {
			if (!entry || entry.kalender_syncstatus_id === undefined || entry.kalender_syncstatus_id === null)
			{
				return;
			}

			this.$refs.modal.hide();
			this.mode = 'edit';

			this.$api
				.call(ApiTempusSync.loadSync(entry.kalender_syncstatus_id))
				.then(result => {
					this.formData = result.data;
					return this.$refs.form.clearValidation();
				})
				.then(() => this.$refs.modal.show())
				.catch(this.$fhcAlert.handleSystemError);
		},

		openStart() {
			this.$refs.modal.hide();

			this.mode = 'start';
			this.max_semester = null;
			this.studienplaene = [];
			this.formData = {
				oe_kurzbz: null,
				studiensemester_kurzbz: this.stsem_kurzbz ?? null,
				datum_bis: null,
				sync_status_kurzbz: null,
				studienplan_id: null,
				ausbildungssemester: null,
				mail: false
			};

			this.$refs.form.clearValidation()
			this.$refs.modal.show()
		},
		getSemester(stg_kz) {
			this.$api.call(ApiTempusSync.getMaxSemester([stg_kz]))
				.then(response => response.data)
				.then(response => {
					this.max_semester = response ?? null;

					let found = this.semester.filter(semester => semester === this.formData.ausbildungssemester);
					if (found.length === 0)
						this.formData.ausbildungssemester = null;
				})
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
				});
		},
		getStudienplan() {
			if (!this.formData.oe_kurzbz || !this.formData.studiensemester_kurzbz) {
				this.studienplaene = [];
				return;
			}

			this.$api.call(ApiTempusSync.getStudienplan(
					this.formData.oe_kurzbz,
					this.formData.studiensemester_kurzbz,
					this.formData.ausbildungssemester ?? null
				))
				.then(response => response.data)
				.then(response => {
					this.studienplaene = response ?? [];
					let found = this.studienplaene.filter(plan => plan.studienplan_id === this.formData.studienplan_id);
					if (found.length === 0)
						this.formData.studienplan_id = null;
				})
				.catch(error => {
					this.$fhcAlert.handleSystemError(error);
				});
		},
		save() {
			if (this.mode === 'start')
				this.start();
			else if (this.mode === 'new')
				this.insert();
			else
				this.update();
		},
		insert() {
			this.$refs.form
				.call(ApiTempusSync.add(this.formData))
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$emit('saved', this.formData.studiensemester_kurzbz);
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		update() {
			this.$refs.form
				.call(ApiTempusSync.updateSync(this.formData))
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.$emit('saved', this.formData.studiensemester_kurzbz);
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		start(data) {
			this.$refs.form
				.call(ApiTempusSync.start(data ?? this.formData))
				.then(() => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successStart'));
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		hideModal()
		{
			this.formData = {}
		}
	},
	created() {

		this.$api
			.call(ApiTempusSync.getSyncStatus())
			.then(result => this.syncStati = result.data ?? [])
			.catch(error => this.$fhcAlert.handleSystemError(error));
	},
	template: `
	<bs-modal ref="modal" @hideBsModal="hideModal">
		<template #title>
			<template v-if="mode === 'new'">{{ $p.t('lehre', 'tempus_sync_new') }}</template>
			<template v-else-if="mode === 'edit'">{{ $p.t('lehre', 'tempus_sync_edit') }}</template>
			<template v-else>{{ $p.t('lehre', 'tempus_sync_start') }}</template>
		</template>

		<core-form ref="form">
			<form-validation></form-validation>

			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.oe_kurzbz"
				name="oe_kurzbz"
				:label="$p.t('lehre/organisationseinheit')"
				required
				>
				<option
					v-for="organisation in config.organisationen"
					:key="organisation.oe_kurzbz"
					:value="organisation.oe_kurzbz"
					>
					[{{ organisation.organisationseinheittyp_kurzbz }}] {{ organisation.bezeichnung }} {{ ['b', 'm'].includes(organisation.typ) ? organisation.stgbezeichnung : '' }}
				</option>
			</form-input>

			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.studiensemester_kurzbz"
				name="studiensemester_kurzbz"
				:label="$p.t('lehre/studiensemester')"
				required
				>
				<option
					v-for="studiensemester in config.studiensemestern"
					:key="studiensemester.studiensemester_kurzbz"
					:value="studiensemester.studiensemester_kurzbz"
					>
					{{ studiensemester.studiensemester_kurzbz }}
				</option>
			</form-input>

			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.ausbildungssemester"
				name="ausbildungssemester"
				:label="$p.t('lehre/ausbildungssemester')"
				:disabled="!max_semester"
				>
				<option :value="null">-- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
				<option
					v-for="number in semester"
					:key="number"
					:value="number"
					>
					{{ number }}
				</option>
			</form-input>

			<form-input
				container-class="mb-3"
				type="select"
				v-model="formData.studienplan_id"
				name="studienplan_id"
				:label="$p.t('lehre/studienplan')"
				:disabled="!studienplaene.length"
				>
				<option :value="null">-- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
				<option
					v-for="plan in studienplaene"
					:key="plan.studienplan_id"
					:value="plan.studienplan_id"
					>
					{{ plan.bezeichnung }}-{{plan.orgform_kurzbz}}
				</option>
			</form-input>

			<form-input
				container-class="mb-3"
				type="DatePicker"
				v-model="formData.datum_bis"
				name="datum_bis"
				:label="$p.t('ui/dateTo')"
				auto-apply
				:enable-time-picker="false"
				text-input
				format="dd.MM.yyyy"
				preview-format="dd.MM.yyyy"
				:teleport="true"
				>
			</form-input>

			<form-input
				
				container-class="mb-3"
				type="select"
				v-model="formData.sync_status_kurzbz"
				name="sync_status_kurzbz"
				:label="$p.t('global/status')"
				>
				<option :value="null">-- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
				<option
					v-for="status in syncStati"
					:key="status.status_kurzbz"
					:value="status.status_kurzbz"
				>
					{{ status.bezeichnung }}
				</option>
			</form-input>

			<form-input
				container-class="mb-3"
				type="checkbox"
				v-model="formData.mail"
				name="mail"
				:label="$p.t('lehre/mail_benachrichtigung')"
				>
			</form-input>

		</core-form>

		<template #footer>
		<button
			type="button"
			class="btn btn-primary"
			@click="save"
			>
			<template v-if="mode === 'start'">{{ $p.t('global', 'jetztStarten') }}</template>
			<template v-else>{{ $p.t('ui', 'ok') }}</template>
		</button>
		</template>
	</bs-modal>`
};
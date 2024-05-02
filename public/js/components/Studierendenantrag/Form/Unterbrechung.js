import {CoreFetchCmpt} from '../../Fetch.js';
import CoreForm from '../../Form/Form.js';
import FormValidation from '../../Form/Validation.js';
import FormInput from '../../Form/Input.js';


export default {
	components: {
		CoreFetchCmpt,
		CoreForm,
		FormValidation,
		FormInput
	},
	emits: [
		'setInfos',
		'setStatus'
	],
	props: {
		prestudentId: Number,
		studierendenantragId: Number
	},
	data() {
		return {
			data: null,
			saving: false,
			attachment: [],
			stsem: null,
			currentWiedereinstieg: '',
			siteUrl: FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router
		}
	},
	computed: {
		statusSeverity() {
			switch (this.data.status)
			{
				case 'Erstellt': return 'info';
				case 'Pause':
				case 'Zurueckgezogen':
				case 'Abgelehnt': return 'danger';
				case 'Genehmigt':
				case 'EmailVersandt': return 'success';
				default: return 'warning';
			}
		},
		datumWsFormatted() {
			let datumUnformatted = '';

			if (this.data.datum_wiedereinstieg) {
				datumUnformatted = this.data.datum_wiedereinstieg;
			} else {
				if (this.stsem !== null && this.data.studiensemester[this.stsem].wiedereinstieg)
					datumUnformatted = this.data.studiensemester[this.stsem].wiedereinstieg;
			}
			if (datumUnformatted === '')
				return datumUnformatted;
			let datum = new Date(datumUnformatted);
			return datum.toLocaleDateString();
		},
		semesterOffsets() {
			if (!this.data || !this.data.studiensemester)
				return [];
			return Object.values(this.data.studiensemester)
				.filter(el => !el.disabled)
				.map(el => el.studiensemester_kurzbz);
		},
		semester() {
			if (!this.stsem)
				return '';
			return this.data.semester + this.semesterOffsets.indexOf(this.stsem);
		}
	},
	methods: {
		load() {
			return this.$fhcApi.factory
				.studstatus.unterbrechung.getDetails(this.studierendenantragId, this.prestudentId)
				.then(
					result => {
						this.data = result.data;
						if (this.data.status) {
							const msg = (this.data.status == 'Pause' && this.data.status_insertvon == "Studienabbruch") ? Vue.computed(() => {
								let status = this.$p.t('studierendenantrag/status_stop');
								return this.$p.t('studierendenantrag', 'status_x', {status});
							}) : Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp}));
							this.$emit("setStatus", {
								msg,
								severity: this.statusSeverity
							});
						}
						return result;
					}
				);
		},
		createAntrag() {
			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_saving')})),
				severity: 'warning'
			});
			this.saving = true;

			this.$refs.form.clearValidation();
			this.$refs.form.factory
				.studstatus.unterbrechung.create(
					this.stsem !== null && this.data.studiensemester[this.stsem].studiensemester_kurzbz,
					this.data.prestudent_id,
					this.data.grund,
					this.stsem !== null && this.currentWiedereinstieg,
					this.attachment
				)
				.then(result => {
					if (Number.isInteger(result.data))
						document.location += "/" + result.data;

					this.data = result.data;
					if (this.data.status)
						this.$emit("setStatus", {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
							severity: this.statusSeverity
						});
					else
						this.$emit('setStatus', {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_created')})),
							severity: 'info'
						});
					this.saving = false;
				})
				.catch(error => {
					this.$emit('setStatus', {
						msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_error')})),
						severity: 'danger'
					});
					this.saving = false;
					this.$fhcAlert.handleSystemError(error);
				});
		},
		cancelAntrag() {
			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_cancelling')})),
				severity: 'warning'
			});
			this.saving = true;

			this.$refs.form.clearValidation();
			this.$refs.form.factory
				.studstatus.unterbrechung.cancel(
					this.data.studierendenantrag_id
				)
				.then(result => {
					if (Number.isInteger(result.data))
						document.location = document.location.replace(/unterbrechung\/([0-9]*)\/[0-9]*[\/]?$/, 'unterbrechung/$1') +  "/" + result.data;

					this.data = result.data;
					if (this.data.status)
						this.$emit("setStatus", {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
							severity: this.statusSeverity
						});
					else
						this.$emit('setStatus', {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_cancelled')})),
							severity: 'danger'
						});
					this.saving = false;
				})
				.catch(error => {
					this.$emit('setStatus', {
						msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_error')})),
						severity: 'danger'
					});
					this.saving = false;
					this.$fhcAlert.handleSystemError(error);
				});
		}
	},
	template: `
	<div class="studierendenantrag-form-unterbrechung">
		<core-fetch-cmpt :api-function="load">
			<core-form ref="form" class="row">
				<div class="col-12">
					<form-validation></form-validation>
					<table class="table">
						<tr>
							<th>{{$p.t('lehre', 'studiengang')}}</th>
							<td align="right">{{data.bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre', 'organisationsform')}}</th>
							<td align="right">{{data.orgform_bezeichnung}}</td>
						</tr>
						<tr>
							<th>{{$p.t('projektarbeitsbeurteilung', 'nameStudierende')}}</th>
							<td align="right">{{data.name}}</td>
						</tr>
						<tr>
							<th>{{$p.t('person', 'personenkennzeichen')}}</th>
							<td align="right">{{data.matrikelnr}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre', 'studienjahr')}}</th>
							<td align="right" v-if="data.studierendenantrag_id">{{data.studienjahr_kurzbz}}</td>
							<td align="right" v-else>{{stsem === null ? '' : data.studiensemester[stsem].studienjahr_kurzbz}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre', 'semester')}}</th>
							<td align="right" v-if="data.studierendenantrag_id">{{data.semester}}</td>
							<td align="right" v-else>{{semester}}</td>
						</tr>
					</table>
				</div>

				<div class="col-sm-6 mb-3">
					<div v-if="data.studierendenantrag_id">
						<label class="form-label">
							{{$p.t('lehre', 'studiensemester')}}
						</label>
						<div>
							{{data.studiensemester_kurzbz}}
						</div>
					</div>
					<form-input
						v-else
						type="select"
						v-model="stsem"
						name="studiensemester"
						:label="$p.t('lehre', 'studiensemester')"
						required
						@input="currentWiedereinstieg = ''"
						>
						<option v-for="(stsem, index) in data.studiensemester" :key="index" :value="index" :disabled="stsem.disabled">
							{{stsem.studiensemester_kurzbz}}
						</option>
					</form-input>
				</div>
				<div class="col-sm-6 mb-3">
					<div v-if="data.studierendenantrag_id">
						<label class="form-label">
							{{$p.t('studierendenantrag', 'antrag_datum_wiedereinstieg')}}
						</label>
						<div>
							{{datumWsFormatted}}
						</div>
					</div>
					<form-input
						v-else-if="stsem === null"
						type="select"
						:label="$p.t('studierendenantrag', 'antrag_datum_wiedereinstieg')"
						modelValue=""
						name="datum_wiedereinstieg"
						disabled
						>
						<template #default>
							<option value="" selected disabled hidden>{{$p.t('ui/select_studiensemester')}}</option>
						</template>
					</form-input>
					<form-input
						v-else
						type="select"
						:label="$p.t('studierendenantrag', 'antrag_datum_wiedereinstieg')"
						v-model="currentWiedereinstieg"
						name="datum_wiedereinstieg"
						>
						<option v-for="sem in data.studiensemester[stsem].wiedereinstieg" :key="sem.studiensemester_kurzbz" :value="sem.start" :disabled="sem.disabled">
							{{sem.studiensemester_kurzbz}}
						</option>
					</form-input>
				</div>
				<div class="col-sm-6 mb-3">
					<form-input
						v-if="data.studierendenantrag_id"
						type="textarea"
						:label="$p.t('studierendenantrag', 'antrag_grund') + ':'"
						v-model="data.grund"
						name="grund"
						rows="5"
						readonly
						>
					</form-input>
					<form-input
						v-else
						ref="grund"
						type="textarea"
						:label="$p.t('studierendenantrag', 'antrag_grund') + ':'"
						v-model="data.grund"
						name="grund"
						:disabled="saving"
						rows="5"
						required
						>
					</form-input>
				</div>
				<div class="col-12 mb-3">
					<div v-if="data.studierendenantrag_id">
						<a v-if="data.dms_id" target="_blank" :href="siteUrl + '/lehre/Antrag/Attachment/Show/' + data.dms_id"> {{$p.t('studierendenantrag', 'antrag_dateianhaenge')}} </a>
						<span v-else>{{$p.t('studierendenantrag', 'no_attachments')}}</span>
					</div>
					<form-input
						v-else
						ref="attachment"
						type="uploadfile"
						:label="$p.t('studierendenantrag', 'antrag_dateianhaenge')"
						v-model="attachment"
						name="attachment"
						>
					</form-input>
				</div>
				<div class="col-12 text-end">
					<button
						v-if="!data.studierendenantrag_id"
						type="button"
						class="btn btn-primary"
						@click="createAntrag"
						:disabled="saving"
						>
						{{$p.t('studierendenantrag', 'btn_create_Unterbrechung')}}
					</button>
					<button
						v-else-if="data.status == 'Erstellt'"
						type="button"
						class="btn btn-danger"
						@click="cancelAntrag"
						:disabled="saving"
						>
						{{$p.t('studierendenantrag', 'btn_cancel')}}
					</button>
				</div>
			</core-form>
			<template v-slot:error="{errorMessage}">
				<div class="alert alert-danger m-0" role="alert">
					{{ errorMessage }}
				</div>
			</template>
		</core-fetch-cmpt>
	</div>`
}

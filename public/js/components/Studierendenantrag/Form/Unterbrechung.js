import {CoreFetchCmpt} from '../../Fetch.js';
import VueDatepicker from '../../vueDatepicker.js.php';

var _uuid = 0;

export default {
	components: {
		CoreFetchCmpt,
		VueDatepicker
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
			errors: {
				grund: [],
				studiensemester: [],
				datum_wiedereinstieg: [],
				default: []
			},
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
		loadUrl() {
			if (this.studierendenantragId)
				return '/components/Antrag/Unterbrechung/getDetailsForAntrag/'+
				this.studierendenantragId;
			return '/components/Antrag/Unterbrechung/getDetailsForNewAntrag/' +
				this.prestudentId;
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
			return axios.get(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				this.loadUrl
			).then(
				result => {
					this.data = result.data.retval;
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
			for(var k in this.errors)
				this.errors[k] = [];

			var formData = new FormData();
			var attachment = this.$refs.attachment;
			formData.append("attachment", attachment.files[0]);
			formData.append("studiensemester", this.stsem !== null && this.data.studiensemester[this.stsem].studiensemester_kurzbz);
			formData.append("prestudent_id", this.data.prestudent_id);
			formData.append("grund", this.$refs.grund.value);
			formData.append("datum_wiedereinstieg", this.stsem !== null && this.currentWiedereinstieg);

			axios.post(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Unterbrechung/createAntrag/',
				formData,
				{
					headers: {
						'Content-Type': 'multipart/form-data'
					}
				}
			).then(
				result => {
					if (result.data.error)
					{
						for (var k in result.data.retval)
						{
							if (this.errors[k] !== undefined)
								this.errors[k].push(result.data.retval[k]);
							else
								this.errors.default.push(result.data.retval[k]);
						}
						this.$emit('setStatus', {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_error')})),
							severity: 'danger'
						});
					}
					else
					{
						if (Number.isInteger(result.data.retval))
							document.location += "/" + result.data.retval;
						this.data = result.data.retval;
						if (this.data.status) {
							this.$emit("setStatus", {
								msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
								severity: this.statusSeverity
							});
						}
						else
							this.$emit('setStatus', {
								msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_created')})),
								severity: 'info'
							});
					}
					this.saving = false;
				}
			);
		},
		cancelAntrag() {
			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_cancelling')})),
				severity: 'warning'
			});
			this.saving = true;
			for(var k in this.errors)
				this.errors[k] = [];
			axios.post(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Unterbrechung/cancelAntrag/', {
					antrag_id: this.data.studierendenantrag_id
				}
			).then(
				result => {
					if (result.data.error)
					{
						for (var k in result.data.retval)
						{
							if (this.errors[k] !== undefined)
								this.errors[k].push(result.data.retval[k]);
							else
								this.errors.default.push(result.data.retval[k]);
						}
						this.$emit('setStatus', {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_error')})),
							severity: 'danger'
						});
					}
					else
					{
						if (Number.isInteger(result.data.retval)) {
							document.location = document.location.replace(/unterbrechung\/([0-9]*)\/[0-9]*[\/]?$/, 'unterbrechung/$1') +  "/" + result.data.retval;
						}
						this.data = result.data.retval;
						if (this.data.status) {
							this.$emit("setStatus", {
								msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
								severity: this.statusSeverity
							});
						}
						else
							this.$emit('setStatus', {
								msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_cancelled')})),
								severity: 'danger'
							});
					}
					this.saving = false;
				}
			);
		}
	},
	created() {
		this.uuid = _uuid++;
	},
	template: `
	<div class="studierendenantrag-form-unterbrechung">
		<core-fetch-cmpt :api-function="load">
			<div class="row">
				<div class="col-12">
					<div v-for="error in errors.default" class="alert alert-danger" role="alert" v-html="error">
					</div>
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
					<label :for="'studierendenantrag-form-abmeldung-' + uuid + '-stsem'" class="form-label">
						{{$p.t('lehre', 'studiensemester')}}
					</label>
					<div v-if="data.studierendenantrag_id">
						{{data.studiensemester_kurzbz}}
					</div>
					<div v-else>
						<select
							class="form-select"
							:class="{'is-invalid': errors.studiensemester.length}"
							v-model="stsem"
							required
							:id="'studierendenantrag-form-abmeldung-' + uuid + '-stsem'"
							@input="currentWiedereinstieg = ''"
							>
							<option v-for="(stsem, index) in data.studiensemester" :key="index" :value="index" :disabled="stsem.disabled">
								{{stsem.studiensemester_kurzbz}}
							</option>
						</select>
						<div v-if="errors.studiensemester.length" class="invalid-feedback">
							{{errors.studiensemester.join(".")}}
						</div>
					</div>
				</div>
				<div class="col-sm-6 mb-3">
					<label class="form-label">
						{{$p.t('studierendenantrag', 'antrag_datum_wiedereinstieg')}}
					</label>

					<div v-if="data.studierendenantrag_id">
						{{datumWsFormatted}}
					</div>
					<div v-else-if="stsem === null">
						<select class="form-select" disabled>
							<option selected>{{$p.t('ui/select_studiensemester')}}</option>
						</select>
					</div>
					<div v-else>
						<select v-model="currentWiedereinstieg" class="form-select">
							<option v-for="sem in data.studiensemester[stsem].wiedereinstieg" :key="sem.studiensemester_kurzbz" :value="sem.start" :disabled="sem.disabled">
								{{sem.studiensemester_kurzbz}}
							</option>
						</select>
					</div>

					<div v-if="errors.datum_wiedereinstieg.length" class="invalid-feedback d-block">
						{{errors.datum_wiedereinstieg.join(".")}}
					</div>
				</div>
				<div v-if="data.studierendenantrag_id" class="mb-3">
					<h5>{{$p.t('studierendenantrag', 'antrag_grund')}}:</h5>
					<textarea class="form-control" rows="5" readonly>{{data.grund}}</textarea>
				</div>
				<div v-else class="col-sm-6 mb-3">
					<label :for="'studierendenantrag-form-abmeldung-' + uuid + '-grund'" class="form-label">Grund:</label>
					<textarea
						class="form-control"
						:class="{'is-invalid': errors.grund.length}"
						:id="'studierendenantrag-form-abmeldung-' + uuid + '-grund'"
						rows="5"
						:disabled="saving"
						ref="grund"
						required
						></textarea>
					<div v-if="errors.grund.length" class="invalid-feedback">
						{{errors.grund.join(".")}}
					</div>
				</div>
				<div class="col-12 mb-3">

					<div v-if="data.studierendenantrag_id">
						<a v-if="data.dms_id" target="_blank" :href="siteUrl + '/lehre/Antrag/Attachment/Show/' + data.dms_id"> {{$p.t('studierendenantrag', 'antrag_dateianhaenge')}} </a>
						<span v-else>{{$p.t('studierendenantrag', 'no_attachments')}}</span>
					</div>
					<div v-else>
						<label
							:for="'studierendenantrag-form-abmeldung-' + uuid + '-attachment'"
							class="form-label">
							{{$p.t('studierendenantrag', 'antrag_dateianhaenge')}}
						</label>
						<input
							class="form-control"
							type="file"
							ref="attachment"
							:id="'studierendenantrag-form-abmeldung-' + uuid + '-attachment'"
							name="attachment">
					</div>
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
			</div>
			<template v-slot:error="{errorMessage}">
				<div class="alert alert-danger m-0" role="alert">
					{{ errorMessage }}
				</div>
			</template>
		</core-fetch-cmpt>
	</div>
	`
}

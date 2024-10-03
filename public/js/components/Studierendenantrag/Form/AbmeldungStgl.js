import {CoreFetchCmpt} from '../../Fetch.js';
import CoreForm from '../../Form/Form.js';
import FormValidation from '../../Form/Validation.js';
import FormInput from '../../Form/Input.js';

var _uuid = 0;

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
			formData: {
				grund: ''
			},
			unrulyInternal: false
		}
	},
	computed: {
		statusSeverity() {
			switch (this.data?.status)
			{
				case 'Erstellt': return 'info';
				case 'Pause':
				case 'Zurueckgezogen': return 'danger';
				case 'EinspruchAbgelehnt':
				case 'Abgemeldet': return 'success';
				default: return 'warning';
			}
		}
	},
	methods: {
		load() {
			return this.$fhcApi.factory
				.studstatus.abmeldung.getDetails(this.studierendenantragId, this.prestudentId)
				.then(result => {
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
				});
		},
		createAntrag() {
			bootstrap.Modal.getOrCreateInstance(this.$refs.modal).hide();
			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_saving')})),
				severity: 'warning'
			});
			this.saving = true;

			this.$refs.form.clearValidation();
			this.$refs.form.factory
				.studstatus.abmeldung.create(
					this.data.studiensemester_kurzbz,
					this.data.prestudent_id,
					this.formData.grund
				)
				.then(result => {

					if(this.unrulyInternal) {
						this.$fhcApi.factory.checkperson.updatePersonUnrulyStatus(this.data.person_id, true).then(
							(res)=> {
								if(res?.meta?.status === "success") {
									this.$fhcAlert.alertSuccess(this.$p.t('studierendenantrag', 'antrag_unruly_updated'))
								}
							})
					}

					if (result.data === true)
						document.location += "";
					
					this.data = result.data;
					if (this.data.status)
						this.$emit("setStatus", {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
							severity: this.statusSeverity
						});
					else
						this.$emit('setStatus', {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_open')})),
							severity:'success'
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
		appendDropDownText(event) {
			this.formData.grund = event.target.value
				? this.$p.t('studierendenantrag', event.target.value)
				: '';
		}
	},
	created() {
		this.uuid = _uuid++;
	},
	watch: {
		'formData.grund'(newVal) {
			this.unrulyInternal = (newVal === this.$p.t('studierendenantrag', 'textLong_unruly'))
		}
	},
	template: `
	<div class="studierendenantrag-form-abmeldung">
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
							<td align="right">{{data.studienjahr_kurzbz}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre', 'studiensemester')}}</th>
							<td align="right">{{data.studiensemester_kurzbz}}</td>
						</tr>
						<tr>
							<th>{{$p.t('lehre', 'semester')}}</th>
							<td align="right">{{data.semester}}</td>
						</tr>
					</table>
				</div>

				<div v-if="data.grund" class="mb-3">
					<h5>{{$p.t('studierendenantrag', 'antrag_grund')}}:</h5>
				
					<pre class="text-prewrap">{{data?.grund}}</pre>
				</div>      
				<div v-else class="col-sm-6 mb-3">
					<label :for="'studierendenantrag-form-abmeldung-' + uuid + '-grund'" class="form-label">Grund:</label>
					<div class="mb-2">
						<select name="grundAv" class="form-select" @change="appendDropDownText">
							<option value="" > --- bitte auswählen, sofern zutreffend ---- </option>
							<option value="textLong_NichtantrittStudium">{{$p.t('studierendenantrag', 'dropdown_NichtantrittStudium')}}
							</option>
							<option value="textLong_ungenuegendeLeistung">{{$p.t('studierendenantrag', 'dropdown_ungenuegendeLeistung')}}
							</option>
							<option value="textLong_studentNichtAnwesend">{{$p.t('studierendenantrag', 'dropdown_nichtAnwesend')}}
							</option>
							<option value="textLong_PruefunstermineNichtEingehalten">{{$p.t('studierendenantrag', 'dropdown_PruefunstermineNichtEingehalten')}}
							</option>
							<option value="textLong_studentNichtGezahlt">{{$p.t('studierendenantrag', 'dropdown_nichtGezahlt')}}
							</option>
							<option value="textLong_plageat">{{$p.t('studierendenantrag', 'dropdown_plageat')}}
							</option>					
							<option value="textLong_MissingZgv">{{$p.t('studierendenantrag', 'dropdown_MissingZgv')}}
							</option>	
							<option value="textLong_unruly">{{$p.t('studierendenantrag', 'dropdown_unruly')}}
							</option>						
						</select>	
					</div>
					<form-input
						type="textarea"
						v-model="formData.grund"
						name="grund"
						:id="'studierendenantrag-form-abmeldung-' + uuid + '-grund'"
						rows="5"
						:disabled="saving"
						required
						>
					</form-input>
				</div>
				
				<div class="col-12 text-end">
					<button
						v-if="!data?.studierendenantrag_id"
						type="button"
						class="btn btn-primary"
						data-bs-toggle="modal"
						:data-bs-target="'#studierendenantrag-form-abmeldung-' + uuid + '-modal'"
						:disabled="saving"
						>
						{{$p.t('studierendenantrag', 'btn_create_Abmeldung')}}
					</button>
					<div
						ref="modal"
						class="modal fade text-start"
						:id="'studierendenantrag-form-abmeldung-' + uuid + '-modal'"
						tabindex="-1"
						:aria-labelledby="'studierendenantrag-form-abmeldung-' + uuid + '-modal-label'"
						aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h5
										class="modal-title"
										:id="'studierendenantrag-form-abmeldung-' + uuid + '-modal-label'"
										>
										{{$p.t('studierendenantrag', 'title_Abmeldung')}}
									</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" :aria-label="$p.t('ui', 'schliessen')"></button>
								</div>
								<div class="modal-body" v-html="$p.t('studierendenantrag', 'warning_AbmeldungStgl')">
								</div>
								<div class="modal-footer">
									<button
										type="button"
										class="btn btn-primary"
										@click="createAntrag">
										{{$p.t('studierendenantrag', 'btn_create_Abmeldung')}}
									</button>
								</div>
							</div>
						</div>
					</div>
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
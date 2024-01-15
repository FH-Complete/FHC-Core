import {CoreFetchCmpt} from '../../Fetch.js';

var _uuid = 0;

export default {
	components: {
		CoreFetchCmpt
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
				default: []
			}
		}
	},
	computed: {
		statusSeverity() {
			switch (this.data?.status)
			{
				case 'Erstellt': return 'info';
				case 'Genehmigt': return 'success';
				default: return 'info';
			}
		},
		loadUrl() {
			if (this.studierendenantragId)
				return '/components/Antrag/Abmeldung/getDetailsForAntrag/'+
					this.studierendenantragId;
			return '/components/Antrag/Abmeldung/getDetailsForNewAntrag/' +
				this.prestudentId;
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
						this.$emit("setStatus", {
							msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
							severity: this.statusSeverity
						});
					}
					return result;
				}
			);
		},
		createAntrag() {
			bootstrap.Modal.getOrCreateInstance(this.$refs.modal).hide();
			this.$emit('setStatus', {
				msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_saving')})),
				severity: 'warning'
			});
			this.saving = true;
			for(var k in this.errors)
				this.errors[k] = [];
			axios.post(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Abmeldung/createAntrag/', {
					studiensemester: this.data.studiensemester_kurzbz,
					prestudent_id: this.data.prestudent_id,
					grund: this.$refs.grund.value
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
						if (result.data.retval === true)
							document.location += "";
						this.data = result.data.retval;
						if (this.data.status) {
							this.$emit("setStatus", {
								msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.data.statustyp})),
								severity: this.statusSeverity
							});
						}
						else
							this.$emit('setStatus', {
								msg: Vue.computed(() => this.$p.t('studierendenantrag', 'status_x', {status: this.$p.t('studierendenantrag', 'status_open')})),
								severity:'success'
							});
					}
					this.saving = false;
				}
			);
		},
		appendDropDownText(event){
			let templateText = this.$refs.grund;

			if(event.target.value)
			{
				let templateT= this.$p.t('studierendenantrag', event.target.value);
				templateText.value = templateT;
			}
			else
				templateText.value = '';
		},
	},
	created() {
		this.uuid = _uuid++;
	},
	template: `
	<div class="studierendenantrag-form-abmeldung">
		<core-fetch-cmpt :api-function="load">
			<div class="row">
				<div class="col-12">
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
						<select name="grundAv" @change="appendDropDownText
							($event)">
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
						</select>	
					</div>			
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
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

import ApiStvProjektarbeit from '../../../../../api/factory/stv/projektarbeit.js';

export default {
	components: {
		FormForm,
		FormInput,
		PvAutoComplete
	},
	emits: ['projekttypChanged'],
	inject: {
		defaultSemester: {
			from: 'defaultSemester'
		}
		//~ config: {
			//~ from: 'config',
			//~ required: true
		//~ }
	},
	computed: {
		// prepared Lehreinheiten (with compound Bezeichnung)
		arrLes() {
			let lehreinheiten = [];
			if (this.formData.lehrveranstaltung_id) {
				let lv = this.arrLvs.find(lv => {return lv.lehrveranstaltung_id == this.formData.lehrveranstaltung_id});
				if (lv) lehreinheiten = lv.lehreinheiten
			}

			for (let le of lehreinheiten)
			{
				let bezeichnung = le.lehrfach_kurzbz + '-' + le.lehrform_kurzbz + ' ' + le.lehrfach_bezeichnung + ' ';

				for (let grp of le.lehreinheitgruppen)
				{
					bezeichnung += grp.gruppe_kurzbz ? grp.gruppe_kurzbz : '' + grp.semester ?? '' + grp.verband ?? '' + grp.gruppe ?? '';
				}

				bezeichnung += ' (' + le.lektoren.join(' ') + ') ID: ' + le.lehreinheit_id;

				le.bezeichnung = bezeichnung;
			}

			return lehreinheiten;
		}
	},
	props: {
		statusNew: Boolean,
		student: Object,
		projektarbeit: Object
	},
	data() {
		return {
			formData: {
				projektarbeit_id: null,
				titel: null,
				titel_english: null,
				themenbereich: null,
				projekttyp_kurzbz: null,
				firma: null,
				lehrveranstaltung_id: null,
				lehreinheit_id: null,
				beginn: null,
				ende: null,
				freigegeben: true,
				gesperrtbis: null,
				note: null,
				final: true,
				anmerkung: null
			},
			arrTypen: [],
			arrFirmen: [],
			arrLvs: [],
			arrNoten: [],
			filteredFirmen: [],
			abortController: {
				firma: null
			}
		}
	},
	watch: {
		'formData.projekttyp_kurzbz'(newValue, oldValue) {
			this.$emit('projekttypChanged', newValue);
		}
	},
	methods: {
		resetForm() {
			this.formData.projektarbeit_id = null;
			this.formData.titel = null;
			this.formData.titel_english = null;
			this.formData.themenbereich = null;
			this.formData.projekttyp_kurzbz = null;
			this.formData.firma = null;
			this.formData.lehrveranstaltung_id = null;
			this.formData.lehreinheit_id = null;
			this.formData.beginn = null;
			this.formData.ende = null;
			this.formData.freigegeben = true;
			this.formData.gesperrtbis = null;
			this.formData.note = null;
			this.formData.final = true;
			this.formData.anmerkung = null;
			this.$refs.formDetails.clearValidation();
		},
		getFormData(statusNew, studiensemester_kurzbz, additional_lehrveranstaltung_id/*, successCallback*/) {

			//~ let callArray = [
				//~ this.$api.call(ApiStvProjektarbeit.getTypenProjektarbeit()),
				//~ this.$api.call(ApiStvProjektarbeit.getLehrveranstaltungen(
					//~ this.student.uid,
					//~ projektarbeit_id ? null : this.student.studiengang_kz,
					//~ studiensemester_kurzbz ?? this.defaultSemester,
					//~ additional_lehrveranstaltung_id ?? null
				//~ )),
				//~ this.$api.call(ApiStvProjektarbeit.getNoten())
			//~ ];

			//~ if (projektarbeit_id) callArray.push(this.$api.call(ApiStvProjektarbeit.loadProjektarbeit(projektarbeit_id)));

			//~ // Run when All promises are settled
			//~ Promise.allSettled(callArray).then((results) => {
				//~ let hasError = false;
				//~ let allFormData = [];
				//~ results.forEach((promise_result) => {

					//~ if (promise_result.status === 'fulfilled' && promise_result.value.meta.status === "success") {
						//~ allFormData.push(promise_result.value.data);
					//~ } else {
						//~ hasError = true;
						//~ //this.$fhcAlert.handleSystemError(promise_result);
					//~ }
					//~ //let data = promise_result.value.data;
				//~ });

				//~ if (!hasError) {
					//~ this.setFormData(allFormData[0], allFormData[1], allFormData[2], allFormData[3], allFormData[4] ?? null);
					//~ if (successCallback) successCallback();
				//~ }
			//~ });

			this.$api
				.call(ApiStvProjektarbeit.getTypenProjektarbeit())
				.then(result => {
					this.arrTypen = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);

			this.$api
				.call(ApiStvProjektarbeit.getLehrveranstaltungen(
					this.student.uid,
					statusNew ? this.student.studiengang_kz : null,
					studiensemester_kurzbz ?? this.defaultSemester,
					additional_lehrveranstaltung_id
				))
				.then(result => {
						this.arrLvs = result.data
					}
				)
				.catch(this.$fhcAlert.handleSystemError);

			this.$api
				.call(ApiStvProjektarbeit.getNoten())
				.then(result => {
					this.arrNoten = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		loadProjektarbeit(projektarbeit_id) {

			return this.$api
				.call(ApiStvProjektarbeit.loadProjektarbeit(projektarbeit_id))
				.then(result => {
					this.formData = result.data;
					if (this.formData.firma_id) this.formData.firma = {firma_id: this.formData.firma_id, name: this.formData.firma_name};
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError)
		},
		addNewProjektarbeit() {

			let dataToSend = {
				uid: this.student.uid,
				formData: this.getPreparedFormData()
			};

			return this.$refs.formDetails
				.call(ApiStvProjektarbeit.addNewProjektarbeit(dataToSend));
		},
		updateProjektarbeit() {

			let dataToSend = {
				projektarbeit_id: this.formData.projektarbeit_id,
				formData: this.getPreparedFormData()
			};
			return this.$refs.formDetails
				.call(ApiStvProjektarbeit.updateProjektarbeit(dataToSend));
		},
		searchFirma(event) {
			if (this.abortController.firma) {
				this.abortController.firma.abort();
			}
			this.abortController.firma = new AbortController();

			return this.$api
				.call(ApiStvProjektarbeit.getFirmen(event.query))
				.then(result => {
					this.filteredFirmen = result.data;
				});
		},
		lvChanged(event) {
			this.formData.lehreinheit_id = null;
		},
		// enrich and modify data before sending
		getPreparedFormData() {
			let preparedFormData = JSON.parse(JSON.stringify(this.formData)); // deep copy

			// set firma Id
			if (preparedFormData.firma)
				preparedFormData.firma_id = preparedFormData.firma.firma_id;
			else
				preparedFormData.firma_id = null;

			// delete "helper" fields
			if (preparedFormData.projektarbeit_id == null) delete(preparedFormData.projektarbeit_id);
			delete(preparedFormData.firma);
			delete(preparedFormData.firma_name);
			delete(preparedFormData.lehrveranstaltung_id);

			return preparedFormData;
		}
	},
	template: `
			<form-form v-if="!this.student.length" ref="formDetails" @submit.prevent>

				<legend>Details</legend>
				<p v-if="statusNew">[{{$p.t('ui', 'neu')}}]</p>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-titel"
						type="text"
						name="titel"
						:label="$p.t('projektarbeit', 'titel')"
						v-model="formData.titel">
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-titel_english"
						type="text"
						name="titel_english"
						:label="$p.t('projektarbeit', 'titelEnglisch')"
						v-model="formData.titel_english"
						>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-themenbereich"
						type="text"
						name="themenbereich"
						:label="$p.t('projektarbeit', 'themenbereich')"
						v-model="formData.themenbereich"
						>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-typ"
						:label="$p.t('projektarbeit', 'typ')"
						type="select"
						v-model="formData.projekttyp_kurzbz"
						name="projekttyp_kurzbz"
						>
						<option
							v-for="typ in arrTypen"
							:key="typ.projekttyp_kurzbz"
							:value="typ.projekttyp_kurzbz"
							>
							{{typ.bezeichnung}}
						</option>
					</form-input>
				</div>


				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-firma"
						:label="$p.t('projektarbeit', 'firma')"
						type="autocomplete"
						optionLabel="name"
						v-model="formData.firma"
						name="firma"
						:suggestions="filteredFirmen"
						@complete="searchFirma"
						:min-length="3"
						>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-lv"
						:label="$p.t('projektarbeit', 'lehrveranstaltung')"
						type="select"
						v-model="formData.lehrveranstaltung_id"
						name="lehrveranstaltung_id"
						@change="lvChanged($event)"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="lv in arrLvs"
							:key="lv.lehrveranstaltung_id"
							:value="lv.lehrveranstaltung_id"
							>
							{{lv.bezeichnung + ' ' + lv.orgform_kurzbz + ' (' + lv.semester + ' Sem) ID: ' + lv.lehrveranstaltung_id}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="stv-details-projektarbeit-le"
						:label="$p.t('projektarbeit', 'lvTeil')"
						type="select"
						v-model="formData.lehreinheit_id"
						name="lehreinheit_id"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="le in arrLes"
							:key="le.lehreinheit_id"
							:value="le.lehreinheit_id"
							>
							{{le.bezeichnung}}
						</option>
					</form-input>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-6 stv-details-projektarbeit-beginn"
						:label="$p.t('projektarbeit', 'beginn')"
						type="DatePicker"
						v-model="formData.beginn"
						auto-apply
						:enable-time-picker="false"
						text-input
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd"
						name="beginn"
						>
					</form-input>
					<form-input
						container-class="col-6 stv-details-projektarbeit-ende"
						:label="$p.t('projektarbeit', 'ende')"
						type="DatePicker"
						v-model="formData.ende"
						auto-apply
						:enable-time-picker="false"
						text-input
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd"
						name="ende"
						>
					</form-input>
				</div>

				<div class="row mb-3 align-items-center">
					<form-input
						container-class="col-8 stv-details-projektarbeit-gesperrtbis"
						:label="$p.t('projektarbeit', 'gesperrtBis')"
						type="DatePicker"
						v-model="formData.gesperrtbis"
						auto-apply
						:enable-time-picker="false"
						text-input
						locale="de"
						format="dd.MM.yyyy"
						model-type="yyyy-MM-dd"
						name="gesperrtbis"
						>
					</form-input>
					<div class="col-4">
						<form-input
							container-class="form-check stv-details-projektarbeit-freigegeben"
							type="checkbox"
							name="freigegeben"
							:label="$p.t('projektarbeit','freigegeben')"
							v-model="formData.freigegeben"
						>
						</form-input>
					</div>
				</div>

				<div class="row mb-3 align-items-center">
					<form-input
						container-class="col-8 stv-details-projektarbeit-note"
						:label="$p.t('projektarbeit', 'note')"
						type="select"
						v-model="formData.note"
						name="note"
						>
						<option :value="null"> -- {{$p.t('fehlermonitoring', 'keineAuswahl')}} -- </option>
						<option
							v-for="note in arrNoten"
							:key="note.note"
							:value="note.note"
							>
							{{note.bezeichnung}}
						</option>
					</form-input>
					<div class="col-4">
						<form-input
							container-class="form-check stv-details-projektarbeit-final"
							type="checkbox"
							name="final"
							label="final"
							v-model="formData.final"
						>
						</form-input>
					</div>
				</div>

				<div class="row mb-3">
					<form-input
						container-class="col-12 stv-details-abschlusspruefung-anmerkung"
						:label="$p.t('projektarbeit', 'anmerkung')"
						type="textarea"
						v-model="formData.anmerkung"
						name="anmerkung"
						:rows= 3
						>
					</form-input>
				</div>

			</form-form>`
}

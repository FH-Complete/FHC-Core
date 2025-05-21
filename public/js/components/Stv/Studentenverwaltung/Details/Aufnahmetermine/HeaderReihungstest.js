import FormForm from "../../../../Form/Form.js";
import FormInput from "../../../../Form/Input.js";
import ApiStvAdmissionDates from '../../../../../api/factory/stv/admissionDates';

export default {
	name: 'HeaderPlacement',
	components: {
		FormForm,
		FormInput
	},
	inject: {
		showAufnahmegruppen: {
			from: 'configShowAufnahmegruppen',
			default: false
		},
		currentSemester: {
			from: 'currentSemester',
		},
	},
	props: {
		student: Object
	},
	data(){
		return {
			statusNew: true,
			formData: {
				aufnahmegruppe_kurzbz: null
			},
			listAufnahmetermine: [],
			listAufnahmegruppen: []
		}
	},
	methods: {
		saveDataRt(prestudent_id) {
			const dataToSend = {
				prestudent_id: prestudent_id,
				formData: this.formData
			};
			return this.$api
				.call(ApiStvAdmissionDates.saveDataRtPrestudent(dataToSend))
				.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		calculateTotalPoints() {
			return this.$api
				.call(ApiStvAdmissionDates.getAufnahmetermine(this.student.person_id))
				.then(result => {
					this.listAufnahmetermine = result.data;

					const listAufnahmetermineFiltered = this.listAufnahmetermine
						.filter(item => item.studiengangkurzbzlang == this.student.studiengang)
						.sort((a, b) => this.parseSemester(b.studiensemester) - this.parseSemester(a.studiensemester));
					const elementSemYoungest = listAufnahmetermineFiltered[0];

					this.formData.rt_gesamtpunkte = elementSemYoungest.punkte;

				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		parseSemester(semester) {
			const type = semester.slice(0, 2).toUpperCase(); // "WS" or "SS"
			const year = parseInt(semester.slice(2), 10);

			// WS > SS
			return year * 10 + (type === 'SS' ? 1 : 2);
		}
	},
	created(){
		this.$api
			.call(ApiStvAdmissionDates.loadDataRtPrestudent(this.student.prestudent_id))
			.then(result => {
				this.formData = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		if(this.showAufnahmegruppen)
		{
			const paramsGroup = {
				uid: this.student.uid,
				semester: this.currentSemester
			};
			this.$api
				.call(ApiStvAdmissionDates.loadAufnahmegruppen(paramsGroup))
				.then(result => {
					this.listAufnahmegruppen = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	template: `
		<div class="stv-details-admission-header-placement h-100 pb-3">
			<h4>{{ $p.t('lehre', 'studiengang') }}</h4>
						
			<form-form class="mt-3" ref="formRtGesamtData" @submit.prevent>
				<div v-if="showAufnahmegruppen" class="row mb-3">
					<div class="col-1">
						<label>{{ $p.t('lehre', 'gruppe') }}</label>		
					</div>
					<div class="col-3">
						<form-input
							container-class="stv-details-admission-header-placement-aufnahmegruppe"
							type="select"
							name="aufnahmegruppe_kurzbz"
							v-model="formData.aufnahmegruppe_kurzbz"
						>
						<option value=null> -- {{ $p.t('fehlermonitoring', 'keineAuswahl') }} --</option>
						<option
							v-for="gruppe in listAufnahmegruppen"
							:key="gruppe.gruppe_kurzbz"
							:value="gruppe.gruppe_kurzbz"
							>
							{{gruppe.bezeichnung}} - {{gruppe.gruppe_kurzbz}}
						</option>
						
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">

					<div class="col-3">
						<form-input
							container-class="form-check stv-details-admission-header-placement-rtangetreten"
							type="checkbox"
							name="reihungstestangetreten"
							:label="$p.t('admission','rtAbsolviert')"
							v-model="formData.reihungstestangetreten"
						>
						</form-input>	
					</div>
					<div class="col-2">
						<label>{{ $p.t('admission', 'gesamtpunkte') }}</label>		
					</div>
					<div class="col-2">
						<form-input
							container-class="stv-details-admission-header-placement-gesamtpunkte"
							type="text"
							name="rt_gesamtpunkte"
							v-model="formData.rt_gesamtpunkte"
						>
						</form-input>
					</div>
					<div class="col-1">
						<button class="btn btn-outline-primary" @click="saveDataRt(student.prestudent_id)"> {{$p.t('ui', 'speichern')}}</button>		
					</div>
					<div class="col-3">
						<button class="btn btn-outline-primary" @click="calculateTotalPoints"> {{$p.t('admission', 'gesamtpunkteBerechnen')}}</button>			
					</div>
							
				</div>
			</form-form>
		
		</div>
	`
}
import {CoreRESTClient} from '../../../../RESTClient.js';
import BsModal from '../../../Bootstrap/Modal.js';
import FhcFormValidation from '../../../Form/Validation.js';
import VueDatePicker from '../../../vueDatepicker.js.php';
import accessibility from '../../../../directives/accessibility.js';

var _uuid = 0;
const FORMDATA_DEFAULT = {
	address: {
		func: 0,
		nation: 'A'
	},
	geburtsnation: 'A',
	staatsbuergerschaft: 'A',
	ausbildungssemester: 1,
	orgform_kurzbz: '',
	studienplan_id: ''
};

export default {
	components: {
		BsModal,
		FhcFormValidation,
		VueDatePicker
	},
	directives: {
		accessibility
	},
	inject: [
		'lists'
	],
	props: {
		studiengangKz: Number,
		studiensemesterKurzbz: String
	},
	data() {
		return {
			places: [],
			formData: FORMDATA_DEFAULT,
			suggestions: {},
			person: null,
			semester: [],
			studienplaene: [],
			abortController: {
				suggestions: null,
				places: null
			}
		}
	},
	computed: {
		formDataPerson() {
			if (this.person)
				return this.person;
			return this.formData;
		},
		orte() {
			return this.places.filter(ort => ort.name == this.formData.address.gemeinde);
		},
		gemeinden() {
			return Object.values(this.places.reduce((res,place) => {
				res[place.name] = place;
				return res;
			}, {}));
		},
		formDataStg: {
			get() {
				return this.formData.studiengang_kz !== undefined ? this.formData.studiengang_kz : this.studiengangKz;
			},
			set(v) {
				this.formData.studiengang_kz = v;
			}
		},
		formDataSem: {
			get() {
				return this.formData.studiensemester_kurzbz !== undefined ? this.formData.studiensemester_kurzbz : this.studiensemesterKurzbz;
			},
			set(v) {
				this.formData.studiensemester_kurzbz = v;
			}
		}
	},
	watch: {
		formDataStg() {
			this.loadStudienplaene();
		},
		formDataSem() {
			this.loadStudienplaene();
		}
	},
	methods: {
		open() {
			this.$refs.modal.show();
		},
		reset() {
			this.formData = FORMDATA_DEFAULT;
			this.person = null;
			this.suggestions = [];
			this.$fhcAlert.resetFormValidation(this.$refs.form)
		},
		loadSuggestions() {
			if (this.abortController.suggestions)
				this.abortController.suggestions.abort();
			if (this.person !== null)
				return;

			this.abortController.suggestions = new AbortController();
			CoreRESTClient
				.post('components/stv/student/check', {
					vorname: this.formData.vorname,
					nachname: this.formData.nachname,
					gebdatum: this.formData.gebdatum
				}, {
					signal: this.abortController.suggestions.signal
				})
				.then(result => CoreRESTClient.getData(result.data) || [])
				.then(result => {
					this.suggestions = result;
				})
				.catch(error => {
					// NOTE(chris): repeat request
					if (error.code != "ERR_CANCELED")
						window.setTimeout(this.loadSuggestions, 100);
				});
		},
		loadPlaces() {
			if (this.abortController.places)
				this.abortController.places.abort();
			if (this.formData.address.nation != 'A' || !this.formData.address.plz)
				return;

			this.abortController.places = new AbortController();
			CoreRESTClient
				.get('components/stv/address/getPlaces/' + this.formData.address.plz, undefined, {
					signal: this.abortController.places.signal
				})
				.then(result => CoreRESTClient.getData(result.data) || [])
				.then(result => {
					this.places = result;
				})
				.catch(error => {
					if (error.code == 'ERR_BAD_REQUEST') {
						return this.$fhcAlert.handleFormValidation(error, this.$refs.form);
					}
					// NOTE(chris): repeat request
					if (error.code != "ERR_CANCELED")
						window.setTimeout(this.loadPlaces, 100);
				});
		},
		loadStudienplaene() {
			CoreRESTClient
				.post('components/stv/studienplan/get', {
					studiengang_kz: this.formDataStg,
					studiensemester_kurzbz: this.formDataSem,
					ausbildungssemester: this.formData.ausbildungssemester,
					orgform_kurzbz: this.formData.orgform_kurzbz
				})
				.then(result => CoreRESTClient.getData(result.data) || [])
				.then(result => {
					this.studienplaene = result;
					if (this.formData.studienplan_id !== '' && !this.studienplaene.filter(plan => plan.studienplan_id == this.formData.studienplan_id).length)
						this.formData.studienplan_id = '';
				})
				.catch(error => {
					if (error.code == 'ERR_BAD_REQUEST') {
						return this.studienplaene = [];
					}
					// NOTE(chris): repeat request
					if (error.code != "ERR_CANCELED")
						window.setTimeout(this.loadStudienplaene, 100);
				})
		},
		changeAddressNation(e) {
			if (this.formData['geburtsnation'] == this.formData['address']['nation'])
				this.formData['geburtsnation'] = e.target.value;
			if (this.formData['staatsbuergerschaft'] == this.formData['address']['nation'])
				this.formData['staatsbuergerschaft'] = e.target.value;
			this.loadPlaces();
		},
		send(e) {
			if (this.person === null)
				return this.person = 0;

			this.$fhcAlert.resetFormValidation(this.$refs.form);
			const data = {...this.formData, ...(this.person || {})};
			if (data.studiengang_kz === undefined)
				data.studiengang_kz = this.studiengangKz;
			if (data.studiensemester_kurzbz === undefined)
				data.studiensemester_kurzbz = this.studiensemesterKurzbz;

			CoreRESTClient
				.post('components/stv/student/add', data)
				.then(result => result.data)
				.then(result => {
					if (CoreRESTClient.isError(result))
						throw new Error(CoreRESTClient.getError(result));
					return CoreRESTClient.getData(result);
				})
				.then(result => {
					this.$fhcAlert.alertSuccess('Gespeichert');
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleFormValidation(this.$refs.form));
		}
	},
	created() {
		this.uuid = _uuid++;
		CoreRESTClient
			.get('components/stv/Studiensemester')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.semester = result;
			})
			.catch(this.$fhcAlert.handleSystemError);

	},
	template: `
	<form ref="form" class="stv-list-new" @submit.prevent="send">
		<bs-modal ref="modal" dialog-class="modal-lg modal-scrollable" @hidden-bs-modal="reset">
			<template #title>
				InteressentIn anlegen
			</template>
			<template #default>
				<fhc-form-validation></fhc-form-validation>
				<template v-if="person === null">
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-nachname-' + uuid">Nachname*</label>
							<fhc-form-validation name="nachname">
								<input :id="'stv-list-new-nachname-' + uuid" type="text" name="nachname" v-model="formDataPerson['nachname']" class="form-control" :disabled="person" @input="loadSuggestions">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-vorname-' + uuid">Vorname</label>
							<fhc-form-validation name="vorname">
								<input :id="'stv-list-new-vorname-' + uuid" type="text" name="vorname" v-model="formDataPerson['vorname']" class="form-control" :disabled="person" @input="loadSuggestions">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'dp-input-stv-list-new-gebdatum-' + uuid">Geburtsdatum</label>
							<fhc-form-validation name="gebdatum">
								<vue-date-picker :uid="'stv-list-new-gebdatum-' + uuid" name="gebdatum" text-input auto-apply no-today v-model="formDataPerson['gebdatum']" :enable-time-picker="false" format="dd.MM.yyyy" @update:model-value="loadSuggestions" :disabled="person"></vue-date-picker>
							</fhc-form-validation>
						</div>
					</div>
					<!-- TODO(chris): more details -->
					<table class="table caption-top table-striped table-hover">
						<caption>Prüfung ob Person bereits existiert</caption>
						<tbody>
							<tr
								v-for="(suggestion, index) in suggestions"
								:key="suggestion.person_id"
								:class="{'active': index == 2}"
								@click="(index == 2) ? suggestions.shift() : person=suggestion"
								v-accessibility:tab.vertical
								>
								<td>{{suggestion.vorname + ' ' + suggestion.nachname}}</td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</template>
				<tempalte v-else>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-anrede-' + uuid">Anrede</label>
							<fhc-form-validation name="anrede">
								<input :id="'stv-list-new-anrede-' + uuid" type="text" name="anrede" v-model="formDataPerson['anrede']" class="form-control" :disabled="person">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-titelpre-' + uuid">Titel (Pre)</label>
							<fhc-form-validation name="titelpre">
								<input :id="'stv-list-new-titelpre-' + uuid" type="text" name="titelpre" v-model="formDataPerson['titelpre']" class="form-control" :disabled="person">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-titelpost-' + uuid">Titel (Post)</label>
							<fhc-form-validation name="titelpost">
								<input :id="'stv-list-new-titelpost-' + uuid" type="text" name="titelpost" v-model="formDataPerson['titelpost']" class="form-control" :disabled="person">
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-nachname-' + uuid">Nachname*</label>
							<fhc-form-validation name="nachname">
								<input :id="'stv-list-new-nachname-' + uuid" type="text" name="nachname" v-model="formDataPerson['nachname']" class="form-control" :disabled="person" @input="loadSuggestions">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-vorname-' + uuid">Vorname</label>
							<fhc-form-validation name="vorname">
								<input :id="'stv-list-new-vorname-' + uuid" type="text" name="vorname" v-model="formDataPerson['vorname']" class="form-control" :disabled="person" @input="loadSuggestions">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-vornamen-' + uuid">Weitere Vornamen</label>
							<fhc-form-validation name="vornamen">
								<input :id="'stv-list-new-vornamen-' + uuid" type="text" name="vornamen" v-model="formDataPerson['vornamen']" class="form-control" :disabled="person">
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-wahlname-' + uuid">Wahlname</label>
							<fhc-form-validation name="wahlname">
								<input :id="'stv-list-new-wahlname-' + uuid" type="text" name="wahlname" v-model="formDataPerson['wahlname']" class="form-control" :disabled="person">
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-geschlecht-' + uuid">Geschlecht*</label>
							<fhc-form-validation name="geschlecht">
								<select :id="'stv-list-new-geschlecht-' + uuid" class="form-control" class="form-select" :disabled="person" name="geschlecht" v-model="formDataPerson['geschlecht']">
									<option v-for="geschlecht in lists.geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
								</select>
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'dp-input-stv-list-new-gebdatum-' + uuid">Geburtsdatum</label>
							<fhc-form-validation name="gebdatum">
								<vue-date-picker :uid="'stv-list-new-gebdatum-' + uuid" name="gebdatum" text-input auto-apply no-today v-model="formDataPerson['gebdatum']" :enable-time-picker="false" format="dd.MM.yyyy" @update:model-value="loadSuggestions" :disabled="person"></vue-date-picker>
							</fhc-form-validation>
						</div>
					</div>
					
					<div v-if="person" class="row">
						<div class="col-sm-6 mb-3">
							<fhc-form-validation name="address[func]">
								<select :id="'stv-list-new-address-func-' + uuid" name="address[func]" class="form-select" v-model="formData['address']['func']">
									<option value="-1">Bestehende Adresse überschreiben</option>
									<option value="1">Adresse hinzufügen</option>
									<option value="0">Adresse nicht anlegen</option>
								</select>
							</fhc-form-validation>
						</div>
					</div>
					
					<fieldset v-if="!person || formData['address']['func']">
						<legend>Adresse</legend>
						<div class="row">
							<div class="col-sm-4 mb-3">
								<label :for="'dp-input-stv-list-new-address-nation-' + uuid">Land</label>
								<fhc-form-validation name="address.nation">
									<select :id="'stv-list-new-address-nation' + uuid" name="address[nation]" class="form-select" v-model="formData['address']['nation']" @input="changeAddressNation">
										<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.langtext}}</option>
									</select>
								</fhc-form-validation>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4 mb-3">
								<label :for="'dp-input-stv-list-new-address-plz-' + uuid">PLZ</label>
								<fhc-form-validation name="address[plz]">
									<input type="text" :id="'stv-list-new-address-plz' + uuid" name="address[plz]" v-model="formData['address']['plz']" @input="loadPlaces">
								</fhc-form-validation>
							</div>
							<div class="col-sm-4 mb-3">
								<label :for="'dp-input-stv-list-new-address-gemeinde-' + uuid">Gemeinde</label>
								<fhc-form-validation name="address[gemeinde]">
									<select v-if="formData['address']['nation'] == 'A'" :id="'stv-list-new-address-gemeinde' + uuid" name="address[gemeinde]" class="form-select" v-model="formData['address']['gemeinde']">
										<option v-if="!gemeinden.length" disabled>Bitte gültige PLZ wählen</option>
										<option v-for="gemeinde in gemeinden" :key="gemeinde.name" :value="gemeinde.name">{{gemeinde.name}}</option>
									</select>
									<input v-else type="text" :id="'stv-list-new-address-gemeinde' + uuid" name="address[gemeinde]" class="form-control" v-model="formData['address']['gemeinde']">
								</fhc-form-validation>
							</div>
							<div class="col-sm-4 mb-3">
								<label :for="'dp-input-stv-list-new-address-ort-' + uuid">Ort</label>
								<fhc-form-validation name="address[ort]">
									<select v-if="formData['address']['nation'] == 'A'" :id="'stv-list-new-address-ort' + uuid" name="address[ort]" class="form-select" v-model="formData['address']['ort']">
										<option v-if="!orte.length" disabled>Bitte gültige Gemeinde wählen</option>
										<option v-for="ort in orte" :key="ort.ortschaftsname" :value="ort.ortschaftsname">{{ort.ortschaftsname}}</option>
									</select>
									<input v-else type="text" :id="'stv-list-new-address-ort' + uuid" name="address[ort]" class="form-control" v-model="formData['address']['ort']">
								</fhc-form-validation>
							</div>
						</div>
						<div class="row">
							<div class="col-12 mb-3">
								<label :for="'dp-input-stv-list-new-address-address-' + uuid">Adresse</label>
								<fhc-form-validation name="address[address]">
									<input type="text" :id="'stv-list-new-address-address' + uuid" name="address[address]" v-model="formData['address']['address']">
								</fhc-form-validation>
							</div>
						</div>
					</fieldset>

					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'dp-input-stv-list-new-geburtsnation-' + uuid">Geburtsnation</label>
							<fhc-form-validation name="geburtsnation">
								<select :id="'stv-list-new-geburtsnation' + uuid" name="geburtsnation" class="form-select" v-model="formData['geburtsnation']">
									<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.langtext}}</option>
								</select>
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'dp-input-stv-list-new-staatsbuergerschaft-' + uuid">Staatsbürgerschaft</label>
							<fhc-form-validation name="staatsbuergerschaft">
								<select :id="'stv-list-new-staatsbuergerschaft' + uuid" name="staatsbuergerschaft" class="form-select" v-model="formData['staatsbuergerschaft']">
									<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.langtext}}</option>
								</select>
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-email-' + uuid">E-Mail</label>
							<fhc-form-validation name="email">
								<input :id="'stv-list-new-email-' + uuid" type="text" name="email" v-model="formDataPerson['email']" class="form-control">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-telefon-' + uuid">Telefon</label>
							<fhc-form-validation name="telefon">
								<input :id="'stv-list-new-telefon-' + uuid" type="text" name="telefon" v-model="formDataPerson['telefon']" class="form-control">
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-mobil-' + uuid">Mobil</label>
							<fhc-form-validation name="mobil">
								<input :id="'stv-list-new-mobil-' + uuid" type="text" name="mobil" v-model="formDataPerson['mobil']" class="form-control">
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-letzteausbildung-' + uuid">Letzte Ausbildung</label>
							<fhc-form-validation name="letzteausbildung">
								<select :id="'stv-list-new-letzteausbildung' + uuid" name="letzteausbildung" class="form-select" v-model="formData['letzteausbildung']">
									<option v-for="ausbildung in lists.ausbildungen" :key="ausbildung.ausbildungcode" :value="ausbildung.ausbildungcode">{{ausbildung.ausbildungbez}}</option>
								</select>
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-ausbildungsart-' + uuid">Ausbildungsart</label>
							<fhc-form-validation name="ausbildungsart">
								<input :id="'stv-list-new-ausbildungsart-' + uuid" type="text" name="ausbildungsart" v-model="formDataPerson['ausbildungsart']" class="form-control">
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-8 mb-3">
							<label :for="'stv-list-new-anmerkungen-' + uuid">Anmerkungen</label>
							<fhc-form-validation name="anmerkungen">
								<textarea :id="'stv-list-new-anmerkungen-' + uuid" name="anmerkungen" v-model="formDataPerson['anmerkungen']" class="form-control"></textarea>
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-studiengang_kz-' + uuid">Studiengang*</label>
							<fhc-form-validation name="studiengang_kz">
								<select :id="'stv-list-new-studiengang_kz-' + uuid" name="studiengang_kz" v-model="formDataStg" class="form-select">
									<option v-for="stg in lists.stgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">{{stg.kuerzel}}</option>
								</select>
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-studiensemester_kurzbz-' + uuid">Studiensemester*</label>
							<fhc-form-validation name="studiensemester_kurzbz">
								<select :id="'stv-list-new-studiensemester_kurzbz-' + uuid" name="studiensemester_kurzbz" v-model="formDataSem" class="form-select">
									<option v-for="sem in semester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">{{sem.studiensemester_kurzbz}}</option>
								</select>
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-ausbildungssemester-' + uuid">Ausbildungssemester*</label>
							<fhc-form-validation name="ausbildungssemester">
								<select :id="'stv-list-new-ausbildungssemester-' + uuid" name="ausbildungssemester" v-model="formData['ausbildungssemester']" class="form-select" @input="loadStudienplaene" :disabled="formData['incoming']">
									<option v-for="sem in Array.from({length:8}).map((u,i) => i+1)" :key="sem" :value="sem">{{sem}}. Semester</option>
								</select>
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-orgform_kurzbz-' + uuid">OrgForm</label>
							<fhc-form-validation name="orgform_kurzbz">
								<select :id="'stv-list-new-orgform_kurzbz-' + uuid" name="orgform_kurzbz" v-model="formData['orgform_kurzbz']" class="form-select" @input="loadStudienplaene">
									<option value="">-- keine Auswahl --</option>
									<option v-for="orgform in lists.orgforms" :key="orgform.orgform_kurzbz" :value="orgform.orgform_kurzbz">{{orgform.bezeichnung}}</option>
								</select>
							</fhc-form-validation>
						</div>
						<div class="col-sm-4 mb-3">
							<label :for="'stv-list-new-studienplan_id-' + uuid">Studienplan</label>
							<fhc-form-validation name="studienplan_id">
								<select :id="'stv-list-new-studienplan_id-' + uuid" name="studienplan_id" v-model="formData['studienplan_id']" class="form-select">
									<option value="">-- keine Auswahl --</option>
									<option v-for="plan in studienplaene" :key="plan.studienplan_id" :value="plan.studienplan_id">{{plan.bezeichnung}}</option>
								</select>
							</fhc-form-validation>
						</div>
					</div>
					<div class="row">
						<div class="col-10 mb-3">
							<div class="form-check">
								<fhc-form-validation name="incoming">
									<input type="checkbox" :id="'stv-list-new-incoming-' + uuid" name="incoming" v-model="formData['incoming']" class="form-check-input" value="1">
								</fhc-form-validation>
								<label :for="'stv-list-new-incoming-' + uuid" class="form-check-label">Incoming</label>
							</div>
						</div>
					</div>

				</template>
			</template>
			<template #footer>
				<button v-if="person !== null" type="button" class="btn btn-secondary" @click="person = null"><i class="fa fa-chevron-left"></i>Zurück</button>
				<button type="submit" class="btn btn-primary">{{ person === null ? 'Person anlegen' : 'InteressentIn anlegen' }}</button>
			</template>
		</bs-modal>
	</form>`
};
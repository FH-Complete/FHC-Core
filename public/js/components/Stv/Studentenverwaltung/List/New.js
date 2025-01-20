import {CoreRESTClient} from '../../../../RESTClient.js';
import BsModal from '../../../Bootstrap/Modal.js';
import FhcForm from '../../../Form/Form.js';
import FormValidation from '../../../Form/Validation.js';
import FormInput from '../../../Form/Input.js';
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
		FhcForm,
		FormValidation,
		FormInput
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
			this.$refs.form.clearValidation();
		},
		loadSuggestions() {
			if (this.abortController.suggestions)
				this.abortController.suggestions.abort();
			if (this.person !== null)
				return;

			this.abortController.suggestions = new AbortController();
			// TODO(chris): move to fhcapi.factory
			this.$fhcApi
				.post('api/frontend/v1/stv/student/check', {
					vorname: this.formData.vorname,
					nachname: this.formData.nachname,
					gebdatum: this.formData.gebdatum
				}, {
					signal: this.abortController.suggestions.signal
				})
				.then(result => this.suggestions = result.data)
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
			this.$refs.form
				.get(
					'api/frontend/v1/stv/address/getPlaces/' + this.formData.address.plz,
					undefined,
					{
						signal: this.abortController.places.signal
					}
				)
				.then(result => {
					this.places = result.data
				})
				.catch(error => {
					if (error.code != "ERR_CANCELED")
						window.setTimeout(this.loadPlaces, 100);
					else
						this.$fhcAlert.handleSystemError(error);
				});
		},
		loadStudienplaene() {
			if (this.formDataStg)
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

			//this.$fhcAlert.resetFormValidation(this.$refs.form);
			const data = {...this.formData, ...(this.person || {})};
			if (data.studiengang_kz === undefined)
				data.studiengang_kz = this.studiengangKz;
			if (data.studiensemester_kurzbz === undefined)
				data.studiensemester_kurzbz = this.studiensemesterKurzbz;

			// TODO(chris): move to fhcapi.factory
			this.$refs.form
				.send('api/frontend/v1/stv/student/add', data)
				.then(result => {
					this.$fhcAlert.alertSuccess('Gespeichert');
					this.$refs.modal.hide();
				})
				.catch(this.$fhcAlert.handleSystemError);
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
	<fhc-form ref="form" class="stv-list-new" @submit.prevent="send">
		<bs-modal ref="modal" dialog-class="modal-lg modal-scrollable" @hidden-bs-modal="reset">
			<template #title>
				InteressentIn anlegen
			</template>
			<template #default>

				<form-validation></form-validation>

				<template v-if="person === null">
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Nachname*"
								type="text"
								id="stv-list-new-nachname"
								name="nachname"
								v-model="formDataPerson['nachname']"
								:disabled="person"
								@input="loadSuggestions"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Vorname"
								type="text"
								:id="'stv-list-new-vorname-' + uuid"
								name="vorname"
								v-model="formDataPerson['vorname']"
								:disabled="person"
								@input="loadSuggestions"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Geburtsdatum"
								type="datepicker"
								uid="stv-list-new-gebdatum"
								name="gebdatum"
								v-model="formDataPerson['gebdatum']"
								:disabled="person"
								@update:model-value="loadSuggestions"
								text-input
								auto-apply
								no-today 
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								>
							</form-input>
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
				<template v-else>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Anrede"
								type="text"
								id="stv-list-new-anrede"
								name="anrede"
								v-model="formDataPerson['anrede']"
								:disabled="person"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Titel (Pre)"
								type="text"
								id="stv-list-new-titelpre"
								name="titelpre"
								v-model="formDataPerson['titelpre']"
								:disabled="person"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Titel (Post)"
								type="text"
								id="stv-list-new-titelpost"
								name="titelpost"
								v-model="formDataPerson['titelpost']"
								:disabled="person"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Nachname*"
								type="text"
								id="stv-list-new-nachname"
								name="nachname"
								v-model="formDataPerson['nachname']"
								:disabled="person"
								@input="loadSuggestions"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Vorname"
								type="text"
								id="stv-list-new-vorname"
								name="vorname"
								v-model="formDataPerson['vorname']"
								:disabled="person"
								@input="loadSuggestions"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Weitere Vornamen"
								type="text"
								id="stv-list-new-vornamen"
								name="vornamen"
								v-model="formDataPerson['vornamen']"
								:disabled="person"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Wahlname"
								type="text"
								id="stv-list-new-wahlname"
								name="wahlname"
								v-model="formDataPerson['wahlname']"
								:disabled="person"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Geschlecht*"
								type="select"
								id="stv-list-new-geschlecht"
								name="geschlecht"
								v-model="formDataPerson['geschlecht']"
								:disabled="person"
								>
								<option v-for="geschlecht in lists.geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Geburtsdatum"
								type="datepicker"
								uid="stv-list-new-gebdatum"
								name="gebdatum"
								v-model="formDataPerson['gebdatum']"
								:disabled="person"
								@update:model-value="loadSuggestions"
								text-input
								auto-apply
								no-today
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								>
							</form-input>
						</div>
					</div>
					
					<div v-if="person" class="row">
						<div class="col-sm-6 mb-3">
							<form-input
								type="select"
								id="stv-list-new-address-func"
								name="address[func]"
								v-model="formData['address']['func']"
								>
								<option value="-1">Bestehende Adresse überschreiben</option>
								<option value="1">Adresse hinzufügen</option>
								<option value="0">Adresse nicht anlegen</option>
							</form-input>
						</div>
					</div>
					
					<fieldset v-if="!person || formData['address']['func']">
						<legend>Adresse</legend>
						<div class="row">
							<div class="col-sm-4 mb-3">
								<form-input
									label="Land"
									type="select"
									id="stv-list-new-address-nation"
									name="address[nation]"
									v-model="formData['address']['nation']"
									@input="changeAddressNation"
									>
									<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.langtext}}</option>
								</form-input>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4 mb-3">
								<form-input
									label="PLZ"
									type="text"
									id="stv-list-new-address-plz"
									name="address[plz]"
									v-model="formData['address']['plz']"
									@input="loadPlaces"
									>
								</form-input>
							</div>
							<div class="col-sm-4 mb-3">
								<form-input
									label="Gemeinde"
									type="select"
									v-if="formData['address']['nation'] == 'A'"
									id="stv-list-new-address-gemeinde"
									name="address[gemeinde]"
									v-model="formData['address']['gemeinde']"
									>
									<option v-if="!gemeinden.length" disabled>Bitte gültige PLZ wählen</option>
									<option v-for="gemeinde in gemeinden" :key="gemeinde.name" :value="gemeinde.name">{{gemeinde.name}}</option>
								</form-input>
								<form-input
									label="Gemeinde"
									type="text"
									v-else
									id="stv-list-new-address-gemeinde"
									name="address[gemeinde]"
									v-model="formData['address']['gemeinde']"
									>
								</form-input>
							</div>
							<div class="col-sm-4 mb-3">
								<form-input
									label="Ort"
									type="select"
									v-if="formData['address']['nation'] == 'A'"
									id="stv-list-new-address-ort"
									name="address[ort]"
									v-model="formData['address']['ort']"
									>
									<option v-if="!orte.length" disabled>Bitte gültige Gemeinde wählen</option>
									<option v-for="ort in orte" :key="ort.ortschaftsname" :value="ort.ortschaftsname">{{ort.ortschaftsname}}</option>
								</form-input>
								<form-input
									label="Ort"
									type="text"
									v-else
									id="stv-list-new-address-ort"
									name="address[ort]"
									v-model="formData['address']['ort']"
									>
								</form-input>
							</div>
						</div>
						<div class="row">
							<div class="col-12 mb-3">
								<form-input
									label="Adresse"
									type="text"
									id="stv-list-new-address-address"
									name="address[address]"
									v-model="formData['address']['address']"
									>
								</form-input>
							</div>
						</div>
					</fieldset>

					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Geburtsnation"
								type="select"
								id="stv-list-new-geburtsnation"
								name="geburtsnation" class="form-select"
								v-model="formData['geburtsnation']"
								>
								<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.langtext}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Staatsbürgerschaft"
								type="select"
								id="stv-list-new-staatsbuergerschaft"
								name="staatsbuergerschaft"
								v-model="formData['staatsbuergerschaft']"
								>
								<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.langtext}}</option>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="E-Mail"
								type="text"
								id="stv-list-new-email"
								name="email"
								v-model="formDataPerson['email']"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Telefon"
								type="text"
								id="stv-list-new-telefon"
								name="telefon"
								v-model="formDataPerson['telefon']"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Mobil"
								type="text"
								id="stv-list-new-mobil"
								name="mobil"
								v-model="formDataPerson['mobil']"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Letzte Ausbildung"
								type="select"
								id="stv-list-new-letzteausbildung"
								name="letzteausbildung"
								v-model="formData['letzteausbildung']"
								>
								<option v-for="ausbildung in lists.ausbildungen" :key="ausbildung.ausbildungcode" :value="ausbildung.ausbildungcode">{{ausbildung.ausbildungbez}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Ausbildungsart"
								type="text"
								id="stv-list-new-ausbildungsart"
								name="ausbildungsart"
								v-model="formDataPerson['ausbildungsart']"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-8 mb-3">
							<form-input
								label="Anmerkungen"
								type="textarea"
								id="stv-list-new-anmerkungen"
								name="anmerkungen"
								v-model="formDataPerson['anmerkungen']"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="Studiengang*"
								type="select"
								id="stv-list-new-studiengang_kz"
								name="studiengang_kz"
								v-model="formDataStg"
								>
								<option v-for="stg in lists.active_stgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">{{stg.kuerzel}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Studiensemester*"
								type="select"
								id="stv-list-new-studiensemester_kurzbz"
								name="studiensemester_kurzbz"
								v-model="formDataSem"
								>
								<option v-for="sem in semester" :key="sem.studiensemester_kurzbz" :value="sem.studiensemester_kurzbz">{{sem.studiensemester_kurzbz}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Ausbildungssemester*"
								type="select"
								id="stv-list-new-ausbildungssemester"
								name="ausbildungssemester"
								v-model="formData['ausbildungssemester']"
								:disabled="formData['incoming']"
								@input="loadStudienplaene"
								>
								<option v-for="sem in Array.from({length:8}).map((u,i) => i+1)" :key="sem" :value="sem">{{sem}}. Semester</option>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								label="OrgForm"
								type="select"
								id="stv-list-new-orgform_kurzbz"
								name="orgform_kurzbz"
								v-model="formData['orgform_kurzbz']"
								@input="loadStudienplaene"
								>
								<option value="">-- keine Auswahl --</option>
								<option v-for="orgform in lists.orgforms" :key="orgform.orgform_kurzbz" :value="orgform.orgform_kurzbz">{{orgform.bezeichnung}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								label="Studienplan"
								type="select"
								id="stv-list-new-studienplan_id"
								name="studienplan_id"
								v-model="formData['studienplan_id']"
								>
								<option value="">-- keine Auswahl --</option>
								<option v-for="plan in studienplaene" :key="plan.studienplan_id" :value="plan.studienplan_id">{{plan.bezeichnung}}</option>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-10 mb-3">
							<div class="form-check">
								<form-input
									label="Incoming"
									type="checkbox"
									id="stv-list-new-incoming"
									name="incoming"
									v-model="formData['incoming']"
									value="1"
									>
								</form-input>
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
	</fhc-form>`
};
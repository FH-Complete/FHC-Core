import {CoreRESTClient} from '../../../../RESTClient.js';
import BsModal from '../../../Bootstrap/Modal.js';
import FhcForm from '../../../Form/Form.js';
import FormValidation from '../../../Form/Validation.js';
import FormInput from '../../../Form/Input.js';
import accessibility from '../../../../directives/accessibility.js';

import ApiStvStudents from '../../../../api/factory/stv/students.js';
import ApiStvAddress from '../../../../api/factory/stv/kontakt/address.js';
import ApiStudiensemester from '../../../../api/factory/studiensemester.js';
import ApiStudienplan from '../../../../api/factory/studienplan.js';

var _uuid = 0;
const FORMDATA_DEFAULT = {
	address: {
		checked: true,
		nation: 'A'
	},
	vorname: '',
	nachname: '',
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
	emits: ['saved'],
	props: {
		personOnly: Boolean,
		studiengangKz: Number,
		studiensemesterKurzbz: String
	},
	data() {
		return {
			places: [],
			formData: null,
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
			return this.places.filter(ort => ort.name == this.formData?.address.gemeinde);
		},
		gemeinden() {
			return Object.values(this.places.reduce((res,place) => {
				res[place.name] = place;
				return res;
			}, {}));
		},
		formDataStg: {
			get() {
				return this.formData?.studiengang_kz !== undefined ? this.formData?.studiengang_kz : this.studiengangKz;
			},
			set(v) {
				this.formData.studiengang_kz = v;
			}
		},
		formDataSem: {
			get() {
				return this.formData?.studiensemester_kurzbz !== undefined ? this.formData?.studiensemester_kurzbz : this.studiensemesterKurzbz;
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
			this.formData = JSON.parse(JSON.stringify(FORMDATA_DEFAULT));
			this.person = null;
			this.suggestions = [];
			if (this.$refs.form) this.$refs.form.clearValidation();
		},
		loadSuggestions() {
			if (this.abortController.suggestions)
				this.abortController.suggestions.abort();
			if (this.person !== null)
				return;

			this.abortController.suggestions = new AbortController();

			this.$api
				.call(ApiStvStudents.getPerson({
					vorname: this.formData?.vorname,
					nachname: this.formData?.nachname,
					gebdatum: this.formData?.gebdatum
				}), {
					signal: this.abortController.suggestions.signal
				})
				.then(result => this.suggestions = result.data)
				.catch(error => {

					if (error.code == 'ERR_BAD_REQUEST') {
						return this.suggestions = [];
					}

					// NOTE(chris): repeat request
					if (error.code != "ERR_CANCELED")
						window.setTimeout(this.loadSuggestions, 100);
				});
		},
		loadPlaces() {
			if (this.abortController.places)
				this.abortController.places.abort();
			if (this.formData?.address?.nation != 'A' || !this?.formData?.address?.plz)
				return;

			this.abortController.places = new AbortController();
			this.$refs.form.call(
				ApiStvAddress.getPlaces(this.formData?.address.plz)
				//~ undefined,
				//~ {
					//~ signal: this.abortController.places.signal
				//~ }
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
			if (this.formDataStg) {
				this.$api
					.call(ApiStudienplan.getStudienplaeneBySemester(
						this.formDataStg,
						this.formDataSem,
						this.formData?.ausbildungssemester,
						this.formData?.orgform_kurzbz
					))
					.then(result => result.data || [])
					.then(result => {
						this.studienplaene = result;
						if (this.formData?.studienplan_id !== '' && !this.studienplaene.filter(plan => plan.studienplan_id == this.formData?.studienplan_id).length)
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
			}
		},
		changeAddressNation(e) {
			if (this.formData.geburtsnation == this.formData?.address.nation)
				this.formData.geburtsnation = e.target.value;
			if (this.formData.staatsbuergerschaft == this.formData?.address.nation)
				this.formData.staatsbuergerschaft = e.target.value;
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

			data.personOnly = this.personOnly;

			this.$refs.form.call(
				ApiStvStudents.add(data)
			)
			.then(result => {
				this.$emit('saved', result.data);
				this.$fhcAlert.alertSuccess('Gespeichert');
				this.$refs.modal.hide();
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
		setPerson(suggestion)
		{
			this.person = suggestion;
			this.formData.address.checked = false;
		},
		dateFormatter(val)
		{
			if (!val)
				return '';
			let date = new Date(val);
			return date.toLocaleDateString('de-AT', {
				"day": "2-digit",
				"month": "2-digit",
				"year": "numeric"
			});
		}
	},
	created() {
		this.uuid = _uuid++;
		this.reset();
		this.$api.call(ApiStudiensemester.getAll())
		.then(result => result.data || [])
		.then(result => {
			this.semester = result;
		})
		.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<fhc-form ref="form" class="stv-list-new" @submit.prevent="send">
		<bs-modal ref="modal" dialog-class="modal-lg modal-dialog-scrollable" style="min-height: 500px" @hidden-bs-modal="reset">
			<template #title>
				{{ personOnly ? $p.t('person', 'personAnlegen') : $p.t('lehre', 'interessentAnlegen') }}
			</template>
			<template #default>

				<template v-if="person === null">
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'nachname')+'*'"
								type="text"
								id="stv-list-new-nachname"
								name="nachname"
								v-model="formDataPerson['nachname']"
								:disabled="!!person"
								@input="loadSuggestions"
								:min-length="3"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'vorname')"
								type="text"
								:id="'stv-list-new-vorname-' + uuid"
								name="vorname"
								v-model="formDataPerson['vorname']"
								:disabled="!!person"
								@input="loadSuggestions"
								:min-length="3"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'geburtsdatum')"
								type="datepicker"
								uid="stv-list-new-gebdatum"
								name="gebdatum"
								v-model="formDataPerson['gebdatum']"
								:disabled="!!person"
								@update:model-value="loadSuggestions"
								text-input
								auto-apply
								no-today
								:enable-time-picker="false"
								format="dd.MM.yyyy"
								:teleport="true"
								>
							</form-input>
						</div>
					</div>
					<table class="table caption-top table-striped table-hover" >
						<caption>{{ $p.t('person', 'personExistiertPruefung') }}</caption>
						<thead v-if="suggestions?.length">
							<th>{{ $p.t('person', 'nachname') }}</th>
							<th>{{ $p.t('person', 'vorname') }}</th>
							<th>{{ $p.t('person', 'weitereVornamen') }}</th>
							<th>{{ $p.t('person', 'geburtsdatum') }}</th>
							<th>{{ $p.t('person', 'geschlecht') }}</th>
							<th>{{ $p.t('person', 'adresse') }}</th>
							<th>Status</th>
						</thead>
						<tbody>
							<tr
								v-for="(suggestion, index) in suggestions"
								:key="suggestion.person_id"
								:class="{'active': index == 2}"
								@click="(index == 2) ? suggestions.shift() : setPerson(suggestion)"
								v-accessibility:tab.vertical
								>
								<td>{{ suggestion.nachname }}</td>
								<td>{{ suggestion.vorname }}</td>
								<td>{{ suggestion.vornamen }}</td>
								<td>{{ dateFormatter(suggestion.gebdatum) }}</td>
								<td>{{ suggestion.geschlecht_bezeichnung }}</td>
								<td>
									<div v-for="adresse in suggestion.adressen">
										{{ (adresse.plz ?? '') + (adresse.plz && adresse.ort ? ' ' : '') + (adresse.ort ?? '') + (adresse.ort && adresse.strasse ? ', ' : '') + (adresse.strasse ?? '') }}
									</div>
								</td>
								<td>
									<div v-for="status in suggestion.status">
										{{ status.status_kurzbz + " " + status.studiengang_kuerzel }}
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</template>
				<template v-else>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'anrede')"
								type="text"
								id="stv-list-new-anrede"
								name="anrede"
								v-model="formDataPerson['anrede']"
								:disabled="!!person"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'titelPre')"
								type="text"
								id="stv-list-new-titelpre"
								name="titelpre"
								v-model="formDataPerson['titelpre']"
								:disabled="!!person"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'titelPost')"
								type="text"
								id="stv-list-new-titelpost"
								name="titelpost"
								v-model="formDataPerson['titelpost']"
								:disabled="!!person"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'nachname')+'*'"
								type="text"
								id="stv-list-new-nachname"
								name="nachname"
								v-model="formDataPerson['nachname']"
								:disabled="!!person"
								@input="loadSuggestions"
								:min-length="3"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'vorname')"
								type="text"
								id="stv-list-new-vorname"
								name="vorname"
								v-model="formDataPerson['vorname']"
								:disabled="!!person"
								@input="loadSuggestions"
								:min-length="3"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'weitereVornamen')"
								type="text"
								id="stv-list-new-vornamen"
								name="vornamen"
								v-model="formDataPerson['vornamen']"
								:disabled="!!person"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'wahlname')"
								type="text"
								id="stv-list-new-wahlname"
								name="wahlname"
								v-model="formDataPerson['wahlname']"
								:disabled="!!person"
								>
							</form-input>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'geschlecht')+'*'"
								type="select"
								id="stv-list-new-geschlecht"
								name="geschlecht"
								v-model="formDataPerson['geschlecht']"
								:disabled="!!person"
								>
								<option v-for="geschlecht in lists.geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'geburtsdatum')"
								type="datepicker"
								uid="stv-list-new-gebdatum"
								name="gebdatum"
								v-model="formDataPerson['gebdatum']"
								:disabled="!!person"
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

					<div class="row">
						<div class="col-sm-6 mb-3">
							<form-input
								:label="$p.t('person', 'adresseHinzufuegen')"
								type="checkbox"
								id="stv-new-adresse"
								name="adresseChecked"
								v-model="formData['address']['checked']"
								value="true"
								>
							</form-input>
						</div>
					</div>
					<fieldset v-if="formData['address']['checked']">
					<hr>
						<legend>Adresse</legend>
						<div class="row">
							<div class="col-sm-4 mb-3">
								<form-input
									:label="$p.t('person', 'land')"
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
									:label="$p.t('person', 'plz')"
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
									:label="$p.t('person', 'gemeinde')"
									type="select"
									v-if="formData['address']['nation'] == 'A'"
									id="stv-list-new-address-gemeinde"
									name="address[gemeinde]"
									v-model="formData['address']['gemeinde']"
									>
									<option v-if="!gemeinden.length" disabled>$p.t('ui', 'bittePlzWaehlen')</option>
									<option v-for="gemeinde in gemeinden" :key="gemeinde.name" :value="gemeinde.name">{{gemeinde.name}}</option>
								</form-input>
								<form-input
									:label="$p.t('person', 'gemeinde')"
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
									:label="$p.t('person', 'ort')"
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
									:label="$p.t('person', 'ort')"
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
									:label="$p.t('person', 'adresse')"
									type="text"
									id="stv-list-new-address-address"
									name="address[address]"
									v-model="formData['address']['address']"
									>
								</form-input>
							</div>
						</div>
					<hr>
					</fieldset>

					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'geburtsnation')"
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
								:label="$p.t('person', 'staatsbuergerschaft')"
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
								:label="$p.t('person', 'telefon')"
								type="text"
								id="stv-list-new-telefon"
								name="telefon"
								v-model="formDataPerson['telefon']"
								>
							</form-input>
						</div>
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'mobil')"
								type="text"
								id="stv-list-new-mobil"
								name="mobil"
								v-model="formDataPerson['mobil']"
								>
							</form-input>
						</div>
					</div>
					<fieldset v-if="!personOnly">
						<div class="row">
							<div class="col-sm-4 mb-3">
								<form-input
									:label="$p.t('lehre', 'letzeAusbildung')"
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
									:label="$p.t('lehre', 'ausbildungsart')"
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
									:label="$p.t('lehre', 'anmerkungen')"
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
									:label="$p.t('lehre', 'studiengang')+'*'"
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
									:label="$p.t('lehre', 'studiensemester')+'*'"
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
									:label="$p.t('lehre', 'ausbildungssemester')+'*'"
									type="select"
									id="stv-list-new-ausbildungssemester"
									name="ausbildungssemester"
									v-model="formData['ausbildungssemester']"
									:disabled="formData['incoming']"
									@change="loadStudienplaene"
									>
									<option v-for="sem in Array.from({length:8}).map((u,i) => i+1)" :key="sem" :value="sem">{{sem}}. Semester</option>
								</form-input>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-4 mb-3">
								<form-input
									:label="$p.t('lehre', 'organisationsform')"
									type="select"
									id="stv-list-new-orgform_kurzbz"
									name="orgform_kurzbz"
									v-model="formData['orgform_kurzbz']"
									@change="loadStudienplaene"
									>
									<option value="">-- keine Auswahl --</option>
									<option v-for="orgform in lists.orgforms" :key="orgform.orgform_kurzbz" :value="orgform.orgform_kurzbz">{{orgform.bezeichnung}}</option>
								</form-input>
							</div>
							<div class="col-sm-4 mb-3">
								<form-input
									:label="$p.t('lehre', 'studienplan')"
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
								<div>
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
					</fieldset>
				</template>
			</template>
			<template #footer>
				<button v-if="person !== null" type="button" class="btn btn-secondary" @click="person = null; formData.address.checked = true;"><i class="fa fa-chevron-left"></i>{{ $p.t('ui', 'zurueck') }}</button>
				<button type="submit" class="btn btn-primary">{{ person === null || personOnly ? $p.t('person', 'personAnlegen') : $p.t('lehre', 'interessentAnlegen') }}</button>
			</template>
		</bs-modal>
	</fhc-form>`
};

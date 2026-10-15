import {CoreRESTClient} from '../../../../RESTClient.js';
import BsModal from '../../../Bootstrap/Modal.js';
import FhcForm from '../../../Form/Form.js';
import FormValidation from '../../../Form/Validation.js';
import FormInput from '../../../Form/Input.js';
import accessibility from '../../../../directives/accessibility.js';
import {debounce} from '../../../../helpers/debounce.js';

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
// Same minimum as Student::getPerson
const NACHNAME_MIN_LENGTH = 2;

// Shows the text with the search term in bold, without v-html
const MarkMatch = {
	props: {
		text: String,
		term: String
	},
	computed: {
		parts() {
			const term = (this.term || '').trim();
			if (!this.text || !term)
				return [this.text || ''];
			// The pattern is one capture group, so the matches are at the odd indexes
			return this.text.split(new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'));
		}
	},
	template: `<template v-for="(part, index) in parts" :key="index"><strong v-if="index % 2">{{ part }}</strong><template v-else>{{ part }}</template></template>`
};

export default {
	components: {
		BsModal,
		FhcForm,
		FormValidation,
		FormInput,
		MarkMatch
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
			suggestions: [],
			suggestionsLoading: false,
			suggestionsMore: false,
			emailOwners: [],
			duplicates: [],
			saving: false,
			person: null,
			semester: [],
			studienplaene: [],
			debouncedLoadSuggestions: null,
			debouncedCheckEmail: null,
			abortController: {
				suggestions: null,
				places: null,
				email: null
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
		},
		nachnameTooShort() {
			return (this.formData?.nachname || '').trim().length < NACHNAME_MIN_LENGTH;
		},
		suggestionsHint() {
			if (this.nachnameTooShort)
				return this.$p.t('person', 'sucheNachnameMindestensZeichen', [NACHNAME_MIN_LENGTH]);
			if (this.suggestionsLoading)
				return '';
			if (!this.suggestions.length)
				return this.$p.t('lehre', 'error_no_person');
			if (this.suggestionsMore)
				return this.$p.t('person', 'sucheMehrAlsTreffer', [this.suggestions.length]);
			return '';
		},
		emailOwnersText() {
			return this.emailOwners.map(this.personText).join(', ');
		},
		duplicatesText() {
			return this.duplicates.map(this.personText).join(', ');
		},
		submitLabel() {
			if (this.person === null)
				return this.$p.t('person', 'neuePersonAnlegen');
			if (this.duplicates.length)
				return this.$p.t('person', 'trotzdemAnlegen');
			return this.personOnly ? this.$p.t('person', 'personAnlegen') : this.$p.t('lehre', 'interessentAnlegen');
		}
	},
	watch: {
		formDataStg() {
			this.loadStudienplaene();
		},
		formDataSem() {
			this.loadStudienplaene();
		},
		// The e-mail field shows the value of the other person now
		person() {
			this.checkEmail();
			this.duplicates = [];
		},
		// Other data needs a new duplicate check before the person is created
		'formData.nachname'() {
			this.duplicates = [];
		},
		'formData.vorname'() {
			this.duplicates = [];
		},
		'formData.gebdatum'() {
			this.duplicates = [];
		}
	},
	methods: {
		open() {
			this.$refs.modal.show();
		},
		reset() {
			this.formData = JSON.parse(JSON.stringify(FORMDATA_DEFAULT));
			this.person = null;
			this.duplicates = [];
			// The fields are empty now: both calls stop a running request and clear their result
			this.loadSuggestions();
			this.checkEmail();
			if (this.$refs.form) this.$refs.form.clearValidation();
		},
		back() {
			this.person = null;
			this.formData.address.checked = true;
			// Page 2 can change the search fields
			this.loadSuggestions();
		},
		// The spinner starts with the keystroke, so no old result or hint shows during the debounce delay
		onSearchInput() {
			if (this.abortController.suggestions)
				this.abortController.suggestions.abort();
			this.suggestionsLoading = !this.nachnameTooShort;
			this.debouncedLoadSuggestions();
		},
		loadSuggestions() {
			if (this.abortController.suggestions)
				this.abortController.suggestions.abort();
			this.suggestionsLoading = false;
			if (this.person !== null)
				return;
			if (this.nachnameTooShort) {
				this.suggestionsMore = false;
				return this.suggestions = [];
			}
			this.fetchSuggestions(0);
		},
		// Appends the next rows of the same search
		loadMoreSuggestions() {
			this.fetchSuggestions(this.suggestions.length);
		},
		fetchSuggestions(offset) {
			this.abortController.suggestions = new AbortController();
			this.suggestionsLoading = true;

			this.$api
				.call(ApiStvStudents.getPerson({
					vorname: this.formData?.vorname,
					nachname: this.formData?.nachname,
					gebdatum: this.formData?.gebdatum,
					offset
				}), {
					signal: this.abortController.suggestions.signal
				})
				.then(result => {
					this.suggestions = offset ? this.suggestions.concat(result.data) : result.data;
					this.suggestionsMore = result.meta.more;
					this.suggestionsLoading = false;
				})
				.catch(error => {
					// A newer search stopped this one
					if (error.code == 'ERR_CANCELED')
						return;
					// A failed next page keeps the rows that the list shows already
					if (!offset)
						this.suggestions = [];
					this.suggestionsMore = false;
					this.suggestionsLoading = false;
					if (error.code != 'ERR_BAD_REQUEST')
						this.$fhcAlert.handleSystemError(error);
				});
		},
		checkEmail() {
			if (this.abortController.email)
				this.abortController.email.abort();
			const email = (this.formDataPerson?.email || '').trim();
			if (!email.includes('@'))
				return this.emailOwners = [];

			this.abortController.email = new AbortController();

			this.$api
				.call(ApiStvStudents.getPersonByEmail({ email }), {
					signal: this.abortController.email.signal
				})
				// The selected person may own the address already
				.then(result => this.emailOwners = result.data.filter(owner => owner.person_id != this.person?.person_id))
				.catch(error => {
					if (error.code == 'ERR_CANCELED')
						return;
					this.emailOwners = [];
					this.$fhcAlert.handleSystemError(error);
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
		async send(e) {
			if (this.person === null)
				return this.person = 0;
			// A second click during the check or the save must not create the person twice
			if (this.saving)
				return;
			this.saving = true;
			try {
				await this.save();
			} finally {
				this.saving = false;
			}
		},
		async save() {
			// New person: the first submit shows exact duplicates, the second submit creates the person anyway
			if (this.person === 0 && !this.duplicates.length) {
				const { nachname, vorname, gebdatum } = this.formData;
				const duplicates = await this.findDuplicates(nachname, vorname, gebdatum);
				// The dialog can close, go back or get other data during the request
				if (this.person !== 0 || duplicates === null
					|| this.formData.nachname !== nachname || this.formData.vorname !== vorname || this.formData.gebdatum !== gebdatum)
					return;
				if (duplicates.length)
					return this.duplicates = duplicates;
			}

			//this.$fhcAlert.resetFormValidation(this.$refs.form);
			const data = {...this.formData, ...(this.person || {})};
			if (data.studiengang_kz === undefined)
				data.studiengang_kz = this.studiengangKz;
			if (data.studiensemester_kurzbz === undefined)
				data.studiensemester_kurzbz = this.studiensemesterKurzbz;

			data.personOnly = this.personOnly;

			return this.$refs.form.call(
				ApiStvStudents.add(data)
			)
			.then(result => {
				this.$emit('saved', result.data);
				this.$fhcAlert.alertSuccess('Gespeichert');
				this.$refs.modal.hide();
			})
			.catch(this.$fhcAlert.handleSystemError);
		},
		// Persons with the same Nachname and Vorname/Wahlname (getPerson filters the Geburtsdatum); null on error
		async findDuplicates(nachname, vorname, gebdatum) {
			let result;
			try {
				result = await this.$api.call(ApiStvStudents.getPerson({ nachname, vorname, gebdatum }));
			} catch (error) {
				this.$fhcAlert.handleSystemError(error);
				return null;
			}
			const same = (a, b) => (a || '').trim().toLowerCase() == (b || '').trim().toLowerCase();
			return result.data.filter(match => same(match.nachname, nachname)
				&& (same(match.vorname, vorname) || (match.wahlname && same(match.wahlname, vorname))));
		},
		personText(person) {
			const name = [person.vorname, person.nachname].filter(Boolean).join(' ');
			return person.gebdatum ? name + ' (' + this.dateFormatter(person.gebdatum) + ')' : name;
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
		this.debouncedLoadSuggestions = debounce(this.loadSuggestions, 300);
		this.debouncedCheckEmail = debounce(this.checkEmail, 300);
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
		<bs-modal ref="modal" dialog-class="modal-lg modal-dialog-scrollable" style="min-height: 500px" allow-fullscreen-expand @hidden-bs-modal="reset">
			<template #title>
				{{ personOnly ? $p.t('person', 'personAnlegen') : $p.t('lehre', 'interessentAnlegen') }}
			</template>
			<template #default>

				<template v-if="person === null">
					<p>{{ $p.t('person', 'personUebernehmenOderAnlegen') }}</p>
					<div class="row">
						<div class="col-sm-4 mb-3">
							<form-input
								:label="$p.t('person', 'nachname')+'*'"
								type="text"
								id="stv-list-new-nachname"
								name="nachname"
								v-model="formDataPerson['nachname']"
								:disabled="!!person"
								@input="onSearchInput"
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
								@input="onSearchInput"
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
								model-type="yyyy-MM-dd"
								:disabled="!!person"
								@update:model-value="onSearchInput"
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
						<caption>
							{{ $p.t('person', 'personExistiertPruefungv2') }}
							<span v-if="suggestionsLoading" class="spinner-border spinner-border-sm ms-2" aria-hidden="true"></span>
						</caption>
						<thead v-if="suggestions?.length">
							<th>{{ $p.t('person', 'nachname') }}</th>
							<th>{{ $p.t('person', 'vorname') }}</th>
							<th>{{ $p.t('person', 'wahlname') }}</th>
							<th>{{ $p.t('person', 'weitereVornamen') }}</th>
							<th>{{ $p.t('person', 'geburtsdatum') }}</th>
							<th>{{ $p.t('person', 'geschlecht') }}</th>
							<th>{{ $p.t('person', 'adresse') }}</th>
							<th>Status</th>
							<th><span class="visually-hidden">{{ $p.t('person', 'personInNeuemTabOeffnen') }}</span></th>
						</thead>
						<tbody>
							<tr
								v-for="suggestion in suggestions"
								:key="suggestion.person_id"
								:class="{'stv-list-new-inactive': !suggestion.aktiv}"
								@click="setPerson(suggestion)"
								v-accessibility:tab.vertical
								>
								<td>
									<mark-match :text="suggestion.nachname" :term="formData.nachname"></mark-match>
									<span v-if="!suggestion.aktiv" class="visually-hidden"> ({{ $p.t('global', 'inaktiv') }})</span>
								</td>
								<td><mark-match :text="suggestion.vorname" :term="formData.vorname"></mark-match></td>
								<td><mark-match :text="suggestion.wahlname" :term="formData.vorname"></mark-match></td>
								<td><mark-match :text="suggestion.vornamen" :term="formData.vorname"></mark-match></td>
								<td>{{ dateFormatter(suggestion.gebdatum) }}</td>
								<td>{{ suggestion.geschlecht_bezeichnung }}</td>
								<td>
									<div v-for="adresse in suggestion.adressen">
										{{ (adresse.plz ?? '') + (adresse.plz && adresse.ort ? ' ' : '') + (adresse.ort ?? '') + (adresse.ort && adresse.strasse ? ', ' : '') + (adresse.strasse ?? '') }}
									</div>
								</td>
								<td>
									<div v-for="status in suggestion.status">
										{{ status.status_kurzbz + " " + status.studiengang_kuerzel + " " + status.studiensemester_kurzbz }}
									</div>
								</td>
								<td>
									<!-- Only a person with a prestudent shows in the StV list; .stop: the link must not select the row -->
									<a
										v-if="suggestion.status?.length"
										:href="$router.resolve({ name: 'person', params: { person_id: suggestion.person_id } }).href"
										target="_blank"
										rel="noopener"
										:title="$p.t('person', 'personInNeuemTabOeffnen')"
										:aria-label="$p.t('person', 'personInNeuemTabOeffnen')"
										@click.stop
										@keydown.enter.stop
										>
										<i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
									</a>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="text-body-secondary" role="status">
						<span v-if="suggestionsLoading" class="visually-hidden">{{ $p.t('ui', 'loading') }}</span>
						{{ suggestionsHint }}
					</div>
					<button v-if="suggestionsMore && !suggestionsLoading" type="button" class="btn btn-outline-secondary btn-sm mt-2" @click="loadMoreSuggestions">
						{{ $p.t('person', 'weitereTrefferLaden') }}
					</button>
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
								model-type="yyyy-MM-dd"
								:disabled="!!person"
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
								@input="debouncedCheckEmail"
								>
							</form-input>
							<div role="status">
								<div v-if="emailOwners.length" class="alert alert-warning small py-1 px-2 mt-1 mb-0">
									{{ $p.t('person', 'emailBereitsVergeben', [emailOwnersText]) }}
								</div>
							</div>
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
				<div v-if="duplicates.length" class="alert alert-warning w-100" role="alert">
					{{ $p.t('person', 'personExistiertTrotzdemAnlegen', [duplicatesText]) }}
				</div>
				<button v-if="person !== null" type="button" class="btn btn-secondary" :disabled="saving" @click="back"><i class="fa fa-chevron-left"></i>{{ $p.t('ui', 'zurueck') }}</button>
				<button type="submit" class="btn btn-primary" :disabled="saving">{{ submitLabel }}</button>
			</template>
		</bs-modal>
	</fhc-form>`
};

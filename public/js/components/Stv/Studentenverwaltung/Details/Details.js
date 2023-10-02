import VueDatePicker from '../../../vueDatepicker.js.php';
import FormUploadImage from '../../../Form/Upload/Image.js';
import {CoreRESTClient} from '../../../../RESTClient.js';

export default {
	components: {
		VueDatePicker,
		FormUploadImage,
		PvToast: primevue.toast
	},
	inject: {
		showBpk: {
			from: 'hasBpkPermission',
			default: false
		},
		showZugangscode: {
			from: 'activeAddonBewerbung',
			default: false
		},
		cisRoot: {
			from: 'cisRoot'
		},
		generateAlias: {
			from: 'configGenerateAlias',
			default: false
		},
		hasAliasPermission: {
			from: 'hasAliasPermission',
			default: false
		}
	},
	props: {
		student: Object
	},
	data() {
		return {
			nations: [],
			sprachen: [],
			geschlechter: [],
			familienstaende: {
				"": "--keine Auswahl--",
				"g": "geschieden",
				"l": "ledig",
				"v": "verheiratet",
				"w": "verwitwet"
			},
			original: null,
			data: null,
			changed: {},
			studentIn: null,
			gebDatumIsValid: false,
			gebDatumIsInvalid: false
		}
	},
	computed: {
		aliasNotAllowed() {
			return this.generateAlias === false && !this.hasAliasPermission;
		},
		changedLength() {
			return Object.keys(this.changed).length;
		}
	},
	watch: {
		student(n) {
			this.updateStudent(n);
		},
		data: {
			handler(n) {
				let res = {};
				for (var k in this.original) {
					if (k == 'gebdatum') {
						if (new Date(this.original[k]).toString() != new Date(n[k]).toString())
							res[k] = n[k];
					} else {
						if (this.original[k] !== n[k])
							res[k] = n[k];
					}
				}
				this.changed = res;
				this.resetErrors();
			},
			deep: true
		}
	},
	methods: {
		updateStudent(n) {
			CoreRESTClient
				.get('components/stv/Student/get/' + n.prestudent_id)
				.then(result => result.data)
				.then(result => {
					this.data = result;
					if (!this.data.familienstand)
						this.data.familienstand = '';
					this.original = {...this.data};
				})
				.catch(err => {
					console.error(err.response.data || err.message);
				});
		},
		save() {
			CoreRESTClient
				.post('components/stv/Student/save/' + this.student.prestudent_id, this.changed)
				.then(result => result.data)
				.then(result => {
					this.resetErrors();
					if (CoreRESTClient.isError(result)) {
						let errors = CoreRESTClient.getError(result);
						
						if (errors === "Generic error")
							console.error(errors, result);
						else {
							for (var k in errors)
								this.addError(k, errors[k]);
						}
					} else {
						for (var node of document.querySelectorAll(Object.keys(this.changed).map(el => '#stv-details-' + el).join(',')))
							node.classList.add('is-valid');
						if (this.changed.gebdatum !== undefined)
							this.gebDatumIsValid = true;
						
						// TODO(chris): phrase
						this.addToast('Gespeichert', '', 'success');

						this.original = {...this.data};
						this.changed = {};
					}
				})
				.catch(err => {
					// TODO(chris): phrase
					this.addToast('Error', err?.response?.data || err?.message, 'error');
				})
		},
		resetErrors() {
			Array.from(this.$refs.form.getElementsByClassName('is-valid')).forEach(el => el.classList.remove('is-valid'));
			Array.from(this.$refs.form.getElementsByClassName('is-invalid')).forEach(el => el.classList.remove('is-invalid'));
			Array.from(this.$refs.form.getElementsByClassName('invalid-feedback')).forEach(el => el.remove());
			this.gebDatumIsValid = false;
			this.gebDatumIsInvalid = false;
		},
		addError(field, msg) {
			let id = 'stv-details-' + field;

			let input = document.getElementById(id);
			if (field === 'gebdatum') {
				this.gebDatumIsInvalid = true;
				input = document.getElementById('dp-input-' + id).parentNode;
			}

			input.classList.add('is-invalid');
			
			let feedback = document.createElement('div');
			feedback.classList.add('invalid-feedback');
			feedback.innerHTML = msg;
			input.after(feedback);
		},
		addToast(header, msg, severity) {
			this.$refs.responseToast.add({
				severity: severity,
				summary: header,
				detail: msg,
				life: 3000
			})
		}
	},
	created() {
		CoreRESTClient
			.get('components/stv/Student/getNations')
			.then(result => {
				this.nations = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
		CoreRESTClient
			.get('components/stv/Student/getSprachen')
			.then(result => {
				this.sprachen = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
		CoreRESTClient
			.get('components/stv/Student/getGeschlechter')
			.then(result => {
				this.geschlechter = result.data;
			})
			.catch(err => {
				console.error(err.response.data || err.message);
			});
		this.updateStudent(this.student);
	},
	mounted() {
		console.log();
	},
	//TODO(chris): Geburtszeit? Anzahl der Kinder?
	template: `
	<div ref="form" class="stv-details-details h-100 pb-3">
		<fieldset>
			<legend>Person</legend>
			<template v-if="data">
				<div class="row mb-3 align-items-center">
					<label for="stv-details-person_id" class="col-sm-1 col-form-label">Person ID</label>
					<div class="col-sm-3">
						<input id="stv-details-person_id" type="text" class="form-control" v-model="data.person_id" disabled>
					</div>
					<label v-if="showZugangscode" for="stv-details-zugangscode" class="col-sm-1 col-form-label">Zugangscode</label>
					<div v-if="showZugangscode" class="col-sm-3">
						<span class="form-text">
							<a :href="cisRoot + 'addons/bewerbung/cis/registration.php?code=' + data.zugangscode + '&emailAdresse=' + data.email_privat" target="_blank">{{data.zugangscode}}</a>
						</span>
					</div>
					<label v-if="showBpk" for="stv-details-bpk" class="col-sm-1 col-form-label">BPK</label>
					<div v-if="showBpk" class="col-sm-3">
						<input id="stv-details-bpk" type="text" class="form-control" v-model="data.bpk" maxlength="28">
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-anrede" class="col-sm-1 col-form-label">Anrede</label>
					<div class="col-sm-3">
						<input id="stv-details-anrede" type="text" class="form-control" v-model="data.anrede" maxlength="16">
					</div>
					<label for="stv-details-titelpre" class="col-sm-1 col-form-label">Titel Pre</label>
					<div class="col-sm-3">
						<input id="stv-details-titelpre" type="text" class="form-control" v-model="data.titelpre" maxlength="64">
					</div>
					<label for="stv-details-titelpost" class="col-sm-1 col-form-label">Titel Post</label>
					<div class="col-sm-3">
						<input id="stv-details-titelpost" type="text" class="form-control" v-model="data.titelpost" maxlength="32">
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-nachname" class="col-sm-1 col-form-label">Nachname</label>
					<div class="col-sm-3">
						<input id="stv-details-nachname" type="text" class="form-control" v-model="data.nachname" maxlength="64">
					</div>
					<label for="stv-details-vorname" class="col-sm-1 col-form-label">Vorname</label>
					<div class="col-sm-3">
						<input id="stv-details-vorname" type="text" class="form-control" v-model="data.vorname" maxlength="32">
					</div>
					<label for="stv-details-vornamen" class="col-sm-1 col-form-label">Vornamen</label>
					<div class="col-sm-3">
						<input id="stv-details-vornamen" type="text" class="form-control" v-model="data.vornamen" maxlength="128">
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-wahlname" class="col-sm-1 col-form-label">Wahlname</label>
					<div class="col-sm-3">
						<input id="stv-details-wahlname" type="text" class="form-control" v-model="data.wahlname" maxlength="128">
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="dp-input-stv-details-gebdatum" class="col-sm-1 col-form-label">Geburtsdatum</label>
					<div class="col-sm-3">
						<vue-date-picker id="stv-details-gebdatum" :input-class-name="gebDatumIsInvalid ? 'form-control is-invalid' : (gebDatumIsValid ? 'form-control is-valid' : 'form-control')" uid="stv-details-gebdatum" v-model="data.gebdatum" :clearable="false" no-today auto-apply :enable-time-picker="false" format="dd.MM.yyyy" preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
					<label for="stv-details-gebort" class="col-sm-1 col-form-label">Geburtsort</label>
					<div class="col-sm-3">
						<input id="stv-details-gebort" type="text" class="form-control" v-model="data.gebort" maxlength="128">
					</div>
					<label for="stv-details-geburtsnation" class="col-sm-1 col-form-label">Geburtsnation</label>
					<div class="col-sm-3">
						<select id="stv-details-geburtsnation" class="form-control" v-model="data.geburtsnation">
							<option value="">-- keine Auswahl --</option>
							<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
							<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-svnr" class="col-sm-1 col-form-label">SVNR</label>
					<div class="col-sm-3">
						<input id="stv-details-svnr" type="text" class="form-control" v-model="data.svnr" maxlength="16">
					</div>
					<label for="stv-details-ersatzkennzeichen" class="col-sm-1 col-form-label">Ersatzkennzeichen</label>
					<div class="col-sm-3">
						<input id="stv-details-ersatzkennzeichen" type="text" class="form-control" v-model="data.ersatzkennzeichen" maxlength="10">
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-staatsbuergerschaft" class="col-sm-1 col-form-label">Staatsbürgerschaft</label>
					<div class="col-sm-3">
						<select id="stv-details-staatsbuergerschaft" class="form-control" v-model="data.staatsbuergerschaft">
							<option value="">-- keine Auswahl --</option>
							<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
							<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
						</select>
					</div>
					<label for="stv-details-matr_nr" class="col-sm-1 col-form-label">Matrikelnummer</label>
					<div class="col-sm-3">
						<input id="stv-details-matr_nr" type="text" class="form-control" v-model="data.matr_nr" maxlength="32">
					</div>
					<label for="stv-details-sprache" class="col-sm-1 col-form-label">Sprache</label>
					<div class="col-sm-3">
						<select id="stv-details-sprache" class="form-control" v-model="data.sprache">
							<option v-for="sprache in sprachen" :key="sprache.sprache" :value="sprache.sprache">{{sprache.sprache}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-geschlecht" class="col-sm-1 col-form-label">Geschlecht</label>
					<div class="col-sm-3">
						<select id="stv-details-geschlecht" class="form-control" v-model="data.geschlecht">
							<option v-for="geschlecht in geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
						</select>
					</div>
					<label for="stv-details-familienstand" class="col-sm-1 col-form-label">Familienstand</label>
					<div class="col-sm-3">
						<select id="stv-details-familienstand" class="form-control" v-model="data.familienstand">
							<option v-for="(bezeichnung, key) in familienstaende" :key="key" :value="key">{{bezeichnung}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-foto" class="col-sm-1 col-form-label">Foto</label>
					<div class="col-sm-3">
						<form-upload-image id="stv-details-foto" v-model="data.foto"></form-upload-image>
					</div>
					<label for="stv-details-anmerkung" class="col-sm-1 col-form-label">Anmerkung</label>
					<div class="col-sm-3">
						<textarea id="stv-details-anmerkung" class="form-control" v-text="anmerkung"></textarea>
					</div>
					<label for="stv-details-homepage" class="col-sm-1 col-form-label">Homepage</label>
					<div class="col-sm-3">
						<input id="stv-details-homepage" type="text" class="form-control" v-model="data.homepage" maxlength="256">
					</div>
				</div>
			</template>
			<div v-else>
				Loading...
			</div>
		</fieldset>
		<fieldset>
			<legend>StudentIn</legend>
			<template v-if="data">
				<div class="row mb-3 align-items-center">
					<label for="stv-details-student_uid" class="col-sm-1 col-form-label">UID</label>
					<div class="col-sm-3">
						<input id="stv-details-student_uid" type="text" class="form-control" v-model="data.student_uid" disabled>
					</div>
					<label for="stv-details-personenkennzeichen" class="col-sm-1 col-form-label">Personenkennzeichen</label>
					<div class="col-sm-3">
						<input id="stv-details-personenkennzeichen" type="text" class="form-control" v-model="data.matrikelnr" disabled>
					</div>
					<label for="stv-details-aktiv" class="col-sm-1 col-form-label">Aktiv</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="stv-details-aktiv" type="checkbox" class="form-check-input" v-model="data.aktiv">
						</div>
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-semester" class="col-sm-1 col-form-label">Semester</label>
					<div class="col-sm-3">
						<input id="stv-details-semester" type="text" class="form-control" v-model="data.semester" maxlength="2">
					</div>
					<label for="stv-details-verband" class="col-sm-1 col-form-label">Verband</label>
					<div class="col-sm-3">
						<input id="stv-details-verband" type="text" class="form-control" v-model="data.verband" maxlength="1">
					</div>
					<label for="stv-details-gruppe" class="col-sm-1 col-form-label">Gruppe</label>
					<div class="col-sm-3">
						<input id="stv-details-gruppe" type="text" class="form-control" v-model="data.gruppe" maxlength="1">
					</div>
				</div>
				<div class="row mb-3 align-items-center">
					<label for="stv-details-alias" class="col-sm-1 col-form-label">Alias</label>
					<div class="col-sm-3">
						<input id="stv-details-alias" type="text" class="form-control" v-model="data.alias" :disabled="aliasNotAllowed">
					</div>
				</div>
				<div>
					<button type="button" class="btn btn-primary" @click="save" :disabled="!changedLength">Speichern</button>
				</div>
			</template>
			<div v-else>
				Loading...
			</div>
		</fieldset>
		<pv-toast ref="responseToast" style="z-index:9999"></pv-toast>
	</div>`
};
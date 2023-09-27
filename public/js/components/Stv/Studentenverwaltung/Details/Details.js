import VueDatePicker from '../../../vueDatepicker.js.php';
import FormUploadImage from '../../../Form/Upload/Image.js';
import {CoreRESTClient} from '../../../../RESTClient.js';

export default {
	components: {
		VueDatePicker,
		FormUploadImage
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
			person: null,
			studentIn: null
		}
	},
	watch: {
		student(n) {
			CoreRESTClient
				.get('components/stv/Student/getPerson/' + this.student.person_id)
				.then(result => result.data)
				.then(result => {
					this.person = result;
					if (!this.person.familienstand)
						this.person.familienstand = '';
				})
				.catch(err => {
					console.error(err.response.data || err.message);
				});
			CoreRESTClient
				.get('components/stv/Student/getStudent/' + this.student.uid)
				.then(result => result.data)
				.then(result => {
					// TODO(chris): IMPLEMENT HERE!
					console.log(result);
					this.studentIn = result;
				})
				.catch(err => {
					console.error(err.response.data || err.message);
				});
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
	},
	template: `
	<div class="stv-details-details h-100 pb-3">
		<fieldset>
			<legend>Person</legend>
			<template v-if="person">
				<div class="row mb-3">
					<label for="stv-details-person_id" class="col-sm-1 col-form-label">Person ID</label>
					<div class="col-sm-3">
						<input id="stv-details-person_id" type="text" class="form-control" v-model="person.person_id">
					</div>
					<label for="stv-details-bpk" class="col-sm-1 col-form-label">BPK</label>
					<div class="col-sm-3">
						<input id="stv-details-bpk" type="text" class="form-control" v-model="person.bpk">
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-anrede" class="col-sm-1 col-form-label">Anrede</label>
					<div class="col-sm-3">
						<input id="stv-details-anrede" type="text" class="form-control" v-model="person.anrede">
					</div>
					<label for="stv-details-titelpre" class="col-sm-1 col-form-label">Titel Pre</label>
					<div class="col-sm-3">
						<input id="stv-details-titelpre" type="text" class="form-control" v-model="person.titelpre">
					</div>
					<label for="stv-details-titelpost" class="col-sm-1 col-form-label">Titel Post</label>
					<div class="col-sm-3">
						<input id="stv-details-titelpost" type="text" class="form-control" v-model="person.titelpost">
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-nachname" class="col-sm-1 col-form-label">Nachname</label>
					<div class="col-sm-3">
						<input id="stv-details-nachname" type="text" class="form-control" v-model="person.nachname">
					</div>
					<label for="stv-details-vorname" class="col-sm-1 col-form-label">Vorname</label>
					<div class="col-sm-3">
						<input id="stv-details-vorname" type="text" class="form-control" v-model="person.vorname">
					</div>
					<label for="stv-details-vornamen" class="col-sm-1 col-form-label">Vornamen</label>
					<div class="col-sm-3">
						<input id="stv-details-vornamen" type="text" class="form-control" v-model="person.vornamen">
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-wahlname" class="col-sm-1 col-form-label">Wahlname</label>
					<div class="col-sm-3">
						<input id="stv-details-wahlname" type="text" class="form-control" v-model="person.wahlname">
					</div>
				</div>
				<div class="row mb-3">
					<label for="dp-input-stv-details-gebdatum" class="col-sm-1 col-form-label">Geburtsdatum</label>
					<div class="col-sm-3">
						<vue-date-picker uid="stv-details-gebdatum" v-model="person.gebdatum" :clearable="false" no-today auto-apply :enable-time-picker="false" format="dd.MM.yyyy" preview-format="dd.MM.yyyy"></vue-date-picker>
					</div>
					<label for="stv-details-gebort" class="col-sm-1 col-form-label">Geburtsort</label>
					<div class="col-sm-3">
						<input id="stv-details-gebort" type="text" class="form-control" v-model="person.gebort">
					</div>
					<label for="stv-details-geburtsnation" class="col-sm-1 col-form-label">Geburtsnation</label>
					<div class="col-sm-3">
						<select id="stv-details-geburtsnation" class="form-control" v-model="person.geburtsnation">
							<option value="">-- keine Auswahl --</option>
							<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
							<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-svnr" class="col-sm-1 col-form-label">SVNR</label>
					<div class="col-sm-3">
						<input id="stv-details-svnr" type="text" class="form-control" v-model="person.svnr">
					</div>
					<label for="stv-details-ersatzkennzeichen" class="col-sm-1 col-form-label">Ersatzkennzeichen</label>
					<div class="col-sm-3">
						<input id="stv-details-ersatzkennzeichen" type="text" class="form-control" v-model="person.ersatzkennzeichen">
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-staatsbuergerschaft" class="col-sm-1 col-form-label">Staatsbürgerschaft</label>
					<div class="col-sm-3">
						<select id="stv-details-staatsbuergerschaft" class="form-control" v-model="person.staatsbuergerschaft">
							<option value="">-- keine Auswahl --</option>
							<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
							<option v-for="nation in nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
						</select>
					</div>
					<label for="stv-details-matr_nr" class="col-sm-1 col-form-label">Matrikelnummer</label>
					<div class="col-sm-3">
						<input id="stv-details-matr_nr" type="text" class="form-control" v-model="person.matr_nr">
					</div>
					<label for="stv-details-sprache" class="col-sm-1 col-form-label">Sprache</label>
					<div class="col-sm-3">
						<select id="stv-details-sprache" class="form-control" v-model="person.sprache">
							<option v-for="sprache in sprachen" :key="sprache.sprache" :value="sprache.sprache">{{sprache.sprache}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-geschlecht" class="col-sm-1 col-form-label">Geschlecht</label>
					<div class="col-sm-3">
						<select id="stv-details-geschlecht" class="form-control" v-model="person.geschlecht">
							<option v-for="geschlecht in geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
						</select>
					</div>
					<label for="stv-details-familienstand" class="col-sm-1 col-form-label">Familienstand</label>
					<div class="col-sm-3">
						<select id="stv-details-familienstand" class="form-control" v-model="person.familienstand">
							<option v-for="(bezeichnung, key) in familienstaende" :key="key" :value="key">{{bezeichnung}}</option>
						</select>
					</div>
				</div>
				<div class="row mb-3">
					<label for="stv-details-foto" class="col-sm-1 col-form-label">Foto</label>
					<div class="col-sm-3">
						<form-upload-image id="stv-details-foto" v-model="person.foto"></form-upload-image>
					</div>
					<label for="stv-details-anmerkung" class="col-sm-1 col-form-label">Anmerkung</label>
					<div class="col-sm-3">
						<textarea id="stv-details-anmerkung" class="form-control" v-text="anmerkung"></textarea>
					</div>
					<label for="stv-details-homepage" class="col-sm-1 col-form-label">Homepage</label>
					<div class="col-sm-3">
						<input id="stv-details-homepage" type="text" class="form-control" v-model="person.homepage">
					</div>
				</div>
			</template>
			<div v-else>
				Loading...
			</div>
		</fieldset>
		<fieldset>
			<legend>StudentIn</legend>
			<template v-if="studentIn">
				<div class="row mb-3">
					<label for="stv-details-student_uid" class="col-sm-1 col-form-label">UID</label>
					<div class="col-sm-3">
						<input id="stv-details-student_uid" type="text" class="form-control" v-model="studentIn.student_uid">
					</div>
					<label for="stv-details-personenkennzeichen" class="col-sm-1 col-form-label">Personenkennzeichen</label>
					<div class="col-sm-3">
						<input id="stv-details-personenkennzeichen" type="text" class="form-control" v-model="studentIn.personenkennzeichen">
					</div>
					<label for="stv-details-aktiv" class="col-sm-1 col-form-label">Aktiv</label>
					<div class="col-sm-3">
						<div class="form-check">
							<input id="stv-details-aktiv" type="checkbox" class="form-check-input" v-model="studentIn.aktiv">
						</div>
					</div>
				</div>
			</template>
			<div v-else>
				Loading...
			</div>
		</fieldset>
	</div>`
};
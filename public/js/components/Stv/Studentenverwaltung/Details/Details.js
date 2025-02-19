import CoreForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import FormUploadImage from '../../../Form/Upload/Image.js';

import CoreUdf from '../../../Udf/Udf.js';


export default {
	components: {
		CoreForm,
		FormInput,
		FormUploadImage,
		CoreUdf
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
		},
		lists: {
			from: 'lists'
		}
	},
	props: {
		modelValue: Object
	},
	data() {
		return {
			test: {udf_viaf: 'TEST'},
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
			udfChanges: false,
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
		},
		noImageSrc() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'skin/images/profilbild_dummy.jpg';
		}
	},
	watch: {
		modelValue(n) {
			this.updateStudent(n);
		},
		data: {
			// TODO(chris): use @input instead?
			handler(n) {
				let res = {};
				for (var k in this.original) {
					if (k == 'gebdatum') {
						if (new Date(this.original[k]).toString() != new Date(n[k]).toString())
							res[k] = n[k];
					} else {
						// TODO(chris): null && ""? should there be an exception for this?
						if (this.original[k] !== n[k] && !(this.original[k] === null && n[k] === ""))
							res[k] = n[k];
					}
				}
				this.changed = res;
			},
			deep: true
		}
	},
	methods: {
		updateStudent(n) {
			// TODO(chris): move to fhcapi.factory
			this.$fhcApi
				.get('api/frontend/v1/stv/student/get/' + n.prestudent_id)
				.then(result => {
					this.data = result.data;
					if (!this.data.familienstand)
						this.data.familienstand = '';
					this.original = {...(this.original || {}), ...this.data};
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		save() {
			if (!this.changedLength)
				return;

			this.$refs.form.clearValidation();
			this.$refs.form
				.post('api/frontend/v1/stv/student/save/' + this.modelValue.prestudent_id, this.changed)
				.then(result => {
					this.original = {...this.data};
					this.changed = {};
					this.$refs.form.setFeedback(true, result.data);
				})
				.catch(this.$fhcAlert.handleSystemError)
		},
		udfsLoaded(udfs) {
			this.original = {...(this.original || {}), ...udfs};
		},
		reload(){
			this.updateStudent(this.modelValue);
		}
	},
	created() {
		this.updateStudent(this.modelValue);
	},
	//TODO(chris): Phrasen
	//TODO(chris): Geburtszeit? Anzahl der Kinder?
	template: `
	<core-form ref="form" class="stv-details-details" @submit.prevent="save">
		<div class="position-sticky top-0 z-1">
			<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!changedLength">Speichern</button>
		</div>
		<fieldset class="overflow-hidden">
			<legend>Person</legend>
			<template v-if="data">
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Person ID"
						type="text"
						v-model="data.person_id"
						name="person_id"
						readonly
						>
					</form-input>
					<div v-if="showZugangscode" class="col-4">
						<label>Zugangscode</label>
						<div class="align-self-center">
							<span class="form-text">
								<a :href="cisRoot + 'addons/bewerbung/cis/registration.php?code=' + data.zugangscode + '&emailAdresse=' + data.email_privat" target="_blank">{{data.zugangscode}}</a>
							</span>
						</div>
					</div>
					<form-input
						v-if="showBpk"
						container-class="col-4"
						label="BPK"
						type="text"
						v-model="data.bpk"
						name="bpk"
						maxlength="28"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Anrede"
						type="text"
						v-model="data.anrede"
						name="anrede"
						maxlength="16"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Titel Pre"
						type="text"
						v-model="data.titelpre"
						name="titelpre"
						maxlength="64"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Titel Post"
						type="text"
						v-model="data.titelpost"
						name="titelpost"
						maxlength="32"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Nachname"
						type="text"
						v-model="data.nachname"
						name="nachname"
						maxlength="64"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Vorname"
						type="text"
						v-model="data.vorname"
						name="vorname"
						maxlength="32"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Vornamen"
						type="text"
						v-model="data.vornamen"
						name="vornamen"
						maxlength="128"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Wahlname"
						type="text"
						v-model="data.wahlname"
						name="wahlname"
						maxlength="128"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Geburtsdatum"
						type="DatePicker"
						v-model="data.gebdatum"
						name="gebdatum"
						:clearable="false"
						no-today
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						teleport
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Geburtsort"
						type="text"
						v-model="data.gebort"
						name="gebort"
						maxlength="128"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Geburtsnation"
						type="select"
						v-model="data.geburtsnation"
						name="geburtsnation"
 						>
						<option value="">-- keine Auswahl --</option>
						<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="SVNR"
						type="text"
						v-model="data.svnr"
						name="svnr"
						maxlength="16"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Ersatzkennzeichen"
						type="text"
						v-model="data.ersatzkennzeichen"
						name="ersatzkennzeichen"
						maxlength="10"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Staatsbürgerschaft"
						type="select"
						v-model="data.staatsbuergerschaft"
						name="staatsbuergerschaft"
 						>
						<option value="">-- keine Auswahl --</option>
						<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
					<form-input
						container-class="col-4"
						label="Matrikelnummer"
						type="text"
						v-model="data.matr_nr"
						name="matr_nr"
						maxlength="32"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Sprache"
						type="select"
						v-model="data.sprache"
						name="sprache"
 						>
						<option v-for="sprache in lists.sprachen" :key="sprache.sprache" :value="sprache.sprache">{{sprache.sprache}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Geschlecht"
						type="select"
						v-model="data.geschlecht"
						name="geschlecht"
 						>
						<option v-for="geschlecht in lists.geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
					</form-input>
					<form-input
						container-class="col-4"
						label="Familienstand"
						type="select"
						v-model="data.familienstand"
						name="familienstand"
 						>
						<option v-for="(bezeichnung, key) in familienstaende" :key="key" :value="key">{{bezeichnung}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Foto"
						type="UploadImage"
						v-model="data.foto"
						name="foto"
 						>
 						<img alt="No Image" :src="noImageSrc" class="w-100">
					</form-input>
					<form-input
						container-class="col-4"
						label="Anmerkung"
						type="textarea"
						v-model="data.anmerkung"
						name="anmerkung"
						rows="8"
 						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Homepage"
						type="text"
						v-model="data.homepage"
						name="homepage"
						maxlength="256"
 						>
					</form-input>
				</div>
			</template>
			<div v-else>
				Loading...
			</div>
			<core-udf @load="udfsLoaded" v-model="data" class="row-cols-3 g-3 mb-3" ci-model="person/person" :pk="{person_id:modelValue.person_id}"></core-udf>
		</fieldset>
		<fieldset v-if="data?.student_uid" class="overflow-hidden">
			<legend>StudentIn</legend>
			<template v-if="data">
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="UID"
						type="text"
						v-model="data.student_uid"
						name="student_uid"
						readonly
						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Personenkennzeichen"
						type="text"
						v-model="data.matrikelnr"
						name="matrikelnr"
						readonly
						>
					</form-input>
					<div class="col-4 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							label="Aktiv"
							type="checkbox"
							v-model="data.aktiv"
							name="aktiv"
							>
						</form-input>
					</div>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Semester"
						type="text"
						v-model="data.semester"
						name="semester"
						maxlength="2"
						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Verband"
						type="text"
						v-model="data.verband"
						name="verband"
						maxlength="1"
						>
					</form-input>
					<form-input
						container-class="col-4"
						label="Gruppe"
						type="text"
						v-model="data.gruppe"
						name="gruppe"
						maxlength="1"
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Alias"
						type="text"
						v-model="data.alias"
						name="alias"
						:disabled="aliasNotAllowed"
						>
					</form-input>
				</div>
			</template>
			<div v-else>
				Loading...
			</div>
		</fieldset>
	</core-form>`
};
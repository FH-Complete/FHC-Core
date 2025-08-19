import CoreForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import FormUploadImage from '../../../Form/Upload/Image.js';

import CoreUdf from '../../../Udf/Udf.js';

import ApiStvDetails from '../../../../api/factory/stv/details.js';


export default {
	name: "TabDetails",
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
		},
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		}
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			original: null,
			data: null,
			changed: {}
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
		},
		familienstaende() {
			return {
				"": "-- " + this.$p.t('fehlermonitoring', 'keineAuswahl') + " --",
				"g": this.$p.t('person', 'geschieden'),
				"l": this.$p.t('person', 'ledig'),
				"v": this.$p.t('person', 'verheiratet'),
				"w": this.$p.t('person', 'verwitwet'),
			};
		}
	},
	watch: {
		modelValue(n) {
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
			return this.$api
				.call(ApiStvDetails.get(n.prestudent_id, this.currentSemester))
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
			return this.$refs.form
				.call(ApiStvDetails.save(
					this.modelValue.prestudent_id,
					this.currentSemester,
					this.changed
					))
				.then(result => {
					this.original = {...this.data};
					this.changed = {};

					const feedback = result.data;

					//to avoid empty alert for updateam, updatevon
					const cleanedFeedback = {};

					const formElement = this.$refs.form.$el || this.$refs.form;
					const inputElements = formElement.querySelectorAll('[name]');
					const validFieldNames = Array.from(inputElements).map(el => el.getAttribute('name'));

					for (const key in feedback) {
						if (validFieldNames.includes(key)) {
							cleanedFeedback[key] = feedback[key];
						}
					}

					if (Object.keys(cleanedFeedback).length > 0) {
						this.$refs.form.setFeedback(true, cleanedFeedback);
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					}

					this.$reloadList();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		udfsLoaded(udfs) {
			this.original = {...(this.original || {}), ...udfs};
		},
		reload(){
			this.updateStudent(this.modelValue);
		},
		sendInfomail(){
			const subject = this.$p.t('person', 'betreffProfilfoto');
			const subjectEncoded = encodeURIComponent(subject);

			const body = this.$p.t('person', 'mailText_profilfoto');
			const bodyWithNewLines = body.replace(/\\n/g, '\n');
			const bodyEncoded = encodeURIComponent(bodyWithNewLines);

			window.location.href = "mailto:" + this.modelValue.mail_intern + "?subject=" + subjectEncoded + "&body=" + bodyEncoded;
		}
	},
	created() {
		this.updateStudent(this.modelValue);
	},
	template: `
	<core-form ref="form" class="stv-details-details" @submit.prevent="save">
		<div class="position-sticky top-0 z-1">
			<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!changedLength">{{$p.t('ui', 'speichern')}}</button>
		</div>
		<fieldset class="overflow-hidden">
			<legend>Person</legend>
			<template v-if="data">
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('person_id')"
						container-class="col-4 stv-details-details-person_id"
						:label="$p.t('person', 'person_id')"
						type="text"
						v-model="data.person_id"
						name="person_id"
						readonly
						>
					</form-input>
					<div v-if="showZugangscode && !config.hiddenFields.includes('zugangscode')" class="col-4 stv-details-details-zugangscode">
						<label>{{$p.t('global', 'zugangscode')}}</label>
						<div class="align-self-center">
							<span class="form-text">
								<a :href="cisRoot + 'addons/bewerbung/cis/registration.php?code=' + data.zugangscode + '&emailAdresse=' + data.email_privat" target="_blank">{{data.zugangscode}}</a>
							</span>
						</div>
					</div>
					<form-input
						v-if="showBpk && !config.hiddenFields.includes('bpk')"
						container-class="col-4 stv-details-details-bpk"
						:label="$p.t('person', 'bpk')"
						type="text"
						v-model="data.bpk"
						name="bpk"
						maxlength="28"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('anrede')"
						container-class="col-4 stv-details-details-anrede"
						:label="$p.t('person', 'anrede')"
						type="text"
						v-model="data.anrede"
						name="anrede"
						maxlength="16"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('titelpre')"
						container-class="col-4 stv-details-details-titelpre"
						:label="$p.t('person', 'titelpre')"
						type="text"
						v-model="data.titelpre"
						name="titelpre"
						maxlength="64"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('titelpost')"
						container-class="col-4 stv-details-details-titelpost"
						:label="$p.t('person', 'titelpost')"
						type="text"
						v-model="data.titelpost"
						name="titelpost"
						maxlength="32"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('nachname')"
						container-class="col-4 stv-details-details-nachname"
						:label="$p.t('person', 'nachname')"
						type="text"
						v-model="data.nachname"
						name="nachname"
						maxlength="64"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('vorname')"
						container-class="col-4 stv-details-details-vorname"
						:label="$p.t('person', 'vorname')"
						type="text"
						v-model="data.vorname"
						name="vorname"
						maxlength="32"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('vornamen')"
						container-class="col-4 stv-details-details-vornamen"
						:label="$p.t('person', 'vornamen')"
						type="text"
						v-model="data.vornamen"
						name="vornamen"
						maxlength="128"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('wahlname')"
						container-class="col-4 stv-details-details-wahlname"
						:label="$p.t('person', 'wahlname')"
						type="text"
						v-model="data.wahlname"
						name="wahlname"
						maxlength="128"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('gebdatum')"
						container-class="col-4 stv-details-details-gebdatum"
						:label="$p.t('person', 'geburtsdatum')"
						type="DatePicker"
						v-model="data.gebdatum"
						name="gebdatum"
						:clearable="false"
						no-today
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						teleport
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('gebort')"
						container-class="col-4 stv-details-details-gebort"
						:label="$p.t('person', 'geburtsort')"
						type="text"
						v-model="data.gebort"
						name="gebort"
						maxlength="128"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('geburtsnation')"
						container-class="col-4 stv-details-details-geburtsnation"
						:label="$p.t('person', 'geburtsnation')"
						type="select"
						v-model="data.geburtsnation"
						name="geburtsnation"
 						>
						<option value="">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('svnr')"
						container-class="col-4 stv-details-details-svnr"
						:label="$p.t('person', 'svnr')"
						type="text"
						v-model="data.svnr"
						name="svnr"
						maxlength="16"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('ersatzkennzeichen')"
						container-class="col-4 stv-details-details-ersatzkennzeichen"
						:label="$p.t('person', 'ersatzkennzeichen')"
						type="text"
						v-model="data.ersatzkennzeichen"
						name="ersatzkennzeichen"
						maxlength="10"
 						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('staatsbuergerschaft')"
						container-class="col-4 stv-details-details-staatsbuergerschaft"
						:label="$p.t('person', 'staatsbuergerschaft')"
						type="select"
						v-model="data.staatsbuergerschaft"
						name="staatsbuergerschaft"
 						>
						<option value="">-- {{$p.t('fehlermonitoring', 'keineAuswahl')}} --</option>
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('matr_nr')"
						container-class="col-4 stv-details-details-matr_nr"
						:label="$p.t('person', 'matrikelnummer')"
						type="text"
						v-model="data.matr_nr"
						name="matr_nr"
						maxlength="32"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('sprache')"
						container-class="col-4 stv-details-details-sprache"
						:label="$p.t('person', 'sprache')"
						type="select"
						v-model="data.sprache"
						name="sprache"
 						>
						<option v-for="sprache in lists.sprachen" :key="sprache.sprache" :value="sprache.sprache">{{sprache.sprache}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('geschlecht')"
						container-class="col-4 stv-details-details-geschlecht"
						:label="$p.t('person', 'geschlecht')"
						type="select"
						v-model="data.geschlecht"
						name="geschlecht"
 						>
						<option v-for="geschlecht in lists.geschlechter" :key="geschlecht.geschlecht" :value="geschlecht.geschlecht">{{geschlecht.bezeichnung}}</option>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('familienstand')"
						container-class="col-4 stv-details-details-familienstand"
						:label="$p.t('person', 'familienstand')"
						type="select"
						v-model="data.familienstand"
						name="familienstand"
 						>
						<option v-for="(bezeichnung, key) in familienstaende" :key="key" :value="key">{{bezeichnung}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('foto')"
						container-class="col-4 stv-details-details-foto"
						:label="$p.t('person', 'foto')"
						type="UploadImage"
						titleActionButton="Infomail"
						v-model="data.foto"
						name="foto"
						@actionbutton-clicked="sendInfomail"
 						>
 						<img alt="No Image" :src="noImageSrc" class="w-100">
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('anmerkung')"
						container-class="col-4 stv-details-details-anmerkung"
						:label="$p.t('global', 'anmerkung')"
						type="textarea"
						v-model="data.anmerkung"
						name="anmerkung"
						rows="8"
 						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('homepage')"
						container-class="col-4 stv-details-details-homepage"
						:label="$p.t('person', 'homepage')"
						type="text"
						v-model="data.homepage"
						name="homepage"
						maxlength="256"
 						>
					</form-input>
				</div>
			</template>
			<div v-else>
				{{$p.t('ui', 'dropdownLoading')}}...
			</div>
			<core-udf
				v-if="!config.hideUDFs"
				@load="udfsLoaded"
				v-model="data"
				class="row-cols-3 g-3 mb-3"
				ci-model="person/person"
				:pk="{person_id:modelValue.person_id}"
				>
			</core-udf>
		</fieldset>
		<fieldset v-if="data?.student_uid" class="overflow-hidden">
			<legend>{{$p.t('person', 'studentIn')}}</legend>
			<template v-if="data">
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('student_uid')"
						container-class="col-4 stv-details-details-student_uid"
						:label="$p.t('person', 'uid')"
						type="text"
						v-model="data.student_uid"
						name="student_uid"
						readonly
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('matrikelnr')"
						container-class="col-4 stv-details-details-matrikelnr"
						:label="$p.t('person', 'personenkennzeichen')"
						type="text"
						v-model="data.matrikelnr"
						name="matrikelnr"
						readonly
						>
					</form-input>
					<div class="col-4 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('aktiv')"
							container-class="form-check stv-details-details-aktiv"
							:label="$p.t('person', 'aktiv')"
							type="checkbox"
							v-model="data.aktiv"
							name="aktiv"
							>
						</form-input>
					</div>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('semester')"
						container-class="col-4 stv-details-details-semester"
						:label="$p.t('lehre', 'semester')"
						type="text"
						v-model="data.semester"
						name="semester"
						maxlength="2"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('verband')"
						container-class="col-4 stv-details-details-verband"
						:label="$p.t('lehre', 'verband')"
						type="text"
						v-model="data.verband"
						name="verband"
						maxlength="1"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('gruppe')"
						container-class="col-4 stv-details-details-gruppe"
						:label="$p.t('lehre', 'gruppe')"
						type="text"
						v-model="data.gruppe"
						name="gruppe"
						maxlength="1"
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('alias')"
						container-class="col-4 stv-details-details-alias"
						:label="$p.t('person', 'alias')"
						type="text"
						v-model="data.alias"
						name="alias"
						:disabled="aliasNotAllowed"
						>
					</form-input>
				</div>
			</template>
			<div v-else>
				{{$p.t('ui', 'dropdownLoading')}}...
			</div>
		</fieldset>
	</core-form>`
};
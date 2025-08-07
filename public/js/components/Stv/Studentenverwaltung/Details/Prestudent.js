import FormForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import TblHistory from "./Prestudent/History.js";

import CoreUdf from '../../../Udf/Udf.js';

import ApiStvPrestudent from '../../../../api/factory/stv/prestudent.js';

export default {
	name: "TabPrestudent",
	components: {
		FormForm,
		FormInput,
		TblHistory,
		CoreUdf
	},
	inject: {
		lists: {
			from: 'lists'
		},
		hasPrestudentPermission: {
			from: 'hasPrestudentPermission',
			default: false
		},
		hasAssistenzPermission: {
			from: 'hasAssistenzPermission',
			default: false
		},
		hasAdminPermission: {
			from: 'hasAdminPermission',
			default: false
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
			data: [],
			listZgvs: [],
			listZgvsmaster: [],
			listZgvsdoktor: [],
			listStgs: [],
			listAusbildung: [],
			listAufmerksamdurch: [],
			listBerufe: [],
			listFoerderrelevant: [
				{ text: 'wie Studiengang', value: null },
				{ text: 'Ja', value: true },
				{ text: 'Nein', value: false }
			],
			listStgTyp: [],
			listBisStandort: [],
			initialFormData: {},
			deltaArray: {},
			actionUpdate: false,
			filteredZgvs: [],
			selectedZgv: null,
			filteredMasterZgvs: [],
			selectedMasterZgv: null,
			filteredDoktorZgvs: [],
			selectedDoktorZgv: null
		};
	},
	computed: {
		deltaLength() {
			return Object.keys(this.deltaArray).length;
		}
	},
	watch: {
		data: {
			// TODO(chris): use @input instead?
			handler(n) {

				const delta = {};
				for (const key in this.data) {
					if (this.data[key] !== this.initialFormData[key]) {
						delta[key] = this.data[key];
						this.actionUpdate = true;
					}
				}
				this.deltaArray = delta;
			},
			deep: true
		},
		modelValue(n){
			this.loadPrestudent(n);
		},
		selectedZgv(newVal) {
			this.data.zgv_code = newVal?.zgv_code || null;
		},
		selectedMasterZgv(newVal) {
			this.data.zgvmas_code = newVal?.zgvmas_code || null;
		},
		selectedDoktorZgv(newVal) {
			this.data.zgvdoktor_code = newVal?.zgvdoktor_code || null;
		},
	},

	methods: {
		loadPrestudent() {
			return this.$api
				.call(ApiStvPrestudent.get(this.modelValue.prestudent_id, this.currentSemester))
				.then(result => result.data)
				.then(result => {
					this.data = result;

					//neue DataVariable um ein Delta der vorgenommenen Änderungen berechnen zu können
					this.initialFormData = {...this.data};
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		udfsLoaded(udfs) {
			this.initialFormData = {...(this.initialFormData || {}), ...udfs};
		},
		updatePrestudent() {
			return this.$refs.form
				.call(ApiStvPrestudent.updatePrestudent(this.modelValue.prestudent_id, this.deltaArray))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.initialFormData = {...this.data};
					this.deltaArray = [];
					this.actionUpdate = false;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
				});
		},
		filterZgvs(event){
			const query = event.query.toLowerCase();
			this.filteredZgvs = this.listZgvs.filter(item =>
				item.label.toLowerCase().includes(query)
			)
		},
		filterMasterZgvs(event){
			const query = event.query.toLowerCase();
			this.filteredMasterZgvs = this.listZgvsmaster.filter(item =>
				item.label.toLowerCase().includes(query)
			)
		},
		filterDoktorZgvs(event){
			const query = event.query.toLowerCase();
			this.filteredDoktorZgvs = this.listZgvsdoktor.filter(item =>
				item.label.toLowerCase().includes(query)
			)
		},
	},
	created() {
		this.loadPrestudent();
		this.$api
			.call(ApiStvPrestudent.getBezeichnungZGV())
			.then(result => result.data)
			.then(result => {
				this.listZgvs = result;
				this.selectedZgv = this.listZgvs.find(
					item => item.zgv_code === this.data.zgv_code
				);
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getBezeichnungMZgv())
			.then(result => result.data)
			.then(result => {
				this.listZgvsmaster = result;
				this.selectedMasterZgv = this.listZgvsmaster.find(
					item => item.zgvmas_code === this.data.zgvmas_code
				);
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getBezeichnungDZgv())
			.then(result => result.data)
			.then(result => {
				this.listZgvsdoktor = result;
				this.selectedDoktorZgv = this.listZgvsdoktor.find(
					item => item.zgvdoktor_code === this.data.zgvdoktor_code
				);
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getStgs())
			.then(result => result.data)
			.then(result => {
				this.listStgs = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getAusbildung())
			.then(result => result.data)
			.then(result => {
				this.listAusbildung = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getAufmerksamdurch())
			.then(result => result.data)
			.then(result => {
				this.listAufmerksamdurch = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getBerufstaetigkeit())
			.then(result => result.data)
			.then(result => {
				this.listBerufe = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getTypenStg())
			.then(result => result.data)
			.then(result => {
				this.listStgTyp = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$api
			.call(ApiStvPrestudent.getBisstandort())
			.then(result => result.data)
			.then(result => {
				this.listBisStandort = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-prestudent h-100 pb-3">
		<form-form ref="form" class="stv-details-prestudent" @submit.prevent="updatePrestudent">
			<div class="position-sticky top-0 z-1">
				<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!deltaLength">Speichern</button>
			</div>
			<fieldset class="overflow-hidden">
				<legend>{{$p.t('lehre', 'title_zgv')}} {{modelValue.nachname}} {{modelValue.vorname}}</legend>
				<div class="row mb-3">
					<form-input
					v-if="!config.hiddenFields.includes('prestudent_id')"
						container-class="col-3 stv-details-prestudent-prestudent_id"
						:label="$p.t('ui', 'prestudent_id')"
						type="text"
						v-model="data.prestudent_id"
						name="prestudent_id"
						readonly
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('person_id')"
						container-class="col-3 stv-details-prestudent-person_id"
						:label="$p.t('person', 'person_id')"
						type="text"
						v-model="data.person_id"
						name="person_id"
						readonly
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('zgv_code')"
						container-class="col-3 stv-details-prestudent-zgv_code"
						label="ZGV"
						type="autocomplete"
						v-model="selectedZgv"
						forceSelection
						optionLabel="label"
						optionValue="zgv_code"
						:suggestions="filteredZgvs"
						dropdown
						name="zgv_code"
						@complete="filterZgvs"
						>
							<template #option="slotProps">
								<div
									:class="!slotProps.option.aktiv
									? 'item-inactive'
									: ''"
									>
										{{slotProps.option.label}}
								</div>
							</template>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvOrt')"
						container-class="col-3 stv-details-prestudent-zgvOrt"
						:label="$p.t('infocenter', 'zgvOrt')"
						type="text"
						v-model="data.zgvort"
						name="zgvort"
						>
					</form-input>	
					<form-input
						v-if="!config.hiddenFields.includes('zgvDatum')"
						container-class="col-3 stv-details-prestudent-zgvDatum"
						:label="$p.t('infocenter', 'zgvDatum')"
						type="DatePicker"
						v-model="data.zgvdatum"
						name="zgvdatum"
						no-today
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvNation')"
						container-class="col-3 stv-details-prestudent-zgvNation"
						:label="$p.t('infocenter', 'zgvNation')"
						type="select"
						v-model="data.zgvnation"
						name="zgvnation"
						>
						<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('zgvmas_code')"
						container-class="col-3 stv-details-prestudent-zgvmas_code"
						:label="$p.t('lehre', 'zgvMaster')"
						type="autocomplete"
						v-model="selectedMasterZgv"
						forceSelection
						optionLabel="label"
						optionValue="zgvmas_code"
						:suggestions="filteredMasterZgvs"
						dropdown
						name="zgvmas_code"
						@complete="filterMasterZgvs"
						>
							<template #option="slotProps">
								<div
									:class="!slotProps.option.aktiv
									? 'item-inactive'
									: ''"
									>
										{{slotProps.option.label}}
								</div>
							</template>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvmaort')"
						container-class="col-3 stv-details-prestudent-zgvmaort"
						:label="$p.t('lehre', 'zgvMasterOrt')"
						type="text"
						v-model="data.zgvmaort"
						name="zgvmaort"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvmadatum')"
						container-class="col-3 stv-details-prestudent-zgvmadatum"
						:label="$p.t('lehre', 'zgvMasterDatum')"
						type="DatePicker"
						v-model="data.zgvmadatum"
						name="zgvmadatum"
						no-today
						auto-apply
						:enable-time-picker="false"
						text-input
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvmanation')"
						container-class="col-3 stv-details-prestudent-zgvmanation"
						:label="$p.t('lehre', 'zgvMasterNation')"
						type="select"
						v-model="data.zgvmanation"
						name="zgvmanation"
						>
						<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
				</div>
				<!--ZGV Doktor-->
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('zgvdoktor_code')"
						container-class="col-3 stv-details-prestudent-zgvdoktor_code"
						:label="$p.t('lehre', 'zgvDoktor')"
						type="autocomplete"
						v-model="selectedDoktorZgv"
						forceSelection
						optionLabel="label"
						optionValue="zgvdoktor_code"
						:suggestions="filteredDoktorZgvs"
						dropdown
						name="zgvdoktor_code"
						@complete="filterDoktorZgvs"
						>
							<template #option="slotProps">
								<div
									:class="!slotProps.option.aktiv
									? 'item-inactive'
									: ''"
									>
										{{slotProps.option.label}}
								</div>
							</template>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvdoktorort')"
						container-class="col-3 stv-details-prestudent-zgvdoktorort"
						:label="$p.t('lehre', 'zgvDoktorOrt')"
						type="text"
						v-model="data.zgvdoktorort"
						name="zgvdoktorort"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvdoktordatum')"
						container-class="col-3 stv-details-prestudent-zgvdoktordatum"
						:label="$p.t('lehre', 'zgvDoktorDatum')"
						type="DatePicker"
						v-model="data.zgvdoktordatum"
						name="zgvdoktordatum"
						no-today
						auto-apply
						text-input
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('zgvdoktornation')"
						container-class="col-3 stv-details-prestudent-zgvdoktornation"
						:label="$p.t('lehre', 'zgvDoktorNation')"
						type="select"
						v-model="data.zgvdoktornation"
						name="zgvdoktornation"
						>
						<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
				</div>
																
				<div class="row mb-3">
					<div class="col-3 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('zgv_erfuellt')"
							container-class="form-check stv-details-prestudent-zgv_erfuellt"
							:label="$p.t('infocenter', 'zgvErfuellt')"
							type="checkbox"
							v-model="data.zgv_erfuellt"
							name="zgv_erfuellt"
							>
						</form-input>
					</div>
					<div class="col-3 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('zgvmas_erfuellt')"
							container-class="form-check stv-details-prestudent-zgvmas_erfuellt"
							:label="$p.t('lehre', 'zgvMasterErfuellt')"
							type="checkbox"
							v-model="data.zgvmas_erfuellt"
							name="zgvmas_erfuellt"
							>
						</form-input>
					</div>
					<div class="col-3 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('zgvdoktor_erfuellt')"
							container-class="form-check stv-details-prestudent-zgvdoktor_erfuellt"
							:label="$p.t('lehre', 'zgvDoktorErfuellt')"
							type="checkbox"
							v-model="data.zgvdoktor_erfuellt"
							name="zgvdoktor_erfuellt"
							>
						</form-input>
					</div>
				</div>
			</fieldset>
			<fieldset class="overflow-hidden">
				<legend>PrestudentIn</legend>
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('aufmerksamdurch_kurzbz')"
						container-class="col-4 stv-details-prestudent-aufmerksamdurch_kurzbz"
						:label="$p.t('person', 'aufmerksamDurch')"
						type="select"
						v-model="data.aufmerksamdurch_kurzbz"
						name="aufmerksamDurch"
						>
						<option v-for="adurch in listAufmerksamdurch" :key="adurch.aufmerksamdurch_kurzbz" :value="adurch.aufmerksamdurch_kurzbz">{{adurch.beschreibung}}</option>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('berufstaetigkeit_code')"
						container-class="col-4 stv-details-prestudent-berufstaetigkeit_code"
						:label="$p.t('person', 'berufstaetigkeit')"
						type="select"
						v-model="data.berufstaetigkeit_code"
						name="berufstaetigkeit_code"
						>
						<option v-for="beruf in listBerufe" :key="beruf.berufstaetigkeit_code" :value="beruf.berufstaetigkeit_code">{{beruf.berufstaetigkeit_bez}} </option>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('ausbildungcode')"
						container-class="col-4 stv-details-prestudent-ausbildungcode"
						:label="$p.t('person', 'ausbildung')"
						type="select"
						v-model="data.ausbildungcode"
						name="ausbildungcode"
						>
						<option v-for="ausbld in listAusbildung" :key="ausbld.ausbildungcode" :value="ausbld.ausbildungcode">{{ausbld.ausbildungbez}} </option>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('aufnahmeschluessel')"
						container-class="col-4 stv-details-prestudent-aufnahmeschluessel"
						label="Aufnahmeschlüssel"
						type="text"
						v-model="data.aufnahmeschluessel"
						name="aufnahmeschluessel"
						disabled			
						>
					</form-input>
					
					<div class="col-4 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('facheinschlaegigBerufstaetig')"
							container-class="form-check stv-details-prestudent-facheinschlaegigBerufstaetig"
							:label="$p.t('person', 'facheinschlaegigBerufstaetig')"
							type="checkbox"
							v-model="data.facheinschlberuf"
							name="facheinschlberuf"
							>
						</form-input>
					</div>
					
					<form-input
						v-if="!config.hiddenFields.includes('standort_code')"
						container-class="col-4 stv-details-prestudent-standort_code"
						:label="$p.t('person', 'bisstandort')"
						type="select"
						v-model="data.standort_code"
						name="standort_code"
						>
						<option v-for="standort in listBisStandort" :key="standort.standort_code" :value="standort.standort_code">{{standort.bezeichnung}}</option>
					</form-input>		 
				</div>
								
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('studiengang_kz')"
						container-class="col-4 stv-details-prestudent-studiengang_kz"
						:label="$p.t('lehre', 'studiengang')"
						type="select"
						v-model="data.studiengang_kz"
						name="studiengang_kz"
						disabled
						>
						<option v-for="stg in listStgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">{{stg.kuerzel}} - {{stg.bezeichnung}}</option>
					</form-input>
					<form-input
						v-if="!config.hiddenFields.includes('gsstudientyp_kurzbz')"
						container-class="col-4 stv-details-prestudent-gsstudientyp_kurzbz"
						:label="$p.t('lehre', 'studientyp')"
						type="select"
						v-model="data.gsstudientyp_kurzbz"
						name="gsstudientyp_kurzbz"
						>
						<option v-for="typ in listStgTyp" :key="typ.gsstudientyp_kurzbz" :value="typ.gsstudientyp_kurzbz">{{typ.bezeichnung}}</option>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						v-if="!config.hiddenFields.includes('anmerkung')"
						container-class="col-4 stv-details-prestudent-anmerkung"
						:label="$p.t('global', 'anmerkung')"
						type="text"
						v-model="data.anmerkung"
						name="anmerkung"
						>
					</form-input>
					<div class="col-2 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('bismelden')"
							container-class="form-check stv-details-prestudent-bismelden"
							:label="$p.t('person', 'bismelden')"
							type="checkbox"
							v-model="data.bismelden"
							name="bismelden"
							>
						</form-input>
					</div>
					<div class="col-2 pt-4 d-flex align-items-center">
						<form-input
							v-if="!config.hiddenFields.includes('dual')"
							container-class="form-check stv-details-prestudent-dual"
							:label="$p.t('lehre', 'dual')"
							type="checkbox"
							v-model="data.dual"
							name="dual"
							>
						</form-input>
					</div>
					
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'foerderrelevant')"
						type="select"
						v-model="data.foerderrelevant"
						name="foerderrelevant"
						>
						<option v-for="entry in listFoerderrelevant" :value="entry.value">{{entry.text}}</option>
					</form-input>
					
					<form-input
						v-if="!config.hiddenFields.includes('priorisierung')"
						container-class="col-1"
						:label="$p.t('lehre', 'prioritaet')"
						type="text"
						v-model="data.priorisierung"
						name="priorisierung"
						:disabled="!hasPrestudentPermission"
						>
					</form-input>
				</div>
				<core-udf
					v-if="!config.hideUDFs" 
					@load="udfsLoaded" 
					v-model="data" 
					class="row-cols-3 g-3 mb-3" 
					ci-model="crm/prestudent" 
					:pk="{prestudent_id:modelValue.prestudent_id}"
					>
				</core-udf>
			</fieldset>
		</form-form>
		<div>
			<legend>Gesamthistorie</legend>
			<tbl-history :person-id="modelValue.person_id" :prestudent-id="modelValue.prestudent_id"></tbl-history>
		</div>
	</div>
	`
};
import FormForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import TblHistory from "./Prestudent/History.js";

import CoreUdf from '../../../Udf/Udf.js';

export default {
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
		showZgvErfuellt: {
			from: 'configShowZgvErfuellt',
			default: false
		},
		showZgvDoktor: {
			from: 'configShowZgvDoktor',
			default: false
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
		defaultSemester: {
			from: 'defaultSemester',
		}
	},
	props: {
		modelValue: Object,
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
			initialFormData: {},
			deltaArray: {},
			actionUpdate: false
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
		}
	},

	methods: {
		loadPrestudent() {
			this.$fhcApi
				.get('api/frontend/v1/stv/prestudent/get/' + this.modelValue.prestudent_id)
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
		updatePrestudent(){
			this.$refs.form
				.post('api/frontend/v1/stv/prestudent/updatePrestudent/' + this.modelValue.prestudent_id, this.deltaArray)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.deltaArray = [];
					this.actionUpdate = false;
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					window.scrollTo(0, 0);
				});
		},
	},
	created() {
		this.loadPrestudent();
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getBezeichnungZGV')
			.then(result => result.data)
			.then(result => {
				this.listZgvs = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getBezeichnungMZgv')
			.then(result => result.data)
			.then(result => {
				this.listZgvsmaster = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getBezeichnungDZgv')
			.then(result => result.data)
			.then(result => {
				this.listZgvsdoktor = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/lists/getStgs')
			.then(result => result.data)
			.then(result => {
				this.listStgs = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getAusbildung')
			.then(result => result.data)
			.then(result => {
				this.listAusbildung = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getAufmerksamdurch')
			.then(result => result.data)
			.then(result => {
				this.listAufmerksamdurch = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getBerufstaetigkeit')
			.then(result => result.data)
			.then(result => {
				this.listBerufe = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/prestudent/getTypenStg')
			.then(result => result.data)
			.then(result => {
				this.listStgTyp = result;
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
						container-class="col-3"
						label="Prestudent_id"
						type="text"
						v-model="data.prestudent_id"
						name="prestudent_id"
						readonly
						>
					</form-input>
					<form-input
						container-class="col-3"
						label="Person_id"
						type="text"
						v-model="data.person_id"
						name="person_id"
						readonly
						>
					</form-input>
				</div>
				<div class="row mb-3">
					<form-input
						container-class="col-3"
						label="ZGV"
						type="select"
						v-model="data.zgv_code"
						name="zgvcode"
						>
					<option v-for="zgv in listZgvs" :key="zgv.zgv_code" :value="zgv.zgv_code">{{zgv.zgv_bez}}</option>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('infocenter', 'zgvOrt')"
						type="text"
						v-model="data.zgvort"
						name="zgvort"
						>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('infocenter', 'zgvDatum')"
						type="DatePicker"
						v-model="data.zgvdatum"
						name="zgvdatum"
						no-today
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
					<form-input
						container-class="col-3"
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
						container-class="col-3"
						:label="$p.t('lehre', 'zgvMaster')"
						type="select"
						v-model="data.zgvmas_code"
						name="zgvmascode"
						>
						<option v-for="mzgv in listZgvsmaster" :key="mzgv.zgvmas_code" :value="mzgv.zgvmas_code">{{mzgv.zgvmas_bez}}</option>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'zgvMasterOrt')"
						type="text"
						v-model="data.zgvmaort"
						name="zgvmaort"
						>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'zgvMasterDatum')"
						type="DatePicker"
						v-model="data.zgvmadatum"
						name="zgvmadatum"
						no-today
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
					<form-input
						container-class="col-3"
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
				<div v-if="showZgvDoktor" class="row mb-3">
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'zgvDoktor')"
						type="select"
						v-model="data.zgvdoktor_code"
						name="zgvdoktor_code"
						>
						<option v-for="zgv in listZgvsdoktor" :key="zgv.zgvdoktor_code" :value="zgv.zgvdoktor_code">{{zgv.zgvdoktor_bez}}</option>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'zgvDoktorOrt')"
						type="text"
						v-model="data.zgvdoktorort"
						name="zgvdoktorort"
						>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'zgvDoktorDatum')"
						type="DatePicker"
						v-model="data.zgvdoktordatum"
						name="zgvdoktordatum"
						no-today
						auto-apply
						:enable-time-picker="false"
						format="dd.MM.yyyy"
						preview-format="dd.MM.yyyy"
						:teleport="true"
						>
					</form-input>
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'zgvDoktorNation')"
						type="select"
						v-model="data.zgvdoktornation"
						name="zgvdoktornation"
						>
						<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
						<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
					</form-input>
				</div>
																
				<div v-if="showZgvErfuellt" class="row mb-3">
					<div class="col-3 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							:label="$p.t('infocenter', 'zgvErfuellt')"
							type="checkbox"
							v-model="data.zgv_erfuellt"
							name="zgv_erfuellt"
							>
						</form-input>
					</div>
					<div class="col-3 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							:label="$p.t('infocenter', 'zgvMasterErfuellt')"
							type="checkbox"
							v-model="data.zgvmas_erfuellt"
							name="zgvmas_erfuellt"
							>
						</form-input>
					</div>
					<div v-if="showZgvDoktor" class="col-3 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							:label="$p.t('infocenter', 'zgvDoktorErfuellt')"
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
						container-class="col-4"
						:label="$p.t('person', 'aufmerksamDurch')"
						type="select"
						v-model="data.aufmerksamdurch_kurzbz"
						name="aufmerksamDurch"
						>
						<option v-for="adurch in listAufmerksamdurch" :key="adurch.aufmerksamdurch_kurzbz" :value="adurch.aufmerksamdurch_kurzbz">{{adurch.beschreibung}}</option>
					</form-input>
					<form-input
						container-class="col-4"
						:label="$p.t('person', 'berufstaetigkeit')"
						type="select"
						v-model="data.berufstaetigkeit_code"
						name="berufstaetigkeit_code"
						>
						<option v-for="beruf in listBerufe" :key="beruf.berufstaetigkeit_code" :value="beruf.berufstaetigkeit_code">{{beruf.berufstaetigkeit_bez}} </option>
					</form-input>
					<form-input
						container-class="col-4"
						:label="$p.t('person', 'ausbildung')"
						type="select"
						v-model="data.ausbildungcode"
						name="ausbildungcode"
						>
						<option v-for="ausbld in listAusbildung" :key="ausbld.ausbildungcode" :value="ausbld.ausbildungcode">{{ausbld.ausbildungbez}} </option>
					</form-input>
				</div>
				
				` + /* TODO(chris): Ausgeblendet für Testing
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						label="Aufnahmeschlüssel"
						type="text"
						v-model="data.aufnahmeschluessel"
						name="aufnahmeschluessel"
						disabled							
						>
					</form-input>
					
					<div class="col-4 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							:label="$p.t('person', 'facheinschlaegigBerufstaetig')"
							type="checkbox"
							v-model="data.facheinschlberuf"
							name="facheinschlberuf"
							>
						</form-input>
					</div>
					
					<!--Todo(manu) validierung Integer, liste hier null-->
					<form-input
						container-class="col-4"
						:label="$p.t('person', 'bisstandort')"
						type="text"
						v-model="data.standort_code"
						name="standort_code"
						disabled
						>
					</form-input>
				 
				</div>
				*/`
				
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						:label="$p.t('lehre', 'studiengang')"
						type="select"
						v-model="data.studiengang_kz"
						name="studiengang_kz"
						disabled
						>
						<option v-for="stg in listStgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">{{stg.kuerzel}} - {{stg.bezeichnung}} </option>
					</form-input>
					<form-input
						container-class="col-4"
						:label="$p.t('lehre', 'studientyp')"
						type="select"
						v-model="data.gsstudientyp_kurzbz"
						name="gsstudientyp_kurzbz"
						>
						<option v-for="typ in listStgTyp" :key="typ.typ" :value="typ.typ">{{typ.bezeichnung}} </option>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
						container-class="col-4"
						:label="$p.t('global', 'anmerkung')"
						type="text"
						v-model="data.anmerkung"
						name="anmerkung"
						>
					</form-input>
					<div class="col-2 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							:label="$p.t('person', 'bismelden')"
							type="checkbox"
							v-model="data.bismelden"
							name="bismelden"
							>
						</form-input>
					</div>
					<div class="col-2 pt-4 d-flex align-items-center">
						<form-input
							container-class="form-check"
							:label="$p.t('lehre', 'dual')"
							type="checkbox"
							v-model="data.dual"
							name="dual"
							>
						</form-input>
					</div>
					` + /* TODO(chris): Ausgeblendet für Testing
					<form-input
						container-class="col-3"
						:label="$p.t('lehre', 'foerderrelevant')"
						type="select"
						v-model="data.foerderrelevant"
						name="foerderrelevant"
						>
						<option v-for="entry in listFoerderrelevant" :value="entry.value">{{entry.text}}</option>
					</form-input>
					*/`
					
					<form-input
						container-class="col-1"
						:label="$p.t('lehre', 'prioritaet')"
						type="text"
						v-model="data.priorisierung"
						name="priorisierung"
						:disabled="!hasPrestudentPermission"
						>
					</form-input>
				</div>
				<core-udf @load="udfsLoaded" v-model="data" class="row-cols-3 g-3 mb-3" ci-model="crm/prestudent" :pk="{prestudent_id:modelValue.prestudent_id}"></core-udf>
			</fieldset>
		</form-form>
		<div>
			<legend>Gesamthistorie</legend>
			<tbl-history :person-id="modelValue.person_id" :prestudent-id="modelValue.prestudent_id"></tbl-history>
		</div>
	</div>
	`
};
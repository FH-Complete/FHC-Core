import {CoreRESTClient} from '../../../../RESTClient.js';
import FormForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import TblHistory from "./Prestudent/History.js";
import TblStatus from "./Prestudent/Status.js";

export default {
	components: {
		CoreRESTClient,
		FormForm,
		FormInput,
		TblHistory,
		TblStatus
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
/*		hasPermissionToSkipStatusCheck: {
			from: 'hasPermissionToSkipStatusCheck',
			default: false
		},*/
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
	data(){
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
				//this.deltaArray.push(delta);
				this.deltaArray = delta;
			},
			deep: true
		}
	},

	methods: {
		loadPrestudent() {
			CoreRESTClient
				.get('components/stv/Prestudent/get/' + this.modelValue.prestudent_id)
				.then(result => result.data)
				.then(result => {
					this.data = result;
					//neue DataVariable um ein Delta der vorgenommenen Änderungen berechnen zu können
					this.initialFormData = {...this.data};
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updatePrestudent(){
					CoreRESTClient.post('components/stv/Prestudent/updatePrestudent/' + this.modelValue.prestudent_id,
						this.deltaArray
					).then(response => {
						if (!response.data.error) {
							this.$fhcAlert.alertSuccess('Speichern erfolgreich');
							this.deltaArray = [];
							this.actionUpdate = false;
						} else {
							const errorData = response.data.retval;
							Object.entries(errorData).forEach(entry => {
								const [key, value] = entry;
								this.$fhcAlert.alertError(value);
							});
						}
					}).catch(error => {
						this.statusMsg = 'Error in Catch';
						this.$fhcAlert.alertError('Fehler bei Speicherroutine aufgetreten');
					}).finally(() => {
						window.scrollTo(0, 0);
					});
		},
/* //besser im watch teil, dann wird Änderung immer verfolgt
detectChanges() {
			const delta = {};
			for (const key in this.data) {
				if (this.data[key] !== this.initialFormData[key]) {
					delta[key] = this.data[key];
					this.actionUpdate = true;
				}
			}
			this.deltaArray.push(delta);
		},*/
	},
	created() {
		this.loadPrestudent();
/*		CoreRESTClient
			.get('components/stv/Prestudent/getHistoryPrestudents/'+ this.modelValue.person_id)
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.historyPrestudents = result;
			})
			.catch(this.$fhcAlert.handleSystemError);*/
		//initiale Daten nach dem Laden
		//this.initialFormData = {...this.data};
		CoreRESTClient
			.get('components/stv/Prestudent/getBezeichnungZGV')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listZgvs = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getBezeichnungMZGV')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listZgvsmaster = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getBezeichnungDZGV')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listZgvsdoktor = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Lists/getStgs')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listStgs = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getAusbildung')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listAusbildung = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getAufmerksamdurch')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listAufmerksamdurch = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getBerufstaetigkeit')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listBerufe = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
		CoreRESTClient
			.get('components/stv/Prestudent/getTypenStg')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listStgTyp = result;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	mounted(){},
	template: `
	<div class="stv-details-details h-100 pb-3">
<!--	TEST: {{modelValue}} -->
		<form-form ref="form" class="stv-details-prestudent" @submit.prevent="updatePrestudent">
		<div class="position-sticky top-0 z-1">
			<button type="submit" class="btn btn-primary position-absolute top-0 end-0" :disabled="!deltaLength">Speichern</button>
		</div>
			<fieldset class="overflow-hidden">
				<legend>Zugangsvoraussetzungen für {{modelValue.nachname}} {{modelValue.vorname}}</legend>

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
								label="ZGV Ort"
								type="text"
								v-model="data.zgvort"
								name="zgvort"
								>
							</form-input>
							<form-input
								container-class="col-3"
								label="ZGV Datum"
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
								label="ZGV Nation"
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
								label="ZGV Master"
								type="select"
								v-model="data.zgvmas_code"
								name="zgvmascode"
								>
								<option v-for="mzgv in listZgvsmaster" :key="mzgv.zgvmas_code" :value="mzgv.zgvmas_code">{{mzgv.zgvmas_bez}}</option>
							</form-input>
							<form-input
								container-class="col-3"
								label="ZGV Master Ort"
								type="text"
								v-model="data.zgvmaort"
								name="zgvmaort"
								>
							</form-input>
							<form-input
								container-class="col-3"
								label="ZGV Master Datum"
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
								label="ZGV Master Nation"
								type="select"
								v-model="data.zgvmanation"
								name="zgvmanation"
								>
								<!-- TODO(chris): gesperrte nationen können nicht ausgewählt werden! Um das zu realisieren müsste man ein pseudo select machen -->
								<option v-for="nation in lists.nations" :key="nation.nation_code" :value="nation.nation_code" :disabled="nation.sperre">{{nation.kurztext}}</option>
							</form-input>
						</div>
						<!--ZGV Doktor Todo(manu) Config -->
						<div v-if="showZgvDoktor" class="row mb-3">
							<form-input
								container-class="col-3"
								label="ZGV Doktor"
								type="select"
								v-model="data.zgvdoktor_code"
								name="zgvdoktor_code"
								>
								<option v-for="zgv in listZgvsdoktor" :key="zgv.zgvdoktor_code" :value="zgv.zgvdoktor_code">{{zgv.zgvdoktor_bez}}</option>
							</form-input>
							<form-input
								container-class="col-3"
								label="ZGV Doktor Ort"
								type="text"
								v-model="data.zgvdoktorort"
								name="zgvdoktorort"
								>
							</form-input>
							<form-input
								container-class="col-3"
								label="ZGV Doktor Datum"
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
								label="ZGV Doktor Nation"
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
									label="ZGV erfüllt"
									type="checkbox"
									v-model="data.zgv_erfuellt"
									name="zgv_erfuellt"
									>
								</form-input>
							</div>
							<div class="col-3 pt-4 d-flex align-items-center">
								<form-input
									container-class="form-check"
									label="ZGV Master erfüllt"
									type="checkbox"
									v-model="data.zgvmas_erfuellt"
									name="zgvmas_erfuellt"
									>
								</form-input>
							</div>
							<div v-if="showZgvDoktor" class="col-3 pt-4 d-flex align-items-center">
								<form-input
									container-class="form-check"
									label="ZGV Doktor erfüllt"
									type="checkbox"
									v-model="data.zgvdoktor_erfuellt"
									name="zgvdoktor_erfuellt"
									>
								</form-input>
							</div>
						</div>
						
						
						
		<!--			</template>-->
			</fieldset>
			<fieldset class="overflow-hidden">
				<legend>PrestudentIn</legend>
				
					<div class="row mb-3">
						<form-input
							container-class="col-4"
							label="Aufmerksam durch"
							type="select"
							v-model="data.aufmerksamdurch_kurzbz"
							name="aufmerksamDurch"
							>
							<option v-for="adurch in listAufmerksamdurch" :key="adurch.aufmerksamdurch_kurzbz" :value="adurch.aufmerksamdurch_kurzbz">{{adurch.beschreibung}}</option>
						</form-input>
						<form-input
							container-class="col-4"
							label="Berufstätigkeit"
							type="select"
							v-model="data.berufstaetigkeit_code"
							name="berufstaetigkeit_code"
							>
							<option v-for="beruf in listBerufe" :key="beruf.berufstaetigkeit_code" :value="beruf.berufstaetigkeit_code">{{beruf.berufstaetigkeit_bez}} </option>
						</form-input>
						<form-input
							container-class="col-4"
							label="Ausbildung"
							type="select"
							v-model="data.ausbildungcode"
							name="ausbildungcode"
							>
							<option v-for="ausbld in listAusbildung" :key="ausbld.ausbildungcode" :value="ausbld.ausbildungcode">{{ausbld.ausbildungbez}} </option>
						</form-input>
					</div>
					
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
								label="Facheinschlägig berufstätig"
								type="checkbox"
								v-model="data.facheinschlberuf"
								name="facheinschlberuf"
								>
							</form-input>
						</div>
						
						<!--Todo(manu) validierung Integer, liste hier null-->
						<form-input
							container-class="col-4"
							label="Bisstandort"
							type="text"
							v-model="data.standort_code"
							name="standort_code"
							disabled
							>
						</form-input>
					 
					</div>
					
					<div class="row mb-3">
						<form-input
							container-class="col-4"
							label="Studiengang"
							type="select"
							v-model="data.studiengang_kz"
							name="studiengang_kz"
							disabled
							>
							<option v-for="stg in listStgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">{{stg.kuerzel}} - {{stg.bezeichnung}} </option>
						</form-input>
						<form-input
							container-class="col-4"
							label="Studientyp"
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
							label="Anmerkung"
							type="text"
							v-model="data.anmerkung"
							name="anmerkung"
							>
						</form-input>
						<div class="col-2 pt-4 d-flex align-items-center">
							<form-input
								container-class="form-check"
								label="Bismelden"
								type="checkbox"
								v-model="data.bismelden"
								name="bismelden"
								>
							</form-input>
						</div>
						<div class="col-2 pt-4 d-flex align-items-center">
							<form-input
								container-class="form-check"
								label="Duales Studium"
								type="checkbox"
								v-model="data.dual"
								name="dual"
								>
							</form-input>
						</div>
						<form-input
							container-class="col-3"
							label="förderrelevant"
							type="select"
							v-model="data.foerderrelevant"
							name="foerderrelevant"
							>
							<option v-for="entry in listFoerderrelevant" :value="entry.value">{{entry.text}}</option>
						</form-input>
						
						<form-input
							container-class="col-1"
							label="Priorität"
							type="text"
							v-model="data.priorisierung"
							name="priorisierung"
							:disabled="!hasPrestudentPermission"
							>
						</form-input>
						</div>
					</div>
									
			</fieldset>
		
		</form-form>
		
		<br>

					
		<div class="col-6 pb-3">
			<legend>Gesamthistorie</legend>
			<TblHistory :person_id="modelValue.person_id"></TblHistory>		
		</div>
					
		
	</div>
	`
};
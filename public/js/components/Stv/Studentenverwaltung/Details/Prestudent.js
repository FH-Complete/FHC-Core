import {CoreRESTClient} from '../../../../RESTClient.js';
import FormForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';

export default {
	components: {
		CoreRESTClient,
		FormForm,
		FormInput
	},
	inject: {
		lists: {
			from: 'lists'
		}
	},
	props: {
		modelValue: Object
	},
	data(){
		return {
			data: [],
			listZgvs: [],
			listStgs: [],
			listAusbildung: [],
			listAufmerksamdurch: [],
			listBerufe: [],
			listStgTyp: [],
			listFoerderrelevant: []
		};
	},
	methods: {
		loadData(prestudent_id){
			console.log("prestudent_id: " + prestudent_id);
			return CoreRESTClient.get('components/stv/Zusatzfelder/loadData/' + prestudent_id)
				.then(
					result => {
						if(result)
							this.data = result.data.retval;
						else
						{
							this.data = [];
							this.$fhcAlert.alertError('Keine Prestudent_id ' + prestudent_id + ' gefunden');
						}
						return result;
					}
				);
		},
		updatePrestudent() {
			CoreRESTClient
				.get('components/stv/Prestudent/get/' + this.modelValue.prestudent_id)
				.then(result => result.data)
				.then(result => {
					this.data = result;
					console.log(result);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	created() {
		this.updatePrestudent();
		CoreRESTClient
			.get('components/stv/Prestudent/getBezeichnungZGV')
			.then(result => CoreRESTClient.getData(result.data) || [])
			.then(result => {
				this.listZgvs = result;
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
	template: `
	<div class="stv-details-details h-100 pb-3">
		<form-form ref="form" class="stv-details-prestudent" @submit.prevent="save">
		<!--<form ref="form">-->
			<fieldset class="overflow-hidden">
				<legend>Zugangsvoraussetzungen für {{modelValue.nachname}} {{modelValue.vorname}}</legend>
	<!--				<template v-if="data">-->
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
								type="text"
								v-model="data.zgvmas_code"
								name="zgvmascode"
								>
								<option v-for="zgv in listZgvs" :key="zgv.zgv_code" :value="zgv.zgv_code">{{zgv.zgv_bez}}</option>
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
							container-class="col-5"
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
							container-class="col-2"
							label="Förderrelevant"
							type="select"
							v-model="data.foerderrelevant"
							name="foerderrelevant"
							>
							<option :value="" selected >wie Studiengang</option>
							<option :value="true">ja</option>
							<option :value="false">nein</option>
						</form-input>
						<form-input
							container-class="col-1"
							label="Priorität"
							type="text"
							v-model="data.priorisierung"
							name="priorisierung"
							>
						</form-input>
					</div>
				
			</fieldset>
		
		</form-form>
		
		<br>
		{{modelValue}}
		<hr>
		{{data}}
		<hr>
		{{listStgTyp}}
		
		
	</div>
	`
};
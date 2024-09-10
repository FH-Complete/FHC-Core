import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import FhcFormValidation from '../../../../Form/Validation.js';
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

export default{
	components: {
		CoreFilterCmpt,
		PvAutoComplete,
		FhcFormValidation,
		BsModal,
		FormForm,
		FormInput
	},
	props: {
		uid: Number
	},
	data() {
		return{
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/kontakt/getAdressen/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				columns:[
					{title:"Typ", field:"bezeichnung"},
					{title:"Strasse", field:"strasse"},
					{title:"Plz", field:"plz"},
					{title:"Ort", field:"ort"},
					{title:"Gemeinde", field:"gemeinde"},
					{title:"Nation", field:"nation"},
					{
						title:"Heimatadresse",
						field:"heimatadresse",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{
						title:"Zustelladresse",
						field:"zustelladresse",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{title:"Abweich.Empf", field:"co_name"},
					{title:"Firma", field:"firmenname"},
					{title:"Firma_id", field:"firma_id", visible:false},
					{title:"Adresse_id", field:"adresse_id", visible:false},
					{title:"Person_id", field:"person_id", visible:false},
					{title:"Name", field:"name", visible:false},
					{title:"letzte Änderung", field:"updateamum", visible:false},
					{title:"Rechnungsadresse", field:"rechnungsadresse", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? "ja" : "nein";
							return output;
						}
					},
					{title:"Anmerkung", field:"anmerkung", visible:false},
					{title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.addEventListener('click', (event) =>
								this.actionEditAdress(cell.getData().adresse_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.addEventListener('click', () =>
								this.actionDeleteAdress(cell.getData().adresse_id)
							);
							container.append(button);

							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	true,
				index: 'adresse_id',
				persistenceID: 'stv-details-kontakt-address'
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['notiz','global','person', 'ui']);
						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'typ')
						});
						cm.getColumnByField('strasse').component.updateDefinition({
							title: this.$p.t('person', 'strasse')
						});
						cm.getColumnByField('plz').component.updateDefinition({
							title: this.$p.t('person', 'plz')
						});
						cm.getColumnByField('ort').component.updateDefinition({
							title: this.$p.t('person', 'ort')
						});
						cm.getColumnByField('gemeinde').component.updateDefinition({
							title: this.$p.t('person', 'gemeinde')
						});
						cm.getColumnByField('nation').component.updateDefinition({
							title: this.$p.t('person', 'nation')
						});
						cm.getColumnByField('heimatadresse').component.updateDefinition({
							title: this.$p.t('person', 'heimatadresse')
						});
						cm.getColumnByField('co_name').component.updateDefinition({
							title: this.$p.t('person', 'co_name')
						});
						cm.getColumnByField('name').component.updateDefinition({
							title: this.$p.t('person', 'firma_zusatz')
						});
						cm.getColumnByField('firmenname').component.updateDefinition({
							title: this.$p.t('person', 'firma')
						});
						cm.getColumnByField('updateamum').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung')
						});
						cm.getColumnByField('rechnungsadresse').component.updateDefinition({
							title: this.$p.t('person', 'rechnungsadresse')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
					}
				}
			],
			addressData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A'
			},
			statusNew: true,
			places: [],
			suggestions: {},
			nations: [],
			adressentypen: [],
			firmen: [],
			filteredFirmen: [],
			abortController: {
				suggestions: null,
				places: null
			}
		}
	},
	computed:{
		orte() {
			return this.places.filter(ort => ort.name == this.addressData.gemeinde);
		},
		gemeinden() {
			return Object.values(this.places.reduce((res,place) => {
				res[place.name] = place;
				return res;
			}, {}));
		}
	},
	watch: {
		uid() {
			this.$refs.table.tabulator.setData('api/frontend/v1/stv/Kontakt/getAdressen/' + this.uid);
		},
	},
	methods:{
		actionNewAdress() {
			this.resetModal();
			this.$refs.adressModal.show();
		},
		actionEditAdress(adress_id) {
			this.statusNew = false;
			this.loadAdress(adress_id).then(() => {
				if(this.addressData.adresse_id)
				{
					this.loadPlaces(this.addressData.plz);
					this.$refs.adressModal.show();

				}
			});
		},
		actionDeleteAdress(adress_id) {
			this.loadAdress(adress_id).then(() => {
				if(this.addressData.adresse_id)
					if(this.addressData.heimatadresse)
						this.$fhcAlert.alertError(this.$p.t('person', 'error_deleteHomeAdress'));
					else {
						this.$fhcAlert
							.confirmDelete()
							.then(result => result
								? adress_id
								: Promise.reject({handled: true}))
							.then(this.deleteAddress)
							.catch(this.$fhcAlert.handleSystemError);
					}
			});
		},
		addNewAddress(addressData) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/addNewAddress/' + this.uid,
				this.addressData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('adressModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadAdress(adress_id) {
			this.statusNew = false;
			return this.$fhcApi.get('api/frontend/v1/stv/kontakt/loadAddress/' + adress_id)
				.then(result => {
						this.addressData = result.data;
						return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateAddress(adress_id) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/updateAddress/' + adress_id,
				this.addressData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('adressModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
			.finally(() => {
				window.scrollTo(0, 0);
				this.reload();
			});
		},
		deleteAddress(adress_id) {
			this.$fhcApi.post('api/frontend/v1/stv/kontakt/deleteAddress/' + adress_id)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					window.scrollTo(0, 0);
					this.reload();
				});
		},
		loadPlaces() {
			if (this.abortController.places)
				this.abortController.places.abort();
			if (this.addressData.nation != 'A' || !this.addressData.plz)
				return;

			this.abortController.places = new AbortController();
			this.$fhcApi
				.get('api/frontend/v1/stv/address/getPlaces/' + this.addressData.plz, undefined, {
					signal: this.abortController.places.signal
				})
				.then(result => {
					this.places = result.data;
				});
/*				.catch(error => {
						if (error.code != "ERR_CANCELED")
							window.setTimeout(this.loadPlaces, 100);
						else
							this.$fhcAlert.handleSystemError(error);
					});*/
		},
		search(event) {
			return this.$fhcApi
				.get('api/frontend/v1/stv/kontakt/getFirmen/' + event.query)
				.then(result => {
					this.filteredFirmen = result.data.retval;
				});
		},
		hideModal(modalRef) {
			this.$refs[modalRef].hide();
		},
		resetModal() {
			this.addressData = {};

			this.addressData.strasse = null;
			this.addressData.zustelladresse = true;
			this.addressData.heimatadresse = true;
			this.addressData.rechnungsadresse = false;
			this.addressData.co_name = null;
			this.addressData.firma_id = null;
			this.addressData.name = null;
			this.addressData.anmerkung = null;
			this.addressData.typ = 'h';
			this.addressData.nation = 'A';
			this.addressData.plz = null;

			this.statusNew = true;
		},
	},
	created() {
		this.$fhcApi
			.get('api/frontend/v1/stv/address/getNations')
			.then(result => {
				this.nations = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		this.$fhcApi
			.get('api/frontend/v1/stv/kontakt/getAdressentypen')
			.then(result => {
				this.adressentypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError)
	},
	template: `
	<div class="stv-details-kontakt-address h-100 pt-3">
		
		<!--Modal: AddressModal-->
		<bs-modal ref="adressModal">
			<template #title>
				<p v-if="statusNew" class="fw-bold mt-3">{{$p.t('person', 'adresse_new')}}</p>
				<p v-else class="fw-bold mt-3">{{$p.t('person', 'adresse_edit')}}</p>
			</template>
			<form-form class="row g-3 mt-2" ref="addressData">

				<div class="row mb-3">
					<form-input
						type="select"
						name="adressentyp"
						:label="$p.t('global/typ')"
						v-model="addressData.typ"
						>
						<option
							v-for="typ in adressentypen"
							:key="typ.adressentyp_kurzbz"
							:value="typ.adressentyp_kurzbz"
							>
							{{typ.bezeichnung}}
						</option>
						</form-input>
					</div>
					
					<div class="row mb-3">
						<form-input
							type="text"
							name="strasse"
							:label="$p.t('person/strasse')"
							v-model="addressData.strasse"
						>
						</form-input>					
				</div>
				
				<div class="row mb-3">
					<form-input
						type="select"
						name="nation"
						:label="$p.t('person/nation')"
						v-model="addressData.nation"
						>
						<option 
							v-for="nation in nations" 
							:key="nation.nation_code" 
							:value="nation.nation_code" 
							:disabled="nation.sperre"
							>
							{{nation.kurztext}}
						</option>
						</form-input>
				</div>
													
				<div class="row mb-3">
					<form-input
						type="text"
						name="plz"
						:label="$p.t('person/plz') + ' *'"
						v-model="addressData.plz"
						required
						@input="loadPlaces"
					>
					</form-input>					
				</div>
											
				<div class="row mb-3">
					<form-input
						v-if="addressData.nation == 'A'"
						type="select"
						name="gemeinde"
						:label="$p.t('person/gemeinde')"
						v-model="addressData.gemeinde"
						>
						<option v-if="!gemeinden.length" disabled>{{$p.t('ui', 'bittePlzWaehlen')}}</option>
						<option 
							v-for="gemeinde in gemeinden" 
							:key="gemeinde.name" 
							:value="gemeinde.name"
							>
							{{gemeinde.name}}
						</option>
						</form-input>
						<form-input
							v-else
							type="text"
							name="addressData.gemeinde"
							v-model="addressData.gemeinde"
						>	
						</form-input>
					</div>
				
				<div class="row mb-3">
					<form-input
						v-if="addressData.nation == 'A'" 
						type="select"
						name="ort"
						:label="$p.t('person/ort')"
						v-model="addressData.ort"
						>
						<option v-if="!orte.length" disabled>{{$p.t('ui', 'bitteGemeindeWaehlen')}}</option>
						<option 
							v-for="ort in orte" 
							:key="ort.ortschaftsname" 
							:value="ort.ortschaftsname"
							>
							{{ort.ortschaftsname}}
						</option>
					</form-input>
					<form-input
							v-else
							type="text"
							name="ort"
							v-model="addressData.ort"
						>	
					</form-input>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-4">
						<form-input
							container-class="form-check"
							type="checkbox"
							name="heimatadresse"
							:label="$p.t('person/heimatadresse')"
							v-model="addressData.heimatadresse"
						>
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-4">
						<form-input
							container-class="form-check"
							type="checkbox"
							name="zustelladresse"
							:label="$p.t('person/zustelladresse')"
							v-model="addressData.zustelladresse"
						>
						</form-input>
					</div>
				</div>
				
				<div class="row mb-3">
					<form-input
						type="text"
						name="co_name"
						:label="$p.t('person/co_name')"
						v-model="addressData.co_name"
					>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<div class="col-sm-4">
						<form-input
							container-class="form-check"
							type="checkbox"
							name="rechnungsadresse"
							:label="$p.t('person/rechnungsadresse')"
							v-model="addressData.rechnungsadresse"
						>
						</form-input>
					</div>
				</div>
					
				<div v-if="statusNew" class="row mb-3">
					<form-input
						type="autocomplete"
						:label="$p.t('person/firma')"
						name="firma_name"
						v-model="addressData.firma"  
						optionLabel="name" 
						:suggestions="filteredFirmen" 
						@complete="search" 
						:min-length="3"
					>
					</form-input>
				</div>				
					
				<div v-else class="row mb-3">
					<form-input
						v-if="addressData.firmenname" 
						type="text"
						name="name"
						:label="$p.t('person/firma')"
						v-model="addressData.firmenname"
					>
					</form-input>
					<form-input
						v-else 
						type="autocomplete"
						:label="$p.t('person/firma')"
						name="firma_name"
						v-model="addressData.firma"  
						optionLabel="name" 
						:suggestions="filteredFirmen" 
						@complete="search" 
						:min-length="3"
					>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<input type="hidden" class="form-control" id="firma_id" v-model="addressData.firma_id">
				</div>
				
				<div class="row mb-3">
					<form-input
						type="text"
						name="firma_zusatz"
						:label="$p.t('person/firma_zusatz')"
						v-model="addressData.name"
					>
					</form-input>
				</div>
				
				<div class="row mb-3">
					<form-input
							type="text"
							name="anmerkung"
							:label="$p.t('global/anmerkung')"
							v-model="addressData.anmerkung"
						>
					</form-input>
				</div>
			
			</form-form>
			
			<template #footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{$p.t('ui', 'abbrechen')}}</button>
				<button v-if="statusNew" type="button" class="btn btn-primary" @click="addNewAddress()">OK</button>
				<button v-else type="button" class="btn btn-primary" @click="updateAddress(addressData.adresse_id)">OK</button>
            </template>
		</bs-modal>
			
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Adresse"
			@click:new="actionNewAdress"
			>
		</core-filter-cmpt>
	</div>`
};


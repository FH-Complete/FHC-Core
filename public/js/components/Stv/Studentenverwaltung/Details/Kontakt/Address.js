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
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: this.$fhcApi.factory.stv.kontakt.getAdressen,
				ajaxParams: () => {
					return {
						id: this.uid
					};
				},
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
					{title:"letzte Änderung", field:"lastupdate", visible:false},
					{title:"Rechnungsadresse", field:"rechnungsadresse", visible:false,
						formatter: (cell, formatterParams, onRendered) => {
							let output = cell.getValue() ? this.$p.t('ui','ja') : this.$p.t('ui','nein');
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
							button.title = this.$p.t('person', 'adresse_edit');
							button.addEventListener('click', (event) =>
								this.actionEditAdress(cell.getData().adresse_id)
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('person', 'adresse_delete');

							button.addEventListener('click', () => {
								if (cell.getData().heimatadresse)
									this.$fhcAlert.alertError(this.$p.t('person', 'error_deleteHomeAdress'));
								else
									this.actionDeleteAdress(cell.getData().adresse_id)
							});

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
						await this.$p.loadCategory(['notiz', 'global', 'person', 'ui']);
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
						cm.getColumnByField('zustelladresse').component.updateDefinition({
							title: this.$p.t('person', 'zustelladresse')
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
						cm.getColumnByField('lastupdate').component.updateDefinition({
							title: this.$p.t('notiz', 'letzte_aenderung')
						});
						cm.getColumnByField('rechnungsadresse').component.updateDefinition({
							title: this.$p.t('person', 'rechnungsadresse')
						});
						cm.getColumnByField('anmerkung').component.updateDefinition({
							title: this.$p.t('global', 'anmerkung')
						});
						cm.getColumnByField('firma_id').component.updateDefinition({
							title: this.$p.t('ui', 'firma_id')
						});
						cm.getColumnByField('adresse_id').component.updateDefinition({
							title: this.$p.t('ui', 'adresse_id')
						});
						cm.getColumnByField('person_id').component.updateDefinition({
							title: this.$p.t('person', 'person_id')
						});
/*						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});*/
					}
				}
			],
			addressData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A',
				address: {plz: null},
				plz: null
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
				places: null,
				firmen: null
			},
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
		actionEditAdress(adresse_id) {
			this.statusNew = false;
			this.loadAdress(adresse_id).then(() => {
				if(this.addressData.adresse_id)
				{
					this.addressData.address.plz = this.addressData.plz;
				//	delete this.addressData.plz;
					this.loadPlaces(this.addressData.address.plz);
					this.$refs.adressModal.show();

				}
			});
		},
		actionDeleteAdress(adresse_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? adresse_id
					: Promise.reject({handled: true}))
				.then(this.deleteAddress)
				.catch(this.$fhcAlert.handleSystemError);
		},
		addNewAddress(addressData) {
			this.addressData.plz = this.addressData.address.plz;
			return this.$fhcApi.factory.stv.kontakt.addNewAddress(this.$refs.addressData, this.uid, this.addressData)
				.then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('adressModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
				this.reload();
			});
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadAdress(adresse_id) {
			this.statusNew = false;
			return this.$fhcApi.factory.stv.kontakt.loadAddress(adresse_id)
				.then(result => {
					this.addressData = result.data;
					this.addressData.address = {};
					this.addressData.address.plz = this.addressData.plz || null;
					return result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateAddress(adresse_id) {
			this.addressData.plz = this.addressData.address.plz;
			return this.$fhcApi.factory.stv.kontakt.updateAddress(this.$refs.addressData, adresse_id,
				this.addressData
			).then(response => {
				this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('adressModal');
					this.resetModal();
			}).catch(this.$fhcAlert.handleSystemError)
			.finally(() => {
				this.reload();
			});
		},
		deleteAddress(adresse_id) {
			return this.$fhcApi.factory.stv.kontakt.deleteAddress(adresse_id)
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
			if (this.addressData.nation != 'A' || !this.addressData.address.plz)
				return;

			this.abortController.places = new AbortController();

			return this.$fhcApi.factory.stv.kontakt.getPlaces(this.addressData.address.plz)
				.then(result => {
					this.places = result.data;
				});
		},
		search(event) {
			if (this.abortController.firmen) {
				this.abortController.firmen.abort();
			}

			this.abortController.firmen = new AbortController();

			return this.$fhcApi.factory.stv.kontakt.getFirmen(event.query)
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
			this.addressData.address = {plz: null};

			this.statusNew = true;
		},
	},
	created() {
		this.$fhcApi.factory.stv.kontakt.getNations()
			.then(result => {
				this.nations = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi.factory.stv.kontakt.getAdressentypen()
			.then(result => {
				this.adressentypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
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
						name="address[plz]"
						:label="$p.t('person/plz') + ' *'"
						v-model="addressData.address.plz"
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
							:label="$p.t('person/gemeinde')"
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
							:label="$p.t('person/ort')"
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
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="reload()">{{$p.t('ui', 'abbrechen')}}</button>
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
			:new-btn-label="this.$p.t('person', 'adresse')"
			@click:new="actionNewAdress"
			>
		</core-filter-cmpt>
	</div>`
};


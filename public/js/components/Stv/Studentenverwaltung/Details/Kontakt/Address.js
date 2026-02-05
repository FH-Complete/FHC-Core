import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import PvAutoComplete from "../../../../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";
import FhcFormValidation from '../../../../Form/Validation.js';
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

import ApiStvAddress from '../../../../../api/factory/stv/kontakt/address.js';
import ApiStvCompany from '../../../../../api/factory/stv/kontakt/company.js';

export default{
	name: 'AddressComponent',
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
			addressData: {
				zustelladresse: true,
				heimatadresse: true,
				rechnungsadresse: false,
				typ: 'h',
				nation: 'A',
				address: {plz: null},
				plz: null,
			},
			statusNew: true,
			places: [],
			suggestions: {},
			nations: [],
			adressentypen: [],
			firmen: [],
			listFirmen: [],
			filteredFirmen: [],
			selectedFirma: null,
			abortController: {
				suggestions: null,
				places: null
			},
		}
	},
	computed:{
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvAddress.get(this.uid)),
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				index: 'adresse_id',
				persistenceID: 'stv-details-kontakt-address',
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
					{
						title:"letzte Änderung",
						field:"lastupdate",
						visible: false,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour: "2-digit",
								minute: "2-digit",
								second: "2-digit",
								hour12: false
							});
						}
					},
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
				height:	'auto'
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['notiz', 'global', 'person', 'ui']);

						const setHeader = (field, text) => {
							const col = this.$refs.table.tabulator.getColumn(field);
							if (!col) return;

							const el = col.getElement();
							if (!el || !el.querySelector) return;

							const titleEl = el.querySelector('.tabulator-col-title');
							if (titleEl) {
								titleEl.textContent = text;
							}
						};

						setHeader('bezeichnung', this.$p.t('global', 'typ'));
						setHeader('strasse', this.$p.t('person', 'strasse'));
						setHeader('plz', this.$p.t('person', 'plz'));
						setHeader('ort', this.$p.t('person', 'ort'));
						setHeader('gemeinde', this.$p.t('person', 'gemeinde'));
						setHeader('nation', this.$p.t('person', 'nation'));
						setHeader('heimatadresse', this.$p.t('person', 'heimatadresse'));
						setHeader('zustelladresse', this.$p.t('person', 'zustelladresse'));
						setHeader('co_name', this.$p.t('person', 'co_name'));
						setHeader('name', this.$p.t('person', 'firma_zusatz'));
						setHeader('firmenname', this.$p.t('person', 'firma'));
						setHeader('lastupdate', this.$p.t('notiz', 'letzte_aenderung'));
						setHeader('rechnungsadresse', this.$p.t('person', 'rechnungsadresse'));
						setHeader('anmerkung', this.$p.t('global', 'anmerkung'));
						setHeader('firma_id', this.$p.t('ui', 'firma_id'));
						setHeader('adresse_id', this.$p.t('ui', 'adresse_id'));
						setHeader('person_id', this.$p.t('person', 'person_id'));
					}
				}
			];
			return events;
		},
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
			this.reload();
		},
		selectedFirma(newVal) {
			this.addressData.firma_id = newVal?.firma_id || null;
		}
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
					this.selectedFirma = this.listFirmen.find(
						item => item.firma_id === this.addressData.firma_id
					);

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
			return this.$refs.addressData
				.call(ApiStvAddress.add(this.uid, this.addressData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('adressModal');
					this.resetModal();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(this.reload);
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		loadAdress(adresse_id) {
			this.statusNew = false;
			return this.$api
				.call(ApiStvAddress.load(adresse_id))
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
			return this.$refs.addressData
				.call(ApiStvAddress.update(adresse_id, this.addressData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('adressModal');
					this.resetModal();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(this.reload);
		},
		deleteAddress(adresse_id) {
			return this.$api
				.call(ApiStvAddress.delete(adresse_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
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

			return this.$api
				.call(ApiStvAddress.getPlaces(this.addressData.address.plz))
				.then(result => {
					this.places = result.data;
				});
		},
		filterFirmen(event) {
			const query = event?.query?.toLowerCase()?.trim() || "";

			this.filteredFirmen = this.listFirmen.filter(item => {
				const label = (item.label || "").toLowerCase();
				return label.includes(query);
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
			this.selectedFirma = null;

			this.statusNew = true;
		},
	},
	created() {
		this.$api
			.call(ApiStvAddress.getNations())
			.then(result => {
				this.nations = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAddress.getTypes())
			.then(result => {
				this.adressentypen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(ApiStvAddress.getAllFirmen())
			.then(result => {
				this.listFirmen = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-kontakt-address h-100 pt-3">

		<!--Modal: AddressModal-->
		<bs-modal ref="adressModal" dialog-class="modal-dialog-scrollable">
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

				<div class="row mb-3">
					<form-input
						type="autocomplete"
						:label="$p.t('person/firma')"
						name="firma_name"
						v-model="selectedFirma"
						optionLabel="label"
						optionValue="firma_id"
						dropdown
						forceSelection
						:suggestions="filteredFirmen" 
						@complete="filterFirmen"
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
						:label="$p.t('global/name')"
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
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			new-btn-show
			:new-btn-label="this.$p.t('person', 'adresse')"
			@click:new="actionNewAdress"
			>
		</core-filter-cmpt>
	</div>`
};




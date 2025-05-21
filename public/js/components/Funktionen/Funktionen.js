import {CoreFilterCmpt} from "../filter/Filter.js";
import FormInput from "../Form/Input.js";
import FormForm from "../Form/Form.js";
import BsModal from "../Bootstrap/Modal.js";
import PvAutoComplete from "../../../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

import ApiCoreFunktion from '../../api/factory/functions.js';

export default {
	name: 'FunctionComponent',
	components: {
		CoreFilterCmpt,
		FormInput,
		FormForm,
		BsModal,
		PvAutoComplete
	},
	props: {
		modelValue: {
			type: Object,
			default: () => ({}),
			required: false
		},
		config: {type: Object, default: () => ({}), required: false},
		readonlyMode: {type: Boolean, required: false, default: false},
		personID: {type: Number, required: true},
		personUID: {type: String, required: true},
		writePermission: {type: Boolean, required: false},
		showDvCompany: {type: Boolean, required: false, default: true},
		saveFunctionAsCopy: {type: Boolean, required: false, default: false},
		stylePv21: {type: Boolean, required: false, default: false},
		companyLinkFormatter: {type: Function || null,	default: null}
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiCoreFunktion.getAllUserFunctions(this.personUID)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{
						title: "dienstverhaeltnis_unternehmen",
						field: "dienstverhaeltnis_unternehmen",
						width: 50,
						headerFilter: "list",
						headerFilterParams: {valuesLookup: true, autocomplete: true, sort: "asc"},
						formatter: this.companyLinkFormatter
					},
					{
						title: "funktion_beschreibung", field: "funktion_beschreibung", headerFilter: "list",
						headerFilterParams: {valuesLookup: true, autocomplete: true, sort: "asc"},
					},
					{
						title: "funktion_oebezeichnung", field: "funktion_oebezeichnung", headerFilter: "list",
						headerFilterParams: {valuesLookup: true, autocomplete: true, sort: "asc"}
					},
					{title: "wochenstunden", field: "wochenstunden", headerFilter: true},
					{
						title: "Von",
						field: "datum_von",
						headerFilter: true,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						},
					},
					{
						title: "Bis",
						field: "datum_bis",
						headerFilter: true,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
							});
						},
					},
					{title: "bezeichnung", field: "bezeichnung", headerFilter: true},
					{title: "aktiv", field: "aktiv", visible: false},
					{title: "benutzerfunktion_id", field: "benutzerfunktion_id", visible: false},
					{title: "uid", field: "uid", visible: false},
					{
						//title: 'Aktionen', field: 'actions',
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";


							if( cell.getRow().getData().dienstverhaeltnis_unternehmen === null ) {
								let button = document.createElement('button');
								button.className = 'btn btn-outline-secondary btn-action';
								if(this.stylePv21)
									button.innerHTML = '<i class="fa fa-pen"></i>';
								else
									button.innerHTML = '<i class="fa fa-edit"></i>';
								button.title = this.$p.t('ui', 'bearbeiten');
								button.addEventListener('click', (event) =>
									this.actionEditFunction(cell.getData().benutzerfunktion_id)
								);
								if(this.readonlyMode === true)	button.disabled = true;
								container.append(button);
							}
							if( cell.getRow().getData().dienstverhaeltnis_unternehmen === null ) {
								let button = document.createElement('button');
								button.className = 'btn btn-outline-secondary btn-action';
								button.innerHTML = '<i class="fa fa-xmark"></i>';
								button.title = this.$p.t('ui', 'loeschen');
								button.addEventListener('click', () =>
									this.actionDeleteFunction(cell.getData().benutzerfunktion_id)
								);
								if(this.readonlyMode === true)	button.disabled = true;
								container.append(button);
							}

							if (cell.getRow().getData().dienstverhaeltnis_unternehmen === null  && this.saveFunctionAsCopy) {
								let button = document.createElement('button');
								button.className = 'btn btn-outline-secondary btn-action';
								button.innerHTML = '<i class="fa fa-copy"></i>';
								button.title = this.$p.t('ui', 'saveAsCopy');
								button.addEventListener('click', () =>
									this.actionCopyFunction(cell.getData().benutzerfunktion_id)
								);
								if(this.readonlyMode === true)	button.disabled = true;
								container.append(button);
							}

							return container;
						},
						frozen: true
					}
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: '300',
				persistenceID: 'core-functions',
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['global', 'lehre', 'person', 'ui']);
						let cm = this.$refs.table.tabulator.columnManager;

						//Field Company: if visible show link to dv
						const column = cm.getColumnByField('dienstverhaeltnis_unternehmen');
						const companyDv = {
							title: this.$p.t('person', 'dv_unternehmen'),
							width: 100,
							visible: this.showDvCompany,
						};
						column.component.updateDefinition(companyDv);

						cm.getColumnByField('funktion_beschreibung').component.updateDefinition({
							title: this.$p.t('person', 'zuordnung_taetigkeit'),
							width: 100
						});
						cm.getColumnByField('funktion_oebezeichnung').component.updateDefinition({
							title: this.$p.t('lehre', 'organisationseinheit'),
							width: 100
						});
						cm.getColumnByField('wochenstunden').component.updateDefinition({
							title: this.$p.t('person', 'wochenstunden')
						});

						const columnDatumVon = cm.getColumnByField('datum_von');
						const fieldVonDatum = {
							title: this.$p.t('ui', 'from')
						};

						columnDatumVon.component.updateDefinition(fieldVonDatum);

						const columnDatumBis = cm.getColumnByField('datum_bis');
						const fieldBisDatum = {
							title: this.$p.t('global', 'bis'),
						};
						columnDatumBis.component.updateDefinition(fieldBisDatum);

						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('ui', 'bezeichnung'),
							width: 100
						});

					}
				}
			],
			isFilterSet: false,
			listOrgHeads: [],
			listOrgUnits: [],
			formData: {
				head: 'gst',
				oe_kurzbz: ''
			},
			statusNew: true,
			listAllFunctions: [],
			abortController: {
				oes: null,
				functions: null
			},
			filteredOes: [],
			filteredFunctions: [],
			newBtnStyle: ''
		}
	},
	computed: {
	},
	methods: {
		onSwitchChange() {
			if (this.isFilterSet) {
				this.$refs.table.tabulator.setFilter("aktiv", "=", true);
			}
			else {
				this.$refs.table.tabulator.clearFilter();
				this.isFilterSet = false;
			}
		},
		actionNewFunction(){
			this.resetModal();
			this.statusNew = true;
			this.$refs.functionModal.show();
		},
		actionCopyFunction(benutzerfunktion_id) {
			this.statusNew = true;
			this.loadFunction(benutzerfunktion_id).then(() => {
				this.$refs.functionModal.show();
			});
		},
		actionDeleteFunction(benutzerfunktion_id) {
			this.$fhcAlert
				.confirmDelete()
				.then(result => result
					? benutzerfunktion_id
					: Promise.reject({handled: true}))
				.then(this.deleteFunction)
				.catch(this.$fhcAlert.handleSystemError);
		},
		actionEditFunction(benutzerfunktion_id) {
			this.resetModal();
			this.statusNew = false;
			this.loadFunction(benutzerfunktion_id);
			this.$refs.functionModal.show();
		},
		addFunction() {
			const dataToSend = {
				uid: this.personUID,
				formData: this.formData
			};
			return this.$refs.functionData
				.call(ApiCoreFunktion.addFunction(dataToSend))
				.then(response => {
						this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('functionModal');
					this.resetModal();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		loadFunction(benutzerfunktion_id) {
			return this.$api
				.call(ApiCoreFunktion.loadFunction(benutzerfunktion_id))
				.then(result => {
					this.formData = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		updateFunction(benutzerfunktion_id){
			const dataToSend = {
				uid: this.personUID,
				formData: this.formData,
				benutzerfunktion_id: benutzerfunktion_id
			};
			return this.$refs.functionData
				.call(ApiCoreFunktion.updateFunction(dataToSend))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSave'));
					this.hideModal('functionModal');
					this.resetModal();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		deleteFunction(benutzerfunktion_id) {
			return this.$api
				.call(ApiCoreFunktion.deleteFunction(benutzerfunktion_id))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					this.reload();
				});
		},
		getOrgetsForCompany(){
			return this.$api
				.call(ApiCoreFunktion.getOrgetsForCompany(this.formData.head))
				.then(result => {
					this.listOrgUnits = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		hideModal(modalRef) {
			this.$refs[modalRef].hide();
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		resetModal(){
			this.formData = {};
			this.formData.head = 'gst';
			this.formData.oe_kurzbz = '';
			this.formData.funktion_kurzbz = '';
		},
		searchOe(event) {
			if (this.abortController.oes) {
				this.abortController.oes.abort();
			}

			this.abortController.oes = new AbortController();

			return this.$api
				.call(ApiCoreFunktion.getOes(this.formData.head, event.query))
				.then(result => {
					this.filteredOes = result.data.retval;
				});
		},
		searchFunctions(event) {
			if (this.abortController.functions) {
				this.abortController.functions.abort();
			}

			this.abortController.functions = new AbortController();

			return this.$api
				.call(ApiCoreFunktion.getFunctions(event.query))
				.then(result => {
					this.filteredFunctions = result.data.retval;
				});
		},
		styleNewButton(){
			if(this.stylePv21) {
				this.newBtnStyle = "btn-sm";
			}
		},
		//helper function: workaround to trigger validation if input is not a number
		normalizeStunden() {
			if (this.formData.wochenstunden === null || this.formData.wochenstunden === '') {
				this.formData.wochenstunden = 'xxx'
			}
		}
	},
	created() {
		this.$api
			.call(ApiCoreFunktion.getOrgHeads())
			.then(result => {
				this.listOrgHeads = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.styleNewButton();
	},
	template: `
		<div class="core-functions h-100 pb-3">

			<div class="d-flex justify-content-end pb-3" v-if="stylePv21">
				<form-input
					container-class="form-check"
					type="checkbox"
					:label="$p.t('funktion/filter_active')"
					v-model="isFilterSet"
					@change="onSwitchChange"
					>
				</form-input>
			</div>
			<div v-else>
				<form-input
					container-class="form-switch"
					type="checkbox"
					:label="$p.t('funktion/filter_active')"
					v-model="isFilterSet"
					@change="onSwitchChange"
					>
				</form-input>
			</div>

			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				:reload= "!this.stylePv21"
				new-btn-show
				:new-btn-class="this.newBtnStyle"
				:new-btn-label="this.$p.t('person', 'funktion')"
				@click:new="actionNewFunction"
				>
			</core-filter-cmpt>

			<!--Modal: functionModal-->
			<bs-modal ref="functionModal" dialog-class="modal-lg">
				<template #title>
					<p v-if="statusNew" class="fw-bold mt-3">{{ $p.t('funktion', 'addFunktion') }}</p>
					<p v-else class="fw-bold mt-3">{{ $p.t('funktion', 'editFunktion') }}</p>
				</template>

				<form-form class="row pt-3" ref="functionData">
					<form-input
						container-class="mb-3 col-8"
						type="select"
						name="companies"
						:label="$p.t('core/unternehmen')"
						v-model="formData.head"
						@change="getOrgetsForCompany"
						>
						<option
							v-for="org in listOrgHeads"
							:key="org.head"
							:value="org.head"
							@change="handleChange"
							>
							{{ org.bezeichnung }}
						</option>
					</form-input>

					<!--DropDown Autocomplete Funktion-->
					<form-input
						container-class="mb-3 col-8"
						type="autocomplete"
						:label="$p.t('person/funktion') + ' *' "
						name="funktion_kurzbz"
						v-model="formData.funktion_kurzbz"
						optionLabel="label"
						:suggestions="filteredFunctions"
						dropdown
						@complete="searchFunctions"
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

					<!--DropDown Autocomplete Organisationseinheit-->
					<form-input
						container-class="mb-3 col-8"
						type="autocomplete"
						:label="$p.t('lehre/organisationseinheit') + ' *'"
						name="oe_kurzbz"
						v-model="formData.oe_kurzbz"
						optionLabel="label"
						:suggestions="filteredOes"
						dropdown
						@complete="searchOe"
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

					<!--DropDown Bezeichnung-->
					<form-input
						container-class="mb-3 col-8"
						type="text"
						name="bezeichnung"
						:label="$p.t('global/bezeichnung')"
						v-model="formData.bezeichnung"
					>
					</form-input>

					<div class="row mb-3">
						<form-input
							container-class="mb-3 col-2"
							type="number"
							name="wochenstunden"
							@blur="normalizeStunden"
							:label="$p.t('person/wochenstunden')"
							v-model="formData.wochenstunden"
						>
						</form-input>

						<form-input
							container-class="mb-3 col-3"
							type="DatePicker"
							v-model="formData.datum_von"
							name="datum_von"
							:label="$p.t('ui/from') + ' *'"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
						>
						</form-input>

						<form-input
							container-class="mb-3 col-3"
							type="DatePicker"
							v-model="formData.datum_bis"
							name="datum_bis"
							:label="$p.t('global/bis')"
							auto-apply
							:enable-time-picker="false"
							format="dd.MM.yyyy"
							preview-format="dd.MM.yyyy"
							:teleport="true"
							>
						</form-input>
					</div>
						<input type="hidden" class="form-control" id="oe_kurzbz" v-model="formData.oe_kurzbz">

				</form-form>

				<template #footer>
					<button type="button" class="btn btn-primary" @click="statusNew ? addFunction() : updateFunction(formData.benutzerfunktion_id)">{{$p.t('ui', 'speichern')}}</button>
				</template>
			</bs-modal>

		</div>
    `
}

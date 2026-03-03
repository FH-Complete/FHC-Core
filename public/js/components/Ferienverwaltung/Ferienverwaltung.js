import {CoreFilterCmpt} from "../filter/Filter.js";
import FormInput from "../Form/Input.js";
import FerienModal from "./Modal.js";

import ApiFerienverwaltung from '../../api/factory/ferienverwaltung/ferienverwaltung.js';

export default {
	name: "Ferienverwaltung",
	components: {
		CoreFilterCmpt,
		FormInput,
		FerienModal
	},
	props: {
		//modelValue: Object,
		//~ config: {
			//~ type: Object,
			//~ default: {}
		//~ }
	},
	data() {
		return {
			filterVonDatum: null,
			filterBisDatum: null,
			loading: false,
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiFerienverwaltung.getFerien(this.filterVonDatum, this.filterBisDatum)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title:"Ferien Id", field:"ferien_id", visible: false, headerFilter: true},
					{
						title:"Datum von",
						field:"vondatum",
						headerFilter: true,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{
						title:"Datum bis",
						field:"bisdatum",
						headerFilter: true,
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}
					},
					{title:"Bezeichnung", field:"bezeichnung", headerFilter: true},
					{title:"Organisationseinheit Kurzbezeichnung", field:"oe_kurzbz", visible: false, headerFilter: true},
					{title:"Organisationseinheit", field:"oe_bezeichnung", headerFilter: true},
					{title:"Studienplan", field:"studienplan_bezeichnung", visible: false, headerFilter: true},
					{title:"Ferientyp Kurzbezeichnung", field:"ferientyp_kurzbz", visible: false, headerFilter: true},
					{
						title:"Mitarbeiterrelevant",
						field:"mitarbeiterrelevant",
						visible: false,
						headerFilter: true,
						hozAlign: "center",
						formatter:'tickCross', formatterParams: {
							tickElement: '<i class="fas fa-check text-success"></i>',
							crossElement: '<i class="fas fa-times text-danger"></i>'
						},
						headerFilter:"tickCross", headerFilterParams: {
							"tristate":true, elementAttributes:{"value":"true"}
						},
						headerFilterEmptyCheck:function(value){return value === null}
					},
					{
						title:"Studierendenrelevant",
						field:"studierendenrelevant",
						visible: false,
						headerFilter: true,
						hozAlign: "center",
						formatter:'tickCross', formatterParams: {
							tickElement: '<i class="fas fa-check text-success"></i>',
							crossElement: '<i class="fas fa-times text-danger"></i>'
						},
						headerFilter:"tickCross", headerFilterParams: {
							"tristate":true, elementAttributes:{"value":"true"}
						},
						headerFilterEmptyCheck:function(value){return value === null}
					},
					{
						title:"Lehre planbar",
						field:"lehre",
						visible: false,
						headerFilter: true,
						hozAlign: "center",
						formatter:'tickCross', formatterParams: {
							tickElement: '<i class="fas fa-check text-success"></i>',
							crossElement: '<i class="fas fa-times text-danger"></i>'
						},
						headerFilter:"tickCross", headerFilterParams: {
							"tristate":true, elementAttributes:{"value":"true"}
						},
						headerFilterEmptyCheck:function(value){return value === null}
					},
					{title:"Aktionen", field: "actions",
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							let button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-edit"></i>';
							button.title = this.$p.t('person', 'ferien_edit');
							button.addEventListener('click', (event) =>
								this.$refs.modal.open(cell.getData())
							);
							container.append(button);

							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary';
							button.innerHTML = '<i class="fa fa-trash"></i>';
							button.addEventListener('click', evt => {
								evt.stopPropagation();
								this.$fhcAlert
									.confirmDelete()
									.then(result => result ? cell.getData().ferien_id : Promise.reject({handled:true}))
									.then(ferien_id => this.$api.call(ApiFerienverwaltung.delete(ferien_id)))
									.then(() => {
										//cell.getRow().delete();
										this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
										this.reload();
									})
									.catch(this.$fhcAlert.handleSystemError);
							});
							container.append(button);

							return container;
						},
						frozen: true
					}
				]
			},
			tabulatorEvents: [
				{
					event: 'tableBuilt',
					handler: async () => {

						//await this.$p.loadCategory(['ferien', 'ui']);
						await this.$p.loadCategory(['global', 'ferien']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('ferien_id').component.updateDefinition({
							title: this.$p.t('ferien', 'ferien_id'),
						});
						cm.getColumnByField('vondatum').component.updateDefinition({
							title: this.$p.t('ferien', 'vondatum'),
						});
						cm.getColumnByField('bisdatum').component.updateDefinition({
							title: this.$p.t('ferien', 'bisdatum'),
						});
						cm.getColumnByField('bezeichnung').component.updateDefinition({
							title: this.$p.t('global', 'bezeichnung'),
						});
						cm.getColumnByField('actions').component.updateDefinition({
							title: this.$p.t('global', 'aktionen')
						});
					}
				}
			]
		}
	},
	computed: {
	},
	methods: {
		reload() {
			this.$refs.table.reloadTable();
		},
		//~ updateData(data) {
			//~ if (!data)
				//~ return this.reload();
			//~ //this.$refs.table.tabulator.updateOrAddData(data);
		//~ },
		actionNew() {
			this.$refs.modal.open();
		}
	},
	created() {
		//this.loading = true;
		//~ this.$api
			//~ .call(ApiFerienverwaltung.getOe())
			//~ .then(result => {
					//~ this.oeList = result.data;
				//~ }
			//~ )
			//~ .catch(error => {
				//~ if (error)
					//~ this.$fhcAlert.handleSystemError(error);
			//~ });

		this.$api
			.call(ApiFerienverwaltung.getDefaultVonBis())
			.then(result => {
					this.filterVonDatum = result.data.defaultVon;
					this.filterBisDatum = result.data.defaultBis;
				}
			)
			.catch(error => {
				if (error)
					this.$fhcAlert.handleSystemError(error);
			});
	},
	template: `
	<div class="h-100 d-flex flex-column">
		<div class="row justify-content-center">
			<div class="col-5">
				<form-input
					type="DatePicker"
					v-model="filterVonDatum"
					name="filtervondatum"
					:label="$p.t('ferien/vondatum')"
					:enable-time-picker="false"
					text-input
					format="dd.MM.yyyy"
					auto-apply
					>
				</form-input>
			</div>
			<div class="col-5">
				<form-input
					type="DatePicker"
					v-model="filterBisDatum"
					name="filterbisdatum"
					:label="$p.t('ferien/bisdatum')"
					:enable-time-picker="false"
					text-input
					format="dd.MM.yyyy"
					auto-apply
					>
				</form-input>
			</div>
			<div class="col-1 align-self-end">
				<button
					class="btn btn-primary"
					@click="reload()"
					:disabled="loading"
					>
					<i v-if="loading" class="fa fa-spinner fa-spin"></i>
					{{ $p.t('ui/anzeigen') }}
				</button>
			</div>
		</div>
		<div class="row mt-3">
			<div class="col">
				<core-filter-cmpt
					ref="table"
					table-only
					:side-menu="false"
					:tabulator-options="tabulatorOptions"
					:tabulator-events="tabulatorEvents"
					reload
					:reload-btn-infotext="this.$p.t('table', 'reload')"
					new-btn-show
					:new-btn-label="$p.t('ui/neu')"
					@click:new="actionNew"
					>
				</core-filter-cmpt>
				<ferien-modal ref="modal" @saved="reload"></ferien-modal>
			</div>
		</div>
	</div>`
};
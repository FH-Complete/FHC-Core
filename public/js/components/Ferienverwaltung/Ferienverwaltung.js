import {CoreFilterCmpt} from "../filter/Filter.js";
import {CoreNavigationCmpt} from "../navigation/Navigation.js";
import FormInput from "../Form/Input.js";
import FerienModal from "./Modal.js";

import ApiFerienverwaltung from '../../api/factory/ferienverwaltung/ferienverwaltung.js';

export default {
	name: "Ferienverwaltung",
	components: {
		CoreFilterCmpt,
		CoreNavigationCmpt,
		FormInput,
		FerienModal
	},
	data() {
		return {
			filterVonDatum: null,
			filterBisDatum: null,
			loading: false,
			sideMenuEntries: {},
			headerMenuEntries: {},
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
								this.$refs.modal.open(JSON.parse(JSON.stringify(cell.getData()))) // deep copy
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

						await this.$p.loadCategory(['global', 'ferien']);

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

						setHeader('ferien_id', this.$p.t('ferien', 'ferienId'));
						setHeader('vondatum', this.$p.t('ferien', 'vondatum'));
						setHeader('bisdatum', this.$p.t('ferien', 'bisdatum'));
						setHeader('bezeichnung', this.$p.t('global', 'bezeichnung'));
						setHeader('oe_kurzbz', this.$p.t('ferien', 'oeKurzbezeichnung'));
						setHeader('oe_bezeichnung', this.$p.t('ferien', 'oeBezeichnung'));
						setHeader('studienplan_bezeichnung', this.$p.t('global', 'studienplanBezeichnung'));
						setHeader('ferientyp_kurzbz', this.$p.t('ferien', 'ferientypKurzbz'));
						setHeader('mitarbeiterrelevant', this.$p.t('ferien', 'mitarbeiterrelevant'));
						setHeader('studierendenrelevant', this.$p.t('ferien', 'studierendenrelevant'));
						setHeader('lehre', this.$p.t('ferien', 'lehrePlanbar'));
						setHeader('actions', this.$p.t('global', 'aktionen'));
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
		actionNew() {
			this.$refs.modal.open();
		},
		importFerien() {
			this.$api
			.call(ApiFerienverwaltung.importFerien(this.filterVonDatum, this.filterBisDatum))
			.then(result => {
					this.$fhcAlert.alertSuccess(this.$p.t('ferien', 'importiert') + ": " + result.data);
					this.reload();
				}
			)
			.catch(error => {
				if (error)
					this.$fhcAlert.handleSystemError(error);
			});
		}
	},
	created() {

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

	<core-navigation-cmpt 
		v-bind:add-side-menu-entries="sideMenuEntries"
		v-bind:add-header-menu-entries="headerMenuEntries"
		>
	</core-navigation-cmpt>

	<div class="h-100 d-flex flex-column">
		<div class="row">
			<div class="col-4">
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
			<div class="col-4">
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
			<div class="col-2 align-self-end">
				<button
					class="btn btn-primary"
					@click="reload()"
					:disabled="loading"
					>
					<i v-if="loading" class="fa fa-spinner fa-spin"></i>
					{{ $p.t('ui/anzeigen') }}
				</button>
			</div>
			<div class="col-2 align-self-end justify-content-end">
				<button
					class="btn btn-secondary"
					@click="importFerien()"
					:disabled="loading"
					>
					<i v-if="loading" class="fa fa-spinner fa-spin"></i>
					{{ $p.t('lehre/ferienImportieren') }}
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
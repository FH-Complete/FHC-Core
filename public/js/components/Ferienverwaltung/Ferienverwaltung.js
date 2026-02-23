import {CoreFilterCmpt} from "../filter/Filter.js";
import FormInput from "../Form/Input.js";
import FerienNew from "./New.js";
//import KontoEdit from "./Konto/Edit.js";

import ApiFerienverwaltung from '../../api/factory/ferienverwaltung/ferienverwaltung.js';

export default {
	name: "Ferienverwaltung",
	components: {
		CoreFilterCmpt,
		FormInput,
		FerienNew
		//KontoEdit
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
			studiengang_kz_list: [],
			studiengang_kz: null,
			loading: false,
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiFerienverwaltung.getFerien(this.studiengang_kz)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title:"Ferien Id", field:"ferien_id", visible: false},
					{title:"Studiengang", field:"studiengang_kuerzel"},
					{
						title:"Datum von",
						field:"vondatum",
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
					{title:"Bezeichnung", field:"bezeichnung"},
					{title:"Aktionen", field: "actions",
						minWidth: 150, // Ensures Action-buttons will be always fully displayed
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							container.className = "d-flex gap-2";

							//~ let button = document.createElement('button');
							//~ button.className = 'btn btn-outline-secondary btn-action';
							//~ button.innerHTML = '<i class="fa fa-edit"></i>';
							//~ button.title = this.$p.t('person', 'adresse_edit');
							//~ button.addEventListener('click', (event) =>
								//~ this.actionEditAdress(cell.getData().adresse_id)
							//~ );
							//~ container.append(button);
							let button = document.createElement('button');
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
						cm.getColumnByField('studiengang_kuerzel').component.updateDefinition({
							title: this.$p.t('ferien', 'studiengang_kuerzel'),
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
		updateData(data) {
			if (!data)
				return this.reload();
			//this.$refs.table.tabulator.updateOrAddData(data);
		},
		actionNew() {
			this.$refs.new.open();
		},
		loadByStg() {
			this.reload();
		}
	},
	created() {
		this.loading = true;
		this.$api
			.call(ApiFerienverwaltung.getStg())
			.then(result => {
					this.studiengang_kz_list = result.data;
					this.loading = false;
				}
			)
			.catch(error => {
				if (error)
					this.$fhcAlert.handleSystemError(error);
				this.loading = false;
			});
	},
	template: `
	<div class="stv-details-konto h-100 d-flex flex-column">
		<div class="row justify-content-end">
			<div class="col-lg-3">
				<div class="input-group w-auto">
					<select class="form-select" v-model="studiengang_kz">
						<option selected="selected" :value="null">-- {{ $p.t('ferien/keineAuswahl') }} --</option>
						<option value="All">-- {{ $p.t('ferien/alleStudiengaenge') }} --</option>
						<option v-for="studiengang in studiengang_kz_list" :key="studiengang.studiengang_kz" :value="studiengang.studiengang_kz">
							{{ studiengang.kuerzel }}
						</option>
					</select>
					<button
						class="btn btn-primary"
						@click="loadByStg()"
						:disabled="loading"
						>
						<i v-if="loading" class="fa fa-spinner fa-spin"></i>
						{{ $p.t('ui/anzeigen') }}
					</button>
				</div>
			</div>
		</div>

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
		<ferien-new ref="new" :studiengang_kz_list="studiengang_kz_list" @saved="updateData"></ferien-new>
	</div>`
};
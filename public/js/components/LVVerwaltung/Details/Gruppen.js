import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ApiGruppe from "../../../api/lehrveranstaltung/gruppe.js";
export default{
	name: "LVGruppen",
	components: {
		CoreFilterCmpt,
		FormForm,
		FormInput
	},
	props: {
		lehreinheit_id: Number
	},
	inject: {
		dropdowns: {
			from: 'dropdowns'
		},
		showLVPlanGruppenDel: {
			from: 'permissionGruppenEntfernen',
			default: false
		},
	},
	computed: {
		tabulatorOptions() {
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiGruppe.getByLehreinheit(this.lehreinheit_id)),
				ajaxResponse: (url, params, response) => response.data,
				columns:[
					{title: this.$p.t('global', 'bezeichnung'), field:"bezeichnung"},
					{title: this.$p.t('global', 'beschreibung'), field:"beschreibung"},
					{title: this.$p.t('lehre', 'studiengang'), field:"studiengang"},
					{title: "ID", field:"id", visible:false},
					{
						title: this.$p.t('lehre', 'verplant'),
						field:"verplant",
						formatter:"tickCross",
						hozAlign:"center",
						formatterParams: {
							tickElement: '<i class="fa fa-check text-success"></i>',
							crossElement: '<i class="fa fa-xmark text-danger"></i>'
						}
					},
					{
						title: this.$p.t('global', 'actions'), field: 'actions',
						minWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							let button = document.createElement('button');
							container.className = "d-flex gap-1";

							button.className = 'btn btn-outline-secondary';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', (event) => {
								event.stopPropagation();
								this.deleteGroup(cell.getData().lehreinheitgruppe_id)
							});
							container.append(button);

							if (this.showLVPlanGruppenDel)
							{
								button = document.createElement('button');
								container.className = "d-flex gap-2";
								button.className = 'btn btn-outline-secondary';
								button.innerHTML = '<i class="fa fa-calendar-xmark"></i>';
								button.title = this.$p.t('lehre', 'auslvplanentfernen');
								button.disabled = !cell.getData().verplant;
								button.addEventListener('click', (event) => {
										event.stopPropagation();
										this.deleteLVPlan(cell.getData().lehreinheitgruppe_id)
								});
								container.append(button);
							}
							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				selectable: true,
				persistenceID: 'lvverwaltung_gruppen_2025_05_27_v1',
				height: 'auto',
			}
		}
	},
	data() {
		return{
			tabulatorEvents: [],
			filteredGroups: [],
			selectedGroup: null,
			abortController: null
		}
	},
	watch: {
		lehreinheit_id() {
			this.$refs.table.reloadTable();
		}
	},
	methods:{

		async searchGroup(event)
		{
			const query = event.query.trim();

			if (!query)
			{
				this.filteredLektor = [];
				return;
			}

			if (query.length < 2)
			{
				return;
			}

			if (this.abortController)
			{
				this.abortController.abort();
			}

			this.abortController = new AbortController();
			const signal = this.abortController.signal;

			this.$api.call(ApiGruppe.getAllSearch(query), { signal })
				.then(result => {
					this.filteredGroups = result.data.map(gruppe => ({
						label: gruppe.bezeichnung
							? `${gruppe.gruppe_kurzbz.trim()} (${gruppe.bezeichnung})`
							: gruppe.gruppe_kurzbz.trim(),
						gid: gruppe.gid,
						gruppe_kurzbz: gruppe.gruppe_kurzbz.trim(),
						lehrverband: gruppe.lehrverband,
						})
					)})
				.catch(this.$fhcAlert.handleSystemError)
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		addGroup()
		{
			let newData = {
				'gid': this.selectedGroup.gid,
				'lehreinheit_id': this.lehreinheit_id,
				'lehrverband': this.selectedGroup.lehrverband,
				'gruppe_kurzbz' : this.selectedGroup.gruppe_kurzbz
			}

			return this.$api.call(ApiGruppe.add(newData))
				.then(result => {
					this.reload()
					this.selectedGroup = ''
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		deleteLVPlan(lehreinheitgruppe_id)
		{
			let deleteData = {
				'lehreinheitgruppe_id': lehreinheitgruppe_id,
				'lehreinheit_id': this.lehreinheit_id
			}

			this.$api.call(ApiGruppe.deleteFromLVPlan(deleteData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				})
		},
		deleteGroup(lehreinheitgruppe_id)
		{
			let deleteData = {
				'lehreinheitgruppe_id': lehreinheitgruppe_id,
				'lehreinheit_id': this.lehreinheit_id
			}

			this.$api.call(ApiGruppe.delete(deleteData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				})
		},
	},
	template: `
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			:reload=true
			>
			<template #search> <!--TODO (david) Slot prüfen -->
				<form-input
					type="autocomplete"
					:suggestions="filteredGroups"
					:placeholder="$p.t('lehre', 'addGroup')"
					v-model="selectedGroup"
					field="label"
					@item-select="addGroup"
					@complete="searchGroup"
				></form-input>
			</template>
			
		</core-filter-cmpt>
		`
};
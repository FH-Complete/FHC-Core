import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ApiLektor from "../../../api/lehrveranstaltung/lektor.js";


export default{
	name: "LVLektorTable",
	components: {
		CoreFilterCmpt,
		FormForm,
		FormInput
	},
	props: {
		lehreinheit_id: Number,
		selected: String
	},
	inject: {
		dropdowns: {
			from: 'dropdowns'
		},
		showLVPlanLektorDel: {
			from: 'permissionLektorEntfernen',
			default: false
		},
	},
	emits: ['update:selected'],

	data() {
		return {
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.lektorSelected
				}
			],
			showAutocomplete: false,
			filteredLektor: [],
			selectedLektor: ''
		}
	},
	computed: {
		tabulatorOptions() {
			return {

				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiLektor.getByLehreinheit(this.lehreinheit_id)),
				ajaxResponse: (url, params, response) => { return response.data || [] },
				columns:[
				{title: this.$p.t('person', 'nachname'), field:"nachname"},
				{title: this.$p.t('person', 'vorname'), field:"vorname"},
				{title: this.$p.t('person', 'uid'), field:"mitarbeiter_uid"},
				{title: this.$p.t('lehre', 'lehreinheit_id'), field:"lehreinheit_id"},
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
					title:  this.$p.t('global', 'actions'), field: 'actions',
					minWidth: 150,
					formatter: (cell, formatterParams, onRendered) => {
						let container = document.createElement('div');
						let button = document.createElement('button');

						container.className = "d-flex gap-2";

						button.className = 'btn btn-outline-secondary btn-action';
						button.innerHTML = '<i class="fa fa-xmark"></i>';
						button.title = this.$p.t('ui', 'loeschen');
						button.addEventListener('click', (event) => {
							event.stopPropagation();
							this.deletePerson(cell.getData().mitarbeiter_uid, cell.getData().lehreinheit_id);
						});

						container.append(button);
						if (this.showLVPlanLektorDel)
						{
							button = document.createElement('button');
							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-calendar-xmark"></i>';
							button.disabled = !cell.getData().verplant;
							button.title = this.$p.t('ui', 'auslvplanentfernen');
							button.addEventListener('click', (event) => {
								event.stopPropagation();
								this.deleteLVPlan(cell.getData().mitarbeiter_uid, cell.getData().lehreinheit_id)
							});
							container.append(button);
						}

						return container;
					},
					frozen: true
				},
			],
				layout: 'fitDataFill',
				height:	'auto',
				selectable: true,
				selectableRangeMode: 'click',
				selectableRows:1,
				persistenceID: 'lehrveranstaltungen_lektor_table_2025_05_27_v1',
		}},
	},
	mounted(){
	},
	watch: {
		lehreinheit_id() {
			this.$refs.table.tabulator.setData('api/frontend/v1/lv/lektor/getLektorenByLE/' + encodeURIComponent(this.lehreinheit_id));
		},
	},
	methods:{
		lektorSelected(data)
		{
			if (data.length > 0)
			{
				let mitarbeiter_uid = data[0].mitarbeiter_uid;
				this.$emit('update:selected', mitarbeiter_uid);
			}
			else
			{
				this.$emit('update:selected', null);
			}
		},
		reload() {
			this.$refs.table.reloadTable();
		},

		deleteLVPlan(uid, lehreinheit_id)
		{
			let deleteData = {
				'mitarbeiter_uid': uid,
				'lehreinheit_id': lehreinheit_id,
			}

			this.$api.call(ApiLektor.deleteFromLVPlan(deleteData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				})
		},
		deletePerson(uid, lehreinheit_id)
		{
			let deleteData = {
				'mitarbeiter_uid': uid,
				'lehreinheit_id': lehreinheit_id,
			}

			this.$api.call(ApiLektor.deletePerson(deleteData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
					this.reload();
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {

				})
		},
		searchLektor(event)
		{
			const query = event.query.toLowerCase().trim();
			this.filteredLektor = this.dropdowns.lektor_array.filter(lektor => {
				const fullName = `${lektor.vorname.toLowerCase()} ${lektor.nachname.toLowerCase()}`;
				const reverseFullName = `${lektor.nachname.toLowerCase()} ${lektor.vorname.toLowerCase()}`;
				return fullName.includes(query) || reverseFullName.includes(query) || lektor.uid.toLowerCase().includes(query);
			}).map(lektor => ({
				label: `${lektor.nachname} ${lektor.vorname} (${lektor.uid})`,
				uid: lektor.uid
			}));
		},
		addLektor()
		{
			let newData = {
				'mitarbeiter_uid': this.selectedLektor.uid,
				'lehreinheit_id': this.lehreinheit_id
			}

			this.$api.call(ApiLektor.add(newData))
				.then(result => {
					this.reload()
					this.selectedLektor = ''
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

	},
	template: `
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:new-btn-label="$p.t('lehre', 'addlektor')"
			new-btn-show
			@click:new="showAutocomplete = !showAutocomplete"
			>
		<template #search> <!--TODO (david) Slot prüfen -->
			<form-input
				v-if="showAutocomplete"
				type="autocomplete"
				:suggestions="filteredLektor"
				:placeholder="$p.t('lehre', 'addLektor')"
				v-model="selectedLektor"
				field="label"
				@item-select="addLektor"
				@complete="searchLektor"
			></form-input>
		</template>

</core-filter-cmpt>
`
};



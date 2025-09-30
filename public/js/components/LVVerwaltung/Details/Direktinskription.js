import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ApiDirektGruppe from "../../../api/lehrveranstaltung/direktgruppe.js";
export default{
	name: "LVDirektGruppen",
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
	},
	computed: {
		tabulatorOptions() {
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiDirektGruppe.getByLehreinheit(this.lehreinheit_id)),
				ajaxResponse: (url, params, response) => { return response.data || [] },
				columns:[
					{title: this.$p.t('person', 'uid'), field:"uid"},
					{title: this.$p.t('person', 'vorname'), field:"vorname"},
					{title: this.$p.t('person', 'nachname'), field:"nachname"},
					{title: this.$p.t('lehre', 'gruppe'), field:"gruppe_kurzbz"},
					{
						title: this.$p.t('global', 'actions'), field: 'actions',
						minWidth: 150,
						formatter: (cell, formatterParams, onRendered) => {
							let container = document.createElement('div');
							let button = document.createElement('button');
							container.className = "d-flex gap-1";

							button.className = 'btn btn-outline-secondary btn-action';
							button.innerHTML = '<i class="fa fa-xmark"></i>';
							button.title = this.$p.t('ui', 'loeschen');
							button.addEventListener('click', (event) => {
								event.stopPropagation();
								this.deleteDirektGroup(cell.getData().gruppe_kurzbz, cell.getData().uid)
							});
							container.append(button);
							return container;
						},
						frozen: true
					},
				],
				layout: 'fitDataFill',
				selectable: true,
				persistenceID: 'lvverwaltung_direkt_gruppen_2025_05_27_v1',
				height: 'auto',
			}
		}
	},
	data() {
		return{
			lastSelected: null,
			gruppen: [],
			tabulatorEvents: [],
			showAutocomplete: false,
			selectedUser: null,
			filteredUsers: [],
			abortController: null,
			searchTimeout: null,
		}
	},
	watch: {
		lehreinheit_id(n) {
			this.$refs.table.reloadTable();
		}
	},
	methods:{
		reload() {
			this.$refs.table.reloadTable();
		},

		deleteDirektGroup(gruppe_kurzbz, uid)
		{
			let deleteData = {
				'gruppe_kurzbz': gruppe_kurzbz,
				'uid': uid,
				'lehreinheit_id': this.lehreinheit_id
			}

			this.$api.call(ApiDirektGruppe.delete(deleteData))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successDelete'));
				})
				.catch(this.$fhcAlert.handleSystemError)
				.finally(()=> {
					this.reload();
				})
		},
		searchUser(event)
		{
			const query = event.query.toLowerCase().trim();
			this.filteredUsers = this.dropdowns.benutzer_array.filter(user => {

				const fullName = `${user.vorname.toLowerCase()} ${user.nachname.toLowerCase()}`;
				const reverseFullName = `${user.nachname.toLowerCase()} ${user.vorname.toLowerCase()}`;
				return fullName.includes(query) || reverseFullName.includes(query) || user.uid.toLowerCase().includes(query) || user.studiengang.toLowerCase().includes(query);
			}).map(user => ({
				label: user.studiengang
					? `${user.nachname} ${user.vorname} ${user.uid} ${user.studiengang} ${user.semester}`
					: `${user.nachname} ${user.vorname} ${user.uid}`,
				uid: user.uid
			}));
		},
		addUser()
		{
			let newData = {
				'uid': this.selectedUser.uid,
				'lehreinheit_id': this.lehreinheit_id
			}
			return this.$api.call(ApiDirektGruppe.add(newData))
				.then(result => {
					this.reload()
					this.selectedUser = ''
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
			:reload=true
			:new-btn-label="$p.t('lehre', 'assignPerson')"
			new-btn-show
			@click:new="showAutocomplete = !showAutocomplete"
			>
			<template #search> <!--TODO (david) Slot prüfen -->
				<form-input
					v-if="showAutocomplete"
					type="autocomplete"
					:suggestions="filteredUsers"
					:placeholder="$p.t('lehre', 'assignPerson')"
					v-model="selectedUser"
					field="label"
					:minLength="3"
					@item-select="addUser"
					@complete="searchUser"
				></form-input>
			</template>
			
		</core-filter-cmpt>
		`
};
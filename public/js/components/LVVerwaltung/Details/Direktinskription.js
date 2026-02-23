import {CoreFilterCmpt} from "../../filter/Filter.js";
import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ApiDirektGruppe from "../../../api/lehrveranstaltung/direktgruppe.js";
import ApiGruppe from "../../../api/lehrveranstaltung/gruppe.js";
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
			selectedUser: null,
			filteredUsers: [],
			abortController: null,
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
			const query = event.query.trim();
			if (!query)
			{
				this.filteredUsers = [];
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

			this.$api.call(ApiGruppe.getBenutzerSearch(query), { signal })
				.then(result => {
					this.filteredUsers = result.data.map(user => ({
						label: user.studiengang
							? `${user.nachname} ${user.vorname} ${user.uid} ${user.studiengang} ${user.semester}`
							: `${user.nachname} ${user.vorname} ${user.uid}`,
						uid: user.uid
						})
					)})
				.catch(this.$fhcAlert.handleSystemError)
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
			>
			<template #search> <!--TODO (david) Slot prüfen -->
				<form-input
					type="autocomplete"
					:suggestions="filteredUsers"
					:placeholder="$p.t('lehre', 'assignPerson')"
					v-model="selectedUser"
					field="label"
					:minLength="2"
					@item-select="addUser"
					@complete="searchUser"
				></form-input>
			</template>
			
		</core-filter-cmpt>
		`
};
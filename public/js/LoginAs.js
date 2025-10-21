/**
 * Copyright (C) 2025 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import PluginsPhrasen from '../js/plugins/Phrasen.js';
import ApiLogin from '../js/api/factory/login.js';

import {CoreNavigationCmpt} from '../js/components/navigation/Navigation.js';
import PvAutoComplete from "../../index.ci.php/public/js/components/primevue/autocomplete/autocomplete.esm.min.js";

const loginAsApp = Vue.createApp({
	data: function() {
		return {
			person_id: '',
			username: '',
			surname: '',
			name: '',
			selectedUser: null,
			filteredUsers: [],
			appSideMenuEntries: {}
		};
	},
	components: {
		CoreNavigationCmpt,
		PvAutoComplete
	},
	methods: {
		loginAs: function() {
			if (this.selectedUser != null)
			{
				this.$api
					.call(ApiLogin.loginASByPersonId({
						person_id: this.selectedUser.person_id
					}))
					.then((response) => {
						location.reload();
					})
					.catch((error) => {
						console.error(error);
					});
			}
		},
		logout: function() {
			this.$api
				.call(ApiLogin.logout())
				.then((response) => {
					location.reload();
				})
				.catch((error) => {
					console.error(error);
				});
		},
		searchUser: function(event) {
			if (event.query.length >= 3)
			{
				this.$api
					.call(ApiLogin.searchUser(event.query))
					.then(result => {
						this.filteredUsers = result.data.retval;
					})
					.catch((error) => {
						console.error(error);
					});
			}
		}
	},
	mounted: function() {
		this.$api
			.call(ApiLogin.whoAmI())
			.then((response) => {
				// If property data exists
				if (Object.hasOwn(response, 'data'))
				{
					if (response.data != null && Object.hasOwn(response.data, 'person_id'))
					{
						this.person_id = response.data.person_id;
						this.username = response.data.username;
						this.surname = response.data.surname;
						this.name = response.data.name;
					}
					else
					{
						this.person_id = 'Not logged';
						this.username = 'Not logged';
						this.surname = 'Not logged';
						this.name = 'Not logged';
					}
				}
			})
			.catch((error) => {
				console.error(error);
			});
	},
	template: `
		<!-- Navigation component -->
		<core-navigation-cmpt v-bind:add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>
		<div style="width: 700px !important">
			<div class="card" style="padding: 20px;">
				<div class="mb-3">
					Who am I?
				</div>
				<div class="mb-3">
					<div class="d-inline-flex align-items-center">
						<label for="person_id" class="form-label" style="width: 150px !important">Person ID</label>
						<input type="text" style="width: 400px !important" disabled="disabled" class="form-control" id="person_id" v-model="person_id">
					</div>
				</div>
				<div class="mb-3">
					<div class="d-inline-flex align-items-center">
						<label for="username" class="form-label" style="width: 150px !important">UID</label>
						<input type="text" style="width: 400px !important" disabled="disabled" class="form-control" id="username" v-model="username">
					</div>
				</div>
				<div class="mb-3">
					<div class="d-inline-flex align-items-center">
						<label for="surname" class="form-label" style="width: 150px !important">Surname</label>
						<input type="text" style="width: 400px !important" disabled="disabled" class="form-control" id="surname" v-model="surname">
					</div>
				</div>
				<div class="mb-3">
					<div class="d-inline-flex align-items-center">
						<label for="name" class="form-label" style="width: 150px !important">Name</label>
						<input type="text" style="width: 400px !important" disabled="disabled" class="form-control" id="name" v-model="name">
					</div>
				</div>
				<div class="d-flex align-items-center justify-content-center">
					<button type="button" class="btn btn-primary" @click="logout">Logout</button>
				</div>
			</div>
			<div class="card" style="padding: 20px;">
				<div class="mb-3">
					Who I want to be?
				</div>
				<div class="mb-3">
					<div class="d-inline-flex align-items-center">
						<PvAutoComplete inputStyle="width: 600px;" v-model="selectedUser" optionLabel="label" :suggestions="filteredUsers" @complete="searchUser" placeholder="Search user..." />
					</div>
				</div>
				<div class="d-flex align-items-center justify-content-center">
					<button type="button" class="btn btn-primary" @click="loginAs">Login as</button>
				</div>
			</div>
		</div>
	`
});

loginAsApp.
	use(PluginsPhrasen).
	use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	}).
	mount('#main');


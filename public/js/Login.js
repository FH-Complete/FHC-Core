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

const loginApp = Vue.createApp({
	data: function() {
		return {
			username: '',
			password: ''
		};
	},
	components: {
	},
	methods: {
		loginLDAP: function() {
			this.$api
				.call(ApiLogin.loginLDAP({
					username: this.username,
					password: this.password
				}))
				.then((response) => {
					// If property data exists
					if (Object.hasOwn(response, 'data'))
					{
						// If property data is a string 
						if (typeof response.data === 'string' || response.data instanceof String)
						{
							// If property data is a valid URL
							try {
								let url = new URL(response.data);
								// If here the URL contained in response.data is fine
								// and can be used to switch to the landing page
								document.location.href = response.data;
							} catch (_) {}
						}
					}
				})
				.catch((error) => {
				        console.error(error);
				});
	}
},
	template: `
		<div class="d-flex align-items-center justify-content-center">
			<div>
			<div class="mb-3">
				<label for="username" class="form-label">Username</label>
				<input type="text" class="form-control" name="username" id="username" v-model="username">
			</div>
			<div class="mb-3">
				<label for="password" class="form-label">Password</label>
				<input type="password" class="form-control" name="password" id="password" v-model="password">
			</div>
			<div class="d-flex align-items-center justify-content-center">
				<button type="button" class="btn btn-primary" @click="loginLDAP">Login</button>
			</div>
			</div>
		</div>
	`
});

loginApp.use(PluginsPhrasen).mount('#main');


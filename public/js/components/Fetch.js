/**
 * Copyright (C) 2022 fhcomplete.org
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

/**
 *
 */
export const CoreFetchCmpt = {
	emits: ['dataFetched'], // this component can emit the event dataFetched that it is catched by this component itself
	props: {
		refresh: { // to refresh this component
			type: Boolean
		},
		apiFunction: { // the function to call, must return a Promise
			required: true,
			type: Function
		},
		apiFunctionParameters: {} // parameters for the apiFunction, type mixed, optional
	},
	watch: {
		/**
		 * If the refresh property is changed then call fetchData
		 */
		refresh: function (newValue, oldValue) {
			this.fetchData();
		}
	},
	data: function() {
		return {
			loading: false, // if in loading or not
			error: false, // if an error occurred while loading data
			errorMessage: null // the error message
		};
	},
	created: function() {
		this.fetchData();
	},
	methods: {
		/**
		 *
		 */
		fetchData: function() {
	        	this.loading = true; // loader started

			// Checks if the apifunction is a callable function
			if (typeof this.apiFunction == "function")
			{
				// Call the function stored in apiFunction
	        		let apiFunctionResult = this.apiFunction(this.apiFunctionParameters);

				// It is expected that the function returns a Promise
				if (apiFunctionResult instanceof Promise)
				{
					apiFunctionResult
						.then(this.successHandler) // on success
						.catch(this.errorHandler) // on error
						.finally(this.finallyHandler); // finally in any case
				}
				else // otherwise display an error
				{
					this.setError("The called apiFunction does not return a Promise");
				}
			}
			else // otherwise display an error
			{
				this.setError("Property apiFunction is not a function");
			}
		},
		/**
		 *
		 */
		setError: function(errorMessage) {
			this.loading = false; // loading ended
			this.error = true; // error occurred
			this.errorMessage = errorMessage; // save the error message
		},
		/**
		 *
		 */
		successHandler: function(response) {
			this.$emit('dataFetched', response.data); // trigger the event dataFetched
		},
		/**
		 *
		 */
		errorHandler: function(error) {
			this.setError(error.message);
		},
		/**
		 *
		 */
		finallyHandler: function() {
			this.loading = false; // loading ended
		}
	},
	template: `
		<slot v-if="loading">
			<div class="fetch-loader">Loading...</div>
		</slot>
		<slot v-if="error">
			<div class="fetch-error">{{ errorMessage }}</div>
		</slot>
	`
};


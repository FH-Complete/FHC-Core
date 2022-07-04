export const CoreFetchCmpt = {
	data: function() {
		return {
			loading: false,
			error: false,
			errorMessage: null
		};
	},
	emits: ['dataFetched'],
	props: {
		apiFunction: Function
	},
	created: function () {
		this.fetchData();
	},
	methods: {
		fetchData: function() {
			// Loader started
	        	this.loading = true;

			// Checks if the apifunction is a callable function
			if (typeof this.apiFunction == "function")
			{
				// Call the function stored in apiFunction
	        		let apiFunctionResult = this.apiFunction();

				// It is expected that the function returns a Promise
				if (apiFunctionResult instanceof Promise)
				{
					apiFunctionResult.then(this._success).catch(this._error).then(this._finally);
				}
				else // otherwise display an error
				{
					this._setError("The called apiFunction does not return a Promise");
				}
			}
			else // otherwise display an error
			{
				this._setError("Property apiFunction is not a function");
			}
		},
		_setError(errorMessage) {
			this.loading = false;
			this.error = true;
			this.errorMessage = errorMessage;
		},
		_success: function(response) {
			this.$emit('dataFetched', response.data);
		},
		_error: function(error) {
			this._setError(error.message);
		},
		_finally: function() {
			this.loading = false;
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


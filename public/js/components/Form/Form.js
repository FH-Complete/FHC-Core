import FhcFragment from "../Fragment.js";

export default {
	components: {
		FhcFragment
	},
	provide() {
		return {
			$registerToForm: component => {
				if (this.inputs.indexOf(component) < 0)
					this.inputs.push(component);
			},
			$clearValidationForName: this.clearValidationForName
		};
	},
	props: {
		tag: {
			type: String,
			default: 'form'
		}
	},
	data() {
		return {
			inputs: []
		}
	},
	computed: {
		sortedInputs() {
			return this.inputs.reduce((a,c) => {
				let name = c.name || '_default';
				if (!a[name])
					a[name] = [];
				a[name].push(c);

				if (c.lcType == 'checkbox' && name.substr(-1) == ']' && name.indexOf('[')) {
					name = name.substr(0, name.lastIndexOf('['));
					if (!a[name])
						a[name] = [];
					a[name].push(c);
				}

				return a;
			}, {});
		},
		factory() {
			const factory = Object.create(Object.getPrototypeOf(this.$fhcApi.factory), Object.getOwnPropertyDescriptors(this.$fhcApi.factory));
			factory.$fhcApi = {
				get: this.get,
				post: this.post,
				_defaultErrorHandlers: this.$fhcApi._defaultErrorHandlers
			};
			return factory;
		}
	},
	methods: {
		get(...args) {
			if (typeof args[0] == 'object' && args[0].clearValidation && args[0].setFeedback)
				args[0] = this;
			else
				args.unshift(this);
			
			return this.$fhcApi.get(...args);
		},
		post(...args) {
			if (typeof args[0] == 'object' && args[0].clearValidation && args[0].setFeedback)
				args[0] = this;
			else
				args.unshift(this);
			
			return this.$fhcApi.post(...args);
		},
		_sendFeedbackToInput(inputs, feedback, valid) {
			if (inputs.length) {
				inputs.forEach(input => input.setFeedback(valid, feedback));
				return false;
			}
			if (this.$fhcAlert) {
				this.$fhcAlert[valid ? 'alertSuccess' : 'alertError'](feedback);
				return false;
			}
			return true;
		},
		setFeedback(valid, feedback) {
			if (Array.isArray(feedback)) {
				let remaining = feedback.filter(fb => 
					this._sendFeedbackToInput(
						this.sortedInputs['_default'] || [],
						fb,
						valid
					)
				);
				return remaining.length ? remaining : null;
			}
			if (typeof feedback === 'object') {
				let remaining = Object.entries(feedback).filter(([name, fb]) => 
					this._sendFeedbackToInput(
						this.sortedInputs[name.split('.')[0] + name.split('.').slice(1).map(p => `[${p}]`).join("")] || this.sortedInputs['_default'] || [],
						fb,
						valid
					)
				);
				return remaining.length ? Object.fromEntries(remaining) : null;
			}

			let remaining = this._sendFeedbackToInput(
				this.sortedInputs['_default'] || [],
				feedback,
				valid
			);
			return remaining ? feedback : null;
		},
		clearValidation() {
			this.inputs.forEach(input => input.clearValidation());
		},
		send(promise) {
			return new Promise((resolve, reject) => {
				promise.then(result => {
					if (result?.status == 200 && result.data) {
						if (typeof result.data !== 'object' || !result.data.hasOwnProperty('retval'))
							// TODO(chris): IMPLEMENT! Error in API
							return reject(result);
						if (result.data.error)
							// TODO(chris): IMPLEMENT! Error in API
							return reject(result);
						const data = result.data.retval;
						// TODO(chris): check for something better/add new standardized return value
						if (result.data.code == 1)
							this.setFeedback(true, data);
						return resolve(data);
					}
					// TODO(chris): IMPLEMENT! Wrong result object
					reject(result);
				}).catch(result => {
					if (result?.response?.status == 400 && result.response.data) {
						if (typeof result.response.data !== 'object' || !result.response.data.hasOwnProperty('retval'))
							// TODO(chris): IMPLEMENT! Error in API
							return reject(result);
						this.clearValidation();
						const remaining = this.setFeedback(
							false,
							result.response.data.retval
						);
						if (remaining) {
							result.response.data.retval = remaining;
							return reject(result);
						}
					} else if (result?.response?.status == 500) {
						if (this.$fhcAlert)
							this.$fhcAlert.handleSystemError(result);
						else
							return reject(result);
					} else {
						return reject(result);
					}
				});
			});
		},
		clearValidationForName(name) {
			(this.sortedInputs[name.split('.')[0] + name.split('.').slice(1).map(p => `[${p}]`).join("")] || this.sortedInputs['_default'] || [])
				.forEach(input => input.clearValidation());
		}
	},
	template: `
	<component :is="tag || 'FhcFragment'" v-bind="$attrs">
		<slot></slot>
	</component>`
}

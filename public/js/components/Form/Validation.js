export default {
	props: {
		name: String
	},
	data() {
		return {
			isValid: false,
			isInvalid: false,
			invalidFeedback: ''
		};
	},
	methods: {
		reset() {
			this.isValid = false;
			this.isInvalid = false;
			this.invalidFeedback = '';
		},
		setValid() {
			this.isValid = true;
		},
		setInvalid(feedback) {
			this.invalidFeedback = feedback?.detail || '';
			this.isInvalid = true;
		}
	},
	render() {
		if (!this.$slots.default)
			return Vue.h('div', {
				'data-fhc-form-error': true,
				class: {
					'alert': true,
					'alert-danger': true,
					'd-none': !this.isInvalid
				},
				role: 'alert',
				onFhcFormReset: this.reset,
				onFhcFormError: this.setInvalid
			}, (this.invalidFeedback || []).map((txt, i) => Vue.h('p', {
				key: i,
				class: i+1 == (this.invalidFeedback || []).length ? 'mb-0' : ''
			}, txt)));
		
		const res = this.$slots.default();
		const orig = res.shift();
		let options = {
			'data-fhc-form-validate': this.name,
			onFhcFormReset: this.reset,
			onFhcFormValidate: this.setValid,
			onFhcFormInvalidate: this.setInvalid,
			class: {
				'form-control': true,
				'is-valid': this.isValid,
				'is-invalid': this.isInvalid
			}
		};
		if (orig.type.__name == 'VueDatePicker') {
			options.class['p-0'] = true;
			options.inputClassName = 'border-0';
		}
		res.unshift(Vue.cloneVNode(orig, options));

		if (this.isInvalid && this.invalidFeedback && this.$slots.default)
			res.push(Vue.h('div', {
				class: 'invalid-feedback'
			}, this.invalidFeedback));

		return res;
	},
};
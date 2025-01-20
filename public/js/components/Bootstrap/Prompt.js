import BsAlert from './Alert.js';

export default {
	mixins: [
		BsAlert
	],
	props: {
		placeholder: String,
		default: String
	},
	data: () => ({
		value: '',
		result: false
	}),
	created() {
		if (this.default)
			this.value = this.default;
	},
	popup(msg, options) {
		if (typeof options === 'string')
			options = { default: options };
		return BsAlert.popup.bind(this)(msg, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-prompt" v-bind="$props">
		<template v-slot:default>
			<slot></slot>
			<div>
				<input ref="input" type="text" class="form-control" :placeholder="placeholder" v-model="value">
			</div>
		</template>
		<template v-slot:footer>
			<button type="button" class="btn btn-primary" @click="result=value;this.hide()">OK</button>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
		</template>
	</bs-modal>`
}

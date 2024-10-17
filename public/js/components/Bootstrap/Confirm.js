import BsAlert from './Alert.js';

export default {
	mixins: [
		BsAlert
	],
	data: () => ({
		result: false
	}),
	popup(msg, options) {
		return BsAlert.popup.bind(this)(msg, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-confirm" v-bind="$props">
		<template v-slot:default>
			<slot></slot>
		</template>
		<template v-slot:footer>
			<button type="button" class="btn btn-primary" @click="result=true;this.hide()">OK</button>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
		</template>
	</bs-modal>`
}

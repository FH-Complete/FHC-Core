import BsModal from './Modal.js';

export default {
	components: {
		BsModal
	},
	mixins: [
		BsModal
	],
	props: {
		dialogClass: {
			type: [String,Array,Object],
			default: 'modal-dialog-centered'
		},
		/*
		 * NOTE(chris):
		 * Hack to expose in "emits" declared events to $props which we use
		 * in the v-bind directive to forward all events.
		 * @see: https://github.com/vuejs/core/issues/3432
		*/
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function
	},
	data: () => ({
		result: true
	}),
	mounted() {
		this.modal = this.$refs.modalContainer.modal;
	},
	popup(msg, options) {
		return BsModal.popup.bind(this)(msg, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-alert" v-bind="$props">
		<template v-slot:default>
			<slot></slot>
		</template>
		<template v-slot:footer>
			<button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
		</template>
	</bs-modal>`
}

import BsModal from "../../Bootstrap/Modal.js";
import WidgetIcon from "./WidgetIcon.js";

export default {
	components: {
		BsModal,
		WidgetIcon,
	},
	props: [
		"widgets"
	],
	data: () => ({
		callbacks: {}
	}),
	methods: {
		getWidget() {
			return new Promise((resolve,reject) => {
				this.callbacks = {resolve,reject};
				this.$refs.modal.show();
			});
		},
		close() {
			if (this.callbacks.reject)
				this.callbacks.reject();
			this.callbacks = {};
		},
		pick(widget_id) {
			if (this.callbacks.resolve)
				this.callbacks.resolve(widget_id);
			this.callbacks = {};
			this.$refs.modal.hide();
		},
		
	},
	template: `<div class="dashboard-widget-picker">
		<bs-modal ref="modal" class="fade" :dialog-class="{'modal-fullscreen-sm-down': 1, 'modal-xl': widgets && widgets.length > 0}" @hiddenBsModal="close">
			<template v-slot:title>Create new widget</template>
			<template v-slot:default>
				<div v-if="widgets" class="row g-2">
					<div v-if="!widgets.length">
						No Widgets available
					</div>
					<div v-for="widget in widgets" :key="widget.widget_id" class="col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2">
						<widget-icon @select="pick" :widget="widget" ></widget-icon>
					</div>
				</div>
				<div v-else class="text-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
			</template>
		</bs-modal>
	</div>`
}

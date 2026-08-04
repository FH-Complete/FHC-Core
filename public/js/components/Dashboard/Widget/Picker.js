import BsModal from "../../Bootstrap/Modal.js";
import WidgetIcon from "./WidgetIcon.js";

export default {
	components: {
		BsModal,
		WidgetIcon,
	},
	props: [
		"widgets",
		"hiddenWidgets"
	],
	data: () => ({
		callbacks: {}
	}),
	computed: {
		hasAnyWidgets() {
			if (!this.widgets)
				return false;
			if (!this.widgets.length && !this.hiddenWidgets?.length)
				return false;
			return true;
		}
	},
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
	template: /* html */`
	<div class="dashboard-widget-picker">
		<bs-modal
			ref="modal"
			class="fade"
			:dialog-class="{ 'modal-fullscreen-sm-down': 1, 'modal-xl': hasAnyWidgets }"
			@hiddenBsModal="close"
		>
			<template v-slot:title>{{ $p.t('dashboard/createWidget') }}</template>
			<template v-slot:default>
				<template v-if="widgets && hiddenWidgets">
					<div
						v-if="!widgets.length && !hiddenWidgets.length"
						class="row g-2"
					>
						<div>{{ $p.t('dashboard/noWidgetsAvailable') }}</div>
					</div>
					<template v-else>
						<div
							v-if="widgets.length"
							class="row g-2"
						>
							<div
								v-for="widget in widgets"
								:key="widget.widget_id"
								class="widget-icon-container col-sm-6 col-md-4 col-lg-3 col-xl-2"
							>
								<widget-icon @select="pick" :widget="widget"></widget-icon>
							</div>
						</div>
						<div
							v-if="hiddenWidgets.length"
							class="row g-2"
							:class="widgets.length ? 'border-top mt-2' : ''"
						>
							<div
								v-for="widget in hiddenWidgets"
								:key="widget.widget_id"
								class="widget-icon-container col-sm-6 col-md-4 col-lg-3 col-xl-2"
							>
								<widget-icon @select="pick(widget)" :widget="widgets.find(w => w.widget_id == widget.widget)"></widget-icon>
							</div>
						</div>
					</template>
				</template>
				<div v-else class="text-center">
					<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
				</div>
			</template>
		</bs-modal>
	</div>`
}

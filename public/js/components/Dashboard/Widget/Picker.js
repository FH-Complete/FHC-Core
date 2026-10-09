import BsModal from "../../Bootstrap/Modal.js";
import WidgetIcon from "./WidgetIcon.js";

export default {
	components: {
		BsModal,
		WidgetIcon,
	},
	props: {
		widgets: {
			type: [ Array, null ],
			required: true
		},
		hiddenWidgets: {
			type: Array,
			default: []
		}
	},
	data: () => ({
		callbacks: {}
	}),
	computed: {
		// filter widgets away the user has no permissions for
		availableWidgets() {
			return (this.widgets || []).filter(widget => widget.permitted !== false);
		},
		hasAnyWidgets() {
			if (!this.widgets)
				return false;
			if (!this.availableWidgets.length && !this.hiddenWidgets?.length)
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
						v-if="!availableWidgets.length && !hiddenWidgets.length"
						class="row g-2"
					>
						<div>{{ $p.t('dashboard/noWidgetsAvailable') }}</div>
					</div>
					<template v-else>
						<div
							v-if="hiddenWidgets.length"
							class="row g-2"
							:class="widgets.length ? 'border-bottom pb-3 mb-3' : ''"
						>
							<div
								v-for="widget in hiddenWidgets"
								:key="widget.widget_id"
								class="widget-icon-container col-sm-6 col-md-4 col-lg-3 col-xl-2"
							>
								<widget-icon @select="pick(widget)" :widget="widgets.find(w => w.widget_id == widget.widget)"></widget-icon>
							</div>
						</div>
						<div
							v-if="availableWidgets.length"
							class="row g-2"
						>
							<template
								v-for="widget in availableWidgets"
								:key="widget.widget_id"
							>
								<div v-if="widget.permitted" class="widget-icon-container col-sm-6 col-md-4 col-lg-3 col-xl-2">
									<widget-icon @select="pick" :widget="widget"></widget-icon>
								</div>
							</template>
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

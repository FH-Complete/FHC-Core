import BsModal from "../Bootstrap/Modal.js";
import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";

export default {
	components: {
		BsModal
	},
	data: () => ({
		component: '',
		arguments: null,
		target: false,
		widget: null,
		tmpConfig: {},
		isLoading: false,
		hasConfig: true
	}),
	emits: [
		"change",
		"remove",
		"dragstart",
		"resizestart",
	],
	props: [
		"id",
		"config",
		"width",
		"height",
		"custom",
		"hidden",
		"editMode",
		"loading"
	],
	computed: {
		isResizeable() {
			if (!this.widget)
				return false;
			return this.widget.setup.width.max || this.widget.setup.height.max;
		},
		ready() {
			return this.component && this.arguments !== null
		}
	},
	methods: {
		mouseDown(e) {
			this.target = e.target;
		},
		startDrag(e) {
			if (this.$refs.dragHandle.contains(this.target)) {
				this.$emit('dragstart', e);
			} else if (this.isResizeable && this.$refs.resizeHandle.contains(this.target)) {
				if (this.isResizeable)
					this.$emit('resizestart', e);
				else
					e.preventDefault();
			} else {
				e.preventDefault();
			}
		},
		openConfig() {
			this.tmpConfig = {...this.arguments};
			this.$refs.config.show();
		},
		setConfig(hasConfig) {
			this.hasConfig = hasConfig;
		},
		changeConfig() {
			this.isLoading = true;
			let config = {...this.tmpConfig};
			this.sendChangeConfig(config);
		},
		changeConfigManually() {
			let config = {...this.arguments};
			this.sendChangeConfig(config);
		},
		sendChangeConfig(config) {
			for (var k in config) {
				if (this.widget.arguments[k] == config[k]) {
					delete config[k];
				}
			}
			this.$emit('change', config);
		}
	},
	watch: {
		config() {
			this.arguments = {...this.widget.arguments, ...this.config};
			this.tmpConfig = {...this.arguments};
			this.$refs.config.hide();
			this.isLoading = false;
		}
	},
	async created() {
		this.widget = await CachedWidgetLoader.loadWidget(this.id);
		let component = (await import('../' + this.widget.setup.file)).default;
		this.$options.components['widget' + this.widget.widget_id] = component;
		this.component = 'widget' + this.widget.widget_id;
		this.arguments = {...this.widget.arguments, ...this.config};
		this.tmpConfig = {...this.arguments};
	},
	template: `
	<div v-if="loading">
		<div class="d-flex justify-content-center align-items-center h-100">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
	</div>
	<div v-else-if="!hidden || editMode" class="dashboard-item card overflow-hidden h-100" :class="arguments && arguments.className ? arguments.className : ''">
		<div v-if="editMode && widget" class="card-header d-flex ps-0 pe-2">
			<span drag-action="move" class="col-auto mx-2 px-2 cursor-move"><i class="fa-solid fa-grip-vertical"></i></span>
			<span class="col">{{ widget.setup.name }}</span>
			<a v-if="hasConfig" class="col-auto px-1" href="#" @click.prevent="openConfig"><i class="fa-solid fa-gear"></i></a>
			<a v-if="custom" class="col-auto px-1" href="#" @click.prevent="$emit('remove')">
				<i class="fa-solid fa-trash"></i>
			</a>
			<div v-else class="col-auto px-1 form-switch">
				<input class="form-check-input ms-0" type="checkbox" role="switch" id="flexSwitchCheckChecked" :checked="!hidden" @input="$emit('remove', hidden)">
			</div>
		</div>
		<div v-if="ready" class="card-body overflow-hidden">
			<component :is="component" :config="arguments" :width="width" :height="height" @setConfig="setConfig" @change="changeConfigManually"></component>
		</div>
		<div v-else class="card-body overflow-hidden text-center d-flex flex-column justify-content-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
		<bs-modal ref="config">
			<template v-slot:title>
				{{ widget ? 'Config for ' + widget.setup.name : '' }}
			</template>
			<template v-slot:default>
				<component v-if="ready && !isLoading" :is="component" :config="tmpConfig" @change="changeConfig" :configMode="true"></component>
				<div v-else class="text-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
			</template>
			<template v-slot:footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" @click="changeConfig">Save changes</button>
			</template>
		</bs-modal>
		<div v-if="editMode && isResizeable" class="card-footer d-flex justify-content-end p-0">
			<span drag-action="resize" class="col-auto px-1 cursor-nw-resize"><i class="fa-solid fa-up-right-and-down-left-from-center mirror-x"></i></span>
		</div>
	</div>`
}
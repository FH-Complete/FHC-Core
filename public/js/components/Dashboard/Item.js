import BsModal from "../Bootstrap/Modal.js";
import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";
import HeightTransition from "../Tranistion/HeightTransition.js";

export default {
	name: 'Item',
	components: {
		BsModal,
		HeightTransition
	},
	data: () => ({
		component: "",
		arguments: null,
		target: false,
		widget: null,
		tmpConfig: {},
		isLoading: false,
		hasConfig: false,
		sharedData: null,
	}),
	emits: [
		"change",
		"remove",
		"dragstart",
		"resizestart",
		"configOpened",
		"configClosed",
		"pinItem",
		"unPinItem"
	],
	props: [
		"id",
		"widgetID",
		"config",
		"width",
		"height",
		"custom",
		"hidden",
		"editMode",
		"loading",
		"item_data",
		"place",
		"setup",
	],
	computed: {
		maxHeight(){
			return this.setup?.height?.max;
		},
		maxWidth(){
			if (Object.prototype.toString.call(this.setup?.width) == "[object Number]"){
				return this.setup?.width;
			}
			return this.setup?.width?.max;
		},
		minHeight() {
			return this.setup?.height?.min;
		},
		minWidth() {
			return this.setup?.width?.min;
		},
		isResizeable(){
			return this.maxWidth >1 || this.maxHeight >1;
		},
		isPinned(){
			return this.place?.pinned ? true : false;
		},
		ready() {
			return this.component && this.arguments !== null;
		},
	},
	methods: {
		unpin(){
			// Unpinning is only possible in edit mode
			if(!this.editMode)
				return;
			let result = { item: this.item_data, x: this.item_data.x, y: this.item_data.y };
			this.$emit('unPinItem', [result]);
		},
		pinItem(){
			let result = { item: this.item_data, x: this.item_data.x, y: this.item_data.y};
			this.$emit('pinItem',[result]);
		},
		getWidgetC4Link(widget) {
			return (FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router + widget.setup.cis4link)
		},
		handleShowBsModal() {
			this.$emit('configOpened')
		},
		handleHideBsModal() {
			this.$emit('configClosed')
		},
		mouseDown(e) {
			this.target = e.target;
		},
		startDrag(e) {
			if (this.$refs.dragHandle.contains(this.target)) {
				this.$emit("dragstart", e);
			} else if (
				this.isResizeable &&
				this.$refs.resizeHandle.contains(this.target)
			) {
				if (this.isResizeable) this.$emit("resizestart", e);
				else e.preventDefault();
			} else {
				e.preventDefault();
			}
		},
		openConfig() {
			this.tmpConfig = { ...this.arguments };
			this.$refs.config.show();
		},
		setConfig(hasConfig) {
			this.hasConfig = hasConfig;
		},
		changeConfig() {
			this.isLoading = true;
			let config = { ...this.tmpConfig };
			this.sendChangeConfig(config);
		},
		changeConfigManually() {
			let config = { ...this.arguments };
			this.sendChangeConfig(config);
		},
		sendChangeConfig(config) {
			for (var k in config) {
				if (this.widget.arguments[k] == config[k]) {
				delete config[k];
				}
			}
			this.$emit("change", config);
		},
	},
	watch: {
		config() {
			this.arguments = { ...this.widget?.arguments, ...this.config };
			this.tmpConfig = { ...this.arguments };
			this.$refs.config && this.$refs.config.hide();
			this.isLoading = false;
		},
	},
	async created() {
		this.widget = await CachedWidgetLoader.loadWidget(this.id);
		let component = (await import("../" + this.widget.setup.file)).default;
		this.$options.components["widget" + this.widget.widget_id] = component;
		this.component = "widget" + this.widget.widget_id;
		this.arguments = { ...this.widget.arguments, ...this.config };
		this.tmpConfig = { ...this.arguments };
	},
	template: /*html*/ `
	<div v-if="loading">
		<div class="d-flex justify-content-center align-items-center h-100">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
	</div>
	<div v-else-if="!hidden || editMode" :id="widgetID" class="dashboard-item card overflow-hidden h-100 position-relative" :class="arguments && arguments.className ? arguments.className : ''">
		<div v-if="widget" class="card-header d-flex ps-0 pe-2 align-items-center">
			<Transition>
				<span v-if="editMode && !isPinned" drag-action="move" class="col-auto mx-2 px-2 cursor-move"><i class="fa-solid fa-grip-vertical"></i></span>
			</Transition>
			<span class="col mx-2 px-2">{{ widget.setup.name }}</span>
			<template v-if="isPinned">
				<div v-if="editMode" pinned="true" @click="unpin" class="pin cursor-pointer col-auto me-2">
					<i class="fa-solid fa-thumbtack "></i>
				</div>
				<div v-else class="col-auto me-2">
					<i class="fa-solid fa-thumbtack "></i>
				</div>
			</template>
			<template v-else>
				<div v-if="editMode"  class="col-auto me-2 pin" @click="pinItem">
					<i class="fa-solid fa-thumbtack" style="color:lightgray;"></i>
				</div>
			</template>
			<a v-if="widget.setup.cis4link" :href="getWidgetC4Link(widget)" class="col-auto ms-auto ">
          		<i class="fa fa-arrow-up-right-from-square me-1"></i>
          	</a>
			<a v-if="hasConfig" class="col-auto px-1" href="#" @click.prevent="openConfig"><i class="fa-solid fa-gear"></i></a>
			<a v-if="custom && editMode" class="col-auto px-1" href="#" @click.prevent="$emit('remove')">
				<i class="fa-solid fa-trash"></i>
			</a>
			<Transition>
				<div v-if="!custom && editMode" class="col-auto px-1 form-switch">
					<input class="form-check-input ms-0" type="checkbox" role="switch" id="flexSwitchCheckChecked" :checked="!hidden" @input="$emit('remove', hidden)">
				</div>
			</Transition>
		</div>
		<div v-if="ready" class="card-body overflow-hidden" style="padding: 0px;">
			<component :is="component" v-model:shared-data="sharedData" :config="arguments" :width="width" :height="height" @setConfig="setConfig" @change="changeConfigManually"></component>
		</div>
		<div v-else class="card-body overflow-hidden text-center d-flex flex-column justify-content-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
		<bs-modal v-if="hasConfig" ref="config" @hideBsModal="handleHideBsModal" @showBsModal="handleShowBsModal">
			<template v-slot:title>
				{{ widget ? 'Config for ' + widget.setup.name : '' }}
			</template>
			<template v-slot:default>
				<component v-if="ready && !isLoading" :is="component" v-model:shared-data="sharedData" :config="tmpConfig" @change="changeConfig" :configMode="true"></component>
				<div v-else class="text-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
			</template>
			<template v-if="!widget?.setup?.hideFooter" v-slot:footer>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" @click="changeConfig">Save changes</button>
			</template>
		</bs-modal>
		<height-transition>
			<div v-if="editMode && isResizeable && !isPinned " class="card-footer d-flex justify-content-end p-0">
				<template v-if="maxWidth < 2">
					<span drag-action="resize" class="col-auto px-1 cursor-ns-resize">
						<i  class="fa-solid fa-up-down pe-2"></i>
					</span>
				</template>
				<template v-else-if="maxHeight < 2">
					<span drag-action="resize" class="col-auto px-1 cursor-ew-resize">
						<i class="fa-solid fa-left-right pe-2"></i>
					</span>
				</template>
				<template v-else>
					<span drag-action="resize" class="col-auto px-1 cursor-nw-resize">
						<i  class="fa-solid fa-up-right-and-down-left-from-center mirror-x"></i>
					</span>
				</template>
			</div>
		</height-transition>
	</div>`,
};

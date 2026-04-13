import BsModal from "../Bootstrap/Modal.js";
import { useCachedWidgetLoader } from "../../composables/Dashboard/CachedWidgetLoader.js";
import HeightTransition from "../Tranistion/HeightTransition.js";

import { enableDragDropTouch } from "../../../../vendor/drag-drop-touch-js/dragdroptouch/dist/drag-drop-touch.esm.min.js";

if (!document.dragDropTouchActive) {
	enableDragDropTouch();
	document.dragDropTouchActive = true;
}

export default {
	name: 'Item',
	components: {
		BsModal,
		HeightTransition
	},
	data: () => ({
		component: "",
		arguments: null,
		widget: null,
		tmpConfig: {},
		isLoading: false,
		hasConfig: false,
		sharedData: null
	}),
	emits: [
		"change",
		"remove",
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
		"resizeLimits",
		"dragstate",
		"resizeOverlay",
		"source"
	],
	computed: {
		sourceInfoTooltip() {
			switch (this.source) {
				case null:
					return '';
				case 'general':
					return this.$p.t('dashboard', 'widgetFromGeneralSection');
				case 'custom':
					return this.$p.t('dashboard', 'widgetFromCustomSection');
				default:
					return this.$p.t('dashboard', 'widgetFromFunktionSection', [this.source]);
			}
		},
		isResizeableHorizontal() {
			if (this.resizeLimits.width === undefined)
				return true;

			if (Object.prototype.toString.call(this.resizeLimits.width) == "[object Number]")
				return false;

			if (this.resizeLimits.width.min === undefined) {
				if (this.resizeLimits.width.max === undefined)
					return true;
				return this.resizeLimits.width.max > 1;
			}

			if (this.resizeLimits.width.max === undefined)
				return true;

			return this.resizeLimits.width.max > this.resizeLimits.width.min;
		},
		isResizeableVertical() {
			if (this.resizeLimits.height === undefined)
				return true;

			if (Object.prototype.toString.call(this.resizeLimits.height) == "[object Number]")
				return false;

			if (this.resizeLimits.height.min === undefined) {
				if (this.resizeLimits.height.max === undefined)
					return true;
				return this.resizeLimits.height.max > 1;
			}

			if (this.resizeLimits.height.max === undefined)
				return true;

			return this.resizeLimits.height.max > this.resizeLimits.height.min;
		},
		isResizeable() {
			return this.isResizeableVertical || this.isResizeableHorizontal;
		},
		isPinned() {
			return this.place?.pinned ? true : false;
		},
		ready() {
			return this.component && this.arguments !== null;
		},
		visible: {
			get() {
				return !this.hidden;
			},
			set(value) {
				this.$emit('remove', this.hidden);
			}
		}
	},
	methods: {
		unpin() {
			// Unpinning is only possible in edit mode
			if (!this.editMode)
				return;
			let result = { item: this.item_data, x: this.item_data.x, y: this.item_data.y };
			this.$emit('unPinItem', [result]);
		},
		pinItem() {
			let result = { item: this.item_data, x: this.item_data.x, y: this.item_data.y };
			this.$emit('pinItem', [result]);
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
		}
	},
	watch: {
		config() {
			this.arguments = { ...this.widget?.arguments, ...this.config };
			this.tmpConfig = { ...this.arguments };
			this.$refs.config && this.$refs.config.hide();
			this.isLoading = false;
		},
	},
	setup() {
		const { actions } = useCachedWidgetLoader();
		return {
			loadWidget: actions.load
		};
	},
	async created() {
		this.widget = await this.loadWidget(this.id);
		let component = (await import(this.widget.setup.file)).default;
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
	<div
		v-else-if="!hidden || editMode"
		:id="widgetID"
		class="dashboard-item card overflow-hidden h-100 position-relative"
		:class="{'hiddenWidget':hidden, 'dashboard-item-overlay':resizeOverlay, [arguments?.className]:arguments && arguments.className}"
	>
		<div v-show="!dragstate" class="h-100 card border-0">
			<div v-if="widget" class="card-header d-flex ps-0 pe-2 align-items-center">
				<Transition>
					<span
						v-if="editMode && !isPinned"
						type="button"
						drag-action="move"
						class="col-auto mx-2 px-2 cursor-move"
						draggable="true"
						aria-label="move widget"
						v-tooltip="{showDelay:1000, value:'move widget'}"
					>
						<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
					</span>
				</Transition>
				<span class="col mx-2 px-2">{{ widget.setup.name }}</span>
				<div
					v-if="source"
					v-tooltip="{ class: 'w-100', value: sourceInfoTooltip }"
					class="col-auto me-2"
				>
					<i class="fa-solid fa-circle-info" aria-hidden="true"></i>
				</div>
				<template v-if="isPinned">
					<div type="button" role="button" v-if="editMode" pinned="true" @click="unpin" title="unpin item" aria-label="unpin item" class="pin cursor-pointer col-auto me-2">
						<i class="fa-solid fa-thumbtack " aria-hidden="true"></i>
					</div>
					<div v-else class="col-auto me-2">
						<i class="fa-solid fa-thumbtack "></i>
					</div>
				</template>
				<template v-else>
					<div type="button" role="button" v-if="editMode"  class="col-auto me-2 pin" @click="pinItem" aria-label="pin item" title="pin item">
						<i class="fa-solid fa-thumbtack" aria-hidden="true" style="color:lightgray;"></i>
					</div>
				</template>
				<a type="button" v-if="widget.setup.cis4link" :href="getWidgetC4Link(widget)" aria-label="widget link" v-tooltip="{showDelay:1000, value:'widget link'}" class="col-auto ms-auto">
					<i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>
				</a>
				<a type="button" v-if="hasConfig" class="col-auto px-1" href="#" @click.prevent="openConfig" aria-label="configure widget" v-tooltip="{showDelay:1000,value:'configure widget'}"><i class="fa-solid fa-gear" aria-hidden="true"></i></a>
				<a type="button" v-if="custom && editMode" class="col-auto px-1" aria-label="delete widget" v-tooltip="{showDelay:1000,value:'delete widget'}" href="#" @click.prevent="$emit('remove')">
					<i class="fa-solid fa-trash" aria-hidden="true"></i>
				</a>
				<Transition>
					<div v-if="!custom && editMode" class="col-auto px-1 form-switch">
						<input class="form-check-input ms-0" type="checkbox" role="switch" aria-label="toggle widget" id="flexSwitchCheckChecked" v-model="visible" :value="true">
					</div>
				</Transition>
			</div>
			<div v-if="ready" class="card-body overflow-hidden p-0">
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
				<div
					v-if="editMode && isResizeable && !isPinned"
					class="card-footer d-flex justify-content-end p-0"
				>
					<span
						v-if="!isResizeableHorizontal"
						type="button"
						drag-action="resize"
						class="col-auto px-1 cursor-ns-resize"
						draggable="true"
						aria-label="resize widget"
						v-tooltip="{showDelay:1000, value:'resize widget'}"
					>
						<i
							class="fa-solid fa-up-down pe-2"
							aria-hidden="true"
						></i>
					</span>
					<span
						v-else-if="!isResizeableVertical"
						type="button"
						drag-action="resize"
						class="col-auto px-1 cursor-ew-resize"
						draggable="true"
						aria-label="resize widget"
						v-tooltip="{showDelay:1000, value:'resize widget'}"
					>
						<i
							class="fa-solid fa-left-right pe-2"
							aria-hidden="true"
						></i>
					</span>
					<span
						v-else
						type="button"
						drag-action="resize"
						class="col-auto px-1 cursor-nw-resize"
						draggable="true"
						aria-label="resize widget"
						v-tooltip="{showDelay:1000, value:'resize widget'}"
					>
						<i
							class="fa-solid fa-up-right-and-down-left-from-center mirror-x"
							aria-hidden="true"
						></i>
					</span>
				</div>
			</height-transition>
		</div>
	</div>`,
};

import BsModal from "../Bootstrap/Modal.js";
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
		"config",
		"width",
		"height",
		"custom",
		"hidden",
		"editMode",
		"loading", // widget got added and is waiting for backend to save in db
		"item_data",
		"place",
		"widgetTemplate",
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
			if (this.widgetTemplate.setup.width === undefined)
				return true;

			if (Object.prototype.toString.call(this.widgetTemplate.setup.width) == "[object Number]")
				return false;

			if (this.widgetTemplate.setup.width.min === undefined) {
				if (this.widgetTemplate.setup.width.max === undefined)
					return true;
				return this.widgetTemplate.setup.width.max > 1;
			}

			if (this.widgetTemplate.setup.width.max === undefined)
				return true;

			return this.widgetTemplate.setup.width.max > this.widgetTemplate.setup.width.min;
		},
		isResizeableVertical() {
			if (this.widgetTemplate.setup.height === undefined)
				return true;

			if (Object.prototype.toString.call(this.widgetTemplate.setup.height) == "[object Number]")
				return false;

			if (this.widgetTemplate.setup.height.min === undefined) {
				if (this.widgetTemplate.setup.height.max === undefined)
					return true;
				return this.widgetTemplate.setup.height.max > 1;
			}

			if (this.widgetTemplate.setup.height.max === undefined)
				return true;

			return this.widgetTemplate.setup.height.max > this.widgetTemplate.setup.height.min;
		},
		isResizeable() {
			return this.isResizeableVertical || this.isResizeableHorizontal;
		},
		resizeClasses() {
			const classes = {
				icon: 'fa-up-right-and-down-left-from-center mirror-x',
				button: 'cursor-nw-resize'
			};
			if (!this.isResizeableHorizontal) {
				classes.icon = 'fa-up-down pe-2';
				classes.button = 'cursor-ns-resize';
			} else if (!this.isResizeableVertical) {
				classes.icon = 'fa-left-right pe-2';
				classes.button = 'cursor-ew-resize';
			}
			return classes;
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
			let result = { item: this.item_data, pinned: false };
			this.$emit('unPinItem', [result]);
		},
		pinItem() {
			let result = { item: this.item_data, pinned: true };
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
				if (this.widgetTemplate.arguments[k] == config[k]) {
					delete config[k];
				}
			}
			this.$emit("change", config);
		},
		async initializeComponent() {
			if (
				this.widgetTemplate
				&& this.widgetTemplate.setup
				&& this.widgetTemplate.widget_id
				&& this.widgetTemplate.arguments
			) {
				let component = (await import(this.widgetTemplate.setup.file)).default;
				this.$options.components["widget" + this.widgetTemplate.widget_id] = component;
				this.component = "widget" + this.widgetTemplate.widget_id;
				this.arguments = { ...this.widgetTemplate.arguments, ...this.config };
				this.tmpConfig = { ...this.arguments };
			}
		}
	},
	watch: {
		config() {
			this.arguments = { ...this.widgetTemplate?.arguments, ...this.config };
			this.tmpConfig = { ...this.arguments };
			this.$refs.config && this.$refs.config.hide();
			this.isLoading = false;
		},
		widgetTemplate() {
			this.initializeComponent();
		}
	},
	created() {
		this.initializeComponent();
	},
	template: /*html*/ `
	<article
		v-if="!hidden || editMode"
		class="dashboard-item card overflow-hidden h-100 position-relative"
		:class="{
			'hidden-widget': hidden,
			[arguments?.className]: arguments && arguments.className
		}"
	>
		<div v-if="loading" class="d-flex justify-content-center align-items-center h-100">
			<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
		</div>
		<template v-else>
			<header
				v-if="widgetTemplate"
				class="card-header d-flex ps-0 pe-2 align-items-center"
			>
				<!-- move handle -->
				<Transition>
					<span
						v-if="editMode && !isPinned"
						type="button"
						drag-action="move"
						class="col-auto mx-2 px-2 cursor-move"
						draggable="true"
						aria-hidden="true"
						aria-label="move widget"
						v-tooltip="{showDelay:1000, value:'move widget'}"
					>
						<i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
					</span>
				</Transition>
				<!-- TITLE -->
				<h4 class="col mb-0 mx-2 px-2 fs-6 lh-base">
					{{ widgetTemplate.setup.name }}
				</h4>
				<!-- source info -->
				<div
					v-if="source"
					v-tooltip="{ class: 'w-100', value: sourceInfoTooltip }"
					class="col-auto me-2"
				>
					<i class="fa-solid fa-circle-info" aria-hidden="true"></i>
				</div>
				<!-- pin button -->
				<template v-if="isPinned">
					<div
						v-if="editMode"
						type="button"
						role="button"
						class="pin cursor-pointer col-auto me-2"
						title="unpin item"
						aria-hidden="true"
						aria-label="unpin item"
						pinned="true"
						@click="unpin"
					>
						<i class="fa-solid fa-thumbtack" aria-hidden="true"></i>
					</div>
					<div v-else class="col-auto me-2" aria-hidden="true">
						<i class="fa-solid fa-thumbtack"></i>
					</div>
				</template>
				<template v-else>
					<div
						v-if="editMode"
						type="button"
						role="button"
						class="col-auto me-2 pin"
						title="pin item"
						aria-hidden="true"
						aria-label="pin item"
						@click="pinItem"
					>
						<i class="fa-solid fa-thumbtack" aria-hidden="true" style="color:lightgray;"></i>
					</div>
				</template>
				<!-- widget link -->
				<a
					v-if="widgetTemplate.setup.cis4link"
					:href="getWidgetC4Link(widgetTemplate)"
					class="col-auto ms-auto"
					aria-label="widget link"
					v-tooltip="{ showDelay: 1000, value: 'widget link' }"
				>
					<i class="fa fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>
				</a>
				<!-- config button -->
				<a
					v-if="hasConfig"
					href="#"
					class="col-auto px-1"
					aria-label="configure widget"
					v-tooltip="{ showDelay: 1000, value: 'configure widget' }"
					@click.prevent="openConfig"
				>
					<i class="fa-solid fa-gear" aria-hidden="true"></i>
				</a>
				<!-- delete button -->
				<a
					v-if="custom && editMode"
					href="#"
					class="col-auto px-1"
					aria-label="delete widget"
					v-tooltip="{ showDelay: 1000, value: 'delete widget' }"
					@click.prevent="$emit('remove')"
				>
					<i class="fa-solid fa-trash" aria-hidden="true"></i>
				</a>
				<!-- hide button -->
				<Transition>
					<div v-if="!custom && editMode" class="col-auto px-1 form-switch">
						<input
							type="checkbox"
							role="switch"
							v-model="visible"
							class="form-check-input ms-0"
							:value="true"
							aria-label="toggle widget"
						>
					</div>
				</Transition>
			</header>
			<div v-if="ready" class="card-body overflow-hidden p-0">
				<component
					:is="component"
					v-model:shared-data="sharedData"
					:config="arguments"
					:width="width"
					:height="height"
					@setConfig="setConfig"
					@change="changeConfigManually"
				></component>
			</div>
			<div
				v-else
				class="card-body overflow-hidden text-center d-flex flex-column justify-content-center"
			>
				<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
			</div>
			<bs-modal
				v-if="hasConfig"
				ref="config"
				@hideBsModal="handleHideBsModal"
				@showBsModal="handleShowBsModal"
			>
				<template v-slot:title>
					{{ widgetTemplate ? 'Config for ' + widgetTemplate.setup.name : '' }}
				</template>
				<template v-slot:default>
					<component
						:is="component"
						v-if="ready && !isLoading"
						v-model:shared-data="sharedData"
						:config="tmpConfig"
						@change="changeConfig"
						:configMode="true"
					></component>
					<div v-else class="text-center">
						<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
					</div>
				</template>
				<template v-if="!widgetTemplate?.setup?.hideFooter" v-slot:footer>
					<button
						type="button"
						class="btn btn-secondary"
						data-bs-dismiss="modal"
					>Close</button>
					<button
						type="button"
						class="btn btn-primary"
						@click="changeConfig"
					>Save changes</button>
				</template>
			</bs-modal>
			<height-transition>
				<footer
					v-if="editMode && isResizeable && !isPinned"
					class="card-footer d-flex justify-content-end p-0"
				>
					<span
						type="button"
						drag-action="resize"
						class="col-auto px-1"
						:class="resizeClasses.button"
						draggable="true"
						aria-hidden="true"
						aria-label="resize widget"
						v-tooltip="{ showDelay: 1000, value: 'resize widget' }"
					>
						<i
							class="fa-solid"
							:class="resizeClasses.icon"
							aria-hidden="true"
						></i>
					</span>
				</footer>
			</height-transition>
		</template>
	</article>`,
};

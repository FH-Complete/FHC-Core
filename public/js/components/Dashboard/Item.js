import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";

export default {
	components: {},
	data: () => ({
		component: '',
		arguments: null,
		configModal: null,
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
		"editMode"
	],
	computed: {
		isResizeable() {
			if (!this.widget)
				return false;
			return this.widget.size.width.max || this.widget.size.height.max;
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
			this.configModal.show();
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
			this.configModal.hide();
			this.isLoading = false;
		}
	},
	async created() {
		this.widget = await CachedWidgetLoader.loadWidget(this.id);
		let component = (await import('../' + this.widget.file)).default;
		this.$options.components['widget' + this.widget.id] = component;
		this.component = 'widget' + this.widget.id;
		this.arguments = {...this.widget.arguments, ...this.config};
		this.tmpConfig = {...this.arguments};
	},
	mounted() {
		this.configModal = new bootstrap.Modal(this.$refs.config);
	},
	template: `<div v-if="!hidden || editMode" :class="'dashboard-item card overflow-hidden ' + (arguments ? arguments.className : '')" @mousedown="mouseDown($event)" @dragstart="startDrag($event)" :draggable="!!editMode">
		<div v-if="editMode && widget" class="card-header d-flex">
			<span ref="dragHandle" class="col-auto pe-3"><i class="fa-solid fa-grip-vertical"></i></span>
			<span class="col">{{ widget.name }}</span>
			<a v-if="hasConfig" class="col-auto ps-1" href="#" @click.prevent="openConfig"><i class="fa-solid fa-gear"></i></a>
			<a v-if="custom" class="col-auto ps-1" href="#" @click.prevent="$emit('remove')">
				<i class="fa-solid fa-trash"></i>
			</a>
			<div v-else class="col-auto ps-1 form-switch">
				<input class="form-check-input ms-0" type="checkbox" role="switch" id="flexSwitchCheckChecked" :checked="!hidden" @input="$emit('remove', hidden)">
			</div>
		</div>
		<div v-if="ready" class="card-body overflow-hidden">
			<component :is="component" :config="arguments" :width="width" :height="height" @setConfig="setConfig" @change="changeConfigManually"></component>
		</div>
		<div v-else class="card-body overflow-hidden text-center d-flex flex-column justify-content-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
		<div ref="config" class="modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 v-if="widget" class="modal-title">Config for {{ widget.name }}</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<component v-if="ready && !isLoading" :is="component" :config="tmpConfig" @change="changeConfig" :configMode="true"></component>
						<div v-else class="text-center"><i class="fa-solid fa-spinner fa-pulse fa-3x"></i></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" @click="changeConfig">Save changes</button>
					</div>
				</div>
			</div>
		</div>
		<div v-if="editMode && isResizeable" class="card-footer d-flex justify-content-end p-0">
			<span ref="resizeHandle" class="col-auto ps-1" @dragstart.prevent="$emit('resize')"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></span>
		</div>
	</div>`
}
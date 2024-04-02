import {CoreRESTClient} from '../RESTClient.js';
import accessibility from "../directives/accessibility.js";

export default {
	directives: {
		accessibility
	},
	emits: [
		'update:modelValue',
		'change',
		'changed'
	],
	props: {
		config: {
			type: [String, Object],
			required: true
		},
		default: String,
		modelValue: [String, Number, Boolean, Array, Object, Date, Function, Symbol],
		vertical: Boolean,
		border: Boolean
	},
	data() {
		return {
			current: null,
			tabs: {}
		}
	},
	computed: {
		currentTab() {
			if (this.tabs[this.current])
				return this.tabs[this.current];
			
			return { component: 'div' };
		},
		value: {
			get() {
				return this.modelValue;
			},
			set(v) {
				this.$emit('update:modelValue', v);
			}
		}
	},
	watch: {
		config(n) {
			this.initConfig(n);
		}
	},
	methods: {
		change(key) {
			this.$emit("change", key)
			this.current = key;
			this.$nextTick(() => this.$emit("changed", key));
		},
		initConfig(config) {
			if (!config)
				return;
			if (typeof config === 'string' || config instanceof String)
				return CoreRESTClient.get(config)
					.then(result => CoreRESTClient.getData(result.data))
					.then(this.initConfig)
					.catch(this.$fhcAlert.handleSystemError);

			const tabs = {};

			if (Array.isArray(config)) {
				config.forEach((item, key) => {
					if (!item.component)
						return console.error('Component missing for ' + key);

					tabs[key] = {
						component: Vue.markRaw(Vue.defineAsyncComponent(() => import(item.component))),
						title: Vue.computed(() => item.title || key),
						config: item.config,
						key
					}
				});
			} else {
				Object.entries(config).forEach(([key, item]) => {
					if (!item.component)
						return console.error('Component missing for ' + key);

					tabs[key] = {
						component: Vue.markRaw(Vue.defineAsyncComponent(() => import(item.component))),
						title: Vue.computed(() => item.title || key),
						config: item.config,
						key
					}
				});
			}

			if (this.current === null || !tabs[this.current]) {
				if (tabs[this.default])
					this.current = this.default;
				else
					this.current = Object.keys(tabs)[0];
			}
			this.tabs = tabs;
		}
	},
	created() {
		this.initConfig(this.config);
	},
	template: `
	<div class="fhc-tabs d-flex" :class="vertical ? 'align-items-stretch gap-3' : (border ? 'flex-column' : 'flex-column gap-3')" v-if="Object.keys(tabs).length">
		<div class="nav" :class="vertical ? 'nav-pills flex-column' : 'nav-tabs'">
			<div
				v-for="tab in tabs"
				:key="tab.key"
				class="nav-item nav-link"
				:class="{active: tab.key == current}"
				@click="change(tab.key)"
				:aria-current="tab.key == current ? 'page' : ''"
				v-accessibility:tab.[vertical]
				>
				{{tab.title}}
			</div>
		</div>
		<div :style="vertical ? '' : 'flex: 1 1 0%; height: 0%'" class="overflow-auto flex-grow-1" :class="vertical || !border ? '' : 'p-3 border-bottom border-start border-end'">
			<keep-alive>
				<component ref="current" :is="currentTab.component" v-model="value" :config="currentTab.config"></component>
			</keep-alive>
		</div>
	</div>`
};

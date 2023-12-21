import {CoreRESTClient} from '../RESTClient.js';
import accessibility from "../directives/accessibility.js";

export default {
	directives: {
		accessibility
	},
	emits: [
		'update:modelValue'
	],
	props: {
		configUrl: String,
		default: String,
		modelValue: [String, Number, Boolean, Array, Object, Date, Function, Symbol]
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
	created() {
		CoreRESTClient
			.get(this.configUrl)
			.then(result => CoreRESTClient.getData(result.data))
			.then(result => {
				const tabs = {};
				// TODO(chris): check if result is array
				Object.entries(result).forEach(([key, config]) => {
					if (!config.component)
						return console.error('Component missing for ' + key);

					tabs[key] = {
						component: Vue.markRaw(Vue.defineAsyncComponent(() => import(config.component))),
						title: config.title || key,
						config: config.config,
						key
					}
				});
				if (tabs[this.default])
					this.current = this.default;
				else
					this.current = Object.keys(tabs)[0];
				this.tabs = tabs;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="fhc-tabs d-flex flex-column">
		<div class="nav nav-tabs">
			<div
				v-for="tab in tabs"
				:key="tab.key"
				class="nav-item nav-link"
				:class="{active: tab.key == current}"
				@click="current=tab.key"
				:aria-current="tab.key == current ? 'page' : ''"
				v-accessibility:tab
				>
				{{tab.title}}
			</div>
		</div>
		<div style="flex: 1 1 0%; height: 0%" class="border-bottom border-start border-end overflow-auto p-3">
			<keep-alive>
				<component :is="currentTab.component" v-model="value" :config="currentTab.config"></component>
			</keep-alive>
		</div>
	</div>`
};
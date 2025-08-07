import accessibility from "../directives/accessibility.js";

import TabView from '../../../index.ci.php/public/js/components/primevue/tabview/tabview.esm.min.js';
import TabPanel from '../../../index.ci.php/public/js/components/primevue/tabpanel/tabpanel.esm.min.js';

export default {
	components: {
		tabview: TabView,
		tabpanel: TabPanel
	},
	directives: {
		accessibility
	},
	emits: [
		'update:modelValue',
		'change',
		'changed',
	],
	props: {
		config: {
			type: [String, Array, Object, Promise],
			required: true
		},
		default: String,
		modelValue: [String, Number, Boolean, Array, Object, Date, Function, Symbol],
		vertical: Boolean,
		border: Boolean,
		useprimevue: {
			type: Boolean,
			default: false
		}
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
		},
		calcActiveIndex: function() {
			let keys = Object.keys(this.tabs);
			let index = keys.indexOf(this.default);
			if( index === -1 ) {
				return 0;
			} else {
				return index;
			}
		}
	},
	watch: {
		config(n) {
			this.initConfig(n);
		}
	},
	methods: {
		handleTabClick: function(index) {
			let keys = Object.keys(this.tabs);
			this.change(keys[index]);
		},
		change(key) {
			this.$emit("change", key)
			this.current = key;
			this.$nextTick(() => this.$emit("changed", key));
		},
		initConfig(config) {
			if (!config)
				return;
			if (config instanceof Promise)
				return config
					.then(result => result.data)
					.then(this.initConfig)
					.catch(this.$fhcAlert.handleSystemError);
			if (typeof config === 'string' || config instanceof String)
				return this.$api
					.get(config)
					.then(result => result.data)
					.then(this.initConfig)
					.catch(this.$fhcAlert.handleSystemError);

			const tabs = {};

			function _addToTabs(key, item) {
				if (!item.component)
					return console.error('Component missing for ' + key);

				//making it reactive for showing headerSuffix
				const value = Vue.reactive({
					suffix: '',
					showSuffix: item.showSuffix || false
				});

				tabs[key] = {
					component: Vue.markRaw(Vue.defineAsyncComponent(() => import(item.component))),
					title: Vue.computed(() => item.title || key),
					config: item.config,
					key,
					value
				};
			}

			if (Array.isArray(config))
				config.forEach((item, key) => _addToTabs(key, item));
			else
				Object.entries(config).forEach(([key, item]) => _addToTabs(key, item));

			if (this.current === null || !tabs[this.current]) {
				if (tabs[this.default])
					this.current = this.default;
				else
					this.current = Object.keys(tabs)[0];
			}
			this.tabs = tabs;
		},
		updateSuffix(event) {
			if (this.currentTab?.value) {
				this.currentTab.value.suffix = event;
			}
		}
	},
	created() {
		this.initConfig(this.config);
	},
	template: `
	<template v-if="useprimevue">

		<tabview 
			:scrollable="true"
			:lazy="true"
			:activeIndex="calcActiveIndex"
			@tab-click="handleTabClick"
		>
			<tabpanel
				v-for="tab in tabs"
				:key="tab.key"
				:header="tab.title + (tab.value.showSuffix && tab.value.suffix ? tab.value.suffix : '')"
			>
				<keep-alive>
					<component
						:is="tab.component"
						v-model="value"
						:config="tab.config"
						@update:suffix="updateSuffix($event)"
						></component>
				</keep-alive>
			</tabpanel>
		</tabview>

	</template>
	<template v-else="">

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
				{{tab.title}} <span v-if="tab.value.showSuffix && tab.value.suffix"> {{ tab.value.suffix }}</span>
			</div>
		</div>
		<div :style="vertical ? '' : 'flex: 1 1 0%; height: 0%'" class="overflow-auto flex-grow-1" :class="vertical || !border ? '' : 'p-3 border-bottom border-start border-end'">
			<keep-alive>
				<component
					ref="current"
					:is="currentTab.component"
					v-model="value"
					:config="currentTab.config"
					@update:suffix="updateSuffix($event)"
					></component>
			</keep-alive>
		</div>
	</div>

	</template>`
};


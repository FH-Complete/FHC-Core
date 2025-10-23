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
		'changed'
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
			tabs: {},
			count: null
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
		handleTabClick: function (e) {
			let keys = Object.keys(this.tabs);
			this.change(keys[e.index]);
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
					value,
					suffixhelper: item.suffixhelper ?? null
				};
			}

			if (Array.isArray(config)) {
				config.forEach((item, key) => _addToTabs(key, item));
			}
			else {
				Object.entries(config).forEach(([key, item]) => _addToTabs(key, item));
			}

			if (this.current === null || !tabs[this.current]) {
				if (tabs[this.default])
					this.current = this.default;
				else
					this.current = Object.keys(tabs)[0];
			}
			this.tabs = tabs;
		},
		updateSuffix() {
			this.getTabSuffix(this.currentTab);
		},
		removeInvalidCountTabs(){
			if(this.modelValue.length)
			{
				let countIst = this.modelValue.length;
				const tabsToDelete = [];

				Object.entries(this.config).forEach(([key, item]) => {

					const target = item?.config ? item : item?.value || item;

					// check config for validCountMulti
					if (target.config?.validCountMulti !== undefined) {
						let tab;
						let countSoll;
						tab = key;
						countSoll = target.config.validCountMulti;

						//check if tab is existing
						if (countSoll !== undefined && countSoll == countIst) {
							//add tab if it was removed before
							if (tab in this.tabs == false) {
								const value = Vue.reactive({
									suffix: '',
									showSuffix: item.showSuffix || false
								});

								this.tabs[tab] = {
									component: Vue.markRaw(Vue.defineAsyncComponent(() => import(item.component))),
									title: Vue.computed(() => item.title || tab),
									config: item.config,
									tab,
									value,
									suffixhelper: item.suffixhelper ?? null
								};
							}
						}

						//add to toDeleteArray if count is not allowed
						if (countSoll !== undefined && countSoll !== countIst) {
							tabsToDelete.push(tab);
						}
					}
				});

				// Delete all tabs with count not allowed
				tabsToDelete.forEach(k => {
					delete this.tabs[k];
				});

			}
		},
		async getTabSuffix(tab) {
			if (!tab.value.showSuffix) {
				return;
			}

			if (tab.suffixhelper !== null) {
				const suffixhelper = await import(tab.suffixhelper);
				const suffix = await suffixhelper.getSuffix(this.$api, this.modelValue);
				tab.value.suffix = suffix;
			} else {
				tab.value.suffix = '';
			}
		},
		getTabSuffixes() {
			Object.entries(this.tabs).forEach(([key, item]) => this.getTabSuffix(item));
		}
	},
	created() {
		this.initConfig(this.config);
	},
	mounted() {
		this.getTabSuffixes();
		this.removeInvalidCountTabs();
	},
	updated() {
		this.getTabSuffixes();
		this.removeInvalidCountTabs();
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
				:header="tab.title + ((tab.value.showSuffix && tab.value.suffix !== '') ? ' ' + tab.value.suffix : '')"
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


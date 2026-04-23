import BsConfirm from "../Bootstrap/Confirm.js";
import DropGrid from '../Drop/Grid.js'
import DashboardItem from "./Item.js";
import WidgetIcon from "./Widget/WidgetIcon.js"

import dragClick from '../../directives/dragClick.js';

import ObjectUtils from "../../helpers/ObjectUtils.js";

export default {
	name: 'Section',
	components: {
		DropGrid,
		DashboardItem,
		WidgetIcon,
	},
	directives: {
		dragClick
	},
	inject: {
		widgetsSetup: {
			type: Array,
			default: [],
		},
		adminMode: {
			type: Boolean,
			default: false
		},
		editMode: {
			type: Boolean,
			default: false
		}
	},
	props: [
		"name",
		"widgets"
	],
	emits: [
		"widgetAdd",
		"widgetUpdate",
		"widgetRemove"
	],
	data() {
		return {
			configOpened: false,
			gridWidth: 1,
			gridHeight: null,
			additionalRow: false
		};
	},
	provide() {
		return {
			editModeIsActive: Vue.computed(() =>
				this.editModeIsActive
			),	
			sectionName: Vue.computed(() => this.name),	
		};
	},
	computed: {
		sectionNameTranslation() {
			switch (this.name) {
				case "general": 
					return this.$p.t('dashboard', this.name); 
				case "custom":
					return this.$p.t('dashboard', this.name);
				default:
					return this.name;
			}
		},
		showSectionInformation() {
			switch (this.name) {
				case "general": 
					return this.$p.t('dashboard', 'dashboardGeneralSectionDescription'); 
				case "custom":
					return this.$p.t('dashboard', 'dashboardCustomSectionDescription');
				default:
					return this.$p.t('dashboard', 'dashboardSectionDescription', [this.name]);
			}
		},
		indexedWidgetsTemplates() {
			if (!this.widgetsSetup)
				return {};
			return this.widgetsSetup.reduce((acc, setup) => {
				acc[setup.widget_id] = setup;
				return acc;
			}, {});
		},
		editModeIsActive() {
			return (this.editMode || this.adminMode) && !this.configOpened	
		},
		items() {
			// reuses the nearest placement of the widget from another viewport 
			/* const computeNearestPlace = (item, gridWidth) =>{
				let place;
				if (Object.keys(item.place).length > 0) {
					const nearestIndex = Object.keys(item.place)
											   .sort((a, b) => Math.abs(a - gridWidth) - Math.abs(b - gridWidth))
											   .shift();
					place = item.place[nearestIndex];
				}
				else{
					place = { x: 0, y: 0, w: 1, h: 1 };
				}
				return place;
			} */
			
			let placedItems = this.widgets.map(item => {
				if(!item?.widgetid && item?.id){
					item.widgetid = item.id;
				}

				let weight = 5;
				if (!item.source)
					weight = 6;
				else if (item.source == 'general')
					weight = 4;

				let placement = item.place[this.gridWidth];
				if (!placement) {
					weight -= 3;
					placement = {};
				}

				return { ...item, ...placement, weight };
			});

			if (this.editModeIsActive)
				return placedItems;
			return placedItems.filter(item => !item.hidden);
		}
	},
	watch: {
		items() {
			this.additionalRow = false;
		}
	},
	methods: {
		handleConfigOpened() {
			this.configOpened = true
		},
		handleConfigClosed() {
			this.configOpened = false
		},
		removeWidget(item, revert) {
			if (item.custom) {
				BsConfirm.popup(this.$p.t('dashboard', 'alert_deleteWidget')).then(() => this.$emit('widgetRemove', item.id, this.name));
			} else {
				let update = {};
				update[item.id] = { hidden: !revert };
				
				if (!revert) {
					// NOTE(chris): move to last line
					update[item.id].place = [];
					let y = this.gridHeight;
					if (this.additionalRow)
						y--;
					update[item.id].place[this.gridWidth] = { x: 0, y };
				}
				
				this.updatePreset(update);
			}
		},
		saveConfig(config, item) {
			let payload = {};
			payload[item.id] = { config };
			this.updatePreset(payload);
		},
		updatePositions(updated) {
			let result = {};
			updated.forEach(update => {
				let item = structuredClone(ObjectUtils.deepToRaw(update.item));

				if (!item.placeholder) {
					if (!item.place[this.gridWidth])
						item.place[this.gridWidth] = { x: 0, y: 0, w: 1, h: 1 };
					
					delete item.x;
					delete item.y;
					delete item.w;
					delete item.h;
					delete item.pinned;
					delete item.weight;

					if (update.x !== undefined)
						item.place[this.gridWidth].x = update.x;
					if (update.y !== undefined)
						item.place[this.gridWidth].y = update.y;
					if (update.w !== undefined)
						item.place[this.gridWidth].w = update.w;
					if (update.h !== undefined)
						item.place[this.gridWidth].h = update.h;
					if (update.pinned !== undefined)
						item.place[this.gridWidth].pinned = update.pinned;

					result[item.id] = item;
				}
			});
			this.updatePreset(result);
		},
		updatePreset(update) {
			this.$emit('widgetUpdate', update, this.name);
		}
	},
	mounted() {
		let self = this;
		let cont = self.$refs.container;
		self.gridWidth = parseInt(window.getComputedStyle(cont).getPropertyValue('--fhc-dashboard-grid-size'));
		
		window.addEventListener('resize', () => {
			self.gridWidth = parseInt(window.getComputedStyle(cont).getPropertyValue('--fhc-dashboard-grid-size'));
		});
	},
	template: /* html */`
	<section
		class="dashboard-section position-relative pb-3 mb-3 border-bottom"
		ref="container"
		:class="{ 'edit-active': editModeIsActive }"
	>
		<h3 v-if="adminMode" class="h4">
			<i v-tooltip="showSectionInformation" class="fa-solid fa-circle-info section-info"></i>
			{{ sectionNameTranslation }}:
		</h3>
		<button
			v-tooltip="$p.t('dashboard/addLine')"
			v-if="!additionalRow && editModeIsActive"
			class="btn btn-outline-secondary rounded-circle newGridRow d-flex justify-content-center align-items-center"
			@click="additionalRow=true"
			v-drag-click="() => additionalRow=true"
		>+</button>
		<drop-grid
			v-model:cols="gridWidth"
			:additional-row="additionalRow"
			:items="items"
			:items-setup="indexedWidgetsTemplates"
			:active="editModeIsActive"
			@rearrange-items="updatePositions"
			@grid-height="gridHeight=$event"
		>
			<template #default="item">
				<div
					v-if="item.placeholder"
					class="empty-tile-hover"
					@click="$emit('widgetAdd', { widget: 1, config: {}, place: {[gridWidth]: {x:item.x,y:item.y,w:1,h:1}}, custom: 1 }, name)"
				></div>
				<dashboard-item 
					v-else
					:id="item.widget"
					:width="item.w"
					:height="item.h"
					:item_data="{config:item.config, custom:item.custom, h:item.h, w:item.w,id:item.id,place:item.place,widget:item.widget,widgetid:item.widgetid,x:item.x,y:item.y}"
					:loading="item.loading"
					:config="item.config"
					:custom="item.custom"
					:hidden="item.hidden"
					:editMode="editModeIsActive"
					:place="item.place[gridWidth]"
					:widget-template="indexedWidgetsTemplates[item.widget]"
					:source="adminMode ? null : item.source || 'custom'"
					@change="saveConfig($event, item)"
					@remove="removeWidget(item, $event)"
					@config-opened="handleConfigOpened"
					@config-closed="handleConfigClosed"
					@pin-item="updatePositions"
					@un-pin-item="updatePositions"
				></dashboard-item>
			</template>
		</drop-grid>
	</section>`
}

/*
OLD VERSION - ON HOVER
<template #empty-tile-hover="{x,y}">
	<div class="empty-tile-hover" @click="$emit('widgetAdd', name, { widget: 1, config: {}, place: {[gridWidth]: {x,y,w:1,h:1}}, custom: 1 })"></div>
</template>
*/
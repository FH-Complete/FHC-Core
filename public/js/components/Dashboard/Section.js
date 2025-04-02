import BsConfirm from "../Bootstrap/Confirm.js";
import DropGrid from '../Drop/Grid.js'
import DashboardItem from "./Item.js";
import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";
import WidgetIcon from "./Widget/WidgetIcon.js"

export default {
	name: 'Section',
	components: {
		DropGrid,
		DashboardItem,
		WidgetIcon,
	},
	inject: {
		widgetsSetup:{
			type: Object,
			default: {},
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
		}
	},
	provide() {
		return {
			editModeIsActive: Vue.computed(() =>
				this.editModeIsActive
			),	
			sectionName: Vue.computed(() => this.name),	
		}
	},
	computed: {
		editModeIsActive() {
			return (this.editMode || this.adminMode) && !this.configOpened	
		},
		getSectionStyle() {
			return 'margin-bottom: 8px;';
		},
		items() {

			const computeNearestPlace = (item, gridWidth) =>{
				let place;
				if (Object.keys(item.place).length > 0) {
					const nearestIndex = Object.keys(item.place)
											   .sort((a, b) => Math.abs(a - gridWidth) - Math.abs(b - gridWidth))
											   .pop();
					place = item.place[nearestIndex];
				}
				else{
					place = { x: 0, y: 0, w: 1, h: 1 };
				}
				return place;
			}
			
			return this.widgets.map(item => {
				return { ...item, ...(item.place[this.gridWidth] || computeNearestPlace(item, this.gridWidth))};
			});
		},
		items_hashmap() {
			let items = {};
			this.items.forEach(item => {
				items[`x${item.x}y${item.y}`] = item;
			});
			return items
		},
		items_placeholders(){
			let placeholders = [];
			let col_max = this.gridWidth;
			let rows_max = this.gridHeight;

			// occupied hashmap to keep track of the occupied cells
			let occupied = {};

			for (let y = 0; y < rows_max; y++) {
				for (let x = 0; x < col_max; x++) {
					// skip current position if it was registered as occupied
					if (Object.keys(occupied).length && occupied[`x${x}y${y}`]) {
						continue;
					}
					let current_item = this.items_hashmap[`x${x}y${y}`];
					if (current_item) {
						//calculate the occupied cells from the width and the height from the items 
						let width = current_item.w;
						let height = current_item.h;
						let max_x = x + width - 1;
						let max_y = y + height - 1;
						if(x != max_x || y != max_y){
							for (let occupied_y = y; occupied_y <= max_y; occupied_y++) {
								for (let occupied_x = x; occupied_x <= max_x; occupied_x++) {
									if (occupied_x != x || occupied_y != y) {
										occupied[`x${occupied_x}y${occupied_y}`]=true;
									}
								}
							}
						}
					}
					else {
						placeholders.push({ x: x, y: y, w: 1, h: 1, placeholder: true, 
							data: { id: 'placeholder_' + String(placeholders.length).padStart(4, "0") } });
					}
				}
			}
			return placeholders;
		},
	},
	methods: {
		handleConfigOpened() {
			this.configOpened = true
		},
		handleConfigClosed() {
			this.configOpened = false
		},
		checkResizeLimit(item, w, h) {
			// NOTE(chris): widgets needs to be loaded for this to work
			let widget = CachedWidgetLoader.getWidget(item.widget);
			if (widget) {
				let minmaxW = widget.setup.width;
				if (minmaxW.max)
					minmaxW.min = minmaxW.min || 1;
				else
					minmaxW = {min:minmaxW,max:minmaxW};
				if (w < minmaxW.min)
					w = minmaxW.min;
				if (w > minmaxW.max)
					w = minmaxW.max;

				let minmaxH = widget.setup.height;
				if (minmaxH.max)
					minmaxH.min = minmaxH.min || 1;
				else
					minmaxH = {min:minmaxH,max:minmaxH};
				if (h < minmaxH.min)
					h = minmaxH.min;
				if (h > minmaxH.max)
					h = minmaxH.max;
			}
			return [w, h];
		},
		removeWidget(item, revert) {
			if (item.custom) {
				BsConfirm.popup('Are you sure you want to delete this widget?').then(() => this.$emit('widgetRemove', this.name, item.id));
			} else {
				let update = {};
				update[item.id] = { hidden: !revert };
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
				
				let item = {...update.item};
				if (!item.placeholder) {
				if (!item.place[this.gridWidth])
					item.place[this.gridWidth] = {x: 0, y: 0, w: 1, h: 1};
				delete item.x;
				delete item.y;
				delete item.w;
				delete item.h;
				if (update.x !== undefined)
					item.place[this.gridWidth].x = update.x;
				if (update.y !== undefined)
					item.place[this.gridWidth].y = update.y;
				if (update.w !== undefined)
					item.place[this.gridWidth].w = update.w;
				if (update.h !== undefined)
					item.place[this.gridWidth].h = update.h;

				result[item.id] = item;
				}
			});
			
			this.updatePreset(result);
		},
		updatePreset(update) {
			let payload = {};
			payload[this.name] = update;
			this.$emit('widgetUpdate', this.name, payload);
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
	template: `
	<div class="dashboard-section position-relative" ref="container" :style="getSectionStyle">
		<template v-for="setup in widgetsSetup">
			<div class="dragged-widget-icon" :id="'widget-'+name+'-'+setup.widget_id" >
				<widget-icon v-if="widgetsSetup" :widget="setup"></widget-icon>
			</div>
		</template>
		<drop-grid v-model:cols="gridWidth" :items="items" :placeholders="items_placeholders" :active="editModeIsActive" :resize-limit="checkResizeLimit" :margin-for-extra-row=".01" @rearrange-items="updatePositions" @gridHeight="gridHeight=$event" >
			<template #default="item">
				
				<dashboard-item 
					v-if="!item.placeholder"
					:id="item.widget"
					:widgetID="item.id"
					:width="item.w"
					:height="item.h"
					:loading="item.loading"
					:config="item.config"
					:custom="item.custom"
					:hidden="item.hidden"
					:editMode="editMode"
					@change="saveConfig($event, item)"
					@remove="removeWidget(item, $event)"
					@config-opened="handleConfigOpened"
					@config-closed="handleConfigClosed">
				</dashboard-item>
				<div v-else class="empty-tile-hover" @click="$emit('widgetAdd', name, { widget: 1, config: {}, place: {[gridWidth]: {x:item.x,y:item.y,w:1,h:1}}, custom: 1 })"></div>
				
			</template>
			
		</drop-grid>
	</div>`
}

/*
OLD VERSION - ON HOVER
<template #empty-tile-hover="{x,y}">
	<div class="empty-tile-hover" @click="$emit('widgetAdd', name, { widget: 1, config: {}, place: {[gridWidth]: {x,y,w:1,h:1}}, custom: 1 })"></div>
</template>
*/
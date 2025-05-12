import BsConfirm from "../Bootstrap/Confirm.js";
import SectionModal from "../Bootstrap/Alert.js";
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
			draggedItem:null,
			additionalRow:false,
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
		computedWidgetsSetup(){
			if(!this.widgetsSetup) return {};
			return this.widgetsSetup.reduce((acc, setup)=>{
				acc[setup.widget_id] = setup.setup;
				return acc;
			},{})
		},
		editModeIsActive() {
			return (this.editMode || this.adminMode) && !this.configOpened	
		},
		getSectionStyle() {
			return 'margin-bottom: 8px;';
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
				return { ...item, reorder: false, ...(item.place[this.gridWidth] || { reorder: true, ...{ x: 0, y: 0, w: 1, h: 1 } })};
			});
			return placedItems;
			
		},
		
	},
	methods: {
		sectionNameTranslation(){
			switch(this.name){
				case "general": 
					return this.$p.t('dashboard',this.name); 
					break;
				case "custom":
					return this.$p.t('dashboard',this.name);
					break;
				default:
					return this.name;
					break;
			}
		},
		showSectionInformation(){
			if (this.name == "general"){
				SectionModal.popup(this.$p.t('dashboard', 'dashboardGeneralSectionDescription')); 
			}
			else if(this.name == "custom"){
				SectionModal.popup(this.$p.t('dashboard', 'dashboardCustomSectionDescription'));
			}
			else{
				SectionModal.popup(this.$p.t('dashboard', 'dashboardSectionDescription', [this.name]));
			}
		},
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
		updatePositions(updated, pinned=false) {
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
				delete item.place[this.gridWidth].pinned;
				if (update.x !== undefined)
					item.place[this.gridWidth].x = update.x;
				if (update.y !== undefined)
					item.place[this.gridWidth].y = update.y;
				if (update.w !== undefined)
					item.place[this.gridWidth].w = update.w;
				if (update.h !== undefined)
					item.place[this.gridWidth].h = update.h;
				if (pinned){
					item.place[this.gridWidth].pinned = true;
				}

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
	<h4 v-if="editModeIsActive" class=" mb-2">
		<i @click="showSectionInformation(name)" class="fa-solid fa-circle-info section-info" ></i>
		{{sectionNameTranslation()}}:
	</h4>
	<div class="dashboard-section position-relative pb-3 border-bottom" ref="container" :style="getSectionStyle">
		<button v-if="!additionalRow && editModeIsActive" @click="additionalRow=true" class="btn btn-outline-secondary rounded-circle newGridRow d-flex justify-content-center align-items-center">+</button>
		<drop-grid v-model:cols="gridWidth" v-model:additionalRow="additionalRow" :items="items" :itemsSetup="computedWidgetsSetup" :active="editModeIsActive" :resize-limit="checkResizeLimit" :margin-for-extra-row=".01" @draggedItem="draggedItem=$event" @rearrange-items="updatePositions" @gridHeight="gridHeight=$event" >
			<template #default="item">
				<div v-if="item.placeholder" class="empty-tile-hover" @click="$emit('widgetAdd', name, { widget: 1, config: {}, place: {[gridWidth]: {x:item.x,y:item.y,w:1,h:1}}, custom: 1 })"></div>
				<dashboard-item 
					v-else
					:id="item.widget"
					:dragstate="item.blank || (item.widgetid && item.widgetid == draggedItem?.data.widgetid)"
					:resizeOverlay="item.resizeOverlay"
					:widgetID="item.id"
					:width="item.w"
					:height="item.h"
					:item_data="{config:item.config, custom:item.custom, h:item.h, w:item.w,id:item.id,reorder:item.reorder,place:item.place,widget:item.widget,widgetid:item.widgetid,x:item.x,y:item.y}"
					:loading="item.loading"
					:config="item.config"
					:custom="item.custom"
					:hidden="item.hidden"
					:editMode="editModeIsActive"
					:place="item.place[gridWidth]"
					:setup="computedWidgetsSetup[item.widget]"
					@change="saveConfig($event, item)"
					@remove="removeWidget(item, $event)"
					@config-opened="handleConfigOpened"
					@config-closed="handleConfigClosed"
					@pinItem="updatePositions($event,true)"
					@unPinItem="updatePositions">
				</dashboard-item>
				
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
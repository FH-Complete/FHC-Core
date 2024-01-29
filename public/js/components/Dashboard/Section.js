import BsConfirm from "../Bootstrap/Confirm.js";
import DropGrid from '../Drop/Grid.js'
import DashboardItem from "./Item.js";
import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";

export default {
	components: {
		DropGrid,
		DashboardItem
	},
	inject: {
		adminMode: {
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
			gridWidth: 1,
			editMode: this.adminMode
		}
	},
	computed: {
		items() {
			return this.widgets.map(item => {
				return {...item, ...(item.place[this.gridWidth] || {})};
			});
		}
	},
	methods: {
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
	<div class="dashboard-section" ref="container">
		<h3 class="d-flex">
			<span class="col">{{name}}</span>
			<button class="col-auto btn" @click.prevent="editMode = !editMode"><i class="fa-solid fa-gear"></i></button>
		</h3>
		<drop-grid v-model:cols="gridWidth" :items="items" :active="editMode" :resize-limit="checkResizeLimit" :margin-for-extra-row=".01" @rearrange-items="updatePositions">
			<template v-slot="item">
				<dashboard-item
					:id="item.widget"
					:loading="item.loading"
					:config="item.config"
					:custom="item.custom"
					:hidden="item.hidden"
					:editMode="editMode"
					@change="saveConfig($event, item)"
					@remove="removeWidget(item, $event)">
				</dashboard-item>
			</template>
			<template #empty-tile-hover="{x,y}">
				<div class="empty-tile-hover" @click="$emit('widgetAdd', name, { widget: 1, config: {}, place: {[gridWidth]: {x,y,w:1,h:1}}, custom: 1 })"></div>
			</template>
		</drop-grid>
	</div>`
}

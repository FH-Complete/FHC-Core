// TODO(chris): Comments

import GridItem from './Grid/Item.js';
import GridLogic from '../../composables/GridLogic.js';

const MODE_IDLE = 0;
const MODE_MOVE = 1;
const MODE_RESIZE = 2;
const MODE_MOUSE_DOWN = 3;

export default {
	name: 'Grid',
	components: {
		GridItem,
	},
	props: {
		cols: Number,
		items: Array,
		resizeLimit: Function,
		active: {
			type: Boolean,
			default: true
		},
		marginForExtraRow: {
			type: Number,
			default: 0
		},
	},
	emits: [
		"rearrangeItems",
		"newItem",
		"gridHeight",
		"draggedItem",
	],
	data() {
		return {
			x: -1,
			y: -1,
			clientX:0,
			clientY: 0,
			mode: MODE_IDLE,
			grid: null,
			dragGrid: null,
			permUpdates: [],
			positionUpdates: null,
			fixedPositionUpdates: null,
			draggedOffset: [0,0],
			draggedItem: null,
			draggedNode: null,
			additionalRow: null,
			reorderedItems:[],
			clonedWidget:null,
		}
	},
	inject:{
		sectionName: {
			type: String,
			default: '',
		},
	},
	computed: {
		items_hashmap() {
			let items = {};
			this.items.forEach(item => {
				if (this.reorderedItems.length > 0){
					if(item.reorder){
						let rearrangedPosition = this.reorderedItems.filter(widget => widget.data.widgetid == item.widgetid)?.pop();
						if (rearrangedPosition) {			
							item.x = rearrangedPosition.x;
							item.y = rearrangedPosition.y;
						}
					}
				}
				items[`x${item.x}y${item.y}`] = item;
			});	
			return items
		},
		items_placeholders(){
			let placeholders = [];
			let col_max = this.cols;
			let rows_max = this.rows;

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
		placedItems_withPlaceholders() {
			return [...this.placedItems, ...this.items_placeholders];
		},
		rows() {
			if ((this.mode == MODE_MOVE || this.mode == MODE_RESIZE) && this.dragGrid){
				return this.dragGrid.h;
			}
			return this.grid ? this.grid.h : 1;
		},
		gridStyle() {
			const addH = this.active ? this.marginForExtraRow : 0;
			return {
				'--fhc-dg-row-height': 100/(this.rows + addH) + '%',
				'--fhc-dg-col-width': 100/this.cols + '%',
				'--fhc-dg-item-padding-horizontal': '0.25%',
				'--fhc-dg-item-padding-top': '0.5%',
				'padding-bottom': 100 * (this.rows + addH)/this.cols + '%'
			}
		},
		indexedItems() {
			return this.items.map(
				(item, index) => {
					return {
						index: index,
						x: item.x,
						y: item.y,
						w: item.w,
						h: item.h,
						reorder: item.reorder,
						weight: item.weight || 0,
						data: item
					}
				}
			);
		},
		prePlacedItems() {
			if (!this.fixedPositionUpdates)
				return this.indexedItems;
			return this.indexedItems.map(item => {
				if (!this.fixedPositionUpdates[item.index])
					return item;
				return {
					index: item.index,
					weight: item.weight,
					data: item.data,
					x: this.fixedPositionUpdates[item.index].x === undefined ? item.x : this.fixedPositionUpdates[item.index].x,
					y: this.fixedPositionUpdates[item.index].y === undefined ? item.y : this.fixedPositionUpdates[item.index].y,
					w: this.fixedPositionUpdates[item.index].w === undefined ? item.w : this.fixedPositionUpdates[item.index].w,
					h: this.fixedPositionUpdates[item.index].h === undefined ? item.h : this.fixedPositionUpdates[item.index].h
				};
			});
		},
		placedItems() {
			if (!this.positionUpdates)
				return this.prePlacedItems;
			let mappedPlacedItems= this.prePlacedItems.map(item => {
				if (!this.positionUpdates[item.index] )
					return item;
				let height_diff = this.positionUpdates[item.index]?.h - item.h;
				let width_diff = this.positionUpdates[item.index]?.w - item.w;
				return {
					resize: this.positionUpdates[item.index]?.resize,
					index: item.index,
					weight: item.weight,
					data: item.data,
					x: this.positionUpdates[item.index].x === undefined ? item.x : this.positionUpdates[item.index].x,
					y: this.positionUpdates[item.index].y === undefined ? item.y : this.positionUpdates[item.index].y,
					w: width_diff>0?item.w:this.positionUpdates[item.index].w === undefined ? item.w : this.positionUpdates[item.index].w,
					h: height_diff > 0 ?item.h:this.positionUpdates[item.index].h === undefined ? item.h : this.positionUpdates[item.index].h
					
				};
			});

			let temporaryResizeItems = [];
			mappedPlacedItems.forEach(item=>{
				if(item.resize){
					let newItem = {
						...item,
						w:this.positionUpdates[item.index].w === undefined ? item.w : this.positionUpdates[item.index].w,
						h:this.positionUpdates[item.index].h === undefined ? item.h : this.positionUpdates[item.index].h,
						resizeOverlay:true,
						blank:true,
					};
					temporaryResizeItems.push(newItem)
				}
			})
			return [...mappedPlacedItems, ...temporaryResizeItems];
		},
		showEmptyTileHover() {
			if (!this.active || !this.grid || this.mode != MODE_IDLE || this.x < 0 || this.y < 0 || this.x >= this.cols || this.y >= this.rows)
				return false;
			return this.grid.isFreeSlot(this.x, this.y);
		},
		widgetSetup(){
			if (!this.widgetsSetup)
				return;
			return this.widgetsSetup.reduce((acc, ele) => { 
				acc[ele.widget_id] =ele;
				return acc;
			} ,{});
		},
	},
	watch: {
		active(active) {
			if (!active)
				this.dragCancel();
		},
		cols() {
			this.dragCancel();
		},
	    rows: {
			handler(value) {
				this.$emit('gridHeight', value);
			},
			immediate: true
		},
		indexedItems: {
			handler(value) {
				this.dragCancel();

				const updated = this.createNewGrid(value);

				this.fixedPositionUpdates = updated;
				if (updated.length)
					this.$emit('rearrangeItems', updated.filter(v => v));
			},
			immediate: true,
			deep: true
		}
	},
	methods: {
		
		toggleDraggedItemOverlay(condition){
			if(!this.draggedNode)
				return;
			if(condition){
				this.draggedNode.firstElementChild.classList.add("dashboard-item-overlay");
			}else{
				this.draggedNode.firstElementChild.classList.remove("dashboard-item-overlay");
			}
		},
		dragging(event){
			if(this.mode == MODE_MOVE){
				this.toggleDraggedItemOverlay(true);
				this.clonedWidget.style.top = `${this.clientY-20}px`;
				this.clonedWidget.style.left = `${this.clientX-15}px`;
			}
		},
		createNewGrid(items) {
			this.grid = new GridLogic(this.cols);
			const result = [];
			let sortedItems = [...items].sort((a, b) => {
				if (a.reorder){
					return 999;
				}
				if (b.reorder){
					return -999;
				}
				return a.weight > b.weight;
			}); 
			let reorderedItems = [];
			sortedItems.forEach(item => {
				let freeSlots = this.grid.getFreeSlots();
				
				if(item.reorder){
					item.reorder=true;
					let firstFreeSlot = freeSlots.shift();
					if (!firstFreeSlot) {
						item.x = 0;
						item.y = this.grid.h;
					}else{
						item.x = firstFreeSlot.x;
						item.y = firstFreeSlot.y;
					}
					reorderedItems.push(item);
					
				}
				if (item.x + item.w > this.cols) {
					let targetW = this.cols-item.x,
						targetX = undefined;
					if (this.resizeLimit) {
						[targetW] = this.resizeLimit(item.data, targetW, item.h);
					}
					if (targetW < 1)
						targetW = 1;
					if (targetW > this.cols)
						targetW = this.cols;
					if (item.x + targetW > this.cols) {
						targetX = this.cols - targetW;
					}
					if (targetW == item.w)
						targetW = undefined;
					result[item.index] = {
						item: item.data,
						x: targetX,
						w: targetW
					};
				}
				item.frame = this.grid.getItemFrame(item);
				this.convertGridResultToUpdate(this.grid.add(item), result, items);
			});
			this.reorderedItems = reorderedItems;
			this.grid.clearWeights();
			return result;
		},
		convertGridResultToUpdate(input, output, baseArray) {
			
			if (!input)
				return;
			if (!baseArray)
				baseArray = this.indexedItems;
			input.forEach(item => {
				let result = {
					item: baseArray[item.index].data
				};
				if (item.x !== undefined)
					result.x = item.x;
				if (item.y !== undefined)
					result.y = item.y;
				if (item.w !== undefined)
					result.w = item.w;
				if (item.h !== undefined)
					result.h = item.h;
				output[item.index] = result;
			});
		},
		mouseLeave() {
			if (this.mode == MODE_IDLE) {
				this.x = -1;
				this.y = -1;
				if (this.additionalRow !== null) {
					let gridHeight = this.grid.getMaxY() + 1;
					if(this.grid.h>gridHeight){
						this.grid.h = gridHeight;
					}
					this.additionalRow = null;
				}
			}
		},
		updateCursor(evt) {
			if (!this.active) {
				this.x = this.y = -1;
				return false;
			}
			const addH = this.active ? this.marginForExtraRow : 0;
			const rect = this.$refs.container.getBoundingClientRect();

			if (!evt.clientX && !evt.clientY && evt.touches) {
				evt.clientX = evt.touches[0].clientX;
				evt.clientY = evt.touches[0].clientY;
			}

			this.clientX = (evt.clientX - rect.left);
			this.clientY = (evt.clientY - rect.top);

			const gridX = Math.floor(this.cols * (evt.clientX - rect.left) / this.$refs.container.clientWidth);
			const gridY = Math.floor((this.rows + addH) * (evt.clientY - rect.top) / this.$refs.container.clientHeight);
			if (this.x == gridX && this.y == gridY)
				return false;
			
			if (this.mode == MODE_IDLE) {
				if (this.additionalRow === null && this.y == this.rows-1 && gridY == this.rows) {
					this.additionalRow = this.grid.h;
					this.grid.h += 1;
				} else if (this.additionalRow !== null && gridY != this.rows - 1) {
					let gridHeight = this.grid.getMaxY() + 1;
					if(this.grid.h > gridHeight){
						this.grid.h = gridHeight;
					}
					this.additionalRow = null;
				}
			}
			this.x = gridX;
			this.y = gridY;

			return true;
		},
		_dragStart(evt, item) {
			if (evt.dataTransfer) {
				evt.dataTransfer.setDragImage(evt.target, -99999, -99999);
				evt.dataTransfer.dropEffect = 'move';
				evt.dataTransfer.effectAllowed = 'move';
			}
		},
		startMove(evt, item) {
			if (!this.active)
				return;
			
			this.mode = MODE_MOVE;
			
			this.draggedItem = item;
			this.$emit('draggedItem',item);
			this.draggedNode = evt.target;
			//clones the widget for the drag Image
			let clone = evt.target.cloneNode(true);
			clone.style.zIndex = 5;
			clone.classList.add("widgetClone");
			this.$refs.container.appendChild(clone);
			this.clonedWidget = clone;
			
			this.draggedOffset = [item.x - this.x, item.y - this.y];
			this._dragStart(evt, item);
		},
		startResize(evt, item) {
			if (!this.active)
				return;
			this.mode = MODE_RESIZE;
			this.draggedItem = item;
			this._dragStart(evt);
		},
		dragOver(evt) {
			if (!this.active)
				return this.dragCancel();
			this.checkPinnedWidgetAnimation();
			if (this.updateCursor(evt)) {
				switch(this.mode) {
					case MODE_MOVE: {
						evt.preventDefault();
						this.dragGrid = new GridLogic(this.grid);
						let x = this.x + this.draggedOffset[0];
						let y = this.y + this.draggedOffset[1];
						if (x < 0) {
							this.draggedOffset[0] -= x;
							x = 0;
						} else if (x + this.draggedItem.w > this.cols) {
							this.draggedOffset[0] += this.cols - this.draggedItem.w - x;
							x = this.cols - this.draggedItem.w;
						}
						if (y < 0) {
							this.draggedOffset[1] -= y;
							y = 0;
						}
						this.positionUpdates = this.dragGrid.move(this.draggedItem, x, y);
						break;
					}
					case MODE_RESIZE: {
						evt.preventDefault();
						this.dragGrid = new GridLogic(this.grid);
						let w = Math.min(this.cols - this.draggedItem.x, Math.max(1, this.x - this.draggedItem.x + 1));
						let h = Math.max(1, this.y - this.draggedItem.y + 1);
						if (this.resizeLimit)
							[w, h] = this.resizeLimit(this.draggedItem.data, w, h);
						this.positionUpdates = this.dragGrid.resize(this.draggedItem, w, h);
						break;
					}
				}
			}
		},
		dragCancel() {
			
			this.toggleDraggedItemOverlay(false);
			this.mode = MODE_IDLE;
			this.positionUpdates = null;
			this.draggedOffset = [0,0],
			this.draggedItem = null;
			this.$emit('draggedItem',null);
			this.draggedNode = null;
			
		},
		dragEnd() {
			let widgetClones = document.getElementsByClassName("widgetClone");
			for(let widget of widgetClones){
				this.$refs.container.removeChild(widget);
			}
			if (this.mode == MODE_IDLE)
				return;
			if (!this.active || this.x < 0 || this.y < 0 || this.x >= this.cols)
				return this.dragCancel();
			this.mode = MODE_IDLE;
			let updated = [];
			this.convertGridResultToUpdate(this.positionUpdates, updated);
			updated = this._updateFixedPositions(updated);
			if (updated.length)
				this.$emit('rearrangeItems', updated.filter(v => v));
		},
		_updateFixedPositions(updated) {
			updated.forEach((item, index) => {
				if (!this.fixedPositionUpdates[index])
					this.fixedPositionUpdates[index] = item;
				else
					this.fixedPositionUpdates[index] = {...this.fixedPositionUpdates[index], ...item};
			});
			let additionalUpdates = this.createNewGrid(this.prePlacedItems);
			if (additionalUpdates.length) {
				// NOTE(chris): this should never happen but it's here for safety
				additionalUpdates.forEach((item, index) => updated[index] = item);
				return this._updateFixedPositions(updated);
			}
			return updated;
		},
		emptyTileClicked() {
			this.$emit('newItem', this.x, this.y);
		},
		updateCursorOnMouseMove(evt){
			if(this.mode == MODE_IDLE){
				this.updateCursor(evt);
			}
		},
		checkPinnedWidgetAnimation(){
			let itemAtPosition=[];
			switch(this.mode){
				case MODE_RESIZE:
					for (let x = this.draggedItem.x; x <= this.x; x++) {
						for (let y = this.draggedItem.y; y <= this.y; y++) {
							this.items.forEach(item => {
								if (item.x == x && item.y == y) {
									itemAtPosition.push(item);
								}
							});
						}
					}
					break;
				case MODE_MOVE:
					itemAtPosition = this.items.filter(item=>item.x == this.x && item.y == this.y);
					break;
			}
			
			Array.from(document.getElementsByClassName("denied-dragging-animation"))?.forEach(ele => {
				ele.classList.remove("denied-dragging-animation");
			})
			
			itemAtPosition.forEach(item=>{
				if (item.place[this.cols] && item.place[this.cols].pinned) {
					let pinnedWidget = document.getElementById(item.widgetid);
					let pinNode = pinnedWidget.querySelector("[pinned='true']");
					if (!pinNode.classList.contains("denied-dragging-animation")) {
						pinNode.classList.add("denied-dragging-animation");
					}
				}	
			})
		},
		mouseDown(){
			this.mode = MODE_MOUSE_DOWN;
		},
		mouseUp() {
			this.mode = MODE_IDLE;
		},
	},
	template: `
	<div
		ref="container"
		class="drop-grid position-relative h-0"
		:style="gridStyle"
		@touchmove.prevent="dragOver"
		@touchend="dragCancel"
		@dragover.prevent="dragOver"
		@drop="dragEnd"
		@mousemove="updateCursorOnMouseMove"
		@mouseleave="mouseLeave">
		<TransitionGroup tag="div">
			<grid-item
				ref="gridItems"
				v-for="(item,index) in (mode == 0 && active ? placedItems_withPlaceholders : placedItems)"
				:key="item.data.id"
				:item="item"
				@start-move="startMove"
				@mouse-down="mouseDown"
				@mouse-up="mouseUp"
				@start-resize="startResize"
				@dragging="dragging"
				@end-drag="dragCancel"
				@drop-drag="dragEnd"
				@touchEvent="updateCursorOnMouseMove"
				class="position-absolute"
				:active="active"
				:style="{
					zIndex: item.resizeOverlay ? -5 : 'auto',
					top: 'calc(' + item.y + ' * var(--fhc-dg-row-height))',
					left: 'calc(' + item.x + ' * var(--fhc-dg-col-width))',
					width: 'calc(' + item.w + ' * var(--fhc-dg-col-width))',
					height: 'calc(' + item.h + ' * var(--fhc-dg-row-height))',
					paddingTop: 'var(--fhc-dg-item-padding-top)',
					paddingLeft: 'var(--fhc-dg-item-padding-horizontal)',
					paddingRight: 'var(--fhc-dg-item-padding-horizontal)'
				}">
				<template v-slot="item">
					<slot v-bind="{...item, ...item.data, index:index}" :x="item.x" :y="item.y" ></slot>
				</template>
			</grid-item>
		</TransitionGroup>
		
	</div>`
}

/*
OLD VERSION - ON HOVER
<div
	v-if="showEmptyTileHover"
	class="position-absolute d-flex justify-content-center align-items-center"
	:style="{
		cursor: 'pointer',
		top: 'calc(' + y + ' * var(--fhc-dg-row-height))',
		left: 'calc(' + x + ' * var(--fhc-dg-col-width))',
		width: 'var(--fhc-dg-col-width)',
		height: 'var(--fhc-dg-row-height)'
	}"
	@click="emptyTileClicked">
	<slot :x="x" :y="y" name="empty-tile-hover"></slot>
</div>
*/
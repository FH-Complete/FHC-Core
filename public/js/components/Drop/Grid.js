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
		itemsSetup: Object,
		resizeLimit: Function,
		active: {
			type: Boolean,
			default: true
		},
		marginForExtraRow: {
			type: Number,
			default: 0
		},
		additionalRow: {
			type: Boolean,
			default: false
		}
	},
	emits: [
		"rearrangeItems",
		"gridHeight",
		"draggedItem",
		"update:additionalRow"
	],
	data() {
		return {
			x: -1,
			y: -1,
			clientX:0,
			clientY: 0,
			mode: MODE_IDLE,
			grid: null,
			tempPositionUpdates: null,
			correctedPositionUpdates: null,
			draggedOffset: [0, 0],
			draggedItem: null,
			draggedNode: null,
			reorderedItems: [],
			clonedWidget: null,
		};
	},
	computed: {
		additionalRowComputed: {
			get() {
				return this.additionalRow;
			},
			set(value) {
				this.$emit('update:additionalRow', value);
			}
		},
		items_hashmap() {
			let items = {};
			this.items.forEach(item => {
				if (this.reorderedItems.length > 0 && this.needsReordering(item)) {
						let rearrangedPosition = this.reorderedItems.filter(widget => widget.data.widgetid == item.widgetid)?.pop();
						if (rearrangedPosition) {			
							item.x = rearrangedPosition.x;
							item.y = rearrangedPosition.y;
						}
				}
				items[`x${item.x}y${item.y}`] = item;
			});	
			return items
		},
		items_placeholders() {
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
		currentItems() {
			if (this.mode != 1 && this.mode != 2 && this.active)
				return [ ...this.placedItems, ...this.items_placeholders ];

			return this.placedItems;
		},
		rows() {
			if (this.additionalRowComputed) {
				return this.grid ? (this.grid.h+1) : 1;
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
						weight: item.weight || 0,
						data: item
					}
				}
			);
		},
		prePlacedItems() {
			if (!this.correctedPositionUpdates)
				return this.indexedItems;
			return this.indexedItems.map(item => {
				if (!this.correctedPositionUpdates[item.index])
					return item;
				return {
					index: item.index,
					weight: item.weight,
					data: item.data,
					x: this.correctedPositionUpdates[item.index].x === undefined ? item.x : this.correctedPositionUpdates[item.index].x,
					y: this.correctedPositionUpdates[item.index].y === undefined ? item.y : this.correctedPositionUpdates[item.index].y,
					w: this.correctedPositionUpdates[item.index].w === undefined ? item.w : this.correctedPositionUpdates[item.index].w,
					h: this.correctedPositionUpdates[item.index].h === undefined ? item.h : this.correctedPositionUpdates[item.index].h
				};
			});
		},
		placedItems() {
			if (!this.tempPositionUpdates)
				return this.prePlacedItems;
			let mappedPlacedItems= this.prePlacedItems.map(item => {
				if (!this.tempPositionUpdates[item.index] )
					return item;
				let height_diff = this.tempPositionUpdates[item.index]?.h - item.h;
				let width_diff = this.tempPositionUpdates[item.index]?.w - item.w;
				return {
					resize: this.tempPositionUpdates[item.index]?.resize,
					index: item.index,
					weight: item.weight,
					data: item.data,
					x: this.tempPositionUpdates[item.index].x === undefined ? item.x : this.tempPositionUpdates[item.index].x,
					y: this.tempPositionUpdates[item.index].y === undefined ? item.y : this.tempPositionUpdates[item.index].y,
					w: width_diff>0?item.w:this.tempPositionUpdates[item.index].w === undefined ? item.w : this.tempPositionUpdates[item.index].w,
					h: height_diff > 0 ?item.h:this.tempPositionUpdates[item.index].h === undefined ? item.h : this.tempPositionUpdates[item.index].h
					
				};
			});

			let temporaryResizeItems = [];
			mappedPlacedItems.forEach(item=>{
				if(item.resize){
					let newItem = {
						...item,
						w:this.tempPositionUpdates[item.index].w === undefined ? item.w : this.tempPositionUpdates[item.index].w,
						h:this.tempPositionUpdates[item.index].h === undefined ? item.h : this.tempPositionUpdates[item.index].h,
						resizeOverlay:true,
						blank:true,
					};
					temporaryResizeItems.push(newItem)
				}
			})
			return [...mappedPlacedItems, ...temporaryResizeItems];
		}
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

				this.correctedPositionUpdates = updated;
				if (updated.length)
					this.$emit('rearrangeItems', updated.filter(v => v));
			},
			immediate: true,
			deep: true
		}
	},
	methods: {
		needsReordering(item) {
			if (!item?.data?.place[this.cols]) {
				return true;
			}
			return false;
		},
		toggleDraggedItemOverlay(condition) {
			if (!this.draggedNode)
				return;
			if (condition) {
				this.draggedNode.firstElementChild.classList.add("dashboard-item-overlay");
			} else {
				this.draggedNode.firstElementChild.classList.remove("dashboard-item-overlay");
			}
		},
		dragging(event) {
			if (this.mode == MODE_MOVE) {
				this.toggleDraggedItemOverlay(true);
				
				const containerRect = this.$refs.container.getBoundingClientRect();
				const clonedWidgetRect = this.clonedWidget.getBoundingClientRect();
				
				let desiredTop = this.clientY - 20;
				let desiredLeft = this.clientX - 15;
				
				const minTop = 0;
				const maxTop = containerRect.height - clonedWidgetRect.height;
				const minLeft = 0;
				const maxLeft = containerRect.width - clonedWidgetRect.width;
				
				const constrainedTop = Math.max(minTop, Math.min(maxTop, desiredTop));
				const constrainedLeft = Math.max(minLeft, Math.min(maxLeft, desiredLeft));
				
				this.clonedWidget.style.top = `${constrainedTop}px`;
				this.clonedWidget.style.left = `${constrainedLeft}px`;
			}
		},
		createNewGrid(items) {
			this.grid = new GridLogic(this.cols);
			const result = [];
			let sortedItems = [...items].sort((a, b) => {
				if (this.needsReordering(a) && this.needsReordering(b)) {
					return 0;
				} else if (this.needsReordering(a)) {
					return 999;
				} else if (this.needsReordering(b)) {
					return -999;
				}
				
				return a.weight > b.weight;
			}); 
			let reorderedItems = [];
			sortedItems.forEach(item => {
				let freeSlots = this.grid.getFreeSlots();
				
				if (this.needsReordering(item)) {
					let firstFreeSlot = freeSlots.shift();
					if (!firstFreeSlot) {
						item.x = 0;
						item.y = this.grid.h;
					} else {
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
		updateCursor(evt) {
			if (!this.active) {
				this.x = this.y = -1;
				return false;
			}
			const addH = this.active ? this.marginForExtraRow : 0;
			const rect = this.$refs.container.getBoundingClientRect();
			
			if (!evt.clientX && !evt.clientY && evt.touches){
				evt.clientX = evt.touches[0].clientX;
				evt.clientY = evt.touches[0].clientY;
			}

			this.clientX = (evt.clientX - rect.left);
			this.clientY = (evt.clientY - rect.top);
			const gridX = Math.floor(this.cols * (evt.clientX - rect.left) / this.$refs.container.clientWidth);
			const gridY = Math.floor((this.rows + addH) * (evt.clientY - rect.top) / this.$refs.container.clientHeight);
			
			if (this.x == gridX && this.y == gridY)
				return false;
			
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
			
			// workaround for chrome fireing event dragend when styles are manipulated during dragging
			setTimeout(() => {
				this.mode = MODE_MOVE;
				this.updateCursor(evt);
				this.draggedItem = item;
				
				this.$emit('draggedItem', item);

				this.draggedNode = evt.target.closest(".drop-grid-item");
				//clones the widget for the drag Image
				
				// NOTE(chris): this is the element that follows the mouse while dragging
				// equivalent to the ghost image
				let clone = evt.target.closest(".drop-grid-item")?.cloneNode(true);

				clone.style.zIndex = 5;
				clone.classList.add("widgetClone");
				this.$refs.container.appendChild(clone);
				const hiddenWidget = clone.querySelector("[style='display: none;']");
				if (hiddenWidget)
					hiddenWidget.style.removeProperty("display");
				this.clonedWidget = clone;

				this.draggedOffset = [item.x - this.x, item.y - this.y];
			}, 0);

			this._dragStart(evt, item);
		},
		startResize(evt, item) {
			if (!this.active)
				return;
			
			// workaround for chrome fireing event dragend when styles are manipulated during dragging
			setTimeout(() => {
				this.mode = MODE_RESIZE;
				this.draggedItem = item;
				this.$emit('draggedItem', item);
			}, 0);
			
			this._dragStart(evt);
		},
		dragOver(evt) {
			if ((this.y + 1) > this.rows && (this.mode == MODE_MOVE || this.mode == MODE_RESIZE)) {
				this.dragCancel();
			}
			if (!this.active)
				return this.dragCancel();
			this.checkPinnedWidgetAnimation();
			if (this.mode == MODE_RESIZE) {
				this.checkWidgetSizeLimitAnimation();
			}
			if (this.updateCursor(evt)) {
				switch(this.mode) {
					case MODE_MOVE: {
						evt.preventDefault();
						const dragGrid = new GridLogic(this.grid);
						let x = this.x + this.draggedOffset[0];
						let y = this.y + this.draggedOffset[1];
						if (x < 0) {
							this.draggedOffset[0] += x;
							x = 0;
						} else if (x + this.draggedItem.w > this.cols) {
							this.draggedOffset[0] += this.cols - this.draggedItem.w - x;
							x = this.cols - this.draggedItem.w;
						}
						if (y < 0) {
							this.draggedOffset[1] += y;
							y = 0;
						}
						this.tempPositionUpdates= dragGrid.move(this.draggedItem, x, y);
						break;
					}
					case MODE_RESIZE: {
						evt.preventDefault();
						const dragGrid = new GridLogic(this.grid);
						let w = Math.min(this.cols - this.draggedItem.x, Math.max(1, this.x - this.draggedItem.x + 1));
						let h = Math.max(1, this.y - this.draggedItem.y + 1);
						if (this.resizeLimit)
							[w, h] = this.resizeLimit(this.draggedItem.data, w, h);
						this.tempPositionUpdates = dragGrid.resize(this.draggedItem, w, h);
						break;
					}
				}
			}
		},
		dragCancel() {
			this.removeWidgetClones();
			this.additionalRowComputed = false;
			this.toggleDraggedItemOverlay(false);
			this.mode = MODE_IDLE;
			this.tempPositionUpdates = null;
			this.draggedOffset = [0,0],
			this.draggedItem = null;
			this.$emit('draggedItem',null);
			this.draggedNode = null;
		},
		dragEnd() {
			this.removeWidgetClones();
			this.toggleDraggedItemOverlay(false);
			
			if (this.mode == MODE_IDLE) {
				return;
			}
			// clean up unused classes
			let draggedItemNode = document.getElementById(this.draggedItem.data.widgetid);
			draggedItemNode.classList.remove("border-danger");
			Array.from(document.getElementsByClassName("denied-dragging-animation"))?.forEach(ele => {
				ele.classList.remove("denied-dragging-animation");
			})
			
			//if (!this.active || this.x < 0 || this.y < 0 || this.x >= this.cols)
				//return this.dragCancel();

			this.mode = MODE_IDLE;
			let updated = [];
			this.convertGridResultToUpdate(this.tempPositionUpdates, updated);
			updated = this._updateCorrectedPositions(updated);
			if (updated.length)
				this.$emit('rearrangeItems', updated.filter(v => v));

			this.draggedItem = null;
			this.draggedNode = null;
			this.$emit('draggedItem', null);
		},
		_updateCorrectedPositions(updated) {
			updated.forEach((item, index) => {
				if (!this.correctedPositionUpdates[index])
					this.correctedPositionUpdates[index] = item;
				else
					this.correctedPositionUpdates[index] = {...this.correctedPositionUpdates[index], ...item};
			});
			let additionalUpdates = this.createNewGrid(this.prePlacedItems);
			if (additionalUpdates.length) {
				// NOTE(chris): this should never happen but it's here for safety
				additionalUpdates.forEach((item, index) => updated[index] = item);
				return this._updateCorrectedPositions(updated);
			}
			return updated;
		},
		checkPinnedWidgetAnimation() {
			let itemAtPosition = [];
			switch (this.mode) {
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
			});

			itemAtPosition.forEach(item => {
				if (item.place[this.cols] && item.place[this.cols].pinned) {
					let pinnedWidget = document.getElementById(item.widgetid);
					let pinNode = pinnedWidget.querySelector("[pinned='true']");
					if (!pinNode.classList.contains("denied-dragging-animation")) {
						pinNode.classList.add("denied-dragging-animation");
					}
				}	
			});
		},
		checkWidgetSizeLimitAnimation() {
			let draggedItemSetup = this.itemsSetup[this.draggedItem.data.widget];
			let draggedItemMaxWidth = draggedItemSetup.width.max ?? draggedItemSetup.width;
			let draggedItemMinWidth = draggedItemSetup.width.min ?? draggedItemSetup.width;
			let draggedItemMaxHeight = draggedItemSetup.height.max ?? draggedItemSetup.height;
			let draggedItemMinHeight = draggedItemSetup.height.min ?? draggedItemSetup.height;
			let draggedItemNode = document.getElementById(this.draggedItem.data.widgetid);

			let width_after_resize = this.x - this.draggedItem.x + 1; 
			let height_after_resize = this.y - this.draggedItem.y + 1; 
			if (
				(width_after_resize > 0 && (width_after_resize > draggedItemMaxWidth
				|| width_after_resize < draggedItemMinWidth)
				)
				||
				(height_after_resize > 0 && (height_after_resize > draggedItemMaxHeight
				|| height_after_resize < draggedItemMinHeight)
				)
			) {
				draggedItemNode.classList.add("border-danger");
			} else {
				draggedItemNode.classList.remove("border-danger");
			}
		},
		removeWidgetClones() {
			let widgetClones = Array.from(document.getElementsByClassName("widgetClone"));
			for (let i = 0; i < widgetClones.length; i++) {
				this.$refs.container.removeChild(widgetClones[i]);
			}
		},
		mouseDown() {
			this.mode = MODE_MOUSE_DOWN;
		},
		mouseUp() {
			this.mode = MODE_IDLE;
		}
	},
	template: /* html */`
	<div
		ref="container"
		class="drop-grid position-relative h-0"
		:style="gridStyle"
		@touchmove="dragOver"
		@touchend="dragCancel"
		@dragover.prevent="dragOver"
		@drop="dragEnd"
	>
		<TransitionGroup>
			<grid-item
				ref="gridItems"
				v-for="(item, index) in currentItems"
				:key="item.data.id"
				class="position-absolute"
				:item="item"
				:active="active"
				:style="{
					zIndex: item.resizeOverlay ? 1 : 'auto',
					top: 'calc(' + item.y + ' * var(--fhc-dg-row-height))',
					left: 'calc(' + item.x + ' * var(--fhc-dg-col-width))',
					width: 'calc(' + item.w + ' * var(--fhc-dg-col-width))',
					height: 'calc(' + item.h + ' * var(--fhc-dg-row-height))',
					paddingTop: 'var(--fhc-dg-item-padding-top)',
					paddingLeft: 'var(--fhc-dg-item-padding-horizontal)',
					paddingRight: 'var(--fhc-dg-item-padding-horizontal)'
				}"
				@start-move="startMove"
				@mouse-down="mouseDown"
				@mouse-up="mouseUp"
				@start-resize="startResize"
				@dragging="dragging"
				@end-drag="dragEnd"
				@touch-end="dragEnd();mouseUp();"
				@touch-start="mouseDown"
			>
				<template v-slot="item">
					<slot
						v-bind="{ ...item, ...item.data, index: index }"
						:x="item.x"
						:y="item.y"
					></slot>
				</template>
			</grid-item>
		</TransitionGroup>
	</div>`
}

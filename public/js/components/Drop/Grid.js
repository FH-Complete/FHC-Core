import GridItem from './Grid/Item.js';
import GridLogic from '../../composables/GridLogic.js';

const MODE_IDLE = 0;
const MODE_MOVE = 1;
const MODE_RESIZE = 2;

export default {
	name: 'Grid',
	components: {
		GridItem,
	},
	props: {
		cols: Number,
		items: Array,
		itemsSetup: Object,
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
		"update:additionalRow"
	],
	data() {
		return {
			// gridlogic
			grid: null,
			tempPositionUpdates: null,
			correctedPositionUpdates: null,
			// dragging
			mode: MODE_IDLE,
			draggedOffset: [0, 0],
			draggedItem: null,
			clonedWidget: null, // ghost image
			// tile coordinates while dragging
			x: -1,
			y: -1,
			// mouse coordinates while dragging
			clientX: 0,
			clientY: 0,
			reorderedItems: [] // holds items that have no inital place
		};
	},
	computed: {
		// gridlogic
		rows() {
			if (this.additionalRowComputed) {
				return this.grid ? (this.grid.h+1) : 1;
			}
			return this.grid ? this.grid.h : 1;
		},
		additionalRowComputed: {
			get() {
				return this.additionalRow;
			},
			set(value) {
				this.$emit('update:additionalRow', value);
			}
		},
		gridStyle() {
			const addH = this.active ? this.marginForExtraRow : 0;
			return {
				'--fhc-dg-row-height': 100/(this.rows + addH) + '%',
				'--fhc-dg-col-width': 100/this.cols + '%',
				'--fhc-dg-item-padding':
					'var(--fhc-dg-item-py, var(--fhc-dg-item-p, .25%))' +
					' ' +
					'var(--fhc-dg-item-px, var(--fhc-dg-item-p, .25%))',
				'padding-bottom': 100 * (this.rows + addH)/this.cols + '%'
			};
		},
		// dragging
		sizeLimits() {
			return Object.fromEntries(Object.entries(this.itemsSetup).map(([type, { setup }]) => {
				const result = {}; // work on a copy
				if (setup.height === undefined)
					result.height = { min: 1, max: undefined };
				else if (Number.isInteger(setup.height))
					result.height = { min: setup.height, max: setup.height };
				else
					result.height = {
						min: setup.height.min ?? 1,
						max: setup.height.max
					};

				if (setup.width === undefined)
					result.width = { min: 1, max: undefined };
				else if (Number.isInteger(setup.width))
					result.width = { min: setup.width, max: setup.width };
				else
					result.width = {
						min: setup.width.min ?? 1,
						max: setup.width.max
					};

				return [type, result];
			}));
		},
		// item pipeline
		items_placeholders() { // empty tiles
			return this.grid.getFreeSlots().map((item, index) => {
				return {
					x: item.x,
					y: item.y,
					h: 1,
					w: 1,
					placeholder: true,
					data: {
						id: 'placeholder_' + index
					}
				};
			});
		},
		indexedItems() { // indexed
			return this.items.map(
				(item, index) => {
					return {
						index: index,
						x: item.x,
						y: item.y,
						w: item.w,
						h: item.h,
						pinned: item.pinned,
						weight: item.weight || 0,
						data: item
					}
				}
			);
		},
		prePlacedItems() { // indexed & corrected
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
					h: this.correctedPositionUpdates[item.index].h === undefined ? item.h : this.correctedPositionUpdates[item.index].h,
					pinned: item.pinned
				};
			});
		},
		placedItems() { // indexed & corrected & dragging
			if (!this.tempPositionUpdates)
				return this.prePlacedItems;
			return this.prePlacedItems.map(item => {
				if (!this.tempPositionUpdates[item.index])
					return item;

				return {
					index: item.index,
					weight: item.weight,
					data: item.data,
					x: this.tempPositionUpdates[item.index].x === undefined ? item.x : this.tempPositionUpdates[item.index].x,
					y: this.tempPositionUpdates[item.index].y === undefined ? item.y : this.tempPositionUpdates[item.index].y,
					w: this.tempPositionUpdates[item.index].w === undefined ? item.w : this.tempPositionUpdates[item.index].w,
					h: this.tempPositionUpdates[item.index].h === undefined ? item.h : this.tempPositionUpdates[item.index].h,
					pinned: item.pinned
				};
			});
		},
		currentItems() { // final items with classes
			if (this.mode == MODE_IDLE && this.active)
				return [ ...this.placedItems, ...this.items_placeholders ];

			if (this.mode != MODE_IDLE && this.draggedItem) {
				// add classes to dragged item
				const draggedItemIndex = this.placedItems.findIndex(item => item.index == this.draggedItem.index);
				const modifiedDraggedItem = {
					...this.placedItems[draggedItemIndex],
					classes: []
				};

				if (this.mode == MODE_MOVE) {
					modifiedDraggedItem.classes.push('drop-grid-item-move');
				}
				if (this.mode == MODE_RESIZE) {
					modifiedDraggedItem.classes.push('drop-grid-item-resize');
					if (this.draggedItem.oversized)
						modifiedDraggedItem.classes.push('drop-grid-item-oversized')
					else if (this.tempPositionUpdates?.length)
						modifiedDraggedItem.classes.push('drop-grid-item-sizechanged')
				}

				return this.placedItems.toSpliced(draggedItemIndex, 1, modifiedDraggedItem);
			}

			return this.placedItems;
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
		// helpers
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
		// has item an initial place
		needsReordering(item) {
			if (!item?.data?.place[this.cols]) {
				return true;
			}
			return false;
		},
		// gridlogic
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

					[ targetW ] = this.cropSizeToAllowed(item.data.widget, targetW, item.h);

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
		// dragging
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
			}, 0);
			
			this._dragStart(evt);
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
		dragOver(evt) {
			if ((this.y + 1) > this.rows && (this.mode == MODE_MOVE || this.mode == MODE_RESIZE)) {
				this.dragCancel();
			}
			if (!this.active)
				return this.dragCancel();

			this.checkPinnedWidgetAnimation();

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
						
						this.tempPositionUpdates = dragGrid.move(this.draggedItem, x, y);
						break;
					}
					case MODE_RESIZE: {
						evt.preventDefault();
						const dragGrid = new GridLogic(this.grid);
						const targetW = this.x - this.draggedItem.x + 1;
						const targetH = this.y - this.draggedItem.y + 1;
						let w = Math.min(this.cols - this.draggedItem.x, targetW);
						let h = targetH;

						[ w, h ] = this.cropSizeToAllowed(this.draggedItem.data.widget, w, h);

						this.draggedItem.oversized = (w !== targetW || h !== targetH);

						if (this.draggedItem.oversized)
							[ w, h ] = [ this.draggedItem.w, this.draggedItem.h ];

						this.tempPositionUpdates = dragGrid.resize(this.draggedItem, w, h);
						break;
					}
				}
			}
		},
		removeWidgetClones() {
			let widgetClones = Array.from(document.getElementsByClassName("widgetClone"));
			for (let i = 0; i < widgetClones.length; i++) {
				this.$refs.container.removeChild(widgetClones[i]);
			}
		},
		_cleanupDragging() {
			if (this.draggedItem) {
				const draggedItem = this.indexedItems.find(item => item.index == this.draggedItem.index);
				delete draggedItem.classes;
				this.draggedItem = null;
			}
		},
		dragCancel() {
			this.removeWidgetClones();
			this.additionalRowComputed = false;
			this.mode = MODE_IDLE;
			this.tempPositionUpdates = null;
			this.draggedOffset = [0,0];
			this._cleanupDragging();
		},
		dragEnd() {
			this.removeWidgetClones();
			
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

			this._cleanupDragging();
		},
		moveGhostImage(event) {
			if (this.mode == MODE_MOVE) {
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
		cropSizeToAllowed(type, w, h) {
			if (w < 1)
				w = 1;
			if (h < 1)
				h = 1;

			const setup = this.sizeLimits[type];

			if (!setup)
				return [w, h];

			if (w < setup.width.min)
				w = setup.width.min;
			if (h < setup.height.min)
				h = setup.height.min;
			if (setup.width.max && w > setup.width.max)
				w = setup.width.max;
			if (setup.height.max && h > setup.height.max)
				h = setup.height.max;
			
			return [w, h];
		}
	},
	template: /* html */`
	<div
		ref="container"
		class="drop-grid position-relative h-0"
		:style="gridStyle"
		@dragover.prevent="dragOver"
		@drop="dragEnd"
	>
		<TransitionGroup>
			<grid-item
				ref="gridItems"
				v-for="(item, index) in currentItems"
				:key="item.data.id"
				class="position-absolute"
				:class="item.classes"
				:item="item"
				:style="{
					top: 'calc(' + item.y + ' * var(--fhc-dg-row-height))',
					left: 'calc(' + item.x + ' * var(--fhc-dg-col-width))',
					width: 'calc(' + item.w + ' * var(--fhc-dg-col-width))',
					height: 'calc(' + item.h + ' * var(--fhc-dg-row-height))',
					padding: 'var(--fhc-dg-item-padding)'
				}"
				@start-move="startMove"
				@start-resize="startResize"
				@drag="moveGhostImage"
				@dragend="dragEnd"
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

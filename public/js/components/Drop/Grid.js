// TODO(chris): Comments

import GridItem from './Grid/Item.js';
import GridLogic from '../../composables/GridLogic.js';

const MODE_IDLE = 0;
const MODE_MOVE = 1;
const MODE_RESIZE = 2;

export default {
	components: {
		GridItem
	},
	inject: {
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
		placeholders: {
			type: Array,
			default: () => []
		}
	},
	emits: [
		"rearrangeItems",
		"newItem",
		"gridHeight"
	],
	data() {
		return {
			x: -1,
			y: -1,
			mode: MODE_IDLE,
			grid: null,
			dragGrid: null,
			permUpdates: [],
			positionUpdates: null,
			fixedPositionUpdates: null,
			draggedOffset: [0,0],
			draggedItem: null,
			additionalRow: null
		}
	},
	computed: {
		rows() {
			if ((this.mode == MODE_MOVE || this.mode == MODE_RESIZE) && this.dragGrid)
				return this.dragGrid.h;
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
			return this.prePlacedItems.map(item => {
				if (!this.positionUpdates[item.index])
					return item;
				return {
					index: item.index,
					weight: item.weight,
					data: item.data,
					x: this.positionUpdates[item.index].x === undefined ? item.x : this.positionUpdates[item.index].x,
					y: this.positionUpdates[item.index].y === undefined ? item.y : this.positionUpdates[item.index].y,
					w: this.positionUpdates[item.index].w === undefined ? item.w : this.positionUpdates[item.index].w,
					h: this.positionUpdates[item.index].h === undefined ? item.h : this.positionUpdates[item.index].h
				};
			});
		},
		placedItems_withPlaceholders(){
			return [...this.placedItems,...this.placeholders];
		},
		showEmptyTileHover() {
			if (!this.active || !this.grid || this.mode != MODE_IDLE || this.x < 0 || this.y < 0 || this.x >= this.cols || this.y >= this.rows)
				return false;
			return this.grid.isFreeSlot(this.x, this.y);
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

				this.fixedPositionUpdates = updated;
				if (updated.length)
					this.$emit('rearrangeItems', updated.filter(v => v));
			},
			immediate: true,
			deep: true
		}
	},
	methods: {
		createNewGrid(items) {
			this.grid = new GridLogic(this.cols);
			const result = [];
			[...items].sort((a, b) => a.weight > b.weight).forEach(item => {
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
					this.grid.h = this.additionalRow;
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

			const gridX = Math.floor(this.cols * (evt.clientX - rect.left) / this.$refs.container.clientWidth);
			const gridY = Math.floor((this.rows + addH) * (evt.clientY - rect.top) / this.$refs.container.clientHeight);
			if (this.x == gridX && this.y == gridY)
				return false;
			
			if (this.mode == MODE_IDLE) {
				if (this.additionalRow === null && this.y == this.rows-1 && gridY == this.rows) {
					this.additionalRow = this.grid.h;
					this.grid.h += 1;
				} else if (this.additionalRow !== null && gridY != this.rows - 1) {
					this.grid.h = this.additionalRow;
					this.additionalRow = null;
				}
			}
			
			this.x = gridX;
			this.y = gridY;

			return true;
		},
		_dragStart(evt) {
			if (evt.dataTransfer) {
				evt.dataTransfer.setDragImage(evt.target, -99999, -99999);
				evt.dataTransfer.dropEffect = 'move';
				evt.dataTransfer.effectAllowed = 'move';
			}
		},
		startMove(evt, item) {
			if (!this.active)
				return;
			this._dragStart(evt);
			this.mode = MODE_MOVE;
			this.updateCursor(evt);
			this.draggedItem = item;
			this.draggedOffset = [item.x - this.x, item.y - this.y];
		},
		startResize(evt, item) {
			if (!this.active)
				return;
			this._dragStart(evt);
			this.mode = MODE_RESIZE;
			this.draggedItem = item;
		},
		dragOver(evt) {
			if (!this.active)
				return this.dragCancel();
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
			this.mode = MODE_IDLE;
			this.positionUpdates = null;
			this.draggedOffset = [0,0],
			this.draggedItem = null;
		},
		dragEnd() {
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
		}
	},
	template: `
	<div
		ref="container"
		class="drop-grid position-relative h-0"
		:style="gridStyle"
		@touchmove="dragOver"
		@touchend="dragCancel"
		@dragover.prevent="dragOver"
		@drop="dragEnd"
		@mousemove="updateCursor"
		@mouseleave="mouseLeave">
		<grid-item
			v-for="item in (mode == 0 && active? placedItems_withPlaceholders : placedItems)"
			:key="item.id"
			:item="item"
			@start-move="startMove"
			@start-resize="startResize"
			@end-drag="dragCancel"
			@drop-drag="dragEnd"
			class="position-absolute"
			:active="active"
			:style="{
				top: 'calc(' + item.y + ' * var(--fhc-dg-row-height))',
				left: 'calc(' + item.x + ' * var(--fhc-dg-col-width))',
				width: 'calc(' + item.w + ' * var(--fhc-dg-col-width))',
				height: 'calc(' + item.h + ' * var(--fhc-dg-row-height))',
				paddingTop: 'var(--fhc-dg-item-padding-top)',
				paddingLeft: 'var(--fhc-dg-item-padding-horizontal)',
				paddingRight: 'var(--fhc-dg-item-padding-horizontal)'
			}">
			<template v-slot="item">
				<slot v-bind="item.data" v-bind="item" :x="item.x" :y="item.y" ></slot>
			</template>
		</grid-item>
		
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
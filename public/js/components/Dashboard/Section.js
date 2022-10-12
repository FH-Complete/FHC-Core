import DashboardItem from "./Item.js";
import CachedWidgetLoader from "../../composables/Dashboard/CachedWidgetLoader.js";

// TODO(chris): handle overflow (moving outside the box)
export default {
	data: () => ({
		gridWidth: 0,
		containerRect: {top:0,left:0},
		changeHeight: 1,
		movedObjects: [],
		editMode: 0,
		gridXLast: 0,
		gridYLast: 0,
	}),
	props: [
		"name",
		"widgets"
	],
	emits: [
		"widgetAdd",
		"widgetUpdate",
		"widgetRemove"
	],
	components: {
		DashboardItem
	},
	computed: {
		items() {
			this.widgets.forEach((item,i) => item.index = i);
			return this.widgets;
		},
		itemCoords() {
			if (!this.gridWidth)
				return [];
			let itemCoords = this.items.map(item => item.place[this.gridWidth] || this.createItemPlacement(item));
			// TODO(chris): verify positions & sizes
			let occupiers = [];
			let wrongPlacedItems = [];
			let gridWidth = this.gridWidth;
			this.items.forEach(item => {
				let x = item._x !== undefined ? item._x : itemCoords[item.index].x;
				let y = item._y !== undefined ? item._y : itemCoords[item.index].y;
				let w = item._w !== undefined ? item._w : itemCoords[item.index].w;
				let h = item._h !== undefined ? item._h : itemCoords[item.index].h;
				// TODO(chris): check with and height params here?
				for (var i = 0; i < w; i++) {
					for (var j = 0; j < h; j++) {
						var c = (y+j-1) * gridWidth + (x+i-1);
						// NOTE(chris): check for overlaping items
						if (occupiers[c] !== undefined) {
							//console.log('try to add ' + item.index + ' to ' + x + '/' + y + ', but ' + occupiers[c] + ' is already there');
							// NOTE(chris): remove possible other entries of this item
							for (var c2 = c; c2; c2--)
								if (occupiers[c2] == item.index)
									occupiers[c2] = undefined;
							wrongPlacedItems.push(item);
							return;
						}
						occupiers[c] = item.index;
					}
				}
			});
			wrongPlacedItems.forEach(item => {
				let w = item._w !== undefined ? item._w : itemCoords[item.index].w;
				let h = item._h !== undefined ? item._h : itemCoords[item.index].h;
				for (var c = 0; c < occupiers.length + gridWidth; c++) {
					if (occupiers[c] === undefined) {
						var occupied = false;
						for (var i = 0; i < w; i++) {
							for (var j = 0; j < h; j++) {
								if (occupiers[c + i + j * gridWidth] !== undefined) {
									i = w;
									occupied = true;
									break;
								}
							}
						}
						if (!occupied) {
							item.place[gridWidth].x = c%gridWidth + 1;
							item.place[gridWidth].y = Math.floor(c/gridWidth) + 1;
							for (var i = 0; i < w; i++) {
								for (var j = 0; j < h; j++) {
									occupiers[c + i + j * gridWidth] = item.index;
								}
							}
							return;
						}
					}
				}
			});
			return itemCoords;
		},
		gridHeight() {
			if (!this.gridWidth || !this.changeHeight)
				return 0;
			let minH = 0;
			this.itemCoords.forEach((item,i) => minH = Math.max(minH, (!this.editMode && this.items[i].hidden) ? 0 : item.y + item.h - 1));
			// TODO(chris): the extraline should only be present if all slots are occupied
			return minH + this.editMode;
		},
		gridOccupiers() {
			let occupiers = [];
			let gridWidth = this.gridWidth;
			this.items.forEach(item => {
				let x = item._x !== undefined ? item._x : this.itemCoords[item.index].x;
				let y = item._y !== undefined ? item._y : this.itemCoords[item.index].y;
				let w = item._w !== undefined ? item._w : this.itemCoords[item.index].w;
				let h = item._h !== undefined ? item._h : this.itemCoords[item.index].h;
				for (var i = 0; i < w; i++) {
					for (var j = 0; j < h; j++) {
						var c = (y+j-1) * gridWidth + (x+i-1);
						occupiers[c] = item.index;
					}
				}
			});
			return occupiers;
		}
	},
	methods: {
		addWidget(evt) {
			if (evt.target != this.$refs.container || !this.editMode)
				return;
			const rect = this.containerRect;
			const gridX = Math.floor(this.gridWidth * (evt.clientX - rect.left) / this.$refs.container.clientWidth) + 1;
			const gridY = Math.floor(this.gridHeight * (evt.clientY - rect.top) / this.$refs.container.clientHeight) + 1;
			if (this.gridOccupiers[gridY * this.gridWidth + gridX] === undefined) {
				let widget = { widget: 1, config: {}, place: {}, custom: 1 };
				widget.place[this.gridWidth] = {
					x: gridX,
					y: gridY,
					w: 1,
					h: 1
				};
				this.$emit('widgetAdd', this.name, widget);
			}
		},
		createItemPlacement(item) {
			// TODO(chris): create correct default placement if it is not there
			item.place[this.gridWidth] = {x:1,y:1,w:1,h:1};
			/*var freeList = [], nextId = 0;
			this.items.forEach(item => {
				if (!item.place[this.gridWidth]) {
					if (!this.gridWidth) {
						item.place[this.gridWidth] = {x:1,y:nextId++,w:1,h:1};
					} else {
						// TODO(chris): IMPLEMENT widths & heights
						if (freeList[nextId])
							while (freeList[++nextId]);
						freeList[nextId] = 1;
						item.place[this.gridWidth] = {x:(nextId%this.gridWidth)+1,y:Math.floor(nextId/this.gridWidth)+1,w:1,h:1};
					}
				}
			});*/
			return item.place[this.gridWidth];
		},
		startDrag(evt, item) {
			this.gridXLast = -1;
			this.gridYLast = -1;
			item._x = this.itemCoords[item.index].x;
			item._y = this.itemCoords[item.index].y;

			evt.dataTransfer.dropEffect = 'move';
			evt.dataTransfer.effectAllowed = 'move';
			evt.dataTransfer.setData('itemAction', 'm');
			evt.dataTransfer.setData('itemId', item.index);
			evt.dataTransfer.setData('itemW', this.itemCoords[item.index].w);
			evt.dataTransfer.setData('itemH', this.itemCoords[item.index].h);
		},
		startResize(evt, item) {
			this.gridXLast = -1;
			this.gridYLast = -1;
			item._w = this.itemCoords[item.index].w;
			item._h = this.itemCoords[item.index].h;

			evt.dataTransfer.setDragImage(evt.target, -99999, -99999);
			evt.dataTransfer.dropEffect = 'move';
			evt.dataTransfer.effectAllowed = 'move';
			evt.dataTransfer.setData('itemAction', 'r');
			evt.dataTransfer.setData('itemId', item.index);
			evt.dataTransfer.setData('itemX', this.itemCoords[item.index].x);
			evt.dataTransfer.setData('itemY', this.itemCoords[item.index].y);
		},
		occupyFields(id, x, y, w, h) {
			var c;
			while ((c = this.movedObjects.pop())) {
				if (this.items[c]._y !== undefined) {
					this.items[c].place[this.gridWidth].y = this.items[c]._y;
					this.items[c]._y = undefined;
				}
			}
			var move = {};
			move[id] = this.items[id];
			this.getOccupiedItems(x,y,w,h,move);
			h = y + h;
			y = 0;
			for (x in move) {
				if (x != id) {
					c = move[x]._y !== undefined ? move[x]._y : this.itemCoords[x].y;
					if (c < h)
						y = Math.max(h-c, y);
				}
			}
			for (x in move) {
				if (x != id) {
					this.movedObjects.push(x);
					if (move[x]._y === undefined) {
						move[x]._y = this.itemCoords[x].y;
					}
					move[x].place[this.gridWidth].y = move[x]._y + y;
				}
			}
		},
		getOccupiedItems(x, y, w, h, move) {
			var i, j, c;
			for (i = 0; i < w; i++) {
				for (j = 0; j < h; j++) {
					c = (y+j-1) * this.gridWidth + (x+i-1);
					if (this.gridOccupiers[c] !== undefined && !move[this.gridOccupiers[c]]) {
						move[this.gridOccupiers[c]] = this.items[this.gridOccupiers[c]];
						c = this.itemCoords[this.gridOccupiers[c]];
						this.getOccupiedItems(c.x, c.y + 1, c.w, c.h, move);
					}
				}
			}
		},
		onDragOver(evt) {
			let id, x, y, w, h;
			const action = evt.dataTransfer.getData('itemAction');
			const rect = this.containerRect;
			const gridX = Math.floor(this.gridWidth * (evt.clientX - rect.left) / this.$refs.container.clientWidth);
			const gridY = Math.floor(this.gridHeight * (evt.clientY - rect.top) / this.$refs.container.clientHeight);

			if (this.gridXLast == gridX && this.gridYLast == gridY)
				return;
			this.gridXLast = gridX;
			this.gridYLast = gridY;

			if (action == 'm') {
				x = gridX + 1;
				y = gridY + 1;
				w = parseInt(evt.dataTransfer.getData('itemW'));
				h = parseInt(evt.dataTransfer.getData('itemH'));

				if (x + w > this.gridWidth + 1)
					x = this.gridWidth + 1 - w;

				id = evt.dataTransfer.getData('itemID');
				this.occupyFields(id, x, y, w, h);

				this.itemCoords[id].x = x;
				this.itemCoords[id].y = y;
			} else if (action == 'r') {
				x = parseInt(evt.dataTransfer.getData('itemX'));
				y = parseInt(evt.dataTransfer.getData('itemY'));
				w = gridX + 2 - x;
				h = gridY + 2 - y;
				w = Math.max(1, w);
				h = Math.max(1, h);

				if (x + w > this.gridWidth + 1)
					w = this.gridWidth + 1 - x;

				id = evt.dataTransfer.getData('itemID');
				let widget = CachedWidgetLoader.getWidget(this.items[id].widget);
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

				this.occupyFields(id, x, y, w, h);
				
				this.itemCoords[id].w = w;
				this.itemCoords[id].h = h;
			}
		},
		onDrop(evt) {
			let id = 0;
			let update = {};
			while ((id = this.movedObjects.pop())) {
				if (this.items[id]._y !== undefined) {
					if (this.itemCoords[id].y != this.items[id]._y) {
						update[this.items[id].id] = {place:{}};
						update[this.items[id].id].place[this.gridWidth] = {y:this.itemCoords[id].y};
					}
					this.items[id]._y = undefined;
				}
			}
			
			id = evt.dataTransfer.getData('itemId');
			
			const action = evt.dataTransfer.getData('itemAction');
			update[this.items[id].id] = {place:{}};
			update[this.items[id].id].place[this.gridWidth] = {};

			if (action == 'm') {
				if (this.items[id]._x !== undefined) {
					if (this.itemCoords[id].x != this.items[id]._x) {
						update[this.items[id].id].place[this.gridWidth].x = this.itemCoords[id].x;
					}
					this.items[id]._x = undefined;
				}
				if (this.items[id]._y !== undefined) {
					if (this.itemCoords[id].y != this.items[id]._y) {
						update[this.items[id].id].place[this.gridWidth].y = this.itemCoords[id].y;
					}
					this.items[id]._y = undefined;
				}
			} else if (action == 'r') {
				update[this.items[id].id].place[this.gridWidth].w = this.itemCoords[id].w;
				update[this.items[id].id].place[this.gridWidth].h = this.itemCoords[id].h;
			}

			if (update[this.items[id].id].place[this.gridWidth].x === undefined && 
				update[this.items[id].id].place[this.gridWidth].y === undefined && 
				update[this.items[id].id].place[this.gridWidth].w === undefined && 
				update[this.items[id].id].place[this.gridWidth].h === undefined) {
				delete update[this.items[id].id].place[this.gridWidth];
			}

			this.updatePreset(update);

			// TODO(chris): find better way to trigger change for gridHeight
			this.changeHeight++
		},
		removeWidget(item, revert) {
			if (item.custom) {
				if (confirm('Are you sure you want to delete this widget?')) {
					this.$emit('widgetRemove', this.name, item.id);
				}
			} else {
				let update = {};
				update[item.id] = { hidden: !revert };
				this.updatePreset(update);
			}
		},
		saveConfig(config, item) {
			let payload = {};
			payload[item.id] = { config };console.log(payload);
			this.updatePreset(payload);
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
		self.gridWidth = window.getComputedStyle(cont).getPropertyValue('grid-template-columns').split(" ").length;
		self.containerRect = cont.getBoundingClientRect();
		
		window.addEventListener('resize', () => {
			for (const child of cont.children) {
				child.style.display = 'none';
			}
			self.gridWidth = window.getComputedStyle(cont).getPropertyValue('grid-template-columns').split(" ").length;
			self.containerRect = cont.getBoundingClientRect();
			for (const child of cont.children) {
				child.style.display = '';
			}
		});
	},
	template: `<div class="dashboard-section">
		<h3 class="d-flex">
			<span class="col">{{name}}</span>
			<button class="col-auto btn" @click.prevent="editMode = editMode ? 0 : 1"><i class="fa-solid fa-gear"></i></button>
		</h3>
		<div class="position-relative" :style="'height:0;padding-bottom:' + (gridHeight * 100/gridWidth) + '%'">
			<div ref="container" 
				class="position-absolute top-0 left-0 w-100 h-100 draganddropcontainer" 
				:style="'display:grid;grid-template-rows:repeat('+gridHeight+',1fr)'" 
				@click="addWidget($event)" 
				@drop="onDrop($event, 1)" 
				@dragover.prevent="onDragOver" 
				@dragenter.prevent>
				
				<dashboard-item 
					v-for="item in items" 
					:key="item.id" 
					:id="item.widget" 
					:config="item.config" 
					:custom="item.custom" 
					:hidden="item.hidden" 
					:editMode="editMode" 
					:x="itemCoords[item.index] ? itemCoords[item.index].x : -1" 
					:y="itemCoords[item.index] ? itemCoords[item.index].y : -1" 
					:style="itemCoords[item.index] ? {'grid-column-start':itemCoords[item.index].x,'grid-column-end':itemCoords[item.index].x+itemCoords[item.index].w,'grid-row-start':itemCoords[item.index].y,'grid-row-end':itemCoords[item.index].y+itemCoords[item.index].h} : {}" 
					:width="itemCoords[item.index] ? itemCoords[item.index].w : 0" 
					:height="itemCoords[item.index] ? itemCoords[item.index].h : 0" 
					@dragstart="startDrag($event, item)" 
					@resizestart="startResize($event, item)" 
					@change="saveConfig($event, item)"
					@remove="removeWidget(item, $event)">
				</dashboard-item>

			</div>
		</div>
	</div>`
}
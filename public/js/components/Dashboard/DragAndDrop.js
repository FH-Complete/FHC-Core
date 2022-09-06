export default {
	data: () => ({
		isMounted: 0,
		movedObjects: [],
		tmpStyle: {
			display: 'none',
			background: 'gray',
			'grid-column-start': 0,
			'grid-column-end': 0,
			'grid-row-start': 0,
			'grid-row-end': 0,
		}
	}),
	props: [
		"width",
		"height",
		"items"
	],
	computed: {
		gridWidth() {
			if(!this.isMounted)
				return 0;
			return window.getComputedStyle(this.$refs.container).getPropertyValue('grid-template-columns').split(" ").length;
		},
		gridHeight() {
			if (!this.gridWidth)
				return 0;
			let minH = 0;
			this.items.forEach(item => {
				// TODO(chris): item change is not detected?
				minH = Math.max(minH, item.y + item.h - 1);
			});
			return Math.max(1, minH);
		},
		gridOccupiers() {
			let occupiers = [];
			let gridWidth = this.gridWidth;
			this.items.forEach(item => {
				for (var i = 0; i < item.w; i++)
					for (var j = 0; j < item.h; j++)
						occupiers[(item.y+j) * gridWidth + (item.x+i)] = item.id;
			});
			return occupiers;
		}
	},
	methods: {
		startDrag(evt, item) {
			evt.dataTransfer.dropEffect = 'move';
			evt.dataTransfer.effectAllowed = 'move';
			evt.dataTransfer.setData('itemId', item.id)
			evt.dataTransfer.setData('itemW', item.w)
			evt.dataTransfer.setData('itemH', item.h)
			console.log(evt.target.style.display);
		},
		moveItem(item) {
			// TODO(chris): IMPLEMENT
			if (!item._x)
				item._x = item.x;
			item.x++;
		},
		moveItemBack(item) {
			item.x = item._x;
		},
		onDragOver(evt) {
			this.tmpStyle.display = 'block';

			let x = Math.floor(this.gridWidth * (evt.layerX / this.$refs.container.clientWidth)) + 1;
			let y = Math.floor(this.gridHeight * (evt.layerY / this.$refs.container.clientHeight)) + 1;
			let w = parseInt(evt.dataTransfer.getData('itemW'));
			let h = parseInt(evt.dataTransfer.getData('itemH'));

			while (x + w > this.gridWidth + 1)
				x--;

			// TODO(chris): start
			let id = 0;
			while (id = this.movedObjects.pop())
				/*this.items[id].c = this.items[id]._c;*/
				this.moveItemBack(this.items[id]);
			for (var i = 0; i < w; i++) {
				for (var j = 0; j < h; j++) {
					let id = (y+j) * this.gridWidth + (x+i);
					if (this.gridOccupiers[id] && this.gridOccupiers[id] != evt.dataTransfer.getData('itemID')) {
						
						/*if (!this.items[this.gridOccupiers[id]]._c)
							this.items[this.gridOccupiers[id]]._c = this.items[this.gridOccupiers[id]].c;
						this.items[this.gridOccupiers[id]].c = 'grey';*/
						this.moveItem(this.items[this.gridOccupiers[id]]);
						
						this.movedObjects.push(this.gridOccupiers[id]);
					}
				}
			}
			// TODO(chris): end

			this.tmpStyle['grid-column-start'] = x;
			this.tmpStyle['grid-column-end'] = x + w;
			this.tmpStyle['grid-row-start'] = y;
			this.tmpStyle['grid-row-end'] = y + h;
		},
		onDrop(evt, list) {
			this.tmpStyle.display = 'none';

			let id = evt.dataTransfer.getData('itemId');
			let x = Math.floor(this.gridWidth * (evt.layerX / this.$refs.container.clientWidth)) + 1;
			let y = Math.floor(this.gridHeight * (evt.layerY / this.$refs.container.clientHeight)) + 1;
			let w = parseInt(evt.dataTransfer.getData('itemW'));

			while (x + w > this.gridWidth + 1)
				x--;

			this.items.forEach(item => {
				if (id == item.id) {
					item.x = x;
					item.y = y;
					console.log(item, id);
				}
			});
			// TODO(chris): find better way to trigger change for gridHeight
			this.isMounted++
		}
	},
	watchers: {
		items() {
			console.log(this.items);
		}
	},
	mounted() {
		this.isMounted = 1;
		window.addEventListener('resize', e => { this.isMounted ? this.isMounted++ : 0 })
	},
	template: `<div class="drag-and-drop position-relative" :style="'height:0;padding-bottom:' + (gridHeight * 100/gridWidth) + '%'">
		<div ref="container" class="position-absolute top-0 left-0 w-100 h-100 draganddropcontainer" :style="'display:grid;grid-template-rows:repeat('+gridHeight+',1fr)'" @drop="onDrop($event, 1)" @dragover.prevent="onDragOver" @dragenter.prevent>
			<div v-for="item in items" :key="item.id" :style="{'grid-column-start':item.x,'grid-column-end':item.x+item.w,'grid-row-start':item.y,'grid-row-end':item.y+item.h,background:item.c}" @dragstart="startDrag($event, item)" draggable="true">
			</div>{{gridWidth2}}
			<div :style="tmpStyle"></div>
		</div>
	</div>`
}

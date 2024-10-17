// TODO(chris): Comments

const DIR_UP = 0;
const DIR_LEFT = 1;
const DIR_RIGHT = 2;
const DIR_DOWN = 3;

class GridLogic {
	constructor(w) {
		if (w.w) {
			this.w = w.w;
			this.h = w.h;
			this.data = [...w.data];
			this.grid = [...w.grid];
		} else {
			this.w = w;
			this.h = 1;
			this.data = [];
			this.grid = [];
		}
	}
	isFreeSlot(x, y) {
		const i = y*this.w + x;
		return !this.grid[i] && this.grid[i] !== 0;
	}
	add(item, prefer) {
		let occupiers = this.getItemsInFrame(item.frame);

		if (!occupiers.length) {
			item.frame.forEach(f => this.grid[f] = item.index);
			this.data[item.index] = item;
			this.h = Math.max(this.h, item.y + item.h);
			return [];
		} else {
			const intermGrid = new GridLogic(this);
			
			item.frame.forEach(f => intermGrid.grid[f] = -1);

			const possiblities = intermGrid.tryMoving(occupiers, prefer);
			if (possiblities.length) {
				const bestOption = possiblities.sort((a,b) => {
					if (a.cost === undefined)
						a.cost = a.moves.reduce((a, v) => a * v.cost, 1);
					if (b.cost === undefined)
						b.cost = b.moves.reduce((a, v) => a * v.cost, 1);
					return a.cost > b.cost;
				});
				const result = [];
				bestOption[0].moves.forEach(move => {
					const currItem = {...this.data[move.index]};
					currItem.x += move.x;
					currItem.y += move.y;
					currItem.frame = this.getItemFrame(currItem);
					this.h = Math.max(this.h, currItem.y + currItem.h);
					this.remove(currItem);
					currItem.frame.forEach(f => this.grid[f] = currItem.index);
					this.data[move.index] = currItem;
					result[move.index] = {
						index: currItem.index,
						x: currItem.x,
						y: currItem.y
					};
				});
				item.frame.forEach(f => this.grid[f] = item.index);
				this.data[item.index] = item;
				return result;
			} else {
				console.error('FATAL', "can't arrange item on grid");
			}
		}
	}
	move(item, x, y) {
		if (item.x == x && item.y == y)
			return [];
		this.remove(item);

		let prefer = undefined;
		if (item.x == x) {
			if (y-item.y > 0)
				prefer = DIR_UP;
			else
				prefer = DIR_DOWN;
		} else if (item.y == y) {
			if (x-item.x > 0)
				prefer = DIR_LEFT;
			else
				prefer = DIR_RIGHT;
		}

		const currItem = {...item};
		currItem.x = x;
		currItem.y = y;
		currItem.frame = this.getItemFrame(currItem);
		
		const updates = this.add(currItem, prefer);
		updates[item.index] = {index: item.index, x, y};
		return updates;
	}
	resize(item, w, h) {
		if (item.w == w && item.h == h)
			return [];
		this.remove(item);

		const currItem = {...item};
		currItem.w = w;
		currItem.h = h;
		currItem.frame = this.getItemFrame(currItem);
		
		const updates = this.add(currItem);
		updates[item.index] = {index: item.index, w, h};
		return updates;
	}
	tryMoving(index, prefer) {
		if (Array.isArray(index)) {
			index.forEach(i => this.remove({index:i}));
			let possiblities = [{grid: this, moves: []}];
			index.forEach(i => {
				let newPoss = [];
				possiblities.forEach(possiblity => {
					possiblity.grid.tryMoving(i, prefer).forEach(p => {
						possiblity.moves
						p.moves = [...p.moves, ...possiblity.moves];
						newPoss.push(p)
					});
				});
				possiblities = newPoss;
			});
			return possiblities;
		}
		const directions = [DIR_UP, DIR_DOWN, DIR_LEFT, DIR_RIGHT];

		this.remove({index});

		const weight = 1 + .2*(this.data[index].weight || 0);

		return directions.reduce((result, dir) => {
			let res = this.tryMovingInDirection(dir, index, 1, (prefer === dir ? .5 : 1 + dir*.1) * weight);
			if (!res)
				return result;
			return [...result, ...res];
		}, []).filter(p => p);
	}
	tryMovingInDirection(dir, index, amount, cost) {
		const move = {index, x:0, y: 0, cost: cost};
		let targetframe;
		switch(dir) {
			case DIR_UP:
				if (this.data[index].y - amount < 0)
					return false;
				targetframe = this.data[index].frame.map(i => i-this.w*amount);
				move.y = -amount;
				break;
			case DIR_DOWN:
				if (this.data[index].y + this.data[index].h + amount > this.h)
					cost += .4;
				targetframe = this.data[index].frame.map(i => i+this.w*amount);
				move.y = amount;
				break;
			case DIR_LEFT:
				if (this.data[index].x - amount < 0)
					return false;
				targetframe = this.data[index].frame.map(i => i-amount);
				move.x = -amount;
				break;
			case DIR_RIGHT:
				if (this.data[index].x + this.data[index].w + amount > this.w)
					return false;
				targetframe = this.data[index].frame.map(i => i+amount);
				move.x = amount;
				break;
		}

		const occupiers = this.getItemsInFrame(targetframe);
		if (occupiers.includes(-1)) {
			return this.tryMovingInDirection(dir, index, amount+1, cost);
		}
		
		const intermGrid = new GridLogic(this);
		targetframe.forEach(f => intermGrid.grid[f] = -1);
		
		if (!occupiers.length) {
			return [{grid: intermGrid, moves: [move]}];
		}
		const possiblities = intermGrid.tryMoving(occupiers).map(possiblity => possiblity.moves.unshift(move) && possiblity);
		return possiblities.length ? possiblities : false;
	}
	clearWeights() {
		this.data.forEach(item => item.weight = undefined);
	}
	getItemsInFrame(frame) {
		return frame.map(i => this.grid[i]).filter((v,i,a) => (v || v === 0) && a.indexOf(v) === i);
	}
	remove(item) {
		this.grid = this.grid.map(i => i != item.index ? i : undefined);
	}
	getItemFrame(item) {
		const frame = [];
		for (let i = 0; i < item.w; i++)
			for (let j = 0; j < item.h; j++)
				frame.push(i + item.x + (j + item.y) * this.w);
		return frame;
	}
	debug() {
		return this.grid;
	}
}

export default GridLogic;
export default {
	name: 'CalendarSlider',
	inject: {
		time: {
			from: "sliderTime",
			default: ".3s"
		},
		onVisibleDatesChanged: {
			from: "calendarVisibleDatesChanged",
			default: null
		}
	},
	emits: [
		'slid'
	],
	data() {
		return {
			target: 0,
			extrasAfter: 0,
			extrasBefore: 0,
			running: false,
			promiseResolve: null,
			intersectionObserver: null,
			mutationObserver: null,
			visibleGrids: Vue.markRaw(new Set()),
			visibilityUpdateScheduled: false,
			lastVisibleDatesKey: null
		}
	},
	computed: {
		itemsAfter() {
			return [...Array(this.extrasAfter)].map((i, k) => 1+k);
		},
		itemsBefore() {
			return [...Array(this.extrasBefore)].map((i, k) => k-this.extrasBefore);
		},
		styleSlider() {
			const style = {
				position: 'absolute',
				top: 0,
				left: 0,
				width: '100%',
				height: '100%'
			};
			if (this.running) {
				style.left = (-this.target * 100) + '%';
				style.transition = 'left ' + this.time + ' ease-in-out';
			}
			return style;
		},
		styleBefore() {
			return {
				position: 'absolute',
				top: 0,
				height: '100%',
				display: 'flex',
				right: '100%',
				width: (this.extrasBefore * 100) + '%'
			};
		},
		styleAfter() {
			return {
				position: 'absolute',
				top: 0,
				height: '100%',
				display: 'flex',
				left: '100%',
				width: (this.extrasAfter * 100) + '%'
			};
		}
	},
	methods: {
		startVisibilityTracking() {
			if (!this.onVisibleDatesChanged || typeof IntersectionObserver === 'undefined')
				return;

			this.intersectionObserver = Vue.markRaw(new IntersectionObserver(entries => {
				let changed = false;

				entries.forEach(entry => {
					if (entry.isIntersecting && entry.intersectionRatio > 0) {
						if (!this.visibleGrids.has(entry.target)) {
							this.visibleGrids.add(entry.target);
							changed = true;
						}
					} else if (this.visibleGrids.delete(entry.target)) {
						changed = true;
					}
				});

				if (changed)
					this.scheduleVisibleDatesUpdate();
			}, {
				root: this.$el,
				threshold: 0
			}));

			this.mutationObserver = Vue.markRaw(new MutationObserver(mutations => {
				let changed = false;

				mutations.forEach(mutation => {
					if (mutation.type === 'attributes') {
						if (this.visibleGrids.has(this.getContainingGrid(mutation.target)))
							changed = true;
						return;
					}

					mutation.addedNodes.forEach(node => this.observeGrids(node));
					mutation.removedNodes.forEach(node => {
						this.getGrids(node).forEach(grid => {
							this.intersectionObserver.unobserve(grid);
							if (this.visibleGrids.delete(grid))
								changed = true;
						});
					});

					if (this.visibleGrids.has(this.getContainingGrid(mutation.target)))
						changed = true;
				});

				if (changed)
				{
					console.log('scheduleVisibleDatesUpdate');
					this.scheduleVisibleDatesUpdate();
				}
			}));

			this.observeGrids(this.$el);
			this.mutationObserver.observe(this.$el, {
				subtree: true,
				childList: true,
				attributes: true,
				attributeFilter: ['data-visible-start', 'data-visible-end']
			});
		},
		getGrids(node) {
			if (!(node instanceof Element))
				return [];

			const grids = node.matches('.fhc-calendar-base-grid') ? [node] : [];
			return grids.concat([...node.querySelectorAll('.fhc-calendar-base-grid')]);
		},
		observeGrids(node) {
			this.getGrids(node).forEach(grid => {
				this.intersectionObserver.observe(grid);
			});
		},
		getContainingGrid(node) {
			if (!(node instanceof Element))
				return null;

			return node.matches('.fhc-calendar-base-grid')
				? node
				: node.closest('.fhc-calendar-base-grid');
		},
		scheduleVisibleDatesUpdate() {
			if (this.visibilityUpdateScheduled)
				return;

			this.visibilityUpdateScheduled = true;
			this.$nextTick(() => {
				this.visibilityUpdateScheduled = false;
				this.emitVisibleDates();
			});
		},
		emitVisibleDates() {
			const datesByKey = new Map();

			this.visibleGrids.forEach(grid => {
				const dates = this.getGridVisibleDates(grid);
				if (dates)
					datesByKey.set(dates.start + '/' + dates.end, dates);
			});

			const dates = [...datesByKey.values()]
				.sort((a, b) => a.start.localeCompare(b.start));
			if (!dates.length)
				return;

			const key = dates.map(date => date.start + '/' + date.end).join('|');

			if (key === this.lastVisibleDatesKey)
				return;

			this.lastVisibleDatesKey = key;
			this.onVisibleDatesChanged(dates);
		},
		getGridVisibleDates(grid) {
			if (!grid.isConnected)
				return null;

			let start = null;
			let end = null;
			grid.querySelectorAll('.fhc-calendar-base-grid-line').forEach(line => {
				const lineStart = line.dataset.visibleStart;
				const lineEnd = line.dataset.visibleEnd;
				if (!lineStart || !lineEnd)
					return;

				if (!start || lineStart.localeCompare(start) < 0)
					start = lineStart;
				if (!end || lineEnd.localeCompare(end) > 0)
					end = lineEnd;
			});

			return start && end ? { start, end } : null;
		},
		stopVisibilityTracking() {
			this.intersectionObserver?.disconnect();
			this.mutationObserver?.disconnect();
			this.intersectionObserver = null;
			this.mutationObserver = null;
			this.visibleGrids.clear();
			this.onVisibleDatesChanged?.(null);
		},
		prevPage() {
			return this.slidePages(-1);
		},
		nextPage() {
			return this.slidePages(1);
		},
		slidePages(dir) {
			return new Promise(resolve => {
				this.promiseResolve = resolve;
				this.running = true;
				const newTarget = this.target + dir;
				if (newTarget > 0) {
					if (this.extrasAfter < newTarget)
						this.extrasAfter = newTarget;
				} else if (newTarget < 0) {
					if (-this.extrasBefore > newTarget)
						this.extrasBefore = -newTarget;
				}
				this.target = newTarget;
			});
		},
		endSlide() {
			if (this.promiseResolve) {
				this.promiseResolve(this.target);
				this.promiseResolve = null;
			}
			this.$emit('slid', this.target);
			this.running = false;
			this.target = 0;
			this.extrasAfter = this.extrasBefore = 0;
		}
	},
	mounted() {
		this.startVisibilityTracking();
	},
	beforeUnmount() {
		this.stopVisibilityTracking();
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-slider h-100"
		style="position:relative;overflow:hidden"
	>
		<div
			:style="styleSlider"
			@transitionend="endSlide"
		>
			<div :style="styleBefore">
				<div
					v-for="i in itemsBefore"
					:key="i"
					style="height:100%;width:100%"
				>
					<slot :offset="i" />
				</div>
			</div>
			<div :style="styleAfter">
				<div
					v-for="i in itemsAfter"
					:key="i"
					style="height:100%;width:100%"
				>
					<slot :offset="i" />
				</div>
			</div>
			<div style="height:100%;width:100%">
				<slot :offset="0" />
			</div>
		</div>
	</div>
	`
}

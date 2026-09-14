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
			gridLineResizeObserver: null,
			visibleGridLines: Vue.markRaw(new Set()),
			gridLineHeights: Vue.markRaw(new Map()),
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

			// if (
			// 	this.$el.closest('.fhc-calendar-mode-range')
			// 	&& typeof ResizeObserver !== 'undefined'
			// )
			// {
			// 	this.gridLineResizeObserver = Vue.markRaw(new ResizeObserver(entries => {
			// 		entries.forEach(entry => this.preserveGridLineHeight(entry.target));
			// 	}));
			// }

			this.intersectionObserver = Vue.markRaw(new IntersectionObserver(entries => {
				let changed = false;

				entries.forEach(entry => {
					if (entry.isIntersecting && entry.intersectionRatio > 0) {
						if (!this.visibleGridLines.has(entry.target)) {
							this.visibleGridLines.add(entry.target);
							changed = true;
						}
					} else if (this.visibleGridLines.delete(entry.target)) {
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
						// this.applyPreservedGridLineHeight(mutation.target);
						// this.gridLineResizeObserver?.observe(mutation.target);
						if (this.visibleGridLines.has(mutation.target))
							changed = true;
						return;
					}

					mutation.addedNodes.forEach(node => this.observeGridLines(node));
					mutation.removedNodes.forEach(node => {
						this.getGridLines(node).forEach(line => {
							this.intersectionObserver.unobserve(line);
							// this.gridLineResizeObserver?.unobserve(line);
							if (this.visibleGridLines.delete(line))
								changed = true;
						});
					});
				});

				if (changed)
					this.scheduleVisibleDatesUpdate();
			}));

			this.observeGridLines(this.$el);
			this.mutationObserver.observe(this.$el, {
				subtree: true,
				childList: true,
				attributes: true,
				attributeFilter: ['data-visible-start', 'data-visible-end']
			});
		},
		getGridLines(node) {
			if (!(node instanceof Element))
				return [];

			const lines = node.matches('.fhc-calendar-base-grid-line') ? [node] : [];
			return lines.concat([...node.querySelectorAll('.fhc-calendar-base-grid-line')]);
		},
		observeGridLines(node) {
			this.getGridLines(node).forEach(line => {
				this.applyPreservedGridLineHeight(line);
				this.intersectionObserver.observe(line);
				this.gridLineResizeObserver?.observe(line);
			});
		},
		getGridLineKey(line) {
			return;
			const start = line.dataset.visibleStart;
			const end = line.dataset.visibleEnd;

			return start && end ? start + '/' + end : null;
		},
		applyPreservedGridLineHeight(line) {
			return;
			if (!this.gridLineResizeObserver)
				return;

			const key = this.getGridLineKey(line);
			const height = key ? this.gridLineHeights.get(key) : null;
			line.style.minHeight = height ? height + 'px' : '';
		},
		preserveGridLineHeight(line) {
			return;
			const key = this.getGridLineKey(line);
			if (!key)
				return;

			const height = Math.ceil(line.getBoundingClientRect().height);
			const preservedHeight = this.gridLineHeights.get(key) || 0;
			if (height > preservedHeight)
				this.gridLineHeights.set(key, height);

			const minHeight = Math.max(height, preservedHeight);
			if (minHeight && line.style.minHeight !== minHeight + 'px')
				line.style.minHeight = minHeight + 'px';
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

			this.visibleGridLines.forEach(line => {
				if (!line.isConnected)
					return;

				const start = line.dataset.visibleStart;
				const end = line.dataset.visibleEnd;
				if (start && end)
					datesByKey.set(start + '/' + end, { start, end });
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
		stopVisibilityTracking() {
			this.intersectionObserver?.disconnect();
			this.mutationObserver?.disconnect();
			this.gridLineResizeObserver?.disconnect();
			this.intersectionObserver = null;
			this.mutationObserver = null;
			this.gridLineResizeObserver = null;
			this.visibleGridLines.clear();
			this.gridLineHeights.clear();
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

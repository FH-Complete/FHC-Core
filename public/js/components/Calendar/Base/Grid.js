import GridLine from './Grid/Line.js';
import GridLineEvent from './Grid/Line/Event.js';

import CalDnd from '../../../directives/Calendar/DragAndDrop.js';

export default {
	name: "CalendarGrid",
	components: {
		GridLine,
		GridLineEvent
	},
	directives: {
		CalDnd
	},
	inject: {
		originalEvents: "events",
		originalBackgrounds: "backgrounds",
		dropAllowed: "dropAllowed"
	},
	provide() {
		return {
			flipAxis: Vue.computed(() => this.flipAxis),
			axisRow: Vue.computed(() => this.axisRow)
		};
	},
	props: {
		axisMain: {
			type: Array,
			required: true,
			validator(value) {
				return value.every(item => item instanceof luxon.DateTime);
			}
		},
		axisParts: {
			type: Array,
			required: true,
			validator(value) {
				return value.every(item => 
					item instanceof luxon.Duration
					|| Number.isInteger(item)
					|| (
						(
							item.start instanceof luxon.Duration
							|| Number.isInteger(item.start)
						) && (
							item.end instanceof luxon.Duration
							|| Number.isInteger(item.end)
						)
					)
				);
			}
		},
		flipAxis: Boolean,
		allDayEvents: Boolean,
		axisMainCollapsible: Boolean,
		snapToGrid: Boolean
	},
	data() {
		return {
			dragging: false,
			resizeObserver: null,
			mutationObserver: null,
			userScroll: true
		};
	},
	computed: {
		axisRow() {
			return this.flipAxis ? 'column' : 'row';
		},
		axisCol() {
			return this.flipAxis ? 'row' : 'column';
		},
		axisPartsWithBreaks() {
			return this.axisParts.reduce((res, tu, index) => {
				const start = tu.start || tu;
				const end = tu.end;

				if (res.length) {
					const lastTuEnd = res.pop();
					if (Array.isArray(lastTuEnd)) {
						res.push({
							start: lastTuEnd[0],
							end: start,
							index: lastTuEnd[1]
						});
					} else if (lastTuEnd != start) {
						// add pause
						res.push({
							start: lastTuEnd,
							end: start
						});
					}
				}

				if (!end) {
					res.push([start, index]);
				} else {
					res.push({
						start,
						end,
						index
					});
					res.push(end);
				}
				return res;
			}, []).slice(0, -1);
		},
		axisPartsSave() {
			if (!this.axisParts[this.axisParts.length - 1].end)
				return this.axisParts.slice(0, -1);
			return this.axisParts;
		},
		start() {
			return this.axisPartsWithBreaks[0].start;
		},
		end() {
			return this.axisPartsWithBreaks[this.axisPartsWithBreaks.length - 1].end;
		},
		ends() {
			const ends = [];
			const partsEnds = this.axisPartsWithBreaks
				.filter(p => p.index !== undefined)
				.map(p => p.end);
			for (var date of this.axisMain)
				for (var part of partsEnds)
					ends.push(date.plus(part));
			
			return ends;
		},
		axisMainBorders() {
			const lastInMainAxis = this.axisMain[this.axisMain.length - 1];
			const extraLength = this.end;

			return [...this.axisMain, lastInMainAxis.plus(extraLength)];
		},
		eventsAllDay() {
			if (!this.allDayEvents)
				return [];
			return this.mapIntoMainAxis(this.originalEvents.filter(event => event.orig.allDayEvent));
		},
		eventsNormal() {
			if (!this.allDayEvents)
				return this.events;
			return this.mapIntoMainAxis(this.originalEvents.filter(event => !event.orig.allDayEvent));
		},
		events() {
			return this.mapIntoMainAxis(this.originalEvents);
		},
		backgrounds() {
			return this.mapIntoMainAxis(this.originalBackgrounds);
		},
		hasValidEvents() {
			return this.events.find(e => e.length);
		},
		styleGridCols() {
			let cols = 'repeat(' + this.axisMain.length + ', 1fr)';
			if (this.axisMainCollapsible) {
				if (this.hasValidEvents)
					cols = this.events
						.map(e => e.length
							? '1fr'
							: 'var(--fhc-calendar-axis-collapsible, .5fr)')
						.join(' ');
			}
			return cols;
		},
		styleGridRows() {
			const gridlines = {};

			this.axisPartsWithBreaks.forEach(part => {
				let ts = part.start.toMillis();
				if (!gridlines[ts])
					gridlines[ts] = ['t_' + ts];
				if (part.index !== undefined)
					gridlines[ts].push('ps_' + part.index);
				ts = part.end.toMillis();
				if (!gridlines[ts])
					gridlines[ts] = ['t_' + ts];
				if (part.index !== undefined)
					gridlines[ts].push('pe_' + part.index);
			});

			this.eventsNormal.forEach((events, mainIndex) => {
				let day = this.axisMain[mainIndex];
				events.forEach(event => {
					if (!event.startsHere && !event.endsHere)
						return;

					if (event.startsHere) {
						let ts = event.start.diff(day).toMillis();
						if (!gridlines[ts])
							gridlines[ts] = ['t_' + ts, 'e_' + ts];
					}
					if (event.endsHere) {
						let ts = event.end.diff(day).toMillis();
						if (!gridlines[ts])
							gridlines[ts] = ['t_' + ts, 'e_' + ts];
					}
				});
			});

			return Object.keys(gridlines).sort((a,b) => parseInt(a)-parseInt(b)).map((start, i, keys) => {
				let end = keys[i + 1];
				if (!end) {
					gridlines[start].push('end');
					return '[' + gridlines[start].join(' ') + ']';
				}
				return '[' + gridlines[start].join(' ') + '] ' + (end - start) + 'fr';
			}).join(' ');
		}
	},
	methods: {
		mapIntoMainAxis(target) {
			const result = Array.from({length: this.axisMain.length}, () => Array());
			
			target.forEach(event => {
				// NOTE(chris): make new Date object to reset the time
				const start = event.start || this.axisMainBorders[0].plus(-1);
				const end = event.end || this.axisMainBorders[this.axisMainBorders.length - 1].plus(1);

				for (var i = 0; i < this.axisMain.length; i++) {
					if (start < this.axisMainBorders[i + 1] && end > this.axisMainBorders[i]) {
						const startsHere = start >= this.axisMainBorders[i];
						const endsHere = end <= this.axisMainBorders[i+1];
						result[i].push({
							...event,
							startsHere,
							endsHere
						});
					}
				}
			});

			return result;
		},

		/* DRAG AND DROP */
		getPageTop(el) {
			let pageTop = el.offsetTop;
			if (el.offsetParent)
				pageTop += this.getPageTop(el.offsetParent);
			return pageTop;
		},
		getPageLeft(el) {
			let pageLeft = el.offsetLeft;
			if (el.offsetParent)
				pageLeft += this.getPageLeft(el.offsetParent);
			return pageLeft;
		},
		getTimestampFromMouse(evt, dayTimestamp) {
			let mouse, mouseFrac;
			if (this.flipAxis) {
				mouse = evt.pageX - this.getPageLeft(this.$refs.body) + this.$refs.main.scrollLeft;
				mouseFrac = mouse / this.$refs.body.offsetWidth;
			} else {
				mouse = evt.pageY - this.getPageTop(this.$refs.body) + this.$refs.main.scrollTop;
				mouseFrac = mouse / this.$refs.body.offsetHeight;
			}

			return dayTimestamp + this.start + Math.floor((this.end - this.start) * mouseFrac);
		},
		
		/* SCROLLING */
		enableAutoScroll() {
			if (!this.resizeObserver)
				this.resizeObserver = new ResizeObserver(this.scrollToEarliestEvent);
			this.resizeObserver.observe(this.$refs.body);
			
			if (!this.mutationObserver)
				this.mutationObserver = new MutationObserver(mutations => {
					if (mutations.some(m => m.addedNodes.length && [].some.call(m.addedNodes, el => el.matches && el.matches('.fhc-calendar-base-grid-line-event'))))
						this.scrollToEarliestEvent();
				});
			this.mutationObserver.observe(this.$refs.body, {
				subtree: true,
				childList: true
			});

			this.scrollToEarliestEvent();
		},
		disableAutoScroll() {
			if (this.resizeObserver)
				this.resizeObserver.disconnect();
			this.resizeObserver = null;

			if (this.mutationObserver)
				this.mutationObserver.disconnect();
			this.mutationObserver = null;
		},
		scrollToEarliestEvent() {
			const eventElements = this.$refs.scroller.querySelectorAll('.fhc-calendar-base-grid-line-event');
			const earliestEventOffset = eventElements.values()
				.reduce((res, el) => {
					const top = el.offsetTop;
					if (!res[1] || top < res[0])
						return [top, el];
					return res;
				}, [0, null]);
			
			this.userScroll = false;
			if (earliestEventOffset[1]) {
				earliestEventOffset[1].scrollIntoView({ behavior: "smooth" });
			} else {
				this.$refs.scroller.scrollTo(0, 0);
			}
		}
	},
	beforeUnmount() {
		this.disableAutoScroll();
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-grid"
		style="display:grid;width:100%;height:100%"
		:style="'grid-template-' + axisRow + 's:auto' + (allDayEvents ? ' auto ' : ' ') + '1fr;grid-template-' + axisCol + 's:auto ' + styleGridCols"
	>
		<div
			class="grid-header"
			style="display:grid"
			:style="'grid-template-' + axisCol + 's:subgrid;grid-' + axisCol + ':1/-1'"
		>
			<div
				v-for="(date, index) in axisMain"
				:key="index"
				class="main-header"
				:class="{'collapsed-header': axisMainCollapsible && hasValidEvents && !events[index].length}"
				:style="'grid-' + axisCol + ':' + (2+index)"
			>
				<slot name="main-header" v-bind="{ index, date }" />
			</div>
		</div>
		<div
			v-if="allDayEvents"
			class="grid-allday"
			style="display:grid"
			:style="'grid-template-' + axisCol + 's:subgrid;grid-' + axisCol + ':1/-1'"
		>
			<div
				v-for="(events, index) in eventsAllDay"
				:key="index"
				class="all-day-events"
				:style="'grid-' + axisCol + ':' + (2+index)"
			>
				<grid-line-event
					v-for="(event, i) in events"
					:key="i"
					:event="event"
				>
					<template v-slot="slot">
						<slot name="event" v-bind="slot" />
					</template>
				</grid-line-event>
			</div>
		</div>
		<div
			ref="scroller"
			@scrollend="userScroll ? disableAutoScroll() : userScroll = true"
			style="display:grid;overflow:auto"
			:style="'grid-' + axisCol + ':1/-1;grid-template-' + axisCol + 's:subgrid'"
		>
			<div
				ref="main"
				class="grid-main"
				style="position:relative;grid-column:1/-1;grid-row:1/-1;display:grid"
				:style="'grid-template-' + axisCol + 's:subgrid;grid-template-' + axisRow + 's:' + styleGridRows"
			>
				<div
					v-for="(part, index) in axisPartsSave"
					:key="index"
					class="part-header"
					:style="'grid-' + axisCol + ':1;grid-' + axisRow + ': ps_' + index + '/pe_' + index"
				>
					<slot name="part-header" v-bind="{ index, part }" />
				</div>

				<div
					ref="body"
					class="grid-body"
					style="display:grid;grid-template-rows:subgrid;grid-template-columns:subgrid"
					:style="'grid-' + axisCol + ':2/-1;grid-' + axisRow + ':1/-1'"
					v-cal-dnd:dropcage
					@calendar-dragenter="dragging = true"
					@calendar-dragleave="dragging = false"
					@dragover="dropAllowed ? $event.preventDefault() : null"
				>
					<template
						v-for="(date, index) in axisMain"
						:key="index"
					>
						<div
							v-for="(part, i) in axisPartsSave"
							:key="i"
							class="part-body"
							style="position:relative"
							:style="'grid-' + axisCol + ':' + (1+index) + ';grid-' + axisRow + ':ps_' + i + '/pe_' + i"
						>
							<slot name="part-body" v-bind="{ index, part }" />
							<div
								v-if="snapToGrid && dragging"
								style="position:absolute;inset:0;z-index:1"
								v-cal-dnd:dropzone.once="{date: date.plus(part.start || part), ends: ends.slice(ends.findIndex(end => end > date))}"
							></div>
						</div>
						<grid-line
							:start="date.plus(start)"
							:end="date.plus(end)"
							:date="date"
							:events="eventsNormal[index]"
							:backgrounds="backgrounds[index]"
							style="position:relative"
							:style="'grid-' + axisRow + ':1/-1;grid-' + axisCol + ':' + (1+index)"
						>
							<template #event="slot">
								<slot name="event" v-bind="slot" />
							</template>
							<template #dropzone>
								<div
									v-if="!snapToGrid && dragging"
									style="position:absolute;inset:0;z-index:1"
									v-cal-dnd:dropzone="evt => getTimestampFromMouse(evt, date)"
								></div>
							</template>
						</grid-line>
					</template>
				</div>
			</div>
		</div>
	</div>
	`
}

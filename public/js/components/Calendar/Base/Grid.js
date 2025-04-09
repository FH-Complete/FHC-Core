import GridLine from './Grid/Line.js';

import CalDnd from '../../../directives/Calendar/DragAndDrop.js';

export default {
	name: "CalendarGrid",
	components: {
		GridLine
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
		axisMain: Array, // timestamps
		axisParts: Array, // offset-timestamp or objects with start-offset-timestamp and optional end-offset-timestamp
		flipAxis: Boolean,
		axisMainCollapsible: Boolean,
		snapToGrid: Boolean
	},
	data() {
		return {
			dragging: false
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
					res.push([start,index]);
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
			if (!this.axisParts[this.axisParts.length-1].end)
				return this.axisParts.slice(0, -1);
			return this.axisParts;
		},
		start() {
			return this.axisPartsWithBreaks[0].start;
		},
		end() {
			return this.axisPartsWithBreaks[this.axisPartsWithBreaks.length-1].end;
		},
		ends() {
			const ends = [];
			const partsEnds = this.axisPartsWithBreaks.filter(p => p.index !== undefined).map(p => p.end);
			for (var timestamp of this.axisMain)
				for (var part of partsEnds)
					ends.push(timestamp + part);
			
			return ends;
		},
		axisMainBorders() {
			const lastInMainAxis = this.axisMain[this.axisMain.length-1];
			const extraLength = this.end;

			return [...this.axisMain, lastInMainAxis + extraLength];
		},
		events() {
			let events = this.originalEvents;
			return this.mapIntoMainAxis(events);
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
			const msPerHr = 3600000; // 1000 * 60 * 60
			
			const msOfAllParts = this.end - this.start;

			const onePercOfAllPartsInMs = msOfAllParts / 100 || 1; // NOTE(chris): prevent 0 division
			
			return this.axisPartsWithBreaks.map((part, i) => 
				part.index !== undefined
					? '[part_' + (part.index + 1) + '] ' + (part.end - part.start) / msPerHr + 'fr'
					: (part.end - part.start) / onePercOfAllPartsInMs + '%'
			).join(' ') + ' [end]';
		}
	},
	methods: {
		mapIntoMainAxis(target) {
			const result = Array.from({length: this.axisMain.length}, e => Array());
			
			target.forEach(event => {
				// NOTE(chris): make new Date object to reset the time
				const startTime = event.start || this.axisMainBorders[0] - 1;
				const endTime = event.end || this.axisMainBorders[this.axisMainBorders.length-1] + 1;

				for (var i = 0; i < this.axisMain.length; i++) {
					if (startTime < this.axisMainBorders[i+1] && endTime > this.axisMainBorders[i]) {
						const startsHere = startTime >= this.axisMainBorders[i];
						const endsHere = endTime <= this.axisMainBorders[i+1];
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
		}
	},
	template: `
	<div
		class="fhc-calendar-base-grid"
		style="display:grid;width:100%;height:100%"
		:style="'grid-template-' + axisRow + 's:auto 1fr;grid-template-' + axisCol + 's:auto ' + styleGridCols"
	>
		<div
			class="grid-header"
			:style="'display:grid;grid-template-' + axisCol + 's:subgrid;grid-' + axisCol + ':1/-1'"
		>
			<div
				v-for="(timestamp, index) in axisMain"
				:key="index"
				class="main-header"
				:class="{'collapsed-header': axisMainCollapsible && hasValidEvents && !events[index].length}"
				:style="'grid-' + axisCol + ':' + (2+index)"
			>
				<slot name="main-header" v-bind="{ index, timestamp }" />
			</div>
		</div>
		<div
			ref="main"
			class="grid-main"
			style="display:grid;overflow:auto"
			:style="'grid-' + axisCol + ':1/-1;grid-template-' + axisCol + 's:subgrid;grid-template-' + axisRow + 's:' + styleGridRows"
		>
			<div
				v-for="(part, index) in axisPartsSave"
				:key="index"
				class="part-header"
				:style="'grid-' + axisCol + ':1;grid-' + axisRow + ': part_' + (index+1)"
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
					v-for="(timestamp, index) in axisMain"
					:key="index"
				>
					<div
						v-for="(part, i) in axisPartsSave"
						:key="i"
						class="part-body"
						style="position:relative"
						:style="'grid-' + axisCol + ':' + (1+index) + ';grid-' + axisRow + ':part_' + (1+i)"
					>
						<slot name="part-body" v-bind="{ index, part }" />
						<div
							v-if="snapToGrid && dragging"
							style="position:absolute;inset:0;z-index:1"
							v-cal-dnd:dropzone.once="{timestamp: timestamp + (part.start || part), ends: ends.slice(ends.findIndex(end => end > timestamp))}"
						></div>
					</div>
					<grid-line
						:start="start + timestamp"
						:end="end + timestamp"

						:timestamp="timestamp"
						:events="events[index]"
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
								v-cal-dnd:dropzone="evt => getTimestampFromMouse(evt, timestamp)"
							></div>
						</template>
					</grid-line>
				</template>
			</div>
		</div>
	</div>
	`
}

import GridLine from './Grid/Line.js';
import GridLineEvent from './Grid/Line/Event.js';

import drop from '../../../directives/drop.js';
import { useResizeHandler } from '../../../helpers/Tempus/ResizeHandler.js';

export default {
	name: "CalendarGrid",
	components: {
		GridLine,
		GridLineEvent
	},
	directives: {
		drop
	},
	inject: {
		originalEvents: "events",
		originalBackgrounds: "backgrounds",
		dropAllowed: "dropAllowed",
		onDrop: "onDrop",
		onResize: "onResize",
		timeGrid: {
			from: "timeGrid",
			default: () => []
		}
	},
	provide() {
		return {
			flipAxis: Vue.computed(() => this.flipAxis),
			axisRow: Vue.computed(() => this.axisRow),
			onDropEvent: Vue.computed(() => this.onDropEvent),
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
		snapToGrid: Boolean,
		dayVisibility: {
			type: Array,
			default: null
		},
		mainColumnWidths: {
			type: Array,
			default: null,
		},
		arePartHeadersRepeated: {
			type: Boolean,
			default: false,
		},
		scrollable: {
			type: Boolean,
			default: true
		},
		stickyHeader: Boolean,
	},
	data() {
		return {
			resizeObserver: null,
			mutationObserver: null,
			userScroll: true,
			isDragging: false
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
		displayedAxisMain() {
			if (!this.$props.arePartHeadersRepeated)
				return this.$props.axisMain;

			let displayedAxisMain = [];
			this.$props.axisMain.forEach((part, index) => {
				if (part.weekday === 1 && index > 0) {
					displayedAxisMain.push(null);
				}
				displayedAxisMain.push(part);
			});
			return displayedAxisMain;
		},
		displayedAxisMainIndexes() {
			let axisMainIndex = 0;
			return this.displayedAxisMain.map(date => date ? axisMainIndex++ : null);
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
			return this.displayedAxisMain.reduce(
				(res, curr) => res.concat(curr ? [curr.plus(this.start), curr.plus(this.end)] : [null, null]),
				[]
			);
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
		styleGridCols()
		{
			const hasVisibleDays = this.dayVisibility?.some(day => day);
			const showAllDaysEqually = Array.isArray(this.dayVisibility)
				&& this.dayVisibility.length
				&& !hasVisibleDays;
			const isManuallyCollapsed = index => hasVisibleDays
				&& this.displayedAxisMain[index]
				&& this.isDayUnchecked(index);

			if (
				Array.isArray(this.mainColumnWidths)
				&& this.mainColumnWidths.length === this.displayedAxisMain.length
			) {
				const weekView = this.$el?.closest('.fhc-calendar-mode-week-view');
				const gridWidth = weekView?.clientWidth || this.$el?.clientWidth;
				const timeColumnWidth = this.$el
					?.querySelector('.part-header')
					?.getBoundingClientRect().width ?? 0;
				const columnGap = this.$el
					? parseFloat(getComputedStyle(this.$el).columnGap) || 0
					: 0;
				const equalDayWidth = gridWidth
					? Math.max(0, (
						gridWidth
						- timeColumnWidth
						- columnGap * this.mainColumnWidths.length
					) / this.mainColumnWidths.length)
					: this.mainColumnWidths.reduce(
						(total, width) => total + width,
						0,
					) / this.mainColumnWidths.length;

				return this.mainColumnWidths
					.map((width, index) => {
						const minWidth = Math.ceil(
							showAllDaysEqually
								? equalDayWidth
								: isManuallyCollapsed(index)
									? Math.max(width * 0.1, equalDayWidth)
									: width,
						);
						const fraction = isManuallyCollapsed(index) ? '0.1fr' : '1fr';
						return `minmax(${minWidth}px, ${fraction})`;
					})
					.join(' ');
			}

			if (hasVisibleDays)
			{
				return this.displayedAxisMain.map((day, i) => day && isManuallyCollapsed(i) ? 'var(--fhc-calendar-axis-collapsible-manual, 0.1fr)' : '1fr').join(' ');
			}

			let cols = 'repeat(' + this.displayedAxisMain.length + ', 1fr)';
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
				let day = this.displayedAxisMain[mainIndex];
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
			const result = Array.from({length: this.displayedAxisMain.length}, () => Array());

			target.forEach(event => {
				const start = event.start || this.axisMainBorders[0].plus(-1);
				const end = event.end || this.axisMainBorders[this.axisMainBorders.length - 1].plus(1);

				this.displayedAxisMain.forEach((part, index) => {
					if (!part) return;

					let laneStart = this.axisMainBorders[index * 2];
					let laneEnd = this.axisMainBorders[index * 2 + 1];
					if (event.orig?.allDayEvent) {
						laneStart = laneStart.startOf('day');
						laneEnd = laneEnd.endOf('day');
					}
					if (start < laneEnd && end > laneStart) {
						const startsHere = start >= laneStart;
						const endsHere = end <= laneEnd;
						result[index].push({
							...event,
							startsHere,
							endsHere
						});
					}
				});
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
		getTimestampFromMouse(evt, dayTimestamp)
		{
			let mouse, mouseFrac;
			const bodyRect = this.$refs.body.getBoundingClientRect();

			const grabOffsetY = parseFloat(evt.dataTransfer.getData('fhc-grab-offset-y')) || 0;
			const grabOffsetX = parseFloat(evt.dataTransfer.getData('fhc-grab-offset-x')) || 0;

			if (this.flipAxis)
			{
				mouse = evt.clientX - bodyRect.left - grabOffsetX;
				mouseFrac = mouse / bodyRect.width;
			}
			else
			{
				mouse = evt.clientY - bodyRect.top - grabOffsetY;
				mouseFrac = mouse / bodyRect.height;
			}

			let rawTimestamp = dayTimestamp + this.start + Math.floor((this.end - this.start) * mouseFrac);
			let fiveMinutes = 5 * 60 * 1000;
			return Math.round(rawTimestamp / fiveMinutes) * fiveMinutes;
		},

		/* SCROLLING */
		enableAutoScroll() {
			if (!this.scrollable)
				return;

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

			let earliestEventOffset = [0, null];
			for (var el of eventElements.values()) {
				const top = el.offsetTop;
				if (!earliestEventOffset[1] || top < earliestEventOffset[0])
					earliestEventOffset = [top, el];
			}

			this.userScroll = false;
			if (earliestEventOffset[1]) {
				earliestEventOffset[1].scrollIntoView({ behavior: "smooth" });
			} else {
				this.$refs.scroller.scrollTo(0, 0);
			}
		},
		calculateNettoDuration(start, end) {
			const startDay = start.startOf('day');
			const blocks = this.axisPartsWithBreaks.filter(p => p.index !== undefined);

			let nettoDuration = luxon.Duration.fromMillis(0);

			for (const block of blocks)
			{
				const blockStart = startDay.plus(block.start);
				const blockEnd = startDay.plus(block.end);

				const overlapStart = blockStart > start ? blockStart : start;
				const overlapEnd = blockEnd < end ? blockEnd : end;

				if (overlapStart < overlapEnd) {
					nettoDuration = nettoDuration.plus(overlapEnd.diff(overlapStart));
				}
			}

			return nettoDuration;
		},

		calculateDropEnd(dropStart, durationMs) {
			const duration = luxon.Duration.fromMillis(durationMs);
			const blocks = this.axisPartsWithBreaks.filter(p => p.index !== undefined);
			let accumulated = luxon.Duration.fromMillis(0);

			for (const block of blocks)
			{
				const blockStart = dropStart.startOf('day').plus(block.start);
				const blockEnd = dropStart.startOf('day').plus(block.end);

				if (blockEnd <= dropStart) continue;

				const relevantStart = blockStart > dropStart ? blockStart : dropStart;
				const relevantDuration = blockEnd.diff(relevantStart);

				accumulated = accumulated.plus(relevantDuration);

				if (accumulated >= duration)
				{
					const overflow = accumulated.minus(duration);
					return blockEnd.minus(overflow);
				}
			}

			const lastBlock = blocks[blocks.length - 1];
			return dropStart.startOf('day').plus(lastBlock.end);
		},

		onDropEvent(evt, items, date)
		{
			if (this.snapToGrid)
				this.onDropSnap(evt, items, date)
			else
				this.onDropFree(evt, items, date)
		},
		onDropSnap(evt, items, date, part) {
			let obj = items;
			if (!obj?.orig) return;

			const dayStr = evt?.currentTarget?.dataset?.day;
			const dropDay = dayStr ? luxon.DateTime.fromISO(dayStr) : date;

			const rawTimestamp = this.getTimestampFromMouse(evt, dropDay.toMillis());
			const grabTime = luxon.DateTime.fromMillis(rawTimestamp);

			const blocks = this.axisPartsWithBreaks.filter(p => p.index !== undefined);
			const grabOffset = grabTime.diff(dropDay);

			let snappedPart = blocks.find(b => grabOffset >= b.start && grabOffset < b.end);

			if (!snappedPart)
				snappedPart = blocks.find(b => b.start >= grabOffset) || blocks[blocks.length - 1];

			const dropStart = snappedPart ? dropDay.plus(snappedPart.start) : grabTime;

			let nettoDuration = this._getNettoDurationForDrop(obj);
			let dropEnd = this.calculateDropEnd(dropStart, nettoDuration);

			this.onDrop?.({
				item: [obj],
				start: dropStart.toISO(),
				end: dropEnd.toISO(),
				ctrlKey: !!(evt?.ctrlKey || evt?.metaKey)
			});
		},

		_getNettoDurationForDrop(obj) {
			if (obj.orig?.isostart && obj.orig?.isoend)
			{
				const s = luxon.DateTime.fromISO(obj.orig.isostart);
				const e = luxon.DateTime.fromISO(obj.orig.isoend);
				if (s.isValid && e.isValid)
					return this.calculateNettoDuration(s, e);
			}

			if (obj.stundenblockung)
			{
				let blocks = this.axisPartsWithBreaks.filter(p => p.index !== undefined);
				let firstBlock = blocks[0];
				let blockMinutes = luxon.Duration.fromISO(firstBlock.end).minus(luxon.Duration.fromISO(firstBlock.start)).as('minutes');
				if (!Number.isFinite(blockMinutes) || blockMinutes <= 0) blockMinutes = 45;
				return luxon.Duration.fromObject({ minutes: obj.stundenblockung * blockMinutes });
			}

			return luxon.Duration.fromObject({ minutes: 45 });
		},
		onDropFree(evt, items, date)
		{
			let obj = items;
			if (!obj?.orig)
				return;

			const timestamp = this.getTimestampFromMouse(evt, date);
			const dropStart = luxon.DateTime.fromMillis(timestamp);

			let nettoDuration = this._getNettoDurationForDrop(obj);
			let dropEnd = this.calculateDropEnd(dropStart, nettoDuration);

			this.onDrop?.({
				item: [obj],
				start: dropStart.toISO(),
				end: dropEnd.toISO(),
				ctrlKey: !!(evt?.ctrlKey || evt?.metaKey)
			});
		},
		handleResizeStart({ edge, evt, horizontal, el, event })
		{
			const gridEl = this.$refs.body;

			if (!gridEl)
				return;

			this.$el.dispatchEvent(new CustomEvent('fhc-calendar-grid-resize-state', {
				bubbles: true,
				detail: {
					active: true,
					eventId: event?.orig?.kalender_id,
				},
			}));

			this.resizeHandler.startResize(edge, evt, {
				el,
				gridEl,
				event,
				horizontal,
				timeGrid: this.timeGrid,
				onFinish: () => {
					this.$el.dispatchEvent(new CustomEvent('fhc-calendar-grid-resize-state', {
						bubbles: true,
						detail: {
							active: false,
							eventId: event?.orig?.kalender_id,
						},
					}));
				},
				onEnd: ({ event, newStart, newEnd }) => {
					const orig = event?.orig;
					if (!orig || !newStart || !newEnd)
						return;

					this.onResize?.({
						item: [{ type: 'kalender', id: orig.kalender_id, orig }],
						start: newStart,
						end: newEnd
					});
				}
			});
		},
		isDayCollapsed(index)
		{
			if (this.dayVisibility?.some(day => day) && this.isDayUnchecked(index))
				return true;

			return this.axisMainCollapsible && this.hasValidEvents && !this.events[index].length;
		},
		isDayUnchecked(index)
		{
			const axisMainIndex = this.displayedAxisMainIndexes[index];
			return axisMainIndex !== null
				&& Array.isArray(this.dayVisibility)
				&& !this.dayVisibility[axisMainIndex];
		}
	},
	setup()
	{
		const resizeHandler = useResizeHandler();
		return { resizeHandler };
	},
	beforeUnmount() {
		this.disableAutoScroll();
	},
	template: /* html */`
	<div
		class="fhc-calendar-base-grid"
		style="display:grid;width:100%;height:100%;overflow:auto"
		data-cy="calendar-base-grid"
		:style="'--fhc-grid-displayed-axis-main-count: ' + displayedAxisMain.length + ';height:' + (scrollable ? '100%' : 'auto') + ';min-height:' + (scrollable ? '0' : '100%') + ';overflow:' + (scrollable ? 'auto' : 'visible') + ';grid-template-' + axisRow + 's:' + (allDayEvents ? 'auto ' : '') + '1fr;grid-template-' + axisCol + 's:max-content ' + styleGridCols"
	>
		<div
			class="grid-header"
			style="display:grid"
			:style="'grid-template-' + axisCol + 's:subgrid;grid-' + axisCol + ':1/-1;position:' + (stickyHeader ? 'sticky' : 'static') + ';top:0;z-index:' + (stickyHeader ? '100' : 'auto') + ';align-self:' + (stickyHeader ? 'start' : 'auto') + ';border-bottom:' + (stickyHeader ? '1px solid var(--bs-gray-500, #adb5bd)' : 'none')"
		>
			<template v-for="(date, index) in displayedAxisMain" :key="index">
				<div
					v-if="date"
					class="main-header"
					:class="{
						'collapsed-header': isDayCollapsed(index),
						'main-header-sunday': date.weekday === 7,
					}"
					:style="'grid-' + axisCol + ':' + (2+index)"
				>
					<slot name="main-header" v-bind="{ index: displayedAxisMainIndexes[index], date }" />
				</div>
				<div
					v-else
					class="main-header main-header-empty"
					:style="'grid-' + axisCol + ':' + (2+index)"
				></div>
			</template>
		</div>
		<div
			v-if="allDayEvents"
			class="grid-allday"
			style="display:grid"
			:style="'grid-template-' + axisCol + 's:subgrid;grid-' + axisCol + ':1/-1'"
		>
			<template v-for="(events, index) in eventsAllDay" :key="index">
				<div
					v-if="displayedAxisMain[index]"
					class="all-day-events"
					:class="{'all-day-events-sunday': displayedAxisMain[index].weekday === 7}"
					:style="'grid-' + axisCol + ':' + (2+index)"
				>
					<grid-line-event
						v-for="(event, i) in events"
						:key="i + 50"
						:event="event"
					>
						<template v-slot="slot">
							<slot name="event" v-bind="slot" />
						</template>
					</grid-line-event>
				</div>
				<div
					v-else
					class="all-day-events"
					:style="'grid-' + axisCol + ':' + (2+index)"
				></div>
			</template>
		</div>
		<div
			ref="scroller"
			@scrollend="userScroll ? disableAutoScroll() : userScroll = true"
			id="grid-main-scrollable"
			style="display:grid;overflow:auto"
			:style="'overflow:' + (scrollable ? 'auto' : 'visible') + ';grid-' + axisCol + ':1/-1;grid-template-' + axisCol + 's:subgrid'"
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
					:style="'grid-' + axisCol + ':1;grid-' + axisRow + ': ps_' + index + '/pe_' + index + ';min-width:50px;'"
				>
					<slot name="part-header" v-bind="{ index, part }" />
				</div>

				<div
					ref="body"
					class="grid-body"
					style="display:grid;grid-template-rows:subgrid;grid-template-columns:subgrid"
					@dragenter="isDragging = true"
					@dragleave.self="isDragging = false"
					@dragend="isDragging = false"
					@drop="isDragging = false"
					:style="'grid-' + axisCol + ':2/-1;grid-' + axisRow + ':1/-1'"
				>
					<template
						v-for="(date, index) in displayedAxisMain"
						:key="index"
					>
						<template v-if="date">
							<div
								v-for="(part, i) in axisPartsSave"
								:key="i"
								class="part-body"
								style="position:relative"
								:style="'grid-' + axisCol + ':' + (1+index) + ';grid-' + axisRow + ':ps_' + i + '/pe_' + i"
								:data-drop-index="displayedAxisMainIndexes[index] * axisPartsSave.length + i + 1"
								data-cy="calendar-grid-part"
							>
								<slot name="part-body" v-bind="{ index: displayedAxisMainIndexes[index], part }" />
								<div
									v-if="snapToGrid"
									class="fhc-calendar-base-grid-dropzone"
									style="position:absolute;inset:0"
									:style="{ zIndex: isDragging ? 10 : 1 }"
									:data-day="date.toFormat('yyyy-MM-dd')"
									v-drop:move.lehreinheit.kalender.reservierung="(evt, item) => onDropSnap(evt, item, date, part)"
								></div>
							</div>
							<grid-line
								:start="date.plus(start)"
								:end="date.plus(end)"
								:date="date"
								:events="eventsNormal[index]"
								:backgrounds="backgrounds[index]"
								:collapsed="isDayUnchecked(index)"
								style="position:relative"
								@resize-start="handleResizeStart"
								:style="'grid-' + axisRow + ':1/-1;grid-' + axisCol + ':' + (1+index)"
							>
								<template #event="slot">
									<slot name="event" v-bind="slot" />
								</template>
								<template #dropzone>
									<div
										v-if="!snapToGrid"
										class="fhc-calendar-base-grid-dropzone"
										style="position:absolute;inset:0"
										:style="{ zIndex: isDragging ? 10 : 1 }"
										v-drop:move.lehreinheit.kalender.reservierung="(evt, item) => onDropFree(evt, item, date)"
									></div>
								</template>
							</grid-line>
						</template>
						<template v-else>
							<div
								v-for="(part, i) in axisPartsSave"
								:key="i"
								class="part-header-repeat part-body"
								style="position:relative"
								:style="'grid-' + axisCol + ':' + (1+index) + ';grid-' + axisRow + ':ps_' + i + '/pe_' + i"
							>
								<slot name="part-header-repeat" v-bind="{ index, part }" />
							</div>
						</template>
					</template>
				</div>
			</div>
		</div>
	</div>
	`
}

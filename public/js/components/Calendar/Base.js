import BaseDraganddrop from './Base/DragAndDrop.js';
import BaseHeader from './Base/Header.js';
import BaseSlider from './Base/Slider.js';

import CalendarDate from '../../helpers/Calendar/Date.js';
import CalendarEvent from '../../helpers/Calendar/Event.js';

import CalClick from '../../directives/Calendar/Click.js';

/**
 * TODO(chris):
 * - Better Interface (maybe config object for modes/header/slider; hideWeeks, timeGrid, collapseEmptyDays, buttons)
 * - rename view to mode?
 * - make view/mode a v-model
 * - check emits
 * - event single view (default for click:event)
 * - get focusDate/currentDate correct
 */

export default {
	name: "CalendarBase",
	components: {
		BaseDraganddrop,
		BaseHeader,
		BaseSlider
	},
	directives: {
		CalClick
	},
	provide() {
		const self = this;
		return {
			locale: Vue.computed(() => this.locale),
			timezone: Vue.computed(() => this.timezone),
			hideWeeks: Vue.computed(() => this.hideWeeks),
			timeGrid: Vue.computed(() => this.timeGrid),
			collapseEmptyDays: Vue.computed(() => this.collapseEmptyDays),
			draggableEvents: Vue.computed(() => {
				if (!this.draggableEvents)
					return () => false;

				if (Array.isArray(this.draggableEvents))
					return event => this.draggableEvents.includes(CalendarEvent.getType(event));
				if (this.draggableEvents instanceof Function)
					return this.draggableEvents;
				
				return () => true;
			}),
			dropableEvents: Vue.computed(() => {
				if (!this.onDrop)
					return () => false;

				if (Array.isArray(this.dropableEvents))
					return item => this.dropableEvents.includes(item.type);
				if (this.dropableEvents instanceof Function)
					return this.dropableEvents;

				return () => true;
			}),
			hasDragoverFunc: Vue.computed(() => this.onDragover),
			mode: Vue.computed(() => this.view)
		};
	},
	props: {
		locale: {
			type: String,
			default: 'de'
		},
		timezone: {
			type: String,
			required: true
		},
		date: {
			type: [Date, String, Number, luxon.DateTime],
			default: luxon.DateTime.local()
		},
		views: {
			type: Object
			// TODO(chris): verfication functiosn
		},
		view: String,
		events: {
			type: Array,
			default: []
		},
		backgrounds: {
			type: Array,
			default: []
		},
		showBtns: Boolean,
		btnMonth: {
			type: Boolean,
			default: undefined
		},
		btnWeek: {
			type: Boolean,
			default: undefined
		},
		btnDay: {
			type: Boolean,
			default: undefined
		},
		btnList: {
			type: Boolean,
			default: undefined
		},
		hideWeeks: Boolean,
		timeGrid: Array,
		collapseEmptyDays: Boolean,
		draggableEvents: [Boolean, Array, Function],
		dropableEvents: [Boolean, Array, Function],
		onDragover: Function,
		onDrop: Function
	},
	emits: [
		"click:next",
		"click:prev",
		"click:view",
		"click:event",
		"click:day",
		"click:week",
		"update:date",
		"update:view",
		"update:range",
		"drop"
	],
	data() {
		return {
			internalView: '',
			internCurrentDate: null
		};
	},
	computed: {
		convertedEvents() {
			return this.events.map(orig => ({
				id: orig.type + orig[orig.type + '_id'],
				start: luxon.DateTime.fromISO(orig.isostart).setZone(this.timezone),
				end: luxon.DateTime.fromISO(orig.isoend).setZone(this.timezone),
				orig
			}));
		},
		convertedBackgrounds() {
			return this.backgrounds.map(bg => {
				const res = { ...bg };
				if (res.start) {
					if (Number.isInteger(res.start))
						res.start = luxon.DateTime.fromMillis(res.start, { zone: this.timezone, locale: this.locale });
					else if (res.start instanceof Date)
						res.start = luxon.DateTime.fromJSDate(res.start, { zone: this.timezone, locale: this.locale });
					else if (typeof res.start === 
						string || res.start instanceof String)
						res.start = luxon.DateTime.fromISO(res.start, { zone: this.timezone, locale: this.locale });
				}
				if (res.end) {
					if (Number.isInteger(res.end))
						res.end = luxon.DateTime.fromMillis(res.end, { zone: this.timezone, locale: this.locale });
					else if (res.end instanceof Date)
						res.end = luxon.DateTime.fromJSDate(res.end, { zone: this.timezone, locale: this.locale });
					else if (typeof res.end === 
						string || res.end instanceof String)
						res.end = luxon.DateTime.fromISO(res.end, { zone: this.timezone, locale: this.locale });
				}
				return res;
			});
		},
		availableViews() {
			return Object.keys(this.views);
		},
		viewComponent() {
			if (this.views[this.internalView])
				return this.views[this.internalView];
			return 'div';
		},
		cDate: {
			get() {
				if (this.internCurrentDate) {
					return this.internCurrentDate.setLocale(this.locale);
				}
				return luxon.DateTime.fromJSDate(new Date(this.date)).setZone(this.timezone).setLocale(this.locale);
			},
			set(value) {
				this.internCurrentDate = value;
				this.$emit('update:date', value);
			}
		}
	},
	methods: {
		clickPrev() {
			const evt = new Event('click:prev', {cancelable: true});
			this.$emit('click:prev', evt);
			if (evt.defaultPrevented)
				return;

			// default: switch picker/view page
			this.$refs.view.prevPage();
		},
		clickNext() {
			const evt = new Event('click:next', {cancelable: true});
			this.$emit('click:next', evt);
			if (evt.defaultPrevented)
				return;

			// default: switch picker/view page
			this.$refs.view.nextPage();
		},
		handleClickDefaults(evt) {
			// TODO(chris): implement
			switch (evt.detail.source) {
			case 'day':
				if (this.internalView != 'day' && this.views['day']) {
					evt.stopPropagation();
					this.cDate = evt.detail.value;
					this.internalView = 'day';
					this.$emit('update:view', this.internalView);
				}
				break;
			case 'week':
				if (this.internalView != 'week' && this.views['week']) {
					evt.stopPropagation();
					this.cDate = luxon.DateTime.fromObject({
						localWeekNumber: evt.detail.value.number,
						localWeekYear: evt.detail.value.year
					}, {
						zone: this.cDate.zoneName,
						locale: this.cDate.locale
					});
					this.internalView = 'week';
					this.$emit('update:view', this.internalView);
				}
				break;
			}
		},
		onDropItem(evt, start, end) {
			this.$emit('drop', evt, start, end);
		}
	},
	created() {
		// choose default view
		let view = this.view;
		if (!view || !this.views[view])
			view = this.availableViews.find(Boolean); // start with first entry as active view

		this.internalView = view;
		this.$emit('update:view', this.internalView);
	},
	template: /* html */`
	<div class="fhc-calendar-base h-100">
		<base-draganddrop
			class="card h-100"
			:events="convertedEvents"
			:backgrounds="convertedBackgrounds"
			@drop="onDropItem"
			v-cal-click:container
			@cal-click-default.capture="handleClickDefaults"
		>
			<base-header
				class="card-header"
				v-model:date="cDate"
				:view="internalView"
				@update:view="internalView = $event; $emit('update:view', internalView)"
				@prev="clickPrev"
				@next="clickNext"
				@click:view="$emit('click:view', $event)"
				:btn-day="!!views['day'] && (btnDay || (showBtns && btnDay !== false))"
				:btn-week="!!views['week'] && (btnWeek || (showBtns && btnWeek !== false))"
				:btn-month="!!views['month'] && (btnMonth || (showBtns && btnMonth !== false))"
				:btn-list="!!views['list'] && (btnList || (showBtns && btnList !== false))"
			>
				<slot name="actions" />
			</base-header>
			<component
				:is="viewComponent"
				ref="view"
				v-model:current-date="cDate"
				@update:range="$emit('update:range', $event)"
			>
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</component>
		</base-draganddrop>
	</div>
	`
}

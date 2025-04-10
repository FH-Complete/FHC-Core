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
 * - default title:click => back to previous view/mode
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
			title: Vue.computed({
				get() {
					return self.title;
				},
				set(n) {
					if (n)
						self.titleStack.unshift(n);
					else
						self.titleStack.shift();
				}
			}),
			locale: Vue.computed(() => this.locale),
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
		currentDate: {
			type: [Date, String, Number],
			default: new Date()
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
		"click:title",
		"click:view",
		"click:event",
		"click:day",
		"click:week",
		"update:currentDate",
		"update:view",
		"update:range",
		"drop"
	],
	data() {
		return {
			titleStack: [],
			internalView: '',
			internCurrentDate: null
		};
	},
	computed: {
		convertedEvents() {
			return this.events.map(CalendarEvent.smartConvert);
		},
		convertedBackgrounds() {
			return this.backgrounds.map(bg => {
				const res = { ...bg };
				if (res.start)
					res.start = CalendarDate.UTC(new Date(res.start));
				if (res.end)
					res.end = CalendarDate.UTC(new Date(res.end));
				return res;
			});
		},
		title() {
			if (this.titleStack.length)
				return this.titleStack.find(Boolean).value;
			return '...';
		},
		availableViews() {
			return Object.keys(this.views);
		},
		viewComponent() {
			if (this.views[this.internalView])
				return this.views[this.internalView];
			return 'div';
		},
		focusDate: {
			get() {
				return this.internCurrentDate || (new Date(this.currentDate)).getTime();
			},
			set(v) {
				this.internCurrentDate = v;
				this.$emit('update:currentDate', new Date(this.internCurrentDate));
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
		clickTitle() {
			const evt = new Event('click:title', {cancelable: true});
			this.$emit('click:title', evt);
			if (evt.defaultPrevented)
				return;

			// defaults:
			if (this.internalView == 'day') {
				if (this.views.month) {
					// switch from day to month
					this.internalView = 'month';
					this.$emit('update:view', this.internalView);
				} else if (this.views.week) {
					// switch from day to week
					this.internalView = 'week';
					this.$emit('update:view', this.internalView);
				}
			} else if (this.$refs.view.showPicker) {
				// open picker if available
				this.$refs.view.showPicker();
			}
		},
		handleClickDefaults(evt) {
			// TODO(chris): implement
			switch (evt.detail.source) {
			case 'day':
				if (this.internalView != 'day' && this.views['day']) {
					evt.stopPropagation();
					this.focusDate = evt.detail.value;
					this.internalView = 'day';
					this.$emit('update:currentDate', new Date(this.focusDate));
					this.$emit('update:view', this.internalView);
				}
				break;
			case 'week':
				if (this.internalView != 'week' && this.views['week']) {
					evt.stopPropagation();
					this.focusDate = CalendarDate.UTC(CalendarDate.getDaysInWeek(evt.detail.value.number, evt.detail.value.year, this.locale)[0]);
					this.internalView = 'week';
					this.$emit('update:currentDate', new Date(this.focusDate));
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
	template: `
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
				:view="view"
				@update:view="view = $event; $emit('update:view', view)"
				:title="title"
				@prev="clickPrev"
				@next="clickNext"
				@click:title="clickTitle"
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
				:current-date="focusDate"
				@update:range="$emit('update:range', $event)"
			>
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</component>
		</base-draganddrop>
	</div>
	`
}

import BaseDraganddrop from './Base/DragAndDrop.js';
import BaseHeader from './Base/Header.js';
import BaseSlider from './Base/Slider.js';
import BsModal from '../Bootstrap/Modal.js';

import CalClick from '../../directives/Calendar/Click.js';

export default {
	name: "CalendarBase",
	components: {
		BaseDraganddrop,
		BaseHeader,
		BaseSlider,
		BsModal
	},
	directives: {
		CalClick
	},
	provide() {
		return {
			locale: Vue.computed(() => this.locale),
			timezone: Vue.computed(() => this.timezone),
			timeGrid: Vue.computed(() => this.timeGrid),
			draggableEvents: Vue.computed(() => {
				if (!this.draggableEvents)
					return () => false;

				if (Array.isArray(this.draggableEvents))
					return event => this.draggableEvents.includes(event.type);
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
			mode: Vue.computed(() => this.mode)
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
		modes: {
			type: Object,
			required: true,
			default: {}
			// TODO(chris): verfication functions
		},
		mode: String,
		modeOptions: Object,
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
		timeGrid: Array,
		draggableEvents: [Boolean, Array, Function],
		dropableEvents: [Boolean, Array, Function],
		onDragover: Function,
		onDrop: Function
	},
	emits: [
		"click:next",
		"click:prev",
		"click:mode",
		"click:event",
		"click:day",
		"click:week",
		"update:date",
		"update:mode",
		"update:range",
		"drop"
	],
	data() {
		return {
			internalView: null,
			internalDate: null,
			modalEvent: null
		};
	},
	computed: {
		convertedEvents() {
			return this.events.map(orig => ({
				id: orig.type + orig[orig.type + '_id'],
				type: orig.type,
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
						'string' || res.start instanceof String)
						res.start = luxon.DateTime.fromISO(res.start, { zone: this.timezone, locale: this.locale });
				}
				if (res.end) {
					if (Number.isInteger(res.end))
						res.end = luxon.DateTime.fromMillis(res.end, { zone: this.timezone, locale: this.locale });
					else if (res.end instanceof Date)
						res.end = luxon.DateTime.fromJSDate(res.end, { zone: this.timezone, locale: this.locale });
					else if (typeof res.end === 
						'string' || res.end instanceof String)
						res.end = luxon.DateTime.fromISO(res.end, { zone: this.timezone, locale: this.locale });
				}
				return res;
			});
		},
		sDate() {
			if (this.date instanceof luxon.DateTime)
				return this.date;
			return luxon.DateTime.fromJSDate(new Date(this.date)).setZone(this.timezone);
		},
		cDate: {
			get() {
				const date = this.internalDate ? this.internalDate : this.sDate;
				return date.setLocale(this.locale);
			},
			set(value) {
				this.internalDate = value;
				this.$emit('update:date', value);
			}
		},
		sMode() {
			// choose default mode
			let mode = this.mode;
			if (mode)
				mode = mode.toLowerCase();
			if (!mode || !this.modes[mode])
				mode = Object.keys(this.modes).find(Boolean); // start with first entry as active mode
			return mode || '';
		},
		cMode: {
			get() {
				return this.internalView ? this.internalView : this.sMode;
			},
			set(value) {
				this.internalView = value;
				this.$emit('update:mode', value);
			}
		}
	},
	watch: {
		sDate(n, o) {
			if (this.sDate.isValid && !this.sDate.hasSame(this.internalDate, 'day'))
				this.internalDate = this.sDate;
		},
		sMode() {
			if (this.sMode)
				this.internalView = this.sMode;
		}
	},
	methods: {
		clickPrev() {
			const evt = new Event('click:prev', {cancelable: true});
			this.$emit('click:prev', evt);
			if (evt.defaultPrevented)
				return;

			// default: switch page
			this.$refs.mode.prevPage();
		},
		clickNext() {
			const evt = new Event('click:next', {cancelable: true});
			this.$emit('click:next', evt);
			if (evt.defaultPrevented)
				return;

			// default: switch page
			this.$refs.mode.nextPage();
		},
		handleClickDefaults(evt) {
			// TODO(chris): implement
			switch (evt.detail.source) {
			case 'day':
				if (this.cMode != 'day' && this.modes['day']) {
					evt.stopPropagation();
					this.cDate = evt.detail.value;
					this.cMode = 'day';
				}
				break;
			case 'week':
				if (this.cMode != 'week' && this.modes['week']) {
					evt.stopPropagation();
					this.cDate = luxon.DateTime.fromObject({
						localWeekNumber: evt.detail.value.number,
						localWeekYear: evt.detail.value.year
					}, {
						zone: this.cDate.zoneName,
						locale: this.cDate.locale
					});
					this.cMode = 'week';
				}
				break;
			}
		},
		onDropItem(evt, start, end) {
			this.$emit('drop', evt, start, end);
		},
		showEventModal(eventObj) {
			this.modalEvent = eventObj;
			this.$refs.modal.show();
		},
		hideEventModal() {
			if (this.modalEvent)
				this.modalEvent.closeFn = undefined;
			this.$refs.modal.hide();
			this.modalEvent = null;
		},
		onModalHidden() {
			if (this.modalEvent.closeFn)
				this.modalEvent.closeFn();
		}
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
				v-model:mode="cMode"
				@prev="clickPrev"
				@next="clickNext"
				@click:mode="$emit('click:mode', $event)"
				:btn-day="!!modes['day'] && (btnDay || (showBtns && btnDay !== false))"
				:btn-week="!!modes['week'] && (btnWeek || (showBtns && btnWeek !== false))"
				:btn-month="!!modes['month'] && (btnMonth || (showBtns && btnMonth !== false))"
				:btn-list="!!modes['list'] && (btnList || (showBtns && btnList !== false))"
				:mode-options="modeOptions ? modeOptions[cMode] : undefined"
			>
				<slot name="actions" />
			</base-header>
			<component
				:is="modes ? modes[cMode] : null || 'div'"
				ref="mode"
				v-model:current-date="cDate"
				@update:range="$emit('update:range', $event)"
				@request-modal-open="showEventModal"
				@request-modal-close="hideEventModal"
				v-bind="modeOptions ? modeOptions[cMode] : null || {}"
			>
				<template v-slot="slot"><slot v-bind="slot" /></template>
			</component>
		</base-draganddrop>
		<bs-modal ref="modal" dialog-class="modal-lg" body-class="" @hidden-bs-modal="onModalHidden">
			<template #title>
				<slot v-if="modalEvent" v-bind="{mode: 'eventheader', event: modalEvent.event}" />
			</template>
			<template #default>
				<slot v-if="modalEvent" v-bind="{mode: 'event', event: modalEvent.event}" />
			</template>
		</bs-modal>
	</div>
	`
}

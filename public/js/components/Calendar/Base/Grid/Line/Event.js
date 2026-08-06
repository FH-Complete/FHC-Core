import draggable from '../../../../../directives/draggable.js';
import drop from '../../../../../directives/drop.js';
import CalClick from '../../../../../directives/Calendar/Click.js';

export default {
	name: "GridLineEvent",
	directives: {
		draggable,
		drop,
		CalClick
	},
	emits: [
		'resize-start'
	],
	data() {
		return {
			contextMenu: {
				show: false,
				x: 0,
				y: 0
			}
		};
	},
	inject: {
		draggableEvents: "draggableEvents",
		resizableEvents: {
			from: "resizableEvents",
			default: () => () => false
		},
		mode: "mode",
		contextMenuActions: {
			from: "contextMenuActions",
			default: () => ({})
		},
		onDrop: {
			from: "onDrop",
			default: () => null
		}
	},
	props: {
		event: {
			type: Object,
			required: true,
			validator(value) {
				return (value.start && value.end && value.orig);
			}
		}
	},
	computed: {
		isHeaderOrFooter() {
			return ['header', 'footer'].includes(this.event.orig);
		},
		draggable() {
			return !this.isHeaderOrFooter && this.draggableEvents(this.event.orig, this.mode);
		},
		resizable() {
			return !this.isHeaderOrFooter && this.resizableEvents(this.event.orig, this.mode);
		},
		classes() {
			const classes = [];
			if (this.isHeaderOrFooter) {
				classes.push('event-' + this.event.orig);
			} else {
				if (this.event.startsHere)
					classes.push('event-begin');
				if (this.event.endsHere)
					classes.push('event-end');
			}

			classes.push(`calender_id-${this.event.orig.kalender_id}`);
			return classes;
		},
		dragKalenderCollection() {
			const orig = this.event.orig;
			return {
				type: 'kalender',
				id: orig?.kalender_id ?? null,
				orig,
			};
		},
		activeContextActions() {
			if (this.isHeaderOrFooter) return [];
			const type = this.event.orig?.type ?? 'lehreinheit';
			return this.contextMenuActions[type] ?? this.contextMenuActions['default'] ?? [];
		}
	},
	methods: {
		onResizeStart(edge, evt) {
			this.$emit('resize-start', {
				edge,
				evt,
				el: this.$refs.eventEl,
				event: this.event
			});
		},
		onRightClick(evt) {
			this.contextMenu.show = true;
			this.contextMenu.x = evt.clientX;
			this.contextMenu.y = evt.clientY;
		},
		onContextAction(action) {
			this.contextMenu.show = false;
			action(this.event.orig);
		},
		closeContextMenu() {
			this.contextMenu.show = false;
		},
		onDragStart(evt) {
			const rect = this.$refs.eventEl.getBoundingClientRect();
			evt.dataTransfer.setData('fhc-grab-offset-y', evt.clientY - rect.top);
			evt.dataTransfer.setData('fhc-grab-offset-x', evt.clientX - rect.left);
		},
		onDropOnCard(evt, items) {
			if (this.isHeaderOrFooter || !this.onDrop)
				return;

			const list = Array.isArray(items) ? items : [items];
			const obj = list[0];
			if (!obj)
				return;

			if ((evt.ctrlKey || evt.metaKey) && obj.type === 'lehreinheit')
			{
				return this.onDrop({
					item: [obj],
					ctrlKey: true,
					targetKalenderId: this.event.orig?.kalender_id ?? null
				});
			}

			return this.onDrop({
				item: [obj],
				start: this.event.start.toISO(),
				end: this.event.end.toISO(),
				ctrlKey: false,
				targetKalenderId: null
			});
		},
	},
	template:`
	<div
		class="fhc-calendar-base-grid-line-event event"
		:class="classes"
		style="z-index: 11"
		:draggable="draggable"
		ref="eventEl"
		@dragstart="onDragStart"
		v-draggable:move.noimage="draggable ? dragKalenderCollection : {}"
		v-drop:move.lehreinheit.kalender.reservierung="onDropOnCard"
		v-cal-click:event="isHeaderOrFooter ? event : event.orig"
		@contextmenu.prevent="onRightClick"
		:data-id="'event-' + event.orig.kalender_id"
		:data-group-id="'event-group-' + event.orig.eindeutige_kalender_gruppen_id"
		data-cy="calendar-event"
	>
		<div
			v-if="resizable"
			class="fhc-resize-bar fhc-resize-bar--top"
			@pointerdown.prevent.stop="onResizeStart('start', $event)"
			@click.stop
		>
			<i class="fa-solid fa-grip-lines text-muted"></i>
		</div>
		<slot :event="isHeaderOrFooter ? event : event.orig">
			{{ event.orig }}
		</slot>
		<div
			v-if="resizable"
			class="fhc-resize-bar fhc-resize-bar--bottom"
			@pointerdown.prevent.stop="onResizeStart('end', $event)"
			@click.stop
		>
			<i class="fa-solid fa-grip-lines text-muted"></i>
		</div>

	
		<teleport to="body">
			<div
				v-if="contextMenu.show"
				style="position:fixed; inset:0; z-index:9998"
				@click="closeContextMenu"
				@contextmenu.prevent="closeContextMenu"
			/>
			<ul
				v-if="contextMenu.show"
				class="dropdown-menu show"
				:style="{ position: 'fixed', top: contextMenu.y + 'px', left: contextMenu.x + 'px', zIndex: 9999 }"
				data-cy="eventContextMenu"
			>
				<li v-for="action in activeContextActions" :key="action.label">
					<button class="dropdown-item" type="button" @click.stop="onContextAction(action.action)">
						<i v-if="action.icon" :class="action.icon + ' me-2'"></i>
						{{ action.label }}
					</button>
				</li>
			</ul>
		</teleport>
	</div>
	`
}
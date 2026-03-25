import draggable from '../../../../../directives/draggable.js';
import CalClick from '../../../../../directives/Calendar/Click.js';

export default {
	name: "GridLineEvent",
	directives: {
		draggable,
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
		}
	},
	template:`
	<div
		class="fhc-calendar-base-grid-line-event event"
		:class="classes"
		style="z-index: 2"
		:draggable="draggable"
		ref="eventEl"
		v-draggable:move.noimage="draggable ? dragKalenderCollection : {}"
		v-cal-click:event="isHeaderOrFooter ? event : event.orig"
		@contextmenu.prevent="onRightClick"
	>
		<div
			v-if="resizable"
			class="fhc-resize-bar fhc-resize-bar--top"
			@pointerdown.prevent.stop="onResizeStart('start', $event)"
			@click.stop
		/>
		<slot :event="isHeaderOrFooter ? event : event.orig">
			{{ event.orig }}
		</slot>
		<div
			v-if="resizable"
			class="fhc-resize-bar fhc-resize-bar--bottom"
			@pointerdown.prevent.stop="onResizeStart('end', $event)"
			@click.stop
		/>

	
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
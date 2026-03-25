import EventCard from '../Calendar/Base/Grid/Line/EventCard.js';
import drop from '../../directives/drop.js';

export default {
	name: "ParkingSlot",
	components: {
		EventCard
	},
	directives: {
		drop
	},
	emits: ['update:parkedKeys'],
	data() {
		return {
			parked: [],
			parkedKeys: new Set()
		};
	},
	methods: {
		park(evt, items) {
			const list = Array.isArray(items) ? items : [items];

			const stored = JSON.parse(localStorage.getItem('tempus_parking') || '[]');

			list.forEach(item => {
				const key = `${item.id}`;
				if (this.parkedKeys.has(key))
					return;
				this.parkedKeys.add(key);
				stored.push({
					type: item.type,
					id: item.id,
					orig: item.orig
				});
				this.parked.push(item);
			});

			localStorage.setItem('tempus_parking', JSON.stringify(stored));
			this.$emit('update:parkedKeys', this.parkedKeys);
		},
		unpark(event) {
			const key = `${event.id}`;
			this.parkedKeys.delete(key);
			this.parked = this.parked.filter(parkedEvent => parkedEvent.id !== event.id);

			const stored = JSON.parse(localStorage.getItem('tempus_parking') || '[]').filter(parkedEvent => parkedEvent.id !== event.id);
			localStorage.setItem('tempus_parking', JSON.stringify(stored));
			this.$emit('update:parkedKeys', this.parkedKeys);
		},
		isParked(id) {
			return this.parkedKeys.has(`${id}`);
		}
	},
	mounted() {
		const stored = JSON.parse(localStorage.getItem('tempus_parking') || '[]');
		this.parked = stored;
		this.parkedKeys = new Set(stored.map(store => `${store.id}`));
		this.$emit('update:parkedKeys', this.parkedKeys);
	},
	template: `
		<div class="overflow-auto" tabindex="-1">
			<div
				id="parkingslot"
				class="parkingslot"
				v-drop:move.kalender-collection="(evt, item) => park(evt, item)"
			>
				<i class="fa-solid fa-square-parking"></i>
				<event-card
					class="parkingevent"
					v-for="parkedEvent in parked"
					:key="parkedEvent.id"
					:event="parkedEvent"
					parked
				/>
			</div>
		</div>
	`
}
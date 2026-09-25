export default {
	name: "RoomSelection",
	props: {
		rooms: {
			type: Array,
			required: true
		}
	},
	emits: ['update:rooms'],
	methods: {
		removeRoom(ort_kurzbz)
		{
			if (ort_kurzbz == null)
			{
				this.$emit('update:rooms', []);
			}
			else
			{
				this.$emit('update:rooms', this.rooms.filter(room => room.ort_kurzbz !== ort_kurzbz));
			}
		}
	},
	template: `
		<div class="room-selection px-2">
			<div class="d-flex align-items-center justify-content-between">
				<span class="fw-semibold"><i class="fa-solid fa-door-open me-2"></i>Räume</span>
				<button
					type="button"
					class="btn btn-sm btn-link text-danger p-0"
					@click="removeRoom(null)"
					title="Alle Räume entfernen"
				>
					<i class="fa-solid fa-xmark"></i>
				</button>
			</div>
			<ul class="list-unstyled mb-0">
				<li
					v-for="room in rooms"
					:key="room.ort_kurzbz"
					class="d-flex align-items-center justify-content-between"
				>
					<span>{{ room.ort_kurzbz }}</span>
					<button
						type="button"
						class="btn btn-sm btn-link text-danger p-0"
						@click="removeRoom(room.ort_kurzbz)"
						title="Raum entfernen"
					>
						<i class="fa-solid fa-xmark"></i>
					</button>
				</li>
			</ul>
		</div>
	`
}
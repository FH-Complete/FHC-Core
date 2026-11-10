import LectureSelection from './Filters/LectureSelection.js';
import VerbandSelection from './Filters/VerbandSelection.js';
import RoomSelection from './Filters/RoomSelection.js';
import ParkingSlot from './ParkingSlot.js';

export default {
	name: 'TempusSidebarMenu',
	components: {
		LectureSelection,
		VerbandSelection,
		RoomSelection,
		ParkingSlot,
	},
	props: {
		previewRole: {
			type: String,
			required: true,
		},
		rooms: {
			type: Array,
			required: true,
		},
		studiengaenge: {
			type: Array,
			required: true,
		},
		studiengaengeAll: {
			type: Array,
			default: () => [],
		},
		lecturers: {
			type: Array,
			required: true,
		},
	},
	emits: [
		'update:previewRole',
		'update:rooms',
		'update:studiengaenge',
		'update:parkedKeys',
		'remove-lecturer',
		'select-kw',
		'sync',
	],
	methods: {
		isParked(id) {
			return this.$refs.parking?.isParked(id) ?? false;
		},
		park(items) {
			return this.$refs.parking?.park(null, items);
		},
		unpark(item) {
			return this.$refs.parking?.unpark(item);
		},
	},
	template: /* html */ `
    <nav
      id="sidebarMenu"
      class="bg-light offcanvas offcanvas-start col-md p-md-0 h-100 w-100"
    >
      <div class="sidebar-icons d-flex flex-row align-items-start py-2 gap-1 ps-2">
        <button
          class="btn btn-outline-secondary"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#verbandMenu"
          aria-controls="verbandMenu"
          aria-expanded="false"
          title="Verband"
        >
          <span class="fa-solid fa-university"></span>
        </button>
        <button
          class="btn btn-outline-secondary"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#raumMenu"
          aria-controls="raumMenu"
          aria-expanded="false"
          title="Raum"
        >
          <span class="fa-solid fa-door-open"></span>
        </button>
      </div>
      <div class="px-2 py-1 w-100">
        <div
          class="d-flex gap-1 py-1"
          data-cy="previewRoleOptionsHolder"
        >
          <button
            class="btn btn-sm"
            :class="previewRole === 'planer' ? 'btn-dark' : 'btn-outline-dark'"
            @click="$emit('update:previewRole', 'planer')"
          >
            <i class="fa-solid fa-pen-ruler me-1"></i>Planer
          </button>
          <button
            class="btn btn-sm"
            :class="previewRole === 'lektor' ? 'btn-primary' : 'btn-outline-primary'"
            @click="$emit('update:previewRole', 'lektor')"
          >
            <i class="fa-solid fa-chalkboard-user me-1"></i>Lektor
          </button>
          <button
            class="btn btn-sm"
            :class="previewRole === 'student' ? 'btn-success' : 'btn-outline-success'"
            @click="$emit('update:previewRole', 'student')"
          >
            <i class="fa-solid fa-user-graduate me-1"></i>Student
          </button>
          <button
            class="btn btn-sm btn-outline-danger"
            @click="$emit('sync')"
          >
            <i class="fa-solid fa-rotate me-1"></i>Sync
          </button>
        </div>
      </div>
      <room-selection
        v-if="rooms.length"
        :rooms="rooms"
        @update:rooms="$emit('update:rooms', $event)"
      ></room-selection>
      <verband-selection
        v-if="studiengaenge.length"
        :studiengaenge="studiengaenge"
        :studiengaenge-all="studiengaengeAll"
        @update:studiengaenge="$emit('update:studiengaenge', $event)"
      ></verband-selection>
      <lecture-selection
        v-if="lecturers.length"
        :lecturers="lecturers"
        @remove="$emit('remove-lecturer', $event)"
      ></lecture-selection>
      <div class="d-flex flex-column flex-grow-1" style="min-height: 0">
        <parking-slot
          ref="parking"
          @update:parked-keys="$emit('update:parkedKeys', $event)"
		  @select-kw="$emit('select-kw', $event)"
        ></parking-slot>
        <slot name="coursepicker"></slot>
        </div>

       
    </nav>
  `,
};

import FhcCoursepicker from './Coursepicker.js';
import StvStudiensemester from '../Stv/Studentenverwaltung/Studiensemester.js';

export default {
	name: 'TempusSecondSidebarMenu',
	components: {
		FhcCoursepicker,
		StvStudiensemester,
	},
	props: {
		studiengaenge: {
			type: Array,
			required: true,
		},
		lecturers: {
			type: Array,
			required: true,
		},
		selectedStudiensemester: {
			type: String,
			required: true,
		},
		raumvorschlagPreview: {
			type: Object,
			default: null,
		},
		raumvorschlagLoading: {
			type: Boolean,
			default: false,
		},
	},
	emits: [
		'update:selectedStudiensemester',
		'select-lecturer',
		'select-kw',
		'preview-raumvorschlag',
	],
	methods: {
		reloadCoursepicker() {
			return this.$refs.coursepicker?.reload();
		},
	},
	computed: {
		courseLecturers() {
			if (!this.lecturers.length)
				return [];
			return this.lecturers
				.filter((lecture) => lecture.showCoursePicker)
				.map((lecture) => lecture.uid);
		},
	},
	template: /* html */ `
		<nav id="secondSidebarMenu" class="bg-light h-100 w-100 d-flex flex-column p-0">
			<div class="d-flex flex-column flex-grow-1" style="min-height: 0">
				<fhc-coursepicker
					ref="coursepicker"
					:studiengaenge="studiengaenge"
					:studiensemester="selectedStudiensemester"
					:lecturers="courseLecturers"
					:preview-lehreinheit-id="raumvorschlagPreview?.lehreinheit_id ?? null"
					:preview-loading="raumvorschlagLoading"
					@preview-raumvorschlag="$emit('preview-raumvorschlag', $event)"
					@select-lecturer="$emit('select-lecturer', $event)"
					@select-kw="$emit('select-kw', $event)"
				></fhc-coursepicker>
			</div>
			<stv-studiensemester
				:studiensemester-kurzbz="selectedStudiensemester"
				@update:studiensemester-kurzbz="$emit('update:selectedStudiensemester', $event)"
			></stv-studiensemester>
		</nav>
	`,
};
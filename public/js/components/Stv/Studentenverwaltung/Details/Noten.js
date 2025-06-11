import NotenZeugnis from './Noten/Zeugnis.js';
import NotenTeacher from './Noten/Teacher.js';
import NotenRepeater from './Noten/Repeater.js';

const LOCAL_STORAGE_ID = 'stv_details_noten_2024-11-25_stdsem_all';

export default {
	name: "TabGrades",
	components: {
		NotenZeugnis,
		NotenTeacher,
		NotenRepeater
	},
	provide() {
		return {
			config: this.config
		}
	},
	props: {
		modelValue: Object,
		config: Object
	},
	data() {
		return {
			stdsem: ''
		};
	},
	methods: {
		reload() {
			this.$refs.zeugnis.$refs.table.reloadTable();
			this.$refs.teacher.$refs.table.reloadTable();
			this.$refs.repeater.$refs.table.reloadTable();
		},
		saveStdsem(event) {
			window.localStorage.setItem(LOCAL_STORAGE_ID, event.target.value);
		}
	},
	created() {
		const savedPath = window.localStorage.getItem(LOCAL_STORAGE_ID);
		this.stdsem = savedPath || '';
	},
	template: `
	<div class="stv-details-noten d-flex flex-column overflow-hidden">
		<div class="mb-3">
			<select class="form-select" v-model="stdsem" @input="saveStdsem">
				<option value="">{{ $p.t('ui/current_semester') }}</option>
				<option value="true">{{ $p.t('ui/all_semester') }}</option>
			</select>
		</div>
		<div class="row">
			<div class="col-8">
				<noten-zeugnis ref="zeugnis" :student="modelValue" :all-semester="!!stdsem"></noten-zeugnis>
			</div>
			<div class="col-4">
				<noten-teacher ref="teacher" :student="modelValue" :all-semester="!!stdsem" @copied="reload"></noten-teacher>
				<noten-repeater class="mt-4" ref="repeater" :student="modelValue" :all-semester="!!stdsem" @copied="reload"></noten-repeater>
			</div>
		</div>
	</div>`
};
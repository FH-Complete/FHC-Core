import NotenZeugnis from './Noten/Zeugnis.js';
import NotenTeacher from './Noten/Teacher.js';

export default {
	components: {
		NotenZeugnis,
		NotenTeacher
	},
	props: {
		modelValue: Object
	},
	methods: {
		reload() {
			this.$refs.zeugnis.$refs.table.reloadTable();
		}
	},
	template: `
	<div class="stv-details-noten h-100 d-flex flex-column overflow-hidden">
		<div class="row">
			<div class="col-8">
				<noten-zeugnis ref="zeugnis" :student="modelValue"></noten-zeugnis>
			</div>
			<div class="col-4">
				<noten-teacher ref="teacher" :student="modelValue" @copied="reload"></noten-teacher>
			</div>
		</div>
	</div>`
};
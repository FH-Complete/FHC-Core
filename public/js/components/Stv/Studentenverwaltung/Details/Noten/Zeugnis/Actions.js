import {CoreRESTClient} from '../../../../../../RESTClient.js';

export default {
	emits: [
		'setGrades'
	],
	props: {
		selected: Array
	},
	data() {
		return {
			grades: []
		};
	},
	computed: {
		current: {
			get() {
				if (!this.selected.length)
					return '';
				if (this.selected.length == 1)
					return this.selected[0].note;
				const grades = Object.keys(this.selected.reduce((a,c) => {
					a[c.note] = true;
					return a;
				}, {}));
				if (grades.length == 1)
					return grades[0];
				return '';
			},
			set(note) {
				this.$emit('setGrades', this.selected.map(zeugnis => {
					const { lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz } = zeugnis;
					return { lehrveranstaltung_id, student_uid, studiensemester_kurzbz, note };
				}));
			}
		}
	},
	created() {
		CoreRESTClient
			.get('components/stv/Noten/get')
			.then(result => result.data)
			.then(result => {
				this.grades = result.retval;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-noten-zeugnis-actions">
		<select class="form-select" v-model="current" :disabled="!selected.length">
			<option value="" disabled>Note setzen</option>
			<option v-for="grade in grades" :key="grade.note" :value="grade.note">{{ grade.bezeichnung }}</option>
		</select>
	</div>`
};
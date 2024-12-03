import CoreForm from '../../../../../Form/Form.js';
import FormInput from '../../../../../Form/Input.js';


export default {
	components: {
		CoreForm,
		FormInput
	},
	emits: [
		'setGrades'
	],
	inject: [
		'config'
	],
	props: {
		selected: Array
	},
	data() {
		return {
			grades: [],
			suggestions: null,
			currentPoints: ''
		};
	},
	computed: {
		current: {
			get() {
				if (!this.selected.length)
					return '';
				if (this.selected.length == 1)
					return this.selected.find(Boolean).note;
				const grades = Object.keys(this.selected.reduce((a,c) => {
					a[c.note] = true;
					return a;
				}, {}));
				if (grades.length == 1)
					return grades.find(Boolean);
				return '';
			},
			set(note) {
				this.$emit('setGrades', this.selected.map(zeugnis => {
					const { lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz } = zeugnis;
					return { lehrveranstaltung_id, student_uid, studiensemester_kurzbz, note };
				}));
			}
		},
		currentLabel() {
			if (this.current == '')
				return 'Note setzen'; // TODO(chris): phrase
			return this.grades.find(grade => grade.note === this.current)?.bezeichnung || '';
		}
	},
	methods: {
		convertPoints({evt, query}) {
			if (!query) {
				return this.suggestions = this.grades;
			}
			this.$refs.points.factory
				.stv.grades.getGradeFromPoints(query, this.selected.find(Boolean)?.lehrveranstaltung_id)
				.then(result => {
					if (result.data === null) {
						this.suggestions = [];
						return result;
					}
					this.suggestions = this.grades.filter(grade => grade.note == result.data);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		setPoints({evt, value: {note}}) {
			if (this.selected)
				this.selected.forEach(grade => grade.note = note);
			this.currentPoints = '';
			this.current = note;
		}
	},
	created() {
		this.$fhcApi.factory
			.stv.grades.list()
			.then(result => {
				this.grades = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	// TODO(chris): phrases
	template: `
	<div class="stv-details-noten-zeugnis-actions">
		<template v-if="['both', 'header'].includes(config.edit)">
			<core-form
				v-if="config.usePoints"
				ref="points"
				>
				<form-input
					type="autocomplete"
					name="points"
					v-model="currentPoints"
					:placeholder="currentLabel"
					:suggestions="suggestions"
					@complete="convertPoints"
					@item-select="setPoints"
					optionLabel="bezeichnung"
					dropdown
					forceSelection
					:disabled="!selected.length"
					>
				</form-input>
			</core-form>
			<select v-else class="form-select" v-model="current" :disabled="!selected.length">
				<option value="" disabled>Note setzen</option>
				<option v-for="grade in grades" :key="grade.note" :value="grade.note">{{ grade.bezeichnung }}</option>
			</select>
		</template>
	</div>`
};
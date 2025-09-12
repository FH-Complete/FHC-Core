import CoreForm from '../../../../../Form/Form.js';
import FormInput from '../../../../../Form/Input.js';
import ZeugnisDocuments from './Documents.js';

import ApiStvGrades from '../../../../../../api/factory/stv/grades.js';

export default {
	name: 'ZeugnisActions',
	components: {
		CoreForm,
		FormInput,
		ZeugnisDocuments
	},
	emits: [
		'setGrade',
		'deleteGrade'
	],
	inject: {
		config: {
			from: 'config',
			required: true
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		}
	},
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
		selectedData() {
			return this.selected.map(zeugnis => {
				const { lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz } = zeugnis;
				return { lehrveranstaltung_id, student_uid, studiensemester_kurzbz };
			})
		},
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
				this.selectedData.forEach(data => this.$emit('setGrade', {...data, ...{note}}));
			}
		},
		currentLabel() {
			if (this.current == '')
				return this.$p.t('stv/grades_setgrade');
			return this.grades.find(grade => grade.note === this.current)?.bezeichnung || '';
		}
	},
	methods: {
		convertPoints({evt, query}) {
			if (!query) {
				return this.suggestions = this.grades;
			}
			this.$refs.points
				.call(ApiStvGrades.getGradeFromPoints(
					query,
					this.selected.find(Boolean)?.lehrveranstaltung_id,
					this.currentSemester
				))
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
		},
		deleteGrades() {
			this.selectedData.forEach(data => this.$emit('deleteGrade', data));
		}
	},
	created() {
		this.$api
			.call(ApiStvGrades.list())
			.then(result => {
				this.grades = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-noten-zeugnis-actions d-flex gap-2">
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
				<option value="" disabled>{{ $p.t('stv/grades_setgrade') }}</option>
				<option v-for="grade in grades" :key="grade.note" :value="grade.note">{{ grade.bezeichnung }}</option>
			</select>
		</template>
		<zeugnis-documents
			v-if="['both', 'header'].includes(config.documents)"
			:data="selected.find(Boolean)"
			:list="config.documentslist"
			:disabled="!selected.length"
			>
		</zeugnis-documents>
		<button
			v-if="['both', 'header'].includes(config.delete)"
			class="btn btn-outline-secondary"
			:disabled="!selected.length"
			@click="deleteGrades"
			>
			<i class="fa fa-trash" :title="$p.t('ui/loeschen')"></i>
		</button>
	</div>`
};
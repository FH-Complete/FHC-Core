import FormInput from '../../../Form/Input.js';
import ApiStudiengang from '../../../../api/factory/studiengang.js';

export default {
	name: 'NewsListFilter',
	emits: ['filter-changed'],
	components: {
		FormInput,
	},
	data() {
		return {
			isPublished: 'all',
			isActive: true,
			degreeProgram: null,
			semester: null,
			degreePrograms: [],
			filteredDegreePrograms: [],
		};
	},
	computed: {
		semesters() {
			this.$p.user_language.value;
			const semesterLabel = this.$capitalize(
				this.$p.t('lehre', 'semester'),
			);

			return [
				{
					label: this.$capitalize(this.$p.t('ui', 'all_semester')),
					value: null,
				},
				...Array.from({ length: 8 }, (_, index) => ({
					label: `${index + 1}. ${semesterLabel}`,
					value: String(index + 1),
				})),
			];
		},
		dropdownParsedDegreePrograms() {
			const degreePrograms = this.degreePrograms
				.map((degreeProgram) => {
					let labelFragment = degreeProgram.typ + degreeProgram.kurzbz;
					labelFragment = labelFragment.trim().toUpperCase();

					return {
						label: `${labelFragment} (${degreeProgram.bezeichnung})`,
						value: degreeProgram.studiengang_kz,
					};
				})
				.sort((a, b) => a.label.localeCompare(b.label));

			return [
				{
					label: this.$capitalize(
						this.$p.t('ui', 'dropdownEmptyOption'),
					),
					value: null,
				},
				...degreePrograms,
			];
		},
	},
	watch: {
		isPublished() {
			this.emitFilterChanged();
		},
		isActive() {
			this.emitFilterChanged();
		},
		degreeProgram() {
			this.emitFilterChanged();
		},
		semester() {
			this.emitFilterChanged();
		},
	},
	methods: {
		emitFilterChanged() {
			this.$emit('filter-changed', {
				isPublished: this.isPublished,
				isActive: this.isActive,
				degreeProgramShortCode: this.degreeProgram?.value ?? null,
				semester: this.semester,
			});
		},
		filterDegreePrograms(event) {
			const query = event.query.toLowerCase();

			this.filteredDegreePrograms = this.dropdownParsedDegreePrograms.filter(
				(degreeProgram) => degreeProgram.label.toLowerCase().includes(query),
			);
		},
	},
	async created() {
		try {
			const response = await this.$api.call(ApiStudiengang.getDegreePrograms());
			if (response.meta.status === 'success') {
				this.degreePrograms = response.data.sort((a, b) =>
					a.bezeichnung.localeCompare(b.bezeichnung),
				);
			}
		} catch (error) {
			this.degreePrograms = [];
		}
	},
	template: /*html*/ `
		<div class="mb-3">
			<div class="border rounded p-3 mt-2 d-flex flex-wrap align-items-end gap-3">
				<form-input
					v-model="isPublished"
					type="select"
					name="is-published-filter"
					:label="$capitalize($p.t('ui', 'visible'))"
					container-class="mb-0"
					class="w-auto"
				>
					<option value="all">{{ $capitalize($p.t('global', 'alle')) }}</option>
					<option :value="true">{{ $capitalize($p.t('ui', 'visible')) }}</option>
					<option :value="false">{{ $capitalize($p.t('ui', 'notPublishedYet')) }}</option>
				</form-input>
				<form-input
					v-model="isActive"
					type="checkbox"
					name="is-active-filter"
					:label="$capitalize($p.t('global', 'aktiv'))"
					container-class="mb-0"
				></form-input>
				<form-input
					v-model="degreeProgram"
					:label="$capitalize($p.t('lehre', 'studiengang'))"
					:suggestions="filteredDegreePrograms"
					:optionValue="(option) => option.value"
					:optionLabel="(option) => option.label"
					@complete="filterDegreePrograms($event)"
					type="autocomplete"
					name="degree-program-filter"
					dropdown
					clear
				></form-input>
				<form-input
					v-model="semester"
					:label="$capitalize($p.t('lehre', 'studiensemester'))"
					type="select"
					name="semester-filter"
					container-class="mb-0"
				>
					<option
						v-for="semesterOption in semesters"
						:key="semesterOption.value"
						:value="semesterOption.value"
					>
						{{ semesterOption.label }}
					</option>
				</form-input>
			</div>
		</div>
	`,
};

import MylvSemesterCards from "./Semester.js";
import MylvTable from "./Table.js";
import MylvSemesterStudiengangAverageGrade from "./AverageGrade.js";

export default {
	name: "Lvs",
	components: {
		MylvSemesterCards,
		MylvTable,
		MylvSemesterStudiengangAverageGrade,
	},
	inject: ['isStudent'],
	props: ["current"],
	data() {
		return {
			mode: localStorage.getItem('myLvaDefaultMode') ?? 'cards'
		};
	},
	methods: {
		selectMode(mode) {
			localStorage.setItem('myLvaDefaultMode', mode)
			this.mode = mode
		},
	},
	template: /*html*/ `
	<div class="d-flex flex-column gap-3">
		<div class="d-flex flex-row justify-content-center gap-2">
			<button
				type="button"
				class="btn btn-outline-secondary"
				:class="{active: mode === 'cards'}"
				@click="selectMode('cards')"
			>
				<i class="fa fa-grip"></i>
			</button>
			<button
				type="button"
				class="btn btn-outline-secondary"
				:class="{active: mode === 'table'}"
				@click="selectMode('table')"
			>
				<i class="fa fa-table"></i>
			</button>
		</div>
		<mylv-semester-cards v-if="mode == 'cards'" v-bind="$props.current">
			<template #averageGrade v-if="isStudent && $props.current.semester">
				<mylv-semester-studiengang-average-grade
					:semesterInfo="$props.current.semester"
				/>
			</template>
		</mylv-semester-cards>
		<mylv-table v-else-if="mode == 'table'" v-bind="$props.current">
			<template #averageGrade v-if="isStudent && $props.current.semester">
				<mylv-semester-studiengang-average-grade
					:semesterInfo="$props.current.semester"
				/>
			</template>
		</mylv-table>
	</div>
	`,
}
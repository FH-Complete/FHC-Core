import MylvSemesterCards from "./Semester.js";
import MylvTable from "./Table.js";

export default {
	name: "Lvs",
	components: {
		MylvSemesterCards,
		MylvTable
	},
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
		<mylv-semester-cards v-if="mode == 'cards'" v-bind="$props.current"/>
		<mylv-table v-else-if="mode == 'table'" v-bind="$props.current"/>
	</div>
	`,
}
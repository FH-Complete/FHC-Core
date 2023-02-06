import MylvSemesterStudiengangLv from "./Studiengang/Lv.js";
import Phrasen from "../../../../mixins/Phrasen.js";

export default {
	components: {
		MylvSemesterStudiengangLv
	},
	mixins: [
		Phrasen
	],
	props: {
		bezeichnung: String,
		kuerzel: String,
		semester: Number,
		lvs: Array
	},
	computed: {
		lehrveranstaltungen() {
			return [... new Map(
				this.lvs
				.map(lv => [
					lv.lehrveranstaltung_id, 
					lv
				])
			).values()]
		}
	},
	methods: {
		note(lv) {
			return lv.benotung ? lv.znote || lv.lvnote || null : null;
		}
	},
	template: `<div class="card mb-3">
		<div class="card-body">
			<h4 class="card-title mb-3">{{bezeichnung}} - {{kuerzel}}
				<small>{{semester}}.{{p.t('lehre/semester')}}</small>
			</h4>
			<div class="row">
				<div v-for="lv in lehrveranstaltungen" :key="lv.lehrveranstaltung_id" class="col-sm-4 col-md-3 mb-3">
					<mylv-semester-studiengang-lv v-bind="lv" class="text-center h-100"></mylv-semester-studiengang-lv>
				</div>
			</div>
		</div>
	</div>`
};
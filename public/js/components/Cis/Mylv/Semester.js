import MylvSemesterStudiengang from "./Semester/Studiengang.js";

export default {
	components: {
		MylvSemesterStudiengang
	},
	provide() {
		return {
			studien_semester: Vue.computed(() => this.semester) 
		}
	},
	props: {
		semester: [String, Number],
		lvs: Array
	},
	computed: {
		ready() { return this.lvs !== null; },
		studiengaenge() {
			return [... new Map(
				this.lvs
				.map(lv => [
					lv.studiengang_kz + '#' + lv.semester, 
					{
						studiengang_kz: lv.studiengang_kz, 
						bezeichnung: lv.sg_bezeichnung, 
						kuerzel: lv.studiengang_kuerzel, 
						semester: lv.semester
					}
				])
			).values()].sort((a, b) => a.bezeichnung.toLowerCase() == b.bezeichnung.toLowerCase() ? a.semester > b.semester : a.bezeichnung.toLowerCase() > b.bezeichnung.toLowerCase());
		},
	},
	methods: {
		lvsForStudiengang(studiengang) {
			return this.lvs.filter(lv => lv.studiengang_kz == studiengang.studiengang_kz && lv.semester == studiengang.semester);
		}
	},
	template: `<div class="mylv-semester" v-if="ready">
		<mylv-semester-studiengang v-for="studiengang in studiengaenge" :key="studiengang.studiengang_kz" v-bind="studiengang" :lvs="lvsForStudiengang(studiengang)"/>
	</div>
	<div class="mylv-semester text-center" v-else>
		<i class="fa-solid fa-spinner fa-pulse fa-3x"></i>
	</div>`
};
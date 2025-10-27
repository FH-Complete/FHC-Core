import PluginsPhrasen from '../../plugins/Phrasen.js';
import AbgabetoolStudent from "../../components/Cis/Abgabetool/AbgabetoolStudent.js";
import AbgabetoolMitarbeiter from "../../components/Cis/Abgabetool/AbgabetoolMitarbeiter.js";
import DeadlineOverview from "../../components/Cis/Abgabetool/DeadlineOverview.js";
import {capitalize} from "../../helpers/StringHelpers.js";

const app = Vue.createApp({
	name: 'AbgabetoolApp',
	components: {
		AbgabetoolStudent,
		AbgabetoolMitarbeiter,
		DeadlineOverview
	},
	data: function() {
		return {
			comp: null,
			uid: null,
			student_uid: null
		};
	},
	methods: {
		
	},
	computed: {
		viewData() {
			return { uid: this.uid}
		},
		student_uid_computed() {
			return this.student_uid ?? null
		}
	},
	created() {
	},
	mounted() {
		
		const root = document.getElementById('abgabetoolroot')
		const route = root.getAttribute("route");
		this.comp = route

		const uid = root.getAttribute("uid");
		this.uid = uid

		const student_uid = root.getAttribute("student_uid_prop");
		this.student_uid = student_uid
		
	},
	template: `
		<template v-if="comp && uid">
			<AbgabetoolStudent v-if="comp == 'AbgabetoolStudent'" :viewData="viewData" :student_uid_prop="student_uid_computed"></AbgabetoolStudent>
			<AbgabetoolMitarbeiter v-if="comp == 'AbgabetoolMitarbeiter'" :viewData="viewData"></AbgabetoolMitarbeiter>
			<DeadlineOverview v-if="comp == 'DeadlinesOverview'" :viewData="viewData"></DeadlineOverview>
		</template>
	`
});
app.config.globalProperties.$capitalize = capitalize;
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.use(PluginsPhrasen);
app.mount('#abgabetoolroot');

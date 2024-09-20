import ResultPrestudent from "./prestudent.js";
import ResultStudent from "./student.js";

export default {
	components: {
		ResultPrestudent,
		ResultStudent
	},
	emits: [ 'actionexecuted' ],
	props: {
		res: Object,
		actions: Object
	},
	computed: {
		prestudent() {
			const prestudent = this.res.list.filter(item => item.type == 'prestudent');
			return prestudent.pop();
		}
	},
	template: `
	<result-prestudent
		v-if="prestudent"
		:res="prestudent"
		:actions="actions"
		@actionexecuted="$emit('actionexecuted')"
		class="searchbar-result-mergedstudent"
	></result-prestudent>
	<result-student
		v-else
		:res="res.list[0]"
		:actions="actions"
		@actionexecuted="$emit('actionexecuted')"
		class="searchbar-result-mergedstudent"
	></result-student>`
};
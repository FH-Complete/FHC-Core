import StudierendenantragAntrag from "../../components/Studierendenantrag/Antrag.js";
import StudierendenantragStatus from "../../components/Studierendenantrag/Status.js";
import StudierendenantragInfoblock from "../../components/Studierendenantrag/Infoblock.js";
import Phrasen from '../../plugin/Phrasen.js';

const app = Vue.createApp({
	components: {
		StudierendenantragAntrag,
		StudierendenantragStatus,
		StudierendenantragInfoblock
	},
	data() {
		return {
			status: {
				msg: '',
				severity: ''
			},
			infoArray: []
		};
	}
});
app
	.use(Phrasen)
	.mount('#wrapper');

import StudierendenantragAntrag from "../../components/Studierendenantrag/Antrag.js";
import StudierendenantragStatus from "../../components/Studierendenantrag/Status.js";
import StudierendenantragInfoblock from "../../components/Studierendenantrag/Infoblock.js";
import PluginsPhrasen from '../../plugins/Phrasen.js';

const app = Vue.createApp({
	name: 'AntragApp',
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
	.use(PluginsPhrasen)
	.mount('#wrapper');
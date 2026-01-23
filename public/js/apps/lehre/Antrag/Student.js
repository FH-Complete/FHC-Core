import LvPopup from '../../../components/Studierendenantrag/Leitung/LvPopup.js';
import FhcBase from "../../../plugins/FhcBase/FhcBase.js";

const app = Vue.createApp({
	name: 'StudentApp',
	components: {
		LvPopup
	}
});
app
	.use(FhcBase)
	.mount('#wrapper');
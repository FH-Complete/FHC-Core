import LvZuweisung from '../../../components/Studierendenantrag/Lvzuweisung.js';
import FhcBase from "../../../plugins/FhcBase/FhcBase.js";

const app = Vue.createApp({
	name: 'LvzuweisungApp',
	components: {
		LvZuweisung
	},
	computed: {
		notinframe() {
			return window.self === window.top;
		}
	}
});
app
	.use(FhcBase)
	.mount('#wrapper');
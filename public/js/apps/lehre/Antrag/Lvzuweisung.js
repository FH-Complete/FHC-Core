import LvZuweisung from '../../../components/Studierendenantrag/Lvzuweisung.js';
import Phrasen from '../../../plugin/Phrasen.js';

const app = Vue.createApp({
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
	.use(Phrasen)
	.mount('#wrapper');

import LvZuweisung from '../../../components/Studierendenantrag/Lvzuweisung.js';
import PluginsPhrasen from '../../../plugins/Phrasen.js';

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
	.use(PluginsPhrasen)
	.mount('#wrapper');
import LvPopup from '../../../components/Studierendenantrag/Leitung/LvPopup.js';
import PluginsPhrasen from '../../../plugins/Phrasen.js';

const app = Vue.createApp({
	name: 'StudentApp',
	components: {
		LvPopup
	}
});
app
	.use(PluginsPhrasen)
	.mount('#wrapper');
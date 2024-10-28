import LvPopup from '../../../components/Studierendenantrag/Leitung/LvPopup.js';
import Phrasen from '../../../plugin/Phrasen.js';

const app = Vue.createApp({
	name: 'StudentApp',
	components: {
		LvPopup
	}
});
app
	.use(Phrasen)
	.mount('#wrapper');
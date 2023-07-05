import StudierendenantragLeitung from '../../../components/Studierendenantrag/Leitung.js';

const app = Vue.createApp({
	components: {
		StudierendenantragLeitung
	}
});
app.use(primevue.config.default).mount('#wrapper');

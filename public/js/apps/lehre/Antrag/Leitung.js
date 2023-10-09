import StudierendenantragLeitung from '../../../components/Studierendenantrag/Leitung.js';

const app = Vue.createApp({
	components: {
		StudierendenantragLeitung
	}
});
app.use(primevue.config.default,{zIndex: {overlay: 9999}}).mount('#wrapper');

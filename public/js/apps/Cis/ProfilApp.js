import Profil from "../../components/Cis/Profil/Profil.js";

import fhcapifactory from "../api/fhcapifactory.js";

Vue.$fhcapi = fhcapifactory;

const app = Vue.createApp({
	components: {
		Profil,
	},
	data() {
		return {
			stunden: [],
			events: null
		}
	},
	methods: {
        testsearch: function() {
			return Vue.$fhcapi.UserData.getUser();
		}
	},
	
});
app.mount('#content');
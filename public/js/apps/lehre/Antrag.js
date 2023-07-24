import StudierendenantragAntrag from "../../components/Studierendenantrag/Antrag.js";
import StudierendenantragStatus from "../../components/Studierendenantrag/Status.js";
import StudierendenantragInfoblock from "../../components/Studierendenantrag/Infoblock.js";
import VueDatePicker from "../../components/vueDatepicker.js.php";

const app = Vue.createApp({
	components: {
		VueDatePicker,
		StudierendenantragAntrag,
		StudierendenantragStatus,
		StudierendenantragInfoblock
	},
	data() {
		return {
			statusMsg: "",
			statusSeverity: "",
			infoArray: []
		};
	}
});
app.mount('#wrapper');

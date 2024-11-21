import MylvStudent from "../../../components/Cis/Mylv/Student.js";
import Phrasen from "../../../plugin/Phrasen.js";
import {setScrollbarWidth} from "../../../helpers/CssVarCalcHelpers";

const app = Vue.createApp({
	name: 'MyLvStudentApp',
	components: {
		MylvStudent
	}
})

setScrollbarWidth();

app.use(Phrasen, {reload: true}).mount('#content');
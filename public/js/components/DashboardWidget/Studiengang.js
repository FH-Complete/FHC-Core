import AbstractWidget from './Abstract.js';
import StudiengangInformation from '../Cis/Cms/StudiengangInformation/StudiengangInformation.js';

export default {
	data(){
		return {};
	},
	components:{
		StudiengangInformation,
	},
	mixins:[AbstractWidget],
	mounted(){
		this.$emit('setConfig', false);
	},
	template:/*html*/`
		<div class="p-3 h-100 overflow-scroll">
			<studiengang-information displayWidget ></studiengang-information>
		</div>
	`	
};
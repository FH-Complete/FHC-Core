export default {
	data(){
		return {

		}
	},
	props:{
		data:{
			type:Object|String,
		}
	},
	computed:{
		componentData: function(){
			if(!this.data){
				return "Pass data to be printed by adding the :data prop on the component";
			}
			return JSON.stringify(this.data, null, 2);
		},
	},
	template:`<pre class="p-2 bg-secondary text-white" >{{componentData}}</pre>`
}
export default {
	data(){
		return {

		}
	},
	methods:{
		onEnter(el,done){
			el.style.height = '0';
			el.style.height = el.scrollHeight + 'px';
		},
		onLeave(el,done){
			el.style.height = el.scrollHeight + 'px';
			el.style.height = '0';
		}
	},
	template:
	/*html*/`
		<Transition name="height" @enter="onEnter" @leave="onLeave">
			<slot>
			</slot>
		</Transition>
	`,
};
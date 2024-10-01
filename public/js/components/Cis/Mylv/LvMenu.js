
export default {
	props:{
		isMenuSelected:{
			type:Boolean,
			default:false,
		},
		menu:{
			type:Array,
			default:null,
		},
		preselectedMenu: {
			type: Object,
			default: null,
		},
	},
	data(){
		return{
			selectedMenu:null,
		}
	},
	emits:["update:isMenuSelected"],
	methods:{
		selectMenu: function (menuItem, index = null) {

			// early return if link is #
			if (index != null && menuItem.c4_linkList[index][1] == '#') return;

			switch (menuItem.id) {
				case "core_menu_mailanstudierende": window.location.href = menuItem.c4_link; break;
				default:
					this.selectedMenu = { ...menuItem };
					this.$emit("update:isMenuSelected", true);
			}

			if (this.selectedMenu && index != null && menuItem.c4_linkList[index][1] != '#') {
				this.selectedMenu.c4_link = menuItem.c4_linkList[index][1];
				this.selectedMenu.name += ' - ' + menuItem.c4_linkList[index][0];
			}

			
		},
		back: function(){
			if(this.preselectedMenu){
				this.$emit('hideModal');
			}else{
				this.$emit('update:isMenuSelected', false);
			}
		},
	},
	watch:{
		'preselectedMenu': function (newValue) {
			if (newValue) {
				this.selectedMenu = newValue;
				this.$emit("update:isMenuSelected", true);
			}
		},
		isMenuSelected: function (newValue) {
			// if no Menu point has been selected, show all Menu options
			if (!newValue) {
				this.selectedMenu = null;
			}
		}
	},
	template:/*html*/`
	<div v-if="selectedMenu" class="d-flex flex-column h-100">
		<div class="d-flex mb-2">
			<button v-if="selectedMenu" @click="back" class="btn btn-secondary me-2"><i class="fa fa-chevron-left"></i> Back</button>
			<h2>{{selectedMenu.name}}</h2>
		</div>
		<iframe class="h-100 w-100" :src="selectedMenu.c4_link" :title="selectedMenu.name"></iframe>
	</div>
	<div v-else-if="!menu">No Menu available</div>
	<div v-else class="container">
		<div class="row">
			<div style="min-height:150px" :title="menuItem.name" role="button" @click="selectMenu(menuItem)" class="col-12 col-lg-6 col-xl-4 border border-1 d-flex flex-column align-items-center justify-content-center p-1 text-center" v-for="(menuItem, index) in menu" :key="index">
				<img :src="menuItem.c4_icon" :alt="menuItem.name" ></img>
				<span @click="selectMenu(menuItem)" class="underline_hover mt-2">{{menuItem.name}}</span>
				<span v-for="([text,link],index) in menuItem.c4_linkList" @click.stop="selectMenu(menuItem,index)"  :class="{'underline_hover':menuItem.c4_linkList[index][1] != '#'}" class="mt-1" :index="index">{{text}}</span>

			</div>
		</div>
	</div>
	`
}
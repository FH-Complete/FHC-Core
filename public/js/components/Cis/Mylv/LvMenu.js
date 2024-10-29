
export default {
	props:{
		menu:{
			type:Array,
			default:null,
		},
	},
	data(){
		return{
			
		}
	},
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
	},
	template:/*html*/`
	<div v-if="!menu">No Menu available</div>
	<div v-else class="container">
		<div class="row">
			<a style="min-height:150px" :title="menuItem.name" role="button" :href="menuItem.c4_link" class="g-2 col-12 col-lg-6 col-xl-4 border border-1 d-flex flex-column align-items-center justify-content-center p-1 text-center text-decoration-none link-dark" v-for="(menuItem, index) in menu" :key="index">
				<img :src="menuItem.c4_icon" :alt="menuItem.name" ></img>
				<span @click="selectMenu(menuItem)" class=" mt-2">{{menuItem.name}}</span>
				<span v-for="([text,link],index) in menuItem.c4_linkList" @click.stop="selectMenu(menuItem,index)"   class="mt-1" :index="index">{{text}}</span>
			</a>
		</div>
	</div>
	`
}
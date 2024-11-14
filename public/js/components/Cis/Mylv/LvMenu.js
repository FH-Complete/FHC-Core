
export default {
	props:{
		menu:{
			type:Array,
			default:null,
		},
		containerStyles: Array,
		rowStyles: Array,
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
	<div v-else >
		<div class="container" :class="containerStyles">
			<div class="row g-2 justify-content-center" :class="rowStyles">
				<div style="min-height:150px; min-width:150px;" class="col-12 col-lg-6 col-xl-4" v-for="(menuItem, index) in menu" :key="index">
					<a :title="menuItem.name" :target="menuItem.c4_target ?? null" role="button" :href="menuItem.c4_link" class="p-2 w-100 text-wrap border border-1 d-flex flex-column align-items-center justify-content-center text-center text-decoration-none link-dark h-100">
						<img :src="menuItem.c4_icon" :alt="menuItem.name" ></img>
						<p @click="selectMenu(menuItem)" class="w-100 mt-2">{{menuItem.name}}</p>
						<p v-for="([text,link],index) in menuItem.c4_linkList" @click.stop="selectMenu(menuItem,index)" class="mt-1 w-100" :index="index">{{text}}</p>
					</a>
				</div>
			</div>
		</div>
	</div>
	`
}
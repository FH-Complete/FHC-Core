
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
		c4_link(menuItem) {
			if (!menuItem) return null;
			if (Array.isArray(menuItem.c4_moodle_links) && menuItem.c4_moodle_links.length) 
			{
				return '#';
			}
			else
			{
				return menuItem.c4_link ?? null;
			}
		},
	},
	template:/*html*/`
	<div v-if="!menu">No Menu available</div>
	<div v-else>
		<div class="container" :class="containerStyles">
			<div class="row g-2 justify-content-center" :class="rowStyles">
				<div style="min-height:150px; min-width:150px;" class="col-12 col-lg-6 col-xl-4" v-for="(menuItem, index) in menu" :key="index">
					<a :id="menuItem.name" :class="{'dropdown-toggle':menuItem.c4_moodle_links?.length }" :target="menuItem.c4_target ?? null" role="button" :href="c4_link(menuItem)"
					:disabled="!c4_link(menuItem)?true:null"
					class="fhc-entry p-2 w-100 text-wrap border border-1 rounded-3 d-flex flex-column align-items-center justify-content-center text-center text-decoration-none link-dark h-100">
						<img :src="menuItem.c4_icon" :alt="menuItem.name" />
						<p @click="selectMenu(menuItem)" class="w-100 mt-2">{{menuItem.name}}</p>
						<p v-for="([text,link],index) in menuItem.c4_linkList" @click.stop="selectMenu(menuItem,index)" class="mt-1 w-100" :index="index">{{text}}</p>
					</a>
					<ul v-if="menuItem.c4_moodle_links?.length" class="dropdown-menu p-0" :aria-labelledby="menuItem.name">
						<li v-for="item in menuItem.c4_moodle_links"><a class="dropdown-item border-bottom" :href="item.url">{{item.lehrform}}</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	`
}
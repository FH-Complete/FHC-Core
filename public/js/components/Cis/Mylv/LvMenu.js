
export default {
	props:{
		menu:{
			type:Array,
			default:null,
		},
		containerStyles: Array,
		rowStyles: Array,
		hasLvPlanEintraege: {
			required:false,
			default:true,
			type:Boolean,
		},
	},
	data(){
		return{
			
		}
	},
	methods:{
		c4_disabled: function (menuItem) {
			if (!this.c4_link(menuItem) && !menuItem.c4_moodle_links?.length) {
				return true;
			}
			if (menuItem.id == "addon_fhtw_menu_lvplan_lva" && !this.hasLvPlanEintraege){
				return true;
			}
			return null;
		},
		c4_target: function (menuItem) {
			if (menuItem.c4_moodle_links?.length > 0) return null;
			return menuItem.c4_target ?? null;
		},
		c4_link(menuItem) {
			if (!menuItem) return null;
			if (Array.isArray(menuItem.c4_moodle_links) && menuItem.c4_moodle_links.length) 
			{
				return null;
			}
			else
			{
				return menuItem.c4_link ?? null;
			}
		},
		getMenuName(menuItem) {
			if(menuItem.phrase) {
				return this.$p.t(menuItem.phrase)
			} else {
				return menuItem.name
			}
		}
	},
	template:/*html*/`
	<div v-if="!menu">{{$p.t('lehre','lehrveranstaltungsUnavailable')}}</div>
	<div id="cis-menu" v-else>
		<div class="container" :class="containerStyles">
			<div class="row g-2 justify-content-center" :class="rowStyles">
				<div style="min-height:150px; min-width:150px;" class="col-12 col-lg-6 col-xl-4" v-for="(menuItem, index) in menu" :key="index">
					<a :id="menuItem.name" :class="{'dropdown-toggle':menuItem.c4_moodle_links?.length }"  role="button" :href="c4_link(menuItem)"
					:disabled="c4_disabled(menuItem)" :data-bs-toggle="menuItem.c4_moodle_links?.length?'dropdown':null"
					class="menu-entry p-2 w-100 text-wrap border border-1 rounded-3 d-flex flex-column align-items-center justify-content-center text-center text-decoration-none link h-100">
						<img :src="menuItem.c4_icon" :alt="menuItem.name" />
						<p class="w-100 mt-2 mb-0">{{ getMenuName(menuItem) }}</p>
						<a v-for="([text,link],index) in menuItem.c4_linkList" target="_blank" :href="link" class="my-1 w-100 submenu text-decoration-none" :index="index">{{text}}</a>
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
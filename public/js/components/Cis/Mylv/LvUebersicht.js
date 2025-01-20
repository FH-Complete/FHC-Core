import BsModal from "../../Bootstrap/Modal";
import LvMenu from "./LvMenu";
export default  {
  
    props:{
        event:{
            type:Object,
            required:true,
            default:null,
        },
		// prop used to preselect a menu item and skip the grid overview
		preselectedMenu: {
			type: Object,
			required: false,
			default: null,
		}
    },
    data(){
        return {
            result: false,
            menu: [],
			isMenuSelected:false,
            
        }
    },
    mixins:[BsModal],
    components:{
        BsModal,
		LvMenu,
    },
    methods:{
        
        hiddenModal: function(){
			this.isMenuSelected = false;
        },
        showModal: function(){
			if(!this.preselectedMenu){
				this.$fhcApi.factory.addons.getLvMenu(this.event.lehrveranstaltung_id, this.event.studiensemester_kurzbz).then(res =>{
					if(res.data){
						this.menu = res.data;
					}
				});
			}else{
				this.isMenuSelected = true;
			}
        },
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
    },
    template:/*html*/`
    <bs-modal :bodyClass="isMenuSelected ? '' : 'px-4 py-5'" @showBsModal="showModal" @hiddenBsModal="hiddenModal" ref="modalContainer" :dialogClass="{'modal-lg': !isMenuSelected, 'modal-fullscreen':isMenuSelected}">
        <template #title>
            <span v-if="event?.lehrfach_bez ">{{event?.lehrfach_bez + (event?.stg_kurzbzlang?' / ' + event?.stg_kurzbzlang:'')}}</span>
            <span v-else>Lehrveranstaltungs Übersicht</span>

        </template>
        <template #default>
			<lv-menu v-model:isMenuSelected="isMenuSelected" :preselectedMenu="preselectedMenu" :menu="menu" @hideModal="hide"></lv-menu>
        </template>
        
    </bs-modal>

    
    `,
};
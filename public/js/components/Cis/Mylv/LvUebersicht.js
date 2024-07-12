import BsModal from "../../Bootstrap/Modal";

export default  {
  
    props:{
        event:{
            type:Object,
            required:true,
            default:null,
        }
    },
    data(){
        return {
            result: false,
            menu: [],
            selectedMenu:null,
            
        }
    },
    mixins:[BsModal],
    components:{
        BsModal,
    },
    methods:{
        selectMenu: function(menuItem, key=null){
            console.log(menuItem,"menuItem")
            switch(menuItem.id){
                case "core_menu_mailanstudierende": window.location.href=menuItem.c4_link; break;
                default:
                    this.selectedMenu= {...menuItem};
            }

            if( this.selectedMenu && key != null && menuItem.c4_linkList[key] !='#'){
                this.selectedMenu.c4_link = menuItem.c4_linkList[key];
                this.selectedMenu.name += ' - ' + key;
            }
            
        },
        hiddenModal: function(){
            this.selectedMenu = null;
        },
        showModal: function(){

            this.$fhcApi.factory.addons.getLvMenu(this.event.lehrveranstaltung_id, this.event.studiensemester_kurzbz).then(res =>{
            //this.$fhcApi.factory.addons.getLvMenu(750, "WS2005").then(res =>{
                    this.menu = res.data;
            });
        },
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
    },
    template:/*html*/`
    <bs-modal :bodyClass="selectedMenu ? '' : 'px-4 py-5'" @showBsModal="showModal" @hiddenBsModal="hiddenModal" ref="modalContainer" :dialogClass="{'modal-lg': !selectedMenu, 'modal-fullscreen':selectedMenu}">
        <template #title>
            <span v-if="event?.lehrfach_bez ">{{event?.lehrfach_bez + (event?.stg_kurzbzlang?' / ' + event?.stg_kurzbzlang:'')}}</span>
            <span v-else>Lehrveranstaltungs Übersicht</span>
            
        </template>
        <template #default>

          <div v-if="selectedMenu" class="d-flex flex-column h-100">
                <div class="d-flex mb-2">
                <button v-if="selectedMenu" @click="selectedMenu=null" class="btn btn-secondary me-2"><i class="fa fa-chevron-left"></i> Back</button>
                <h2>{{selectedMenu.name}}</h2>
                </div>
                <iframe class="h-100 w-100" :src="selectedMenu.c4_link" :title="selectedMenu.name"></iframe>
            </div>
            <div v-else :style="{'display':'grid', 'row-gap':'10px', 'column-gap':'10px', 'grid-template-columns':'repeat(3,minmax(100px,1fr))', 'grid-template-rows':'repeat('+Math.ceil(menu.length / 3)+',minmax(100px,1fr))'} ">
                <div :title="menuItem.name" role="button" @click="selectMenu(menuItem)" class="lvUebersichtMenuPunkt border border-1 d-flex flex-column align-items-center justify-content-center p-1" v-for="(menuItem, index) in menu" :key="index">
                    <img :src="menuItem.c4_icon" :alt="menuItem.name" ></img>    
                    <span @click="selectMenu(menuItem)" class="underline_hover mt-2">{{menuItem.name}}</span> 
                    <span v-for="(value,key,index) in menuItem.c4_linkList" @click.stop="selectMenu(menuItem,key)" class="underline_hover mt-1" :index="index">{{key}}</span>
                        
                </div>
            </div>
            <div>{{JSON.stringify(event,null,2)}}</div>
        </template>
        
    </bs-modal>

    
    `,
};
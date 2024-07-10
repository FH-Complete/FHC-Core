import BsModal from "../../Bootstrap/Modal";

export default  {
  
    props:{
        event:{
            type:Object,
            required:true,
        }
    },
    data(){
        return {
            result: false,
            menu: [],
        }
    },
    mixins:[BsModal],
    components:{
        BsModal,
    },
    methods:{
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
    <bs-modal @showBsModal="showModal" ref="modalContainer" dialogClass="modal-lg">
        <template #title>
            <!--<span v-if="event.lv ">{{event.lv + (event.stg?' / ' + event.stg:'')}}</span>-->
            <span >{{JSON.stringify(event,null,2)}}</span>
            <!--Lehrveranstaltungs Übersicht-->
        </template>
        <template #default>
            <div :style="{'display':'grid', 'row-gap':'10px', 'column-gap':'10px', 'grid-template-columns':'repeat(3,minmax(100px,1fr))', 'grid-template-rows':'repeat('+Math.ceil(menu.length / 3)+',minmax(100px,1fr))'} ">
                <div  v-for="(menuItem, index) in menu" :key="index">
                    <a :onclick="menuItem.cis4_link_onclick?menuItem.cis4_link_onclick:null" class="d-flex flex-column align-items-center justify-content-center" :href="menuItem.cis4_link_onclick?'#':menuItem.cis4_link" :title="menuItem.name">
                        <span>{{menuItem.name}}</span>
                        <img :src="menuItem.cis4_icon" :alt="menuItem.name" ></img>
                    </a>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </template>
    </bs-modal>
    `,
};
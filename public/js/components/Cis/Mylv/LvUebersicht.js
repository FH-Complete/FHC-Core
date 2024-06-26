import BsModal from "../../Bootstrap/Modal";

export default  {
  
    
    data(){
        return {
            // reactive data
            items:["lehrveranstaltungsInformationen","Notenlisten","Moodle","Gesamtnote","E-mail","Pinboard","Alle Termine der LV","Anrechnung","Evaluierung","Neue Einmeldung"],
            lehreinheit:null,
            stg:null,
            lv:null,

            result: false,
        }
    },
    inject:["active_addons"],
    mixins:[BsModal],
    components:{
        BsModal,
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
        
    },
    
    template:/*html*/`
    <bs-modal ref="modalContainer" dialogClass="modal-lg">
        <template #title>
            <span v-if="lv ">
            {{lv + (stg?' / ' + stg:'')}}
            </span>
            <span v-else>
            Lehrveranstaltungs Übersicht
            </span>
        </template>
        <template #default>
            <div :style="{'display':'grid', 'row-gap':'10px', 'column-gap':'10px', 'grid-template-columns':'repeat(3,minmax(100px,1fr))', 'grid-template-rows':'repeat('+Math.ceil(items.length / 3)+',minmax(100px,1fr))'} ">
                <div class="d-flex flex-column align-items-center justify-content-center" v-for="item in items" :key="item">
                    <span>{{item}}</span>
                    <i class="fa fa-file"></i>
                </div>
            </div>
        </template>
        <template #footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </template>
    </bs-modal>
    `,
};
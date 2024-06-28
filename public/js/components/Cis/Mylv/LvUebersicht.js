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
            // reactive data
            items:["lehrveranstaltungsInformationen","Notenlisten","Moodle","Gesamtnote","E-mail","Pinboard","Alle Termine der LV","Anrechnung","Evaluierung","Neue Einmeldung"],
            lehreinheit:null,
            stg:null,
            lv:null,
            emailAnStudierende:null,

            result: false,
        }
    },
    inject:["active_addons","mail_studierende"],
    mixins:[BsModal],
    components:{
        BsModal,
    },
    methods:{
        showModal: function(){
            this.$fhcApi.factory.lehre.getStudentenMail(this.event.lehreinheit_id).then(res =>
            {
                // prepare the mailto link with all the emails from the students of the lv
                this.emailAnStudierende = "mailto:"+res.data.join(",");
            });
        },
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
        
    },
    template:/*html*/`
    <bs-modal @showBsModal="showModal" ref="modalContainer" dialogClass="modal-lg">
        <template #title>
        
        <p>{{JSON.stringify(emailAnStudierende,null,2)}}</p>    
        <p>{{JSON.stringify(event,null,2)}}</p>
            <!--<span v-if="lv ">
            {{lv + (stg?' / ' + stg:'')}}
            </span>
            <span v-else>
            Lehrveranstaltungs Übersicht
            </span>-->
        </template>
        <template #default>
            <div :style="{'display':'grid', 'row-gap':'10px', 'column-gap':'10px', 'grid-template-columns':'repeat(3,minmax(100px,1fr))', 'grid-template-rows':'repeat('+Math.ceil(items.length / 3)+',minmax(100px,1fr))'} ">
                <div class="d-flex flex-column align-items-center justify-content-center" v-for="item in items" :key="item">
                    <a v-if="item=='E-mail'" :href="emailAnStudierende">
                        <span>{{item}}</span>
                        <i class="fa fa-file"></i>
                    </a>
                    <a v-else :href="#">
                        <span>{{item}}</span>
                        <i class="fa fa-file"></i>
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
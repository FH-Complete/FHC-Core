import BsModal from "../../Bootstrap/Modal";


export default {

   
    mixins:[BsModal],
    
    components:{
        BsModal,
    },
    data(){
        return{
            content_id:null,
            content:null,
            ort_kurzbz:null,
            result: false,
        };
    },
    methods:{
        modalShown: function(){
            
        }
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
        document.addEventListener("show.bs.modal", function(){
            console.log("modal is shown inside the mounted hook")
        })  
    },
    
    template:/*html*/`
    <bs-modal @showBsModal="modalShown" ref="modalContainer" dialogClass="modal-lg">
        <template #title>
            <span v-if="ort_kurzbz">{{ort_kurzbz}}</span>
            <span v-else>Ort Übersicht</span>
        </template>
        <template #default>
            <div v-if="content" v-html="content"></div>
            <div v-else>this is the else div</div>
        </template>
        <template #footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </template>
    </bs-modal>
    `
};
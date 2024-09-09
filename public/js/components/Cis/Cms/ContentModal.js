import BsModal from "../../Bootstrap/Modal";


export default {

   
    mixins:[BsModal],
    
    components:{
        BsModal,
    },
    props:{
        contentID:{
            type: Number,
            required: true,
        },
        ort_kurzbz:{
            type: String,
            required: true,
        }
    },
    data(){
        return{
            result: false,
            content: null,
        };
    },
    
    methods:{
        modalHidden: function(){
            // reseting the content of the modal
            this.content = null;
        },
        // this method is always called when the modal is shown
        modalShown: function(){
            
            if(this.contentID){
                this.$fhcApi.factory.cms.content(this.contentID).then(res =>{
                this.content = res.data;
                
                })
            } 
        },
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
          
    },
    
    template:/*html*/`
    <bs-modal @hideBsModal="modalHidden" @showBsModal="modalShown" ref="modalContainer" dialogClass="modal-lg">
        <template #title>
            <span v-if="ort_kurzbz">{{ort_kurzbz}}</span>
            <span v-else>Raum Informationen</span>
        </template>
        <template #default>
            <div v-if="content" v-html="content"></div>
            <div v-else>Der Content für diesen Raum konnte nicht geladen werden</div>
        </template>
        <template #footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </template>
    </bs-modal>
    `
};
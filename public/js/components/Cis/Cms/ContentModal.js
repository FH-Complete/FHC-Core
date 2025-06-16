import BsModal from "../../Bootstrap/Modal.js";
import RaumContent from "./Content_types/Raum_contentmittitel.js";

import ApiCms from '../../../api/factory/cms.js';

export default {

   
    mixins:[BsModal],
    
    components:{
        BsModal,
        RaumContent,
    },
    props:{
        content_id:{
            type: Number
        },
        ort_kurzbz:{
            type: String
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
            
            if (this.content_id) {
                this.$api
                    .call(ApiCms.content(this.content_id))
                    .then(res => {
                        this.content = res.data.content;
                        this.type = res.data.type;
                    });
            } 
        },
    },
    mounted(){
        this.modal = this.$refs.modalContainer;
        
    },
    
    template:/*html*/`
    <bs-modal @hideBsModal="modalHidden" dialogClass="modal-xl" @showBsModal="modalShown" ref="modalContainer">
        <template #title>
            <span v-if="ort_kurzbz">{{ort_kurzbz}}</span>
            <span v-else>Raum Informationen</span>
        </template>
        <template #default>
            <RaumContent v-if="content" :content="content" :content_id="content_id"></RaumContent>
            <div v-else>Der Content für diesen Raum konnte nicht geladen werden</div>
        </template>
        <template #footer>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </template>
    </bs-modal>
    `
};
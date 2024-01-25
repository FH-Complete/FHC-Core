import Adresse from "./Adresse.js";
import Kontakt from "./Kontakt.js";

export default {
    components:{
        Adresse,
        Kontakt,
    },
    data(){
        return {

        }
    },
    computed:{
        getComponentView: function(){
            let title = this.topic.toLowerCase();
            if(title.includes("adressen")) return "Adresse";
            else if(title.includes("kontakte"))return "Kontakt";
            else return "text_input";
            
        },
        cardHeader: function(){
            let title = this.topic.toLowerCase();
            if(title.includes("delete")) return "Delete";
            else if(title.includes("add")) return "Add";
            else return "Update";
        }
    },
    props:{
        data:{type:Object},
        view:{type:String},
        status:{type:String},
        status_message:{type:String},
        status_timestamp:{type:String},
        update:{type:Boolean},
        topic:{type:String},
    },
    created(){

    },template:`
  
    <div  class="form-underline mb-2">
    <div class="form-underline-titel">Status</div>
    <span  class="form-underline-content">{{status}} </span>
    </div>

    <div v-if="status_message" class="form-underline mb-2 ">
    <div class="form-underline-titel">Status message</div>
    <span  class="form-underline-content">{{status_message}} </span>
    </div>

    <div  class="form-underline mb-2">
    <div class="form-underline-titel">Date</div>
    <span  class="form-underline-content">{{status_timestamp}} </span>
    </div>
    
    <div class="card mt-4">
    <div class="card-header">
    <i class="fa" :class="{'fa-trash':cardHeader==='Delete', 'fa-edit':cardHeader==='Update', 'fa-plus':cardHeader==='Add'}" ></i>
    {{cardHeader}} 
    </div>
    <div class="card-body">
    <div v-if="getComponentView === 'text_input'"  class="form-underline mb-2">
    <div class="form-underline-titel">{{topic}}</div>
    <span  class="form-underline-content">{{data}} </span>
    </div>
    <component v-else :is="getComponentView" :data="data"></component>
    
    </div>
    </div>
    `,

}
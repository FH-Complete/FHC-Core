export default {

    data(){
        return {

        }
    },
    props:{
        data:{type:Object},
        view:{type:String},
        status:{type:String},
        status_message:{type:String},
        status_timestamp:{type:String},
        update:{type:Boolean},
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
    </div>`,

}
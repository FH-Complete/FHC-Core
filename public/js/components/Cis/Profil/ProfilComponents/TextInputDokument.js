import Dms from "../../../Form/Upload/Dms.js";


export default {
    data(){
        return {
            dmsData:[],
            originalValue:null,
            originalFiles:null,
        }
    },
    components:{
        Dms,
    },
    props:{
       
        data:{
            type:Object,
        },
        withFiles:{
            type:Boolean,
            default:false,
        },
        files:{
            type:FileList,
        },
        update:{
            type:Boolean,
        }
    },
    computed: {
        isChanged: function(){
            if(this.update ){
                if(this.originalFiles !== this.dmsData || this.originalValue !== JSON.stringify(this.data)){
                    return true;
                }else{
                    return false;
                }
            }else{
                //? controls whether the user is allowed to send the profil update or not
                if(this.withFiles && !this.dmsData.length) {return false;}     
                return JSON.stringify(this.data) !== Vue.toRaw(this.originalValue);
            }
            
        }

    },
    emits:["profilUpdate"],
    watch: {
        //? watcher to trigger the event emit when a file was uploaded or removed
        dmsData(value) {
          this.emitChanges();
          console.log("dmsData",this.dmsData);
          console.log("original files",Vue.toRaw(this.originalFiles));
          console.log("compare",this.dmsData == Vue.toRaw(this.originalFiles));
        }
      },
    methods:{

        emitChanges: function(){
            if(this.isChanged){
                
                this.$emit('profilUpdate', this.withFiles?{value:this.data.value, files:this.dmsData}:{value:this.data.value});
            }else{
                
                this.$emit('profilUpdate',null);
            }  
        },
        
    },
    created(){
        
        this.originalValue = JSON.stringify(this.data);
        this.originalFiles = this.files;

        if(this.files){
            this.dmsData = this.files;
        }
        
    },
    template:`
   
    <div class="form-underline">
    <div class="form-underline-titel">{{data.titel?data.titel:"titel"}}</div>

    <input  class="form-control" @input="emitChanges"  v-model="data.value" :placeholder="data.value">
  
    
    <dms v-if="withFiles" id="files" :noList="false" :multiple="true" v-model="dmsData"  ></dms>

    </div>
    `,
}
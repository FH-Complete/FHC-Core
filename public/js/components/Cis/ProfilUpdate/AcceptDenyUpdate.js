import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import Kontakt from "../Profil/ProfilComponents/Kontakt.js";
import Adresse from "../Profil/ProfilComponents/Adresse.js";


export default {
  components: {
    BsModal,
    Alert,
    Kontakt,
    Adresse,
   
  },
  mixins: [BsModal],
  props: {
    title:{
        type:String,
        default:"Profil Update Request"
    },
    value: {
        type:Object,
    },
    /*
     * NOTE(chris):
     * Hack to expose in "emits" declared events to $props which we use
     * in the v-bind directive to forward all events.
     * @see: https://github.com/vuejs/core/issues/3432
     */
    onHideBsModal: Function,
    onHiddenBsModal: Function,
    onHidePreventedBsModal: Function,
    onShowBsModal: Function,
    onShownBsModal: Function,
  },
  data() {
    return {      
      data: this.value,
      //? result is returned from the Promise when the modal is closed
      result: false,
      info: null,
    }
  },

  methods: {
    acceptRequest: function(){
      
      Vue.$fhcapi.ProfilUpdate.acceptProfilRequest(this.data).then(res =>{
        console.log("res",res);
        console.log("res.data",res.data);
        this.result = true;
      })
      this.hide();
    },

    denyRequest: function(){
      console.log(this.data.profil_update_id);
      Vue.$fhcapi.ProfilUpdate.denyProfilRequest(this.data).then(res =>{
        console.log("res",res);
        console.log("res.data",res.data);
        this.result = true;
      })
      this.hide();
    },
    
    submitProfilChange(){
        //TODO: check if the updated value is different from the original value before submitting the request
       if(false){

        //? inserts new row in public.tbl_cis_profil_update 
        //* calls the update api call if an update field is present in the data that was passed to the module
        Vue.$fhcapi.UserData[this.editData.update?'updateProfilRequest':'insertProfilRequest'](this.topic,this.profilUpdate).then((res)=>{
          
          if(res.data.error == 0){
            this.result= true;
            this.hide();
            Alert.popup("Ihre Anfrage wurde erfolgreich gesendet. Bitte warten Sie, während sich das Team um Ihre Anfrage kümmert.");
          }else{
            this.result= false;
            this.hide();
            Alert.popup("Ein Fehler ist aufgetreten: "+ JSON.stringify(res.data.retval));
          } 
         
        });
    }
    },
  },
  computed: {
    getComponentView: function(){
        
        if(this.data.topic.toLowerCase().includes("kontakt")){
            return "kontakt";
        }else if (this.data.topic.toLowerCase().includes("adresse")){
            return "adresse";
        }else{
            return "text_input";
        }
    },
  },
  created() {
    console.log("data passed as prop",this.data);

  },
  mounted() {
    this.modal = this.$refs.modalContainer.modal;
  },
  popup(options) {
    return BsModal.popup.bind(this)(null, options);
  },
  template: `

  <bs-modal ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" backdrop="false" >
    
    <template v-slot:title>
      {{title}}  
    </template>


    <template v-slot:default>

    <!-- debugging prints 
    <pre>{{JSON.stringify(data.profil_update_id,null,2)}}</pre>
    <pre>view {{getComponentView}}</pre>
    <pre>topic {{JSON.stringify(data.topic,null,2)}}</pre>
    
    -->
    
   <div class="row">
    <div  class="form-underline mb-2 col">
      <div class="form-underline-titel">Status: </div>

      <span  class="form-underline-content" >{{data.status}}</span>
    </div>


    <div  class="form-underline mb-2 col">
      <div class="form-underline-titel">Date of Status: </div>
      <!-- only status timestamp and status message can be null in the database -->
      <span  class="form-underline-content" >{{data.status_timestamp?data.status_timestamp:'-'}}</span>
    </div>


    
    <div  class="form-underline mb-2 col">
      <div class="form-underline-titel">Status message: </div>
      <!-- only status timestamp and status message can be null in the database -->
      <span  class="form-underline-content" >{{data.status_message? data.status_message : '-'}}</span>
    </div>




    <div  class="form-underline mb-2 col">
      <div class="form-underline-titel">UserID: </div>

      <span  class="form-underline-content" >{{data.uid}}</span>
    </div>




    <div  class="form-underline mb-2 col">
      <div class="form-underline-titel">Topic of Request: </div>

      <span  class="form-underline-content" >{{data.topic}}</span>
    </div>




    <div  class="form-underline mb-2 col">
      <div class="form-underline-titel">Date of Request:</div>

      <span  class="form-underline-content" >{{data.change_timestamp}}</span>
    </div>

    </div>

    <div class="row my-4">
    <div class="col ">
    <div class="card">
    <div class="card-header">update</div>
    <div class="card-body">
    
    <div v-if="getComponentView==='text_input'" class="form-underline mb-2">
      <div class="form-underline-titel">{{data.topic}}</div>

      <span  class="form-underline-content" >{{data.requested_change}}</span>
    </div>


    <component v-else :is="getComponentView" :data="data.requested_change"></component>
    </div>
    </div>
    </div>
    </div>
    
    </template>
    

    <template v-if="data.status === 'pending'"  v-slot:footer>
    <div  class="form-underline flex-fill">
      <div class="form-underline-titel">Message</div>

      <div class="d-flex flex-row gap-2">
        <input  class="form-control " v-model="data.status_message"  >
        <button  @click="acceptRequest" class="text-nowrap btn btn-success">Accept <i class="fa fa-check"></i></button>
        <button @click="denyRequest" class="text-nowrap btn btn-danger">Deny <i class="fa fa-xmark"></i></button>
      </div>
    </div>
     
    </template>
  
    </bs-modal>`,
};

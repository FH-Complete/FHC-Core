import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import Kontakt from "../Profil/ProfilComponents/Kontakt.js";
import Adresse from "../Profil/ProfilComponents/Adresse.js";


export default {
  components: {
    BsModal,
    Kontakt,
    Adresse,
  },
  mixins: [BsModal],
  props: {
    title: {
      type: String,
      default: "Profil Update Request",
    },
    value: {
      type: Object,
    },
    setLoading:{
      type: Function,
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
      loading: false,
      //? result is returned from the Promise when the modal is closed
      result: false,
      info: null,
      files:null,
    };
  },

  methods: {
    getDocumentLink: function(dms_id){
      return FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/show/${dms_id}`;
    },
    acceptRequest: function () {
      this.loading = true;
      this.setLoading(true);
      Vue.$fhcapi.ProfilUpdate.acceptProfilRequest(this.data).then((res) => {
        this.setLoading(false);
        this.loading = false;
        this.result = true;
      }).catch((e) => {
        Alert.popup(Vue.h('div',{innerHTML:e.response.data}));
      });
      this.hide();
    },

    denyRequest: async function () {
      this.loading = true;
      this.setLoading(true);
      Vue.$fhcapi.ProfilUpdate.denyProfilRequest(this.data).then((res) => {
        this.setLoading(false);
        this.loading = false;
        this.result = true;
      }).catch((e) => {
        Alert.popup(Vue.h('div',{innerHTML:e.response.data}));
      });
      this.hide();
    },

   
  },
  computed: {
    getComponentView: function () {
      if (this.data.topic.toLowerCase().includes("kontakt")) {
        return "kontakt";
      } else if (this.data.topic.toLowerCase().includes("adresse")) {
        return "adresse";
      } else {
        return "text_input";
      }
    },
  },
  created() {
   
     Vue.$fhcapi.ProfilUpdate.getProfilRequestFiles(this.data.profil_update_id).then((res) =>{
      this.files=res.data;
    }) 
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
    <div  class="form-underline mb-2 col-12 col-sm-6">
      <div class="form-underline-titel">Status: </div>

      <span  class="form-underline-content" >{{data.status}}</span>
    </div>


    <div v-if="data.status!=='pending'" class="form-underline mb-2 col-12 col-sm-6">
      <div class="form-underline-titel">Date of Status: </div>
      <!-- only status timestamp and status message can be null in the database -->
      <span  class="form-underline-content" >{{data.status_timestamp?data.status_timestamp:'-'}}</span>
    </div>


    
    <div  class="form-underline mb-2 col-12 col-sm-6">
      <div class="form-underline-titel">UserID: </div>

      <span  class="form-underline-content" >{{data.uid}}</span>
    </div>

    <div  class="form-underline mb-2 col-12 col-sm-6">
      <div class="form-underline-titel">Name: </div>

      <span  class="form-underline-content" >{{data.name}}</span>
    </div>

    <div  class="form-underline mb-2 col-12 col-sm-6">
      <div class="form-underline-titel">Topic of Request: </div>

      <span  class="form-underline-content" >{{data.topic}}</span>
    </div>




    <div  class="form-underline mb-2 col-12 col-sm-6">
      <div class="form-underline-titel">Date of Request:</div>

      <span  class="form-underline-content" >{{data.insertamum}}</span>
    </div>

    </div>

    <!-- Row with the status message is only visible if the request is not pending and the message is not empty -->
    <div v-if="data.status !=='pending' && data.status_message" class="row">
    <div class="col">
    <div  class="form-underline mb-2 ">
    <div class="form-underline-titel">Status message</div>
    <textarea  class="form-control" rows="4" disabled>{{data.status_message}} </textarea>
    </div>
    </div>
    </div>

    <div class="row my-4">
    <div class="col ">
    <div class="card">
    <div class="card-header">update</div>
    <div class="card-body">
    <template v-if="getComponentView==='text_input'">
    <div  class="form-underline mb-2">
      <div class="form-underline-titel">{{data.topic}}</div>

      <span  class="form-underline-content" >{{data.requested_change.value}}</span>
    </div>
    <div v-if="files?.length" class="ms-2">
    
    <a  v-for="file in files" target="_blank" :href="getDocumentLink(file.dms_id)" >{{file.name}}</a>
    </div>
    </template>


    <component v-else :is="getComponentView" :withZustelladresse="getComponentView==='adresse'?true:false" :data="data.requested_change"></component>
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

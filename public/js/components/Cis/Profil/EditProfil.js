import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import EditProfilSelect from "./EditProfilSelect.js";

export default {
  components: {
    BsModal,
    Alert,
    EditProfilSelect,
  },
  mixins: [BsModal],
  props: {

    value: Object,
    timestamp: Object,
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
      topic:null,
      profilUpdate:null,      
      editData: this.value,
      breadcrumb:null,
      
      result: false,
      info: null,
    }
  },

  methods: {
    submitProfilChange(){
      
       if(this.topic && this.profilUpdate){

        //? inserts new row in public.tbl_cis_profil_update 
        //* calls the update api call if an update field is present in the data that was passed to the module
        Vue.$fhcapi.UserData[this.editData.update?'updateProfilRequest':'insertProfilRequest'](this.topic,this.profilUpdate).then((res)=>{
          console.log("topic",this.topic);
          console.log("profilUpdate",this.profilUpdate);
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
  },
  created() {

    if(this.editData.topic){
      //? if the topic was passed through the prop add it to the component
      this.topic = this.editData.topic;
    }
   

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
      {{"Profil bearbeiten" }}  
    </template>
    <template v-slot:default>

    <nav aria-label="breadcrumb" class="ps-2  ">
      <ol class="breadcrumb ">
        <li class="breadcrumb-item"  v-for="element in breadcrumb">{{element}}</li>
      
      </ol>
    </nav>

    <pre>{{JSON.stringify(profilUpdate,null,2)}}</pre>
    <pre>{{JSON.stringify(topic,null,2)}}</pre>

    <edit-profil-select @submit="submitProfilChange" v-model:breadcrumb="breadcrumb" v-model:topic="topic" v-model:profilUpdate="profilUpdate" ariaLabel="test" :list="editData"></edit-profil-select>
   

    </template>
    <!-- optional footer -->
    <template   v-slot:footer>
      <button class="btn btn-outline-danger " @click="hide">Abbrechen</button>
      <!--<p v-if="editTimestamp" class="flex-fill">Letzte Anfrage: {{editTimestamp}}</p>-->
    
      <button v-if="profilUpdate"  @click="submitProfilChange" role="button" class="btn btn-primary">Senden</button>
    </template>
    <!-- end of optional footer --> 
  </bs-modal>`,
};

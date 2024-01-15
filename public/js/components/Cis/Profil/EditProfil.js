import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import BreadCrumb from "../Selection/Breadcrumb.js";
import EditProfilSelect from "./EditProfilSelect.js";

export default {
  components: {
    BsModal,
    Alert,
    BreadCrumb,
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
      propertySelected: false,
      testValue:null,
      testListe:{
        privateInfo:{username:"hans33",Titel:"Doktor", Anrede:"Herr"},
        privateKontakte:[{strasse:"strasse1",plz:100},{strasse:"strasse1",plz:100},{strasse:"strasse1",plz:100}],
        privateAdressen:[{kontakt:"telefon",anmerkung:"1"},{kontakt:"email",anmerkung:"2"},{kontakt:"telefon",anmerkung:"3"}]
      },
      testSelectedItems:[],
      profilUpdate:null,


      topic:null,
      firstSelectedOption:null,
      secondSelectedOption: null,
      secondSelectedOptionIndex: null,
    
    
      inputField:null,
      
      editData: this.value,
      //? tracks what specific profil data was changed
      changesData: {},
      editTimestamp: this.timestamp,
      
      result: true,
      info: null,
    }
  },

  methods: {

    
    
    selectEvent: function (option){
      this.editData = this.editData[option];
    },
    createDeepCopy: function(object){
      //? using Vue.toRaw because deep clones with structuredClone can not be done on proxies
      return structuredClone(Vue.toRaw(object));
    },

    changeInput: function(event, inputField,index){
      let newValue = event.target.value? event.target.value: null;
      inputField[index] = newValue; 

    },


     
    
    submitProfilChange(){
      
     
        //* only inserts new row if the inputField value is different from the original value
        if(this.topic && this.profilUpdate){

        //? inserts new row in public.tbl_cis_profil_update 
      if(this.editData.update){
        
        Vue.$fhcapi.UserData.updateProfilRequest(this.topic,this.profilUpdate).then((res)=>{
          this.result = {
            editData: this.editData,
            timestamp: res.data.retval,
          };
          this.hide(); 
          
          if(res.data.error == 0){
           
            Alert.popup("Ihre Anfrage wurde erfolgreich gesendet. Bitte warten Sie, während sich das Team um Ihre Anfrage kümmert.");
          }else{
            Alert.popup("Ein Fehler ist aufgetreten: "+ JSON.stringify(res.data.retval));
          } 
          //
        });
        //
     
      }else{

        Vue.$fhcapi.UserData.insertProfilRequest(this.topic,this.profilUpdate).then((res)=>{
          this.result = {
            editData: this.editData,
            timestamp: res.data.retval,
          };
          this.hide(); 
          
          if(res.data.error == 0){
           
            Alert.popup("Ihre Anfrage wurde erfolgreich gesendet. Bitte warten Sie, während sich das Team um Ihre Anfrage kümmert.");
          }else{
            Alert.popup("Ein Fehler ist aufgetreten: "+ JSON.stringify(res.data.retval));
          } 
          //
        });
        
      
    }
   
    }
    },
  },
  computed: {
  },
  created() {

    if(this.editData.topic){
      //? if the topic was passed through the prop add it to the reactive data
      this.topic = this.editData.topic;
    }
   

  },
  mounted() {
    this.modal = this.$refs.modalContainer.modal;
  },
  popup(options) {
    console.log("popup start");
    return BsModal.popup.bind(this)(null, options);
  },
  template: `
  
  <bs-modal ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" backdrop="false" >
    
  <template v-slot:title>
      {{"Profil bearbeiten" }}  
    </template>
    <template v-slot:default>
    <edit-profil-select v-model:topic="topic" v-model:profilUpdate="profilUpdate" ariaLabel="test" :list="editData"></edit-profil-select>
   

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

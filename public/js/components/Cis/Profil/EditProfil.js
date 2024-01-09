import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";


export default {
  components: {
    BsModal,
    Alert,
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
        if(this.isInputFieldChanged && this.topic){

        //? inserts new row in public.tbl_cis_profil_update 
      
        Vue.$fhcapi.UserData.editProfil(this.topic,this.inputField).then((res)=>{
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
    },
  },
  computed: {

    isInputFieldChanged: function(){
      if(this.inputField){
        return JSON.stringify(this.inputField) !== JSON.stringify(this.secondSelectedOption);
      }
      return false;
    },

    firstSelection(){
      return Object.keys(this.value);
    },

    secondSelection(){
      switch(this.firstSelectedOption){
        case "Personen_Informationen": return Object.keys(this.editData[this.firstSelectedOption]);
        case "Private_Kontakte": return this.editData[this.firstSelectedOption];
        case "Private_Adressen": return this.editData[this.firstSelectedOption];
        default: return [];
      }
    },

  
  },
  created() {
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

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item" v-if="firstSelectedOption">{{firstSelectedOption}}</li>
        <li class="breadcrumb-item" v-if="secondSelectedOption" >{{firstSelectedOption ==="Personen_Informationen" ? Object.keys(secondSelectedOption)[0] : secondSelectedOptionIndex+1 }}</li>
        <!--<li class="breadcrumb-item" aria-current="page">Drei</li>-->
      </ol>
    </nav>


    <select v-if="!firstSelectedOption" v-model="firstSelectedOption"  class="form-select" size="3" aria-label="size 3 select example">
      <option  v-for="option in firstSelection" :value="option">{{option}}</option>
    </select>

    <select v-if="firstSelectedOption && !secondSelectedOption" class="form-select" size="3" aria-label="size 3 select example">
      <option @click="
      
      if(Array.isArray(editData[firstSelectedOption])){
        secondSelectedOptionIndex = index;
        secondSelectedOption = createDeepCopy(option);
        inputField= createDeepCopy(option);
        //* topic is the property in which the array is stored when it is an array
        topic = firstSelectedOption;
      }else{
        secondSelectedOption={[option]:editData[firstSelectedOption][option]};
        inputField={[option]:editData[firstSelectedOption][option]};
        //* topic is the selected property if is an object
        topic = option;
      }" v-for="(option, index) in secondSelection" :value="option"><div v-if="typeof(option)==='object'"><span class="m-2 d-block" v-for="(value,property) in option">{{property}}:{{value}}</span></div><template v-else>{{option}}</template></option>
    </select>
    <div v-if="inputField">
    <div v-for="(field,index) in inputField">
    
    <div class="form-underline">
    <div class="form-underline-titel">{{index}}</div>

    <input  class="form-control" :id="index" :value="inputField[index]" @input="changeInput($event,inputField,index)" :placeholder="field">
    </div>
    
      </div>
    </div>

    
 
    
  
    </template>
    <!-- optional footer -->
    <template   v-slot:footer>
      <button class="btn btn-outline-danger " @click="hide">Abbrechen</button>
      <p v-if="editTimestamp" class="flex-fill">Letzte Anfrage: {{editTimestamp}}</p>
    
      <button v-if="isInputFieldChanged"  @click="submitProfilChange" role="button" class="btn btn-primary">Senden</button>
    </template>
    <!-- end of optional footer --> 
  </bs-modal>`,
};

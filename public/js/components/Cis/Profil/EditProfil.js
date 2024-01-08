import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
const infos = {};

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
    selection: null,
    propertySelection:true,
    selectedProperty:null,
    inputField:null,
    detailSelection:false,
    editData: this.value,
    //? tracks what specific profil data was changed
    changesData: {},
    editTimestamp: this.timestamp,
    selectionOrder: {firstSelect: true, secondSelect:false},
    
    result: true,
    info: null,
  }
 
  },

  methods: {

    formSelection: function(selection){
      
      if(Array.isArray(selection)){
        return ['a','b'];
      }else if(typeof(selection) === 'object'){
        console.log(selection);
        return Object.keys(selection);

      }else{
        // it is not an array or and object
        return null;
      }
    },
     
    updateData: function(event,key,ArrayKey,ObjectKey=null){

      const cleanUpObjectProperties= () => {
        Object.entries(this.changesData).forEach( ([property, value]) => { if(!Object.keys(value).length) delete this.changesData[property]; })
      }
      
      let newValue = event.target.value;
      if(!this.changesData[key]){
        Array.isArray(this.editData[key])? this.changesData[key] = [] : this.changesData[key] = {};
      } 
      
      if(Array.isArray(this.editData[key])){
        if(newValue.length > 0) this.editData[key][ArrayKey][ObjectKey]= newValue;
          
        else this.editData[key][ArrayKey][ObjectKey]= null;

        let Obj = {key:ArrayKey, new: this.editData[key][ArrayKey], old: JSON.parse(this.originalEditData)[key][ArrayKey]};

        
        if(JSON.stringify(this.editData[key][ArrayKey]) === JSON.stringify(JSON.parse(this.originalEditData)[key][ArrayKey]) ){
            this.changesData[key] = this.changesData[key].filter( arrayElement => arrayElement.key !== ArrayKey );
            cleanUpObjectProperties();
        }else{
          if(!this.changesData[key].filter( arrayElement => arrayElement.key === ArrayKey ).length){
            this.changesData[key].push(Obj);
          }
          
        }
      }else{
        if(newValue.length > 0) this.editData[key][ArrayKey]= newValue;
          
        else this.editData[key][ArrayKey]= null;

        let Obj = { new: this.editData[key][ArrayKey], old: JSON.parse(this.originalEditData)[key][ArrayKey]};

        
        if(JSON.stringify(this.editData[key][ArrayKey]) === JSON.stringify(JSON.parse(this.originalEditData)[key][ArrayKey])){
          delete this.changesData[key][ArrayKey];
          cleanUpObjectProperties();
        }else{
          this.changesData[key][ArrayKey]= Obj;
        }

      }
      
     
    },
    submitProfilChange(){
      
     
        //? inserts new row in public.tbl_cis_profil_update 


        Vue.$fhcapi.UserData.editProfil(this.inputField).then((res)=>{
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
     
      
    },
  },
  computed: {

    firstSelection(){
      return Object.keys(this.value);
    },

    secondSelection(){
      switch(this.selectedProperty){
        case "Personen_Informationen": return Object.keys(this.editData[this.selectedProperty]);
        case "Private_Kontakte": return this.editData[this.selectedProperty];
        case "Private_Adressen": return this.editData[this.selectedProperty];
        default: return [];
      }
    },

  

    
   
    
      isEditDataChanged(){
        return this.originalEditData != JSON.stringify(this.editData)
      },
  },
  created() {
    this.originalEditData = JSON.stringify(this.editData);
    
    /* 
    if (infos[this.lehrveranstaltung_id]) {
      this.info = infos[this.lehrveranstaltung_id];
    } else {
      axios
        .get(
          FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          "/components/Cis/Mylv/Info/" +
          this.studien_semester +
          "/" +
          this.lehrveranstaltung_id
        )
        .then((res) => {
          this.info = infos[this.lehrveranstaltung_id] = res.data.retval || [];
        })
        .catch(() => (this.info = {}));
    } */
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

    

    <select v-if="!selectedProperty"  class="form-select" size="3" aria-label="size 3 select example">
      <option @click="selectedProperty=option;  " v-for="option in firstSelection" :value="option">{{option}}</option>
    </select>

    <select v-if="selectedProperty && !inputField" class="form-select" size="3" aria-label="size 3 select example">
      <option @click="
      if(Array.isArray(editData[selectedProperty])){
        inputField=option
      }else{
        inputField={[option]:editData[selectedProperty][option]};
      }" v-for="option in secondSelection" :value="option"><div v-if="typeof(option)==='object'"><p v-for="(value,property) in option">{{property}}:{{value}}</p></div><template v-else>{{option}}</template></option>
    </select>
    <div v-if="inputField">
    <div v-for="(field,index) in inputField">
    
    <div class="form-underline">
    <div class="form-underline-titel">{{index}}</div>

    <input  class="form-control" :id="index" v-model="inputField[index]" :placeholder="field">
    </div>
    
      </div>
    </div>

    
 
    
  
    </template>
    <!-- optional footer -->
    <template   v-slot:footer>
      <p v-if="editTimestamp" class="flex-fill">Letzte Anfrage: {{editTimestamp}}</p>
    
      <button  @click="submitProfilChange" role="button" class="btn btn-primary">Senden</button>
    </template>
    <!-- end of optional footer --> 
  </bs-modal>`,
};

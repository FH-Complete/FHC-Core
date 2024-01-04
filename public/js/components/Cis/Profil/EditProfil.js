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
    editData: this.value,
    //? tracks what specific profil data was changed
    changesData: {Emails: [],Private_Adressen:[],Private_Kontakte:[],Personen_Informationen:{},Mitarbeiter_Informationen:{} },
    editTimestamp: this.timestamp,
    result: true,
    info: null,
  }
 
  },

  methods: {
     
    updateData: function(event,key,ArrayKey,ObjectKey=null){
      if(Array.isArray(this.editData[key])){
        this.editData[key][ArrayKey][ObjectKey]= event.target.value;
        if(event.target.value === JSON.parse(this.originalEditData)[key][ArrayKey][ObjectKey]){
          this.changesData[key].splice(ArrayKey,1);
        }else{
          if(!this.changesData[key].includes(this.editData[key][ArrayKey])){
            this.changesData[key].push(this.editData[key][ArrayKey]);
          }
          
        }
      }else{
        console.log(key);
        this.editData[key][ArrayKey]= event.target.value;
        if(event.target.value === JSON.parse(this.originalEditData)[key][ArrayKey]){
          delete this.changesData[key][ArrayKey];
        }else{
          this.changesData[key][ArrayKey]= this.editData[key][ArrayKey];
        }
      }
      
     
    },
    submitProfilChange(){
      
       if(this.isEditDataChanged){
        //? inserts new row in public.tbl_cis_profil_update 


        Vue.$fhcapi.UserData.editProfil(this.editData).then((res)=>{
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
   
    getFormatedDate: function(){
			return [
			  this.editTimestamp.getDate().toString().padStart(2,'0'),
			  (this.editTimestamp.getMonth()+1).toString().padStart(2,'0'),
			  this.editTimestamp.getFullYear(),
			].join('/');
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


    
    <!-- START OF THE ACCORDION
     -->
     <pre>{{JSON.stringify(changesData,null,2)}}</pre>

    <div class="accordion accordion-flush" id="accordionFlushExample" >
      <div class="accordion-item" v-for="(value,key) in editData ">
        <h2 class="accordion-header" :id="'flush-headingOne'+key">
          <button style="font-weight:500" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" :data-bs-target="'#flush-collapseOne'+key" aria-expanded="false" :aria-controls="'flush-collapseOne'+key">
            {{key.replace("_"," ")}}
          </button>
        </h2>
        <!-- SHOWING ALL MAILS IN THE FIRST PART OF THE ACCORDION -->
        <div :id="'flush-collapseOne'+key" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
          <div class="accordion-body">
        
          <div v-if="Array.isArray(value)" class="row gy-5">
          
            <template  v-for="(object,objectKey) in value"  >
            <div class="col-12 ">
              <div class="row gy-3">
              <div v-for="(propertyValue,propertyKey) in object" class="col-6" >
              
              <div  class="form-underline ">
              <div class="form-underline-titel">
              <label :for="propertyKey+'input'" >{{propertyKey}}</label>
              </div>
              <div>
                <input  class="form-control" :id="propertyKey+'input'" :value="editData[key][objectKey][propertyKey]" @input="updateData($event,key,objectKey,propertyKey)" :placeholder="propertyValue">
              </div>
              </div>

              </div>
              </div>

              </div>
              <hr class="mb-0" v-if="value[value.length-1] != object">
            </template>
            

          
          </div>
          <div v-else class="row gy-3">
          <div  v-for="(propertyValue,propertyKey) in value" class="col-6">
        
          <div  class="form-underline ">
          <div class="form-underline-titel">
          <label :for="propertyKey+'input'" >{{propertyKey}}</label>
          </div>
          <div>
            
            <input type="email" class="form-control" :id="propertyKey+'input'" :value="editData[key][propertyKey]" @input="updateData($event,key,propertyKey)"  :placeholder="propertyValue">
          </div>
          </div>
          </div>
          </div>

          
          
          
          

          </div>
        </div>
      </div>

      <!-- -->

     


    <!-- END OF THE ACCORDION -->

    </template>
    <!-- optional footer -->
    <template v-if="editTimestamp || isEditDataChanged"  v-slot:footer>
      <p v-if="editTimestamp" class="flex-fill">Letzte Anfrage: {{editTimestamp}}</p>
    
      <button v-if="isEditDataChanged" @click="submitProfilChange" role="button" class="btn btn-primary">Senden</button>
    </template>
    <!-- end of optional footer --> 
  </bs-modal>`,
};

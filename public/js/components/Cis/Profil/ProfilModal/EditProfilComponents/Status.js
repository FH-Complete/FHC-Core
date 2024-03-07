import Adresse from "../../ProfilComponents/Adresse.js";
import Kontakt from "../../ProfilComponents/Kontakt.js";

export default {
  components: {
    Adresse,
    Kontakt,
  },
  data() {
    return {
      files:null,
    };
  },
  methods:{
    getDocumentLink: function(dms_id){
      return FHC_JS_DATA_STORAGE_OBJECT.app_root +
          FHC_JS_DATA_STORAGE_OBJECT.ci_router +
          `/Cis/ProfilUpdate/show/${dms_id}`;
    }
  },
  computed: {
    getComponentView: function () {
      let title = this.topic.toLowerCase();
      if (title.includes("adressen")) return "Adresse";
      else if (title.includes("kontakte")) return "Kontakt";
      else return "text_input";
    },
    cardHeader: function () {
      let title = this.topic.toLowerCase();
      if (title.includes("delete")) return "Delete";
      else if (title.includes("add")) return "Add";
      else return "Update";
    },
  },
  props: {
    data: { type: Object },
    view: { type: String },
    status: { type: String },
    status_message: { type: String },
    status_timestamp: { type: String },
    updateID: { type: Number },
    topic: { type: String },
  },
  created() {
    Vue.$fhcapi.ProfilUpdate.getProfilRequestFiles(this.updateID).then((res) =>{
      this.files = res.data;
    })
  },
  template: `
    <div class="row">

    <div class="col">
    <div  class="form-underline mb-2">
    <div class="form-underline-titel">Status</div>
    <span  class="form-underline-content">{{status}} </span>
    </div>
    </div>

    <div class="col">
    <div  class="form-underline mb-2">
    <div class="form-underline-titel">Date</div>
    <span  class="form-underline-content">{{status_timestamp}} </span>
    </div>
    </div>
 
    </div>
    <div class="row">
    <div class="col">
    <div v-if="status_message" class="form-underline mb-2 ">
    <div class="form-underline-titel">Status message</div>
    <textarea  class="form-control" rows="4" disabled>{{status_message}} </textarea>
    </div>
    </div>
    </div>


    <div class="card mt-4">
    <div class="card-header">
    <i class="fa" :class="{'fa-trash':cardHeader==='Delete', 'fa-edit':cardHeader==='Update', 'fa-plus':cardHeader==='Add'}" ></i>
    {{cardHeader}} 
    </div>
    <div class="card-body">
    <template v-if="getComponentView === 'text_input'">
    <div   class="form-underline mb-2">
    <div class="form-underline-titel">{{topic}}</div>
    <span  class="form-underline-content">{{data.value}} </span>
    </div>
    <div v-if="files?.length" class="ms-2">
    
    <a target="_blank" :href="getDocumentLink(file.dms_id)" v-for="file in files">{{file.name}}</a>
    </div>
    </template>
    <component v-else :is="getComponentView" :data="data"></component>
    
    </div>
    </div>
    `,
};

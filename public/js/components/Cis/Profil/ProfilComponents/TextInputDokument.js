import Dms from "../../../Form/Upload/Dms.js";

export default {
  data() {
    return {
      dmsData: [],
      originalValue: null,
    };
  },
  components: {
    Dms,
  },
  props: {
    data: {
      type: Object,
    },
    withFiles: {
      type: Boolean,
      default: false,
    },
    files: {
      type: Array,
    },
    updateID: {
      type: Boolean,
    },
  },
  computed: {
    didFilesChange: function () {
      let res = false;
      //? case in which the profilRequest has already associated files 
      if(this.files){ 
        Array.from(this.dmsData).forEach((file) => {
          if (this.files.some((f) => f.name !== file.name)) {
            res = true;
          }
        });
        return !(this.dmsData.length == this.files.length) || res;
      }
      //? case in which the user creates a new profilRequest
      else{  
        return Array.from(this.dmsData).length? true:false;
      }
    },
    didDataChange: function(){
      return JSON.stringify(this.data) !== this.originalValue;
    },
    isChanged: function () {
      if (this.withFiles) {
        if(this.updateID){
          return this.didDataChange || this.didFilesChange;
        }
        return this.didDataChange && this.didFilesChange;
      }
      return this.didDataChange
    },
  },
  emits: ["profilUpdate"],
  watch: {
    //? watcher to trigger the event emit when a file was uploaded or removed
    dmsData(value) {
      this.emitChanges();
    },
  },
  methods: {
    emitChanges: function () {
      if (this.isChanged) {
        this.$emit(
          "profilUpdate",
          this.withFiles
            ? { value: this.data.value, files: this.dmsData }
            : { value: this.data.value }
        );
      } else {
        this.$emit("profilUpdate", null);
      }
    },
  },
  mounted() {
    this.originalValue = JSON.stringify(Vue.toRaw(this.data));

    if (this.files) {
      this.dmsData = this.files;
    }
  },
  template: `
    <p style="opacity:0.8" class="ms-2" v-if="withFiles && !updateID">Please update your {{data.titel}} and upload the corresponding Document for proof</p>

    <div class="form-underline">
    <div class="form-underline-titel">{{data.titel?data.titel:"titel"}}</div>
    
    <input  class="mb-2 form-control" @input="emitChanges"  v-model="data.value" :placeholder="data.value">
  
    
    <dms ref="update" v-if="withFiles" id="files" :noList="false" :multiple="true" v-model="dmsData" @update:model-value="didFilesChange"  ></dms>

    </div>
    `,
};

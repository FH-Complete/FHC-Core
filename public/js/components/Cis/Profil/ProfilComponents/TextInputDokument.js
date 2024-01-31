import Dms from "../../../Form/Upload/Dms.js";

export default {
  data() {
    return {
      dmsData: [],
      originalValue: null,
      originalFiles: null,
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
      type: FileList,
    },
    updateID: {
      type: Boolean,
    },
  },
  computed: {
    isChanged: function () {
      //? controls whether the user is allowed to send the profil update or not
      if (this.withFiles && !this.dmsData.length) {
        return false;
      }
      return JSON.stringify(this.data) !== Vue.toRaw(this.originalValue);
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
      if (this.updateID || this.isChanged) {
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
    this.originalValue = JSON.stringify(this.data);

    if (this.files) {
      this.dmsData = this.files;

      for (let i = 0; i < this.dmsData.length; i++) {
        console.log("here", this.dmsData[i]);
      }

      this.originalFiles = null;
    }
  },
  template: `
   
    <p style="opacity:0.8" class="ms-2" v-if="withFiles && !updateID">Please update your {{data.titel}} and upload the corresponding Document for proof</p>

    <div class="form-underline">
    <div class="form-underline-titel">{{data.titel?data.titel:"titel"}}</div>
    
    <input  class="mb-2 form-control" @input="emitChanges"  v-model="data.value" :placeholder="data.value">
  
    
    <dms ref="update" v-if="withFiles" id="files" :noList="false" :multiple="true" v-model="dmsData"  ></dms>

    </div>
    `,
};

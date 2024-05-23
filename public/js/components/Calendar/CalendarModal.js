import BsModal from "../Bootstrap/Modal.js";
import Alert from "../Bootstrap/Alert.js";

export default {
  components: {
    BsModal,
    Alert,
  },
  mixins: [BsModal],
  props: {
    event:Object,
    title:{
        type:String,
        default:"title"
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
      data:this.event,
      topic: null,
      profilUpdate: null,
      editData: this.value,
      fileID: null,
      breadcrumb: null,
      loading: false,

      result: false,
      info: null,
    };
  },
  methods: {
    updateFileIDFunction: function (newFileID) {
      this.fileID = newFileID;
    },

    async submitProfilChange() {
      //? check if data is valid before making a request
      if (this.topic && this.profilUpdate) {
        //? if profil update contains any attachment
        if (this.fileID) {
          const fileData = await this.uploadFiles(this.fileID);

          this.fileID = fileData ? fileData : null;
        }

        //? inserts new row in public.tbl_cis_profil_update
        //* calls the update api call if an update field is present in the data that was passed to the modal
        const handleApiResponse = (res) => {
          //? toggles the loading to false and closes the loading modal
          this.loading = false;
          this.setLoading(false);

          if (res.data.error == 0) {
            this.result = true;
            this.hide();
            Alert.popup(
              "Ihre Anfrage wurde erfolgreich gesendet. Bitte warten Sie, während sich das Team um Ihre Anfrage kümmert."
            );
          } else {
            this.result = false;
            this.hide();
            Alert.popup(
              "Ein Fehler ist aufgetreten: " + JSON.stringify(res.data.retval)
            );
          }
        };

        //* v-show on EditProfil modal binded to this.loading
        //? hides the EditProfil modal and shows the loading modal by calling a callback that was passed as prop from the parent component
        this.loading = true;
        this.setLoading(true);

        this.editData.updateID
          ? Vue.$fhcapi.ProfilUpdate.updateProfilRequest(
              this.topic,
              this.profilUpdate,
              this.editData.updateID,
              this.fileID ? this.fileID[0] : null
            )
              .then((res) => {
                handleApiResponse(res);
              })
              .catch((err) => {
                console.error(err);
              })
          : Vue.$fhcapi.ProfilUpdate.insertProfilRequest(
              this.topic,
              this.profilUpdate,
              this.fileID ? this.fileID[0] : null
            )
              .then((res) => {
                handleApiResponse(res);
              })
              .catch((err) => {
                console.error(err);
              });
      }
    },

    uploadFiles: async function (files) {
      if (files[0].type !== "application/x.fhc-dms+json") {
        let formData = new FormData();
        formData.append("files[]", files[0]);
        const result = this.editData.updateID
          ? //? updating old attachment by replacing
            //* second parameter of api request insertFile checks if the file has to be replaced or not
            await Vue.$fhcapi.ProfilUpdate.insertFile(
              formData,
              this.editData.updateID
            ).then((res) => {
              return res.data?.map((file) => file.dms_id);
            })
          : //? fresh insert of new attachment
            await Vue.$fhcapi.ProfilUpdate.insertFile(formData).then((res) => {
              return res.data?.map((file) => file.dms_id);
            });
        return result;
      } else {
        //? attachment hasn't been replaced
        return false;
      }
    },
  },
  computed: {},
  created() {
    console.log("this is an test")
  },
  mounted() {
    this.modal = this.$refs.modalContainer.modal;
  },
  popup(options) {
    return BsModal.popup.bind(this)(null, options);
  },
  template: /*html*/ `
  <bs-modal ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" backdrop="false" >

    <template v-slot:default>
        <p>{{JSON.stringify(data,null,2)}}</p>
    </template>
 
    <!-- end of optional footer --> 
  </bs-modal>`,
};

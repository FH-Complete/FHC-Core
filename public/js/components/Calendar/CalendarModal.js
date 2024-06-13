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
    
      result: false,
      info: null,
    };
  },
  

  mounted() {
    this.modal = this.$refs.modalContainer.modal;
  },
  popup(options) {
    return BsModal.popup.bind(this)(null, options);
  },
  template: /*html*/ `
  <bs-modal ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" backdrop="false" >
    <template v-slot:title>
      {{data.title + ' - ' + data.lehrfach_bez + ' [' + data.ort_kurzbz+']' }}  
    </template>
    <template v-slot:default>
        <div class="row">
        <div class="offset-3 col-4"><span>Datum:</span></div>
        <div class=" col"><span>{{data.datum}}</span></div>
        </div>
        <div class="row">
        <div class="offset-3 col-4"><span>Raum:</span></div>
        <div class=" col"><span>{{data.ort_kurzbz}}</span></div>
        </div>
        <div class="row">
        <div class="offset-3 col-4"><span>LV:</span></div>
        <div class=" col"><span>{{'('+data.lehrform+') ' + data.lehrfach_bez}}</span></div>
        </div>
        <div class="row">
        <div class="offset-3 col-4"><span>Lektor:</span></div>
        <div class=" col"><span>{{data.lektor}}</span></div>
        </div>
        <div class="row">
        <div class="offset-3 col-4"><span>Zeitraum:</span></div>
        <div class=" col"><span>{{start_time + ' - ' + end_time}}</span></div>
        </div>
    </template>
 
     <!-- optional footer -->
     <template  v-slot:footer >
        
     <button class="btn btn-outline-secondary " @click="hide">{{$p.t('ui','cancel')}}</button>    
       </template>
     <!-- end of optional footer --> 
  </bs-modal>`,
};

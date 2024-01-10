import configurable from '../../mixins/vbform/configurable.js';
import sharedstate from './vbsharedstate.js';

export default {
  template: `
  <div class="col-4">
    <div class="input-group input-group-sm">
      <input v-model="gueltig_ab" :disabled="isdisabled" @change="gueltigkeitchanged" type="text" class="form-control form-control-sm" placeholder="gültig ab" aria-label="gueltig ab">
      <span class="input-group-text">&dash;</span>
      <input v-model="gueltig_bis" :disabled="isdisabled" @change="gueltigkeitchanged" type="text" class="form-control form-control-sm" placeholder="gültig bis" aria-label="gueltig bis">
      <span class="input-group-text" v-if="(this.sharedstatemode === 'reflect')">
        <i @click="changesharedstatemode('ignore')" class="fas fa-link"></i>
      </span>
      <span class="input-group-text" v-else-if="(this.sharedstatemode === 'ignore')">
        <i @click="changesharedstatemode('reflect')" class="fas fa-unlink"></i>
      </span>
      <span class="input-group-text bg-white border-0" v-else-if="(this.sharedstatemode === 'set')">
        <i class="fas fa-square text-white"></i>
      </span>
    </div>
  </div>
  `,
  props: {
    'initialsharedstatemode': {
      type: String,
      default: 'reflect',
      validator: function(value) {
        return ['reflect', 'set', 'ignore'].includes(value);
      }
    }
  },
  data: function() {
    return {
      sharedstate: sharedstate,
      sharedstatemode: '',
      gueltig_ab: '',
      gueltig_bis: ''
    }
  },
  components: {},
  mixins: [
    configurable
  ],
  created: function() {
    this.sharedstatemode = this.initialsharedstatemode;
    this.setDataFromSharedSate();
    this.setGUIOptionsFromConfig();
    this.setDataFromConfig();
  },
  watch: {
    'sharedstate.gueltigkeit.gueltig_ab': function() {
      if( this.sharedstatemode === 'reflect' ) {
        this.gueltig_ab = this.sharedstate.gueltigkeit.gueltig_ab;
      }
    },
    'sharedstate.gueltigkeit.gueltig_bis': function() {
      if( this.sharedstatemode === 'reflect' ) {
        this.gueltig_bis = this.sharedstate.gueltigkeit.gueltig_bis;
      }
    },
  },
  methods: {
    setDataFromConfig: function() {
      if( typeof this.config === 'undefined' ) {
        return;
      }

      if( typeof this.config.data === 'undefined' ) {
        return;
      }

      if( typeof this.config.data.gueltig_ab !== 'undefined' ) {
        this.gueltig_ab = this.config.data.gueltig_ab;
      }
      if( typeof this.config.gueltig_bis !== 'undefined' ) {
        this.gueltig_bis = this.config.data.gueltig_bis;
      }
    },
    setDataFromSharedSate: function() {
      if( this.sharedstatemode === 'reflect' ) {
        this.gueltig_ab = this.sharedstate.gueltigkeit.gueltig_ab;
        this.gueltig_bis = this.sharedstate.gueltigkeit.gueltig_bis;
      }
    },
    setGUIOptionsFromConfig: function() {
      if( typeof this.config?.guioptions?.sharedstatemode !== 'undefined' ) {
        this.sharedstatemode = this.config.guioptions.sharedstatemode;
      }
    },
    getPayload: function() {
      return {
        guioptions: {
          sharedstatemode: this.sharedstatemode,
        },
        data: {
          gueltig_ab: this.gueltig_ab,
          gueltig_bis: this.gueltig_bis
        }
      };
    },
    gueltigkeitchanged: function() {
      if( this.sharedstatemode === 'set' ) {
        this.sharedstate.gueltigkeit.gueltig_ab = this.gueltig_ab;
        this.sharedstate.gueltigkeit.gueltig_bis = this.gueltig_bis;
      }
    },
    changesharedstatemode: function(mode) {
      this.sharedstatemode = mode;
      this.setDataFromSharedSate();
    }
  },
  computed: {
    isdisabled: function() {
      return (this.sharedstatemode === 'reflect');
    }
  }
}

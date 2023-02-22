import gueltigkeit from './gueltigkeit.js';
import configurable from '../../mixins/vbform/configurable.js';

export default {
  template: `
  <div class="col-7">Aenderung</div>
    <gueltigkeit ref="gueltigkeit" :initialsharedstatemode="'set'" :config="getgueltigkeit"></gueltigkeit>
  <div class="col-1">&nbsp;</div>
  `,
  components: {
    'gueltigkeit': gueltigkeit
  },
  mixins: [
    configurable
  ],
  methods: {
    getPayload: function() {
      return {
        dienstverhaeltnisid: this.config.dienstverhaeltnisid,
        gueltigkeit: this.$refs.gueltigkeit.getPayload()
      }
    },

  }
}

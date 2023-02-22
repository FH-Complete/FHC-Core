export default {
  props: [
    'config'
  ],
  computed: {
    isremoveable: function() {
      return (this.config?.guioptions?.removeable === undefined)
        ? false : this.config.guioptions.removeable;
    },
    getgehaltsbestandteile: function() {
      return (this.config?.gbs !== undefined) ? this.config.gbs : [];
    },
    getgueltigkeit: function() {
      return (this.config?.data?.gueltigkeit !== undefined)
        ? this.config.data.gueltigkeit : {};
    }
  }
}

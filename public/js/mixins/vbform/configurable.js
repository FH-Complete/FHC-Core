export default {
  props: [
    'config'
  ],
  computed: {
    isremoveable: function() {
      return (typeof this.config.guioptions.removeable === 'undefined')
        ? false : this.config.guioptions.removeable;
    },
    getgehaltsbestandteile: function() {
      return (typeof this.config.gbs !== 'undefined') ? this.config.gbs : [];
    }
  }
}

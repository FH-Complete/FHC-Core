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
      if( this.config?.gueltigkeit !== undefined ) {
        return this.config.gueltigkeit;
      } else if ( this.config?.data?.gueltigkeit !== undefined ) {
        return this.config.data.gueltigkeit;
      } else {
        return {};
      }
    }
  }
}

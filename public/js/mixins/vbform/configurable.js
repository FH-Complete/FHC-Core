export default {
  props: [
    'config'
  ],
  methods: {
    isinputdisabled: function(inputname) {
      if( this.config?.guioptions?.disabled === undefined ) {
        return false;
      }
      return this.config.guioptions.disabled.includes(inputname);
    }
  },
  computed: {
    isremoveable: function() {
      return (this.config?.guioptions?.removeable === undefined)
        ? false : this.config.guioptions.removeable;
    },
    canhavegehaltsbestandteile: function() {
      return (this.config?.guioptions?.canhavegehaltsbestandteile === undefined)
        ? true : this.config.guioptions.canhavegehaltsbestandteile;
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
    },
    vbcssclasses: function() {
      var classes = [];
      if( (this.config?.guioptions?.nobottomborder === undefined)
        || ((this.config?.guioptions?.nobottomborder !== undefined)
              && this.config.guioptions.nobottomborder === false) ) {
        classes.push('border-bottom');
      }
      if( (this.config?.guioptions?.nobottommargin === undefined)
        || ((this.config?.guioptions?.nobottommargin !== undefined)
              && this.config.guioptions.nobottommargin === false) ) {
        classes.push('mb-3');
      }
      return classes;
    }
  }
}

export default {
  props: [
    'preset'
  ],
  data: function() {
    return {
      children: []
    }
  },
  created: function() {
    this.children = JSON.parse(JSON.stringify(this.preset));
  },
  watch: {
    preset: function() {
      this.children = [];
      this.$nextTick(function() {
        this.children = JSON.parse(JSON.stringify(this.preset));
      });
    }
  }
}

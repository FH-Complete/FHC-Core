

export default {
  components: {
  },
  props: {
    //! this should throw an error in the js console, have to check later
    list:Number,
   
  },
  data() {
    return {
   
    }
  },

  methods: {
  },
  computed: {
    lastElement: function(){
        return this.list[this.list.length-1];
    }
  },
  created() {
    
  },
  mounted() {
  },
  template: `
  <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <!-- https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Attributes/aria-current -->
    <li class="breadcrumb-item"  :class="{'active':element===lastElement}" :aria-current="element===lastElement?'page':'false'" v-for="element in list">{{element}}</li>
   
  </ol>
</nav>`,
};

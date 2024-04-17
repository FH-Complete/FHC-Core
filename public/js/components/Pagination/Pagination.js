export default {
  components: {
    paginator: primevue.paginator,
  },
  props: {
    maxPageCount: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {};
  },
  methods: {
    newPageEvent: function (data) {
      //console.log("hier", data.page);
    },
  },
  mounted() {},
  template: /*html*/ `
    
    
    <paginator @page="(data)=>$emit('page',{...data, page:data.page+1})" :rows="10" :totalRecords="maxPageCount" :rowsPerPageOptions="[10, 20, 30]" ></paginator>
    <slot>
    Placeholder
    </slot>
   
  `,
};

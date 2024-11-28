export default {
  components: {
    paginator: primevue.paginator,
  },
  emits: ["update:rows"],
  props: {
    maxPageCount: {
      type: Number,
      default: 0,
    },
    page_size: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {};
  },
  methods: {
    newPageEvent: function (data) {
      
    },
  },
  mounted() {},
  template: /*html*/ `
    
   <paginator v-model:rows="page_size" @page="(data)=>$emit('page',{...data, page:data.page+1})" :rows="page_size" :totalRecords="maxPageCount" :rowsPerPageOptions="[10, 20, 30]" ></paginator>
    <slot>
    Placeholder
    </slot>
   
  `,
};

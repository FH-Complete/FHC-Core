export default {
  components: {
    paginator: primevue.paginator,
  },
  data() {
    return {};
  },
  methods: {
    newPageEvent: function (data) {
      //console.log("hier", data.page);
    },
  },
  mounted() {
    console.log("pagination mounted");
  },
  template: /*html*/ `
    
  
    <paginator @page="(page)=>$emit('page',{...page, page:page.page+1})" :rows="1"  :totalRecords="120" ></paginator>
    <slot>
    NO CONTENT WAS PROVIDED
    </slot>
    <paginator :rows="10" :totalRecords="120" :rowsPerPageOptions="[10, 20, 30]"></paginator>

  `,
};

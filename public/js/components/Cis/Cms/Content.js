
export default {
  props:{
    content_id:{
        type:Number,
        required:true,
    },
    version:{
        type:[String, Number],
        default: null,
    },
    sprache:{
        type:[String, Number],
        default: null,
    },
    sichtbar:{
        type:[String, Number],
        default: null,
    }


  },
  data() {
    return {
      content: null,
      
    };
  },
  created() {
    console.log("this is the api", this.$fhcApi);
    this.$fhcApi.factory.cms.content(this.content_id,this.version, this.sprache, this.sichtbar).then(res =>{
        this.content = res.data;
    });
  },
  template: /*html*/ `
    <!-- div that contains the content -->
    <div v-if="content" v-html="content"></div>
    <p v-else>No content is available to display</p>
    `,
};

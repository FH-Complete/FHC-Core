import raum_contentmittitel from './Content_types/Raum_contentmittitel.js'
import news from './Content_types/News.js'


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
  components:{
    raum_contentmittitel,
  },
  data() {
    return {
      content: null,
      
    };
  },
  created() {
    this.$fhcApi.factory.cms.content(this.content_id,this.version, this.sprache, this.sichtbar).then(res =>{
        this.content = res.data.content;
        this.content_type=res.data.content;
    });
  },
  mounted(){
    
  },
  template: /*html*/ `
    <!-- div that contains the content -->
    <component :is="'raum_contentmittitel'" v-if="content" :content="content" />
    <p v-else>No content is available to display</p>

    `,
};

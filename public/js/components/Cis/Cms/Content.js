import raum_contentmittitel from './Content_types/Raum_contentmittitel.js'
import general from './Content_types/General.js'


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
    general,
  },
  data() {
    return {
      content: null,
    };
  },
  computed:{
    computeContentType: function(){
      switch(this.content_type){
        case "raum_contentmittitel":
          return "raum_contentmittitel";
        default:
          return "general";
      };
    },
  },
  created() {
    this.$fhcApi.factory.cms.content(this.content_id,this.version, this.sprache, this.sichtbar).then(res =>{
        this.content = res.data.content;
        this.content_type=res.data.type;
    });
  },
  mounted(){
	
  },
  template: /*html*/ `
    <!-- div that contains the content -->
    <component :is="computeContentType" v-if="content" :content="content" />
    <p v-else>No content is available to display</p>
    `,
};

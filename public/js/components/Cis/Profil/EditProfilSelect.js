import {Kontakt, EditKontakt, Adresse, EditAdresse} from "./ProfilComponents.js";


export default {
    components: {
      Kontakt,
      EditKontakt,
      Adresse,
      EditAdresse,
    },
    props: {

      //! this should throw an error in the js console, have to check later
      list:Object,
  
      //? Prop used to determine how many options the select should initially show
      size:{
        type:Number,
        default: null,
      },
      //? Content for the aria label of the select
      ariaLabel:{
        type:String,
        required:true,
      },
      profilUpdate:String,
      topic:String,
      breadcrumb:String,

     
    },
    emits:{
        //? update:modelValue event is needed to notify the v-model when the value has changed
        ['update:profilUpdate']:null,
        ['update:topic']:null,
        ['update:breadcrumb']:null,
        submit:null,
        select:null,

    },
    data() {
      return {
        view:null,
        data:null,
        breadcrumbItems:[],
      }
    },
  
    methods: {

      deleteItem: function(item){
        let data = item.data;
        data.delete = true;
        this.$emit('update:profilUpdate',item.data);
        //? updates the topic when a Kontakt or an Address should be deleted
        this.$emit('update:topic',item.data.kontakt?"Delete Kontakte":"Delete Adressen");
        this.$emit('submit');
      },

      profilUpdateEmit: function(event){
        console.log(event);
        //? passes the updated profil information to the parent component
        this.$emit('update:profilUpdate',event);
      },
      updateOptions: function(event, item){
        
        this.data=item.data; 
        this.view=item.view; 
        
        if(item.title){
          //? emits the selected topic to the parent component
          this.$emit('update:topic',item.title);
        
          //? emits the new item for the breadcrumb in the parent component
          this.breadcrumbItems.push(item.title);
        }else{
          if(item.data.kontakttyp){
            this.breadcrumbItems.push(item.data.kontakttyp);
            this.breadcrumbItems.push(item.data.kontakt);
          }else if(item.data.strasse){
            this.breadcrumbItems.push(item.data.strasse);
          }
        }
        this.$emit('update:breadcrumb',this.breadcrumbItems);
        
      },
     
    },
    computed: {
      
      
    },
    created() {
      this.data = JSON.parse(JSON.stringify(this.list.data));
      this.view = JSON.parse(JSON.stringify(this.list.view));
        
        
    },
    mounted() {
    },
   
    template: `
  

    <div v-if="!view" class="list-group">
      <button style="position:relative" type="button" class=" list-group-item list-group-item-action" @click="updateOptions($event,item)" v-for="item in data">
      
        <p v-if="item.title" class="my-1"   >{{item.title}}</p>
        <!-- this is used for multiple elements in the select -->
        <div class="my-2 me-4" v-else>
        <component  :is="item.listview" v-bind="item"></component>
        <i @click="deleteItem(item)" style="color:lightcoral; position:absolute; top:10px; right:10px;" class="fa fa-trash"></i>
        
        </div>
      </button>
    
    </div>

    <div v-else-if="view==='text_input'" class="form-underline">
      <div class="form-underline-titel">{{data.titel?data.titel:'titel'}}</div>

      <input  class="form-control" @input="$emit('update:profilUpdate',data.value)"  v-model="data.value" :placeholder="data.value">
    </div>


    <!-- if it not a normal text input field then reder the custom edit input component -->
    <!-- custom component is required to emit an profilUpdate event to register the new entered value --> 
    <template v-else>
      <component @profilUpdate="profilUpdateEmit"  :is="view" :data="data"></component>
    </template>
   `,
  };
  
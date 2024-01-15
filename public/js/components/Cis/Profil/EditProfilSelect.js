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

     
    },
    emits:{
        //? update:modelValue event is needed to notify the v-model when the value has changed
        ['update:profilUpdate']:null,
        ['update:topic']:null,
        select:null,

    },
    data() {
      return {
        view:null,
        data:null,
      }
    },
  
    methods: {
      profilUpdateEmit: function(event){
        console.log(event);
        //? passes the updated profil information to the parent component
        this.$emit('update:profilUpdate',event);
      },
      updateOptions: function(event, item){
        
        this.data=item.data; 
        this.view=item.view; 
        //? emits the selected topic to the parent component
        if(item.title){
          this.$emit('update:topic',item.title);
        }
      },
     
    },
    computed: {
      listLength: function(){
        return this.list.length;
      },
      lastElement: function(){
          return this.list[this.list.length-1];
      },
      computedList: function(){
        if(Array.isArray(this.list)){
            //? the passed data is an array
            return this.list;
        }else if(typeof(this.list) === 'object' && this.list !== null){
            //? the passed data is an object
            return Object.keys(this.list);
        }else{
            console.warn("The passed data is neither an Array or an Object");
            return null;
            //! the passed data is neither an object or an array
        }
        
      }
    },
    created() {
      this.data = JSON.parse(JSON.stringify(this.list.data));
      this.view = JSON.parse(JSON.stringify(this.list.view));
        
        //? sets the default length of the options to show equal to the number of elements in the list
        if(!this.optionLength){
            //? if it is an object, then it will take the length of the object keys, otherwise it takes the normal length
            this.optionLength = this.computedList.length;
        }
    },
    mounted() {
    },
   
    template: `
  

    <div v-if="!view" class="list-group">
      <button type="button" class=" list-group-item list-group-item-action" @click="updateOptions($event,item)" v-for="item in data">
      
        <p v-if="item.title" class="my-1"   >{{item.title}}</p>
        <!-- this is used for multiple elements in the select -->
        <component class="my-2" :is="item.listview" v-bind="item"></component>
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
  
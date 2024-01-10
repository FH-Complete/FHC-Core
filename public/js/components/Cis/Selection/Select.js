

export default {
    components: {
    },
    props: {

      //! this should throw an error in the js console, have to check later
      list:[Array,Object],
      //? prop modelValue allows v-model on custom Components
      modelValue:String,
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
     
    },
    emits:{
        //? update:modelValue event is needed to notify the v-model when the value has changed
        ['update:modelValue']:null,
        select:null,
    },
    data() {
      return {
        selectedOption: [],
        optionLength:this.size,
      }
    },
  
    methods: {
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
        console.log(this.optionLength);
        //? sets the default length of the options to show equal to the number of elements in the list
        if(!this.optionLength){
            //? if it is an object, then it will take the length of the object keys, otherwise it takes the normal length
            this.optionLength = this.computedList.length;
        }
    },
    mounted() {
    },
    template: `
    <p>size:{{optionLength}}</p>
    <!-- styling des Selects und dessen Options könnte man noch anpassen -->
    <div id="SelectStyle">
    <select  v-model="selectedOption"  class="form-select" :size="this.optionLength" :aria-label="ariaLabel">
      <option  @click="$emit('update:modelValue', option); $emit('select')" v-for="(option,index) in computedList" :value="option">
        <template v-if="typeof(option) === 'object' && option !== null" >
            <div class="row " v-for="(element,index) in option"><div class="col">{{index}} : </div><div class="col">{{element}}</div></div>
        </template>
        <template v-else >
            {{option}}
        </template>
      </option>
    </select>
    </div>`,
  };
  
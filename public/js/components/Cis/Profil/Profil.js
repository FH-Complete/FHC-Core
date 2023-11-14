

export default {
   
    data: function() {
        return {
            person: null,
        }
    },
    methods: {
        testsearch: function() {
             
        }
      },
      created(){
        this.$parent.testsearch().then((res) => {
            this.person = res.data;
        }); 
        
       
      },
     
    template: `
            <div>
            <h1>test</h1>
            <code>{{JSON.stringify(person)}}</code>
            </div>
    `,
};
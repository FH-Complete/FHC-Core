
export default {
    props:{
      content:{
          type:String,
          required:true,
      },
    },
    mounted(){
        let tables = document.getElementsByClassName("tablesorter");
        
        for(let table of tables){
            new Tabulator(table, {
                layout:"fitDataStretch",
               
                columnDefaults:{
                    formatter:"html",
                    resizable:false,
                    minWidth: "100px",
                }
            })

            table.classList.add("mx-auto");
            table.style.width="30em"; 
        }
    },
    template: /*html*/ `
      <!-- div that contains the content -->
      <div v-html="content" v-if="content" :content="content" />
      <p v-else>Content was not found</p>
      `,
  };
  
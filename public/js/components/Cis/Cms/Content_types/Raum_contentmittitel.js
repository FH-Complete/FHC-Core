
export default {
    props:{
      content:{
          type:String,
          required:true,
      },
    },
    mounted(){
		// replaces the tablesorter with the tabulator
		let tables = document.getElementsByClassName("tablesorter");

		for (let table of tables) {
			new Tabulator(table, {
				layout: "fitDataStretch",

				columnDefaults: {
					formatter: "html",
					resizable: false,
					minWidth: "100px",
				}
			})
		}
    },
    template: /*html*/ `
      <!-- div that contains the content -->
      <div v-html="content" v-if="content" ></div>
      <p v-else>Content was not found</p>
      `,
  };
  
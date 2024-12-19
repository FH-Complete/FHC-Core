
export default {
    props:{
      content:{
          type:String,
          required:true,
      },
	  content_id:{
		type:Number,
	  }
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

		// tries to wrap the Raum titel with a link tag that redirects to the Reservierungen of that Raum
		let title = document.getElementsByTagName("h1");
		title = title.length ? title[0] : null;
		if (title) 
		{
			let room_name = title.innerText;
			let room_name_reg_exp = new RegExp("\\w*\\s([a-zA-Z][0-9\\.]+)$");
			let room_name_reg_exp_result = room_name.match(room_name_reg_exp);
			if(room_name_reg_exp_result)
			{
				room_name = room_name_reg_exp_result[0];
				room_name = room_name.replace(" ","_");
				let link_element = document.createElement("a");
				link_element.href = FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + "/CisVue/Cms/getRoomInformation/" + room_name;
				link_element.appendChild(title.cloneNode(true));
				title.replaceWith(link_element);
			}
			else
			{
				console.error(`the regular expression did not match the room name: ${room_name}`);
			}
			
		}
		else
		{
			console.error(`was not able to get the title of the raum_contentmittitel by searching for the first h1 element`);
		}
    },
    template: /*html*/ `
      <!-- div that contains the content -->
      <div v-html="content" v-if="content" ></div>
      <p v-else>Content was not found</p>
      `,
  };
  
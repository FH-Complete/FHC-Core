
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

        document.querySelectorAll("#cms [data-confirm]").forEach((el) => {
            el.addEventListener("click", (evt) => {
              evt.preventDefault();
              BsConfirm.popup(el.dataset.confirm)
                .then(() => {
                  Axios.get(el.href)
                    .then((res) => {
                      // TODO(chris): check for success then show message and/or reload
                      location = location;
                    })
                    .catch((err) => console.error("ERROR:", err));
                })
                .catch(() => {});
            });
          });
          document.querySelectorAll("#cms [data-href]").forEach((el) => {
            el.href = el.dataset.href.replace(
              /^ROOT\//,
              FHC_JS_DATA_STORAGE_OBJECT.app_root
            );
          });
    },
    template: /*html*/ `
      <!-- div that contains the content -->
      <div v-html="content" v-if="content" ></div>
      <p v-else>Content was not found</p>
      `,
  };
  
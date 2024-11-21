
export default {
    props:{
      content:{
          type:String,
          required:true,
      },
    },
	methods: {
		sanitizeLegacyTables(table) {
			
			// find nested tables and replace with p element
			const tt = table.querySelectorAll('table')
			tt.forEach(t => {
				const textContent = t.textContent.trim();
				const pElement = document.createElement('p');
				pElement.textContent = textContent;
				t.parentNode.replaceChild(pElement, t);
			})

			// find unordered lists, traverse li childs and replace with p element -> more readable than 1 p tag for ul
			const ul = table.querySelectorAll('ul')
			ul.forEach(u => {
				Array.from(u.children).forEach(li => {
					const p = document.createElement('p');
					p.textContent = li.textContent
					u.parentNode.appendChild(p)
				})
				u.parentNode.removeChild(u)
				
			})

			// find bare text nodes and put into p element
			const td = Array.from(table.querySelectorAll('td')).filter(el => el.scrollWidth > 100)
			td.forEach(element => {
				if (element.firstChild?.nodeType === Node.TEXT_NODE && element.firstChild.length > 10) {
					const p = document.createElement('p');
					p.appendChild(element.firstChild)
					element.appendChild(p);
				}
			});

			// flatten nested th elements
			const ths = Array.from(table.querySelectorAll('th'))
			ths.forEach(th => {

				if(th.children.length > 1) {
					th.innerHTML = Array.from(th.childNodes).find(cn => cn.textContent).textContent
				}
			})

			// let p elements wrap on overflow
			const p = table.querySelectorAll('p')
			p.forEach(p => {
				p.style.setProperty('word-wrap', 'break-word');
				p.style.setProperty('white-space', 'normal');
				p.style.setProperty('max-width', '400px');
			})
		}
	},
    mounted(){
		// replaces the tablesorter with the tabulator
		let tables = Array.from(document.getElementsByClassName("tablesorter"));

		tables.forEach((table, index) =>  {
			this.sanitizeLegacyTables(table)
			
			new Tabulator(table, {
				index: index,
				layout: "fitDataFill",

				columnDefaults: {
					formatter: "html",
					resizable: true,
					minWidth: "100px"
				}
			})
		})

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
      <div v-if="content" class="container" style="max-width: 100%;"><div class="row"><div class="col">
      	<div v-html="content"  ></div>
      </div></div></div>
      <p v-else>Content was not found</p>
      `,
  };
  

export default {
	name: "RaumComponent",
	data() {
		return {
			imgContent: null
		}
	},
    props:{
      content:{
          type:String,
          required:true,
      },
	  content_id:{
		type:Number,
	  }
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
		let tables = document.getElementsByClassName("tablesorter");
		
		for (let table of tables) {
			this.sanitizeLegacyTables(table)
			new Tabulator(table, {
				layout: "fitDataStretch",

				columnDefaults: {
					formatter: "html",
					resizable: false,
					minWidth: "100px",
				}
			})
		}
		
		let title = document.getElementsByTagName("h1");
		title = title.length ? title[0] : null;
		// tries to wrap the Raum titel with a link tag that redirects to the Reservierungen of that Raum
		if (title && title.innerText) 
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
			
			return
		}
		
		const parser = new DOMParser()
		const doc = parser.parseFromString(`<div>${this.content}</div>`, "text/html");

		const img = doc.querySelector("img")
		if(img && img.title)
		{
			const imgAttributes = {}
			for (let attr of img.attributes) {
				imgAttributes[attr.name] = attr.value
			}

			this.imgContent = imgAttributes
		}
		
		console.error(`was not able to get the title of the raum_contentmittitel`);
		
    },
    template: /*html*/ `
      <!-- div that contains the content -->
<!--       TODO: test with more img content from cms-->
      <div v-if="imgContent"><img v-bind="imgContent"></img></div>
      <div v-html="content" v-else-if="content" ></div>
      <p v-else>Content was not found</p>
      `,
  };
  
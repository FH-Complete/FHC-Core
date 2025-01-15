// collection of relative and absolute regex to replace legacy links
//
const regexList = {
	relative:[
		{ 
			priority: 1, 
			regex: new RegExp(/^\.\.\/cms\/content\.php\?content_id=([0-9]+)/),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/content',
		},
		{ 
			priority: 2,
			regex: new RegExp(/^\.\.\/cms\/news\.php/),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/news',
		},
		{ 
			priority: 3,
			regex: new RegExp(/^\.\.\/index\.ci\.php\//),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router,
		},
		{ 
			priority: 10, 
			regex: new RegExp("/^\.\.\//"),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root,
		},
	],
	absolute:[
		{}
	]
};

// sorts the relative regex array by priority ascending
const relative_regex = regexList.relative
	.sort((a, b) => {
		return a.priority - b.priority;
	})
	.map(regex => {
		return {
			regex: regex.regex, 
			replacement: regex.replacement,
		}
	});

// sorts the absolute regex array by priority ascending
const absolute_regex = regexList.absolute
	.sort((a, b) => {
		return a.priority - b.priority;
	})
	.map(regex => {
		return {
			regex: regex.regex,
			replacement: regex.replacement,
		}
	})


export function replaceRelativeLegacyLink(relativeLegacyLink){
	for (let {regex,replacement} of relative_regex){		
		// if any of the regex matches the relativeLegacyLink, replace the matched part with the new app_root path
		let match = relativeLegacyLink.match(regex);
		if (match) {
			let new_link = relativeLegacyLink.replace(regex, replacement);
			for(let query_parameter of match.slice(1)){
				new_link = new_link.concat(`/${query_parameter}`)
			}
			return new_link; 
		}
	}
	// if none of the regex matched with the string return the original path
	return relativeLegacyLink;
}


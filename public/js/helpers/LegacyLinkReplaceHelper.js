// collection of relative and absolute regex to replace legacy links
const GROUP_REPLACEMENT_STRATEGIES = {
	QUERY_PARAMETERS:'QUERY_PARAMETERS',
	PATH_SEGMENTS:'PATH_SEGMENTS',
}

const regexList = {
	relative:[
		{ 
			priority: 1, 
			regex: new RegExp(/^\.\.\/cms\/content\.php\?content_id=([0-9]+)/),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/content',
			group_replacement_strategy:GROUP_REPLACEMENT_STRATEGIES.PATH_SEGMENTS,
		},
		{ 
			priority: 2,
			regex: new RegExp(/^\.\.\/cms\/news\.php/),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/news',
		},
		{ 
			priority: 3,
			regex: new RegExp(/^\.\.\/index\.ci\.php/),
			replacement: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router,
		},
		{ 
			priority: 10, 
			regex: new RegExp(/^\.\.\//),
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
			group_replacement_strategy: regex.group_replacement_strategy,
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
			group_replacement_strategy: regex.group_replacement_strategy,
		}
	})


export function replaceRelativeLegacyLink(relativeLegacyLink){
	for (let {regex,replacement,group_replacement_strategy} of relative_regex){	
		// if any of the regex matches the relativeLegacyLink, replace the matched part with the new app_root path
		let match = relativeLegacyLink.match(regex);
		if (match) {
			let new_link = relativeLegacyLink.replace(regex, replacement);
			
				switch (group_replacement_strategy){
					case 'QUERY_PARAMETERS':
						//TODO: this doesn't really work yet because the query parameter are key/value pairs
						new_link = new_link.concat(`?${match[1]}`);
						for (let query_parameter of match.slice(2)) {
							new_link = new_link.concat(`&${query_parameter}`);
						}
						break;
					case 'PATH_SEGMENTS':
						for (let query_parameter of match.slice(1)) { 
							new_link = new_link.concat(`/${query_parameter}`);
						} 
						break;
					default:
						break;
				}
			
			return new_link; 
		}
	}
	// if none of the regex matched with the string return the original path
	return relativeLegacyLink;
}


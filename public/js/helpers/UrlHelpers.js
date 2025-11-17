const absoluteJsImportUrl = function(relativeurl)
{
	const absoluteurl = FHC_JS_DATA_STORAGE_OBJECT.app_root
		+ relativeurl
		+ '?'
		+ FHC_JS_DATA_STORAGE_OBJECT.fhcomplete_build_version;
	return absoluteurl;
}

export { absoluteJsImportUrl };

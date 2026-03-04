const absoluteJsImportUrl = function(relativeurl)
{
	if(true === FHC_JS_DATA_STORAGE_OBJECT.use_fhcomplete_build_version_in_path)
	{
		const absoluteurl = FHC_JS_DATA_STORAGE_OBJECT.app_root
			+ relativeurl.replace(
				/^public\//,
				'public/' + FHC_JS_DATA_STORAGE_OBJECT.fhcomplete_build_version + '/'
			);
		return absoluteurl;
	}
	else
	{
		const absoluteurl = FHC_JS_DATA_STORAGE_OBJECT.app_root
			+ relativeurl
			+ '?'
			+ FHC_JS_DATA_STORAGE_OBJECT.fhcomplete_build_version;
		return absoluteurl;
	}	
};

export { absoluteJsImportUrl };

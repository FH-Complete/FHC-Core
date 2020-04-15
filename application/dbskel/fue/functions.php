<?php

$functionsArray = array(
	'get_highest_content_version' =>
		'CREATE OR REPLACE FUNCTION fue.get_highest_content_version(bigint) RETURNS smallint AS $$
					DECLARE i_content_id ALIAS FOR $1;
					DECLARE rec RECORD;
				BEGIN

					SELECT INTO rec version
					  FROM campus.tbl_contentsprache
					 WHERE content_id = i_content_id
				  ORDER BY version desc
					 LIMIT 1;

					RETURN rec.version;
				END;
				$$ LANGUAGE plpgsql;'
);

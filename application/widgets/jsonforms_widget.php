<?php

/*
 * JSON-Forms widget
 */
class jsonforms_widget extends Widget 
{
    public function display($data) 
	{
		// set default values if needed
		if (! isset($data['objectname']))
			$data['objectname'] = 'bf';
		if (! isset($data['id']))
			$data['id'] = 'jsonforms';
		if (! isset($data['style']))
			$data['style'] = 'width: 500px; height: 200px;';
		if (! isset($data['schema']))
			$data['schema'] = '{
  "$schema": "http://json-schema.org/draft-03/schema#",
  "title": "Person",
  "type": "object",
  "properties": {
    "anrede": {
      "type": "string",
      "enum": [
        "Herr",
        "Frau"
      ],
      "default": "Herr"
    },
    "vorname": {
      "type": "string",
      "description": "Firstname",
      "minLength": 2,
      "default": "Vorname"
    },
    "nachname": {
      "type": "string",
      "description": "Firstname",
      "minLength": 2,
      "default": "Nachname"
    },
    "code": {
      "type": "string",
      "description": "Accesscode",
      "minLength": 6,
      "default": "1q2w3e4r5t6z7u8i9o0"
    },
    "link": {
      "type": "string",
      "description": "LoginURL",
      "minLength": 6,
      "default": "https://cis.fhcomplete.org"
    }
  }
}';
        $this->view('widgets/jsonforms', $data);
    }
}

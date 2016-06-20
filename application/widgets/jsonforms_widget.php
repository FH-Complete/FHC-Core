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
			$data['style'] = 'width: 50%; ';
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
				  "description": "Surename",
				  "minLength": 2,
				  "default": "Nachname"
				}
			  }
			}';
        $this->view('widgets/jsonforms', $data);
    }
}

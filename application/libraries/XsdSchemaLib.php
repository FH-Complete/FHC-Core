<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use \DOMDocument as DOMDocument;

class XsdSchemaLib
{
	/**
	 * Converts an XSD into a field schema.
	 * @param string $xsd            the XSD as a string
	 * @param string $templateKurzbz short name of the template
	 * @return object success(array) with template_kurzbz, root, fields
	 */
	public function parseSchema($xsd, $templateKurzbz)
	{
		libxml_use_internal_errors(true);
		$dom = new DOMDocument();

		if (!$dom->loadXML($xsd))
		{
			libxml_clear_errors();
			return error('cms/xsdUngueltig');
		}

		libxml_clear_errors();

		$root = null;
		$fields = array();
		$this->_walkElements($dom->documentElement, $root, $fields);

		return success(array(
			'template_kurzbz' => $templateKurzbz,
			'root'   => $root,
			'fields' => $fields
		));
	}

	/**
	 * Reads the values of a content from the XML.
	 * @param string $xml    the value of tbl_contentsprache.content
	 * @param array  $schema the result of parseSchema
	 * @return object success(array) field name => value
	 */
	public function extractValues($xml, $schema)
	{
		$values = array();

		foreach ($schema['fields'] as $field)
		{
			$values[$field['name']] = '';
		}

		if ($xml === null || trim($xml) === '')
		{
			return success($values);
		}

		libxml_use_internal_errors(true);
		$dom = new DOMDocument();

		if (!$dom->loadXML($xml))
		{
			libxml_clear_errors();
			return success($values);
		}

		libxml_clear_errors();

		foreach ($schema['fields'] as $field)
		{
			$nodes = $dom->getElementsByTagName($field['name']);
			if ($nodes->length > 0)
			{
				$values[$field['name']] = $nodes->item(0)->nodeValue;
			}
		}

		return success($values);
	}

	/**
	 * Builds the XML from the values.
	 * @param array $schema the result of parseSchema
	 * @param array $values field name => value
	 * @return object success(string) the XML
	 */
	public function buildXml($schema, $values)
	{
		$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= "\n<" . $schema['root'] . '>';

		foreach ($schema['fields'] as $field)
		{
			$value = isset($values[$field['name']]) ? $values[$field['name']] : '';
			$xml .= "\n<" . $field['name'] . '><![CDATA[' . $this->_escapeCdata($value) . ']]></' . $field['name'] . '>';
		}

		$xml .= "\n</" . $schema['root'] . '>';

		libxml_use_internal_errors(true);
		$check = new DOMDocument();

		if (!$check->loadXML($xml))
		{
			libxml_clear_errors();
			return error('cms/xmlUngueltig');
		}

		libxml_clear_errors();

		return success($xml);
	}

	// -------------------------------------------------------------------------
	// Private methods

	/**
	 * Walks xs:element nodes recursively.
	 * The first element without a type attribute is the root.
	 * Elements with a type attribute are fields.
	 * xs:complexType and xs:sequence are traversed and ignored.
	 */
	private function _walkElements($node, &$root, &$fields)
	{
		if ($node->nodeType !== XML_ELEMENT_NODE)
		{
			return;
		}

		$localName = $node->localName;

		if ($localName === 'element')
		{
			$typeAttr = $node->getAttribute('type');

			if ($typeAttr === '')
			{
				if ($root === null)
				{
					$root = $node->getAttribute('name');
				}
			}
			else
			{
				$minOccurs = $node->getAttribute('minOccurs');
				$required = ($minOccurs === '') ? true : ((int)$minOccurs > 0);

				$fields[] = array(
					'name'     => $node->getAttribute('name'),
					'type'     => $this->_mapType($typeAttr),
					'required' => $required
				);
			}
		}

		foreach ($node->childNodes as $child)
		{
			$this->_walkElements($child, $root, $fields);
		}
	}

	/**
	 * Maps an XSD type to the field schema type.
	 */
	private function _mapType($xsdType)
	{
		$map = array(
			'xs:string'          => 'string',
			'xs:decimal'         => 'string',
			'xs:integer'         => 'string',
			'xs:positiveInteger' => 'string',
			'xs:date'            => 'date',
			'wysiwyg'            => 'wysiwyg',
			'file'               => 'file',
			'boolean'            => 'boolean'
		);

		return isset($map[$xsdType]) ? $map[$xsdType] : 'string';
	}

	// Escapes ]]> in the value. The legacy code does not do this and writes broken XML.
	private function _escapeCdata($value)
	{
		return str_replace(']]>', ']]]]><![CDATA[>', $value);
	}
}

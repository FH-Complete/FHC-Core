<?php

/* Copyright (C) 2007 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Cristina Hainberger <hainberg@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/mail.class.php');
require_once(dirname(__FILE__).'/vorlage.class.php');

const DEFAULT_SANCHO_HEADER_IMG = 'sancho_header_DEFAULT.jpg';
const DEFAULT_SANCHO_FOOTER_IMG = 'sancho_footer_DEFAULT.jpg';

/**
 * Send single Mail with Sancho Design and Layout.
 * @param string $vorlage_kurzbz Name of the template for specific mail content.
 * @param array $vorlage_data Associative array with specific mail content varibales
 *  to be replaced in the content template.
 * @param string $to Email-adress.
 * @param string $subject Subject of mail.
 * @param string $headerImg Filename of the specific Sancho header image.
 * @param string $replyTo default Email-adress for reply.
 * @return boolean True, if succeeded.
 */
function sendSanchoMail($vorlage_kurzbz, $vorlage_data, $to, $subject, $headerImg = DEFAULT_SANCHO_HEADER_IMG, $footerImg = DEFAULT_SANCHO_FOOTER_IMG, $replyTo = '')
{	
	$from = 'sancho@'. DOMAIN;
	$sanchoHeader_img = dirname(__FILE__). '/../skin/images/sancho/'. $headerImg;
	$sanchoFooter_img = dirname(__FILE__). '/../skin/images/sancho/'. $footerImg;

	// Set unique content id for embedding header and footer image
	$cid_header = uniqid();
	$cid_footer = uniqid();
			
	// Set specific mail content into specific content template
	$content = parseMailContent($vorlage_kurzbz, $vorlage_data);

	// Create data array with specific content and image content ids
	$layout = array(
		'CID_header' => $cid_header,
		'CID_footer' => $cid_footer,
		'content' => $content
	);

	// Set the data array into overall sancho mail template
	$body = parseMailContent('Sancho_Mail_Template', $layout);

	// Send mail
	$mail = new Mail($to, $from, $subject, $body);
	
	// * embed the images
	$mail->addEmbeddedImage($sanchoHeader_img, 'image/jpg', '', $cid_header);
	$mail->addEmbeddedImage($sanchoFooter_img, 'image/jpg', '', $cid_footer);

	// * Set reply-to
	if (isset($replyTo) && $replyTo != '')
		$mail->setReplyTo($replyTo);

	// * embed the html content
	$mail->setHTMLContent($body);
	
	return $mail->send();
}


// *******************************************	HELPER FUNCTIONS
/**
 * Replace variables in the mail content template with specific mail content data.
 * @param string $vorlage_kurzbz Name of the template for specific mail content.
 * @param array $vorlage_data Associative array with specific mail content varibales
 *  to be replaced in the content template.
 * @return string
 */
function parseMailContent($vorlage_kurzbz, $vorlage_data)
{
	$vorlage = new Vorlage();
	$vorlage->getAktuelleVorlage('etw', $vorlage_kurzbz);
	
	// If the text and the subject of the template are not empty
	if (!empty($vorlage->text))
	{
		// Parses template text
		$parsedText = parseVorlagetext($vorlage->text, $vorlage_data);

		return $parsedText;
	}
}

/**
 * parseVorlagetext() - will parse a Vorlagetext.
 *
 * @param   string  $text	REQUIRED
 * @param   array  $data	REQUIRED
 * @return  string
 */
function parseVorlagetext($text, $data = array())
{
	if (empty($text))
		return 'Error in parsing Vorlagentext';
	$text = parse_string($text, $data, true);
	return $text;
}

/**
 * Parse a String
 *
 * Parses pseudo-variables contained in the specified string,
 * replacing them with the data in the second param
 *
 * @param	string
 * @param	array
 * @param	bool
 * @return	string
 */
function parse_string($template, $data, $return = FALSE)
{
	if ($template === '')
	{
		return FALSE;
	}

	$replace = array();
	foreach ($data as $key => $val)
	{
		$replace = array_merge(
			$replace,
			is_array($val)
				? parse_pair($key, $val, $template)
				: parse_single($key, (string) $val, $template)
		);
	}

	unset($data);
	$template = strtr($template, $replace);

	if ($template === FALSE)
	{
		return false;
	}

	return $template;
}

/**
 * Parse a single key/value
 *
 * @param	string
 * @param	string
 * @param	string
 * @return	string
 */
function parse_single($key, $val, $string)
{
	return array('{'. $key. '}' => (string) $val);
}

/**
 * Parse a tag pair
 *
 * Parses tag pairs: {some_tag} string... {/some_tag}
 *
 * @param	string
 * @param	array
 * @param	string
 * @return	string
 */
function parse_pair($variable, $data, $string)
{
	$replace = array();
	preg_match_all(
		'#'.preg_quote('{'. $variable. '}').'(.+?)'.preg_quote('{'.'/'.$variable. '}').'#s',
		$string,
		$matches,
		PREG_SET_ORDER
	);

	foreach ($matches as $match)
	{
		$str = '';
		foreach ($data as $row)
		{
			$temp = array();
			foreach ($row as $key => $val)
			{
				if (is_array($val))
				{
					$pair = parse_pair($key, $val, $match[1]);
					if ( ! empty($pair))
					{
						$temp = array_merge($temp, $pair);
					}

					continue;
				}

				$temp['{'.$key. '}'] = $val;
			}

			$str .= strtr($match[1], $temp);
		}

		$replace[$match[0]] = $str;
	}

	return $replace;
}

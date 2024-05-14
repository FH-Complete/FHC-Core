<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Klasse Mail
 * @create 2008-11-20
 *
 * Versendet ein Mail als Text, Html, CC und BCC Empfaenger,
 * Replyto und Attachments
 */

class mail
{
	public $to;
	public $CC_recievers;
	public $BCC_recievers;
	public $sender;
	public $replyTo;
	public $subject;
	public $textContent;
	public $htmlContent;
	public $attachments;
	public $errormsg;

	/**
	 * MAIL - Konstruktor
	 * $to Empfaenger
	 * $from Absender
	 * $subject Betreff
	 * $text Text des Mails
	 */
	public function __construct($to, $from, $subject, $text)
	{
		$this->to = $to;
		$this->subject = $subject;
		$this->setTextContent($text, 'UTF-8', '8bit');
		$this->sender = $from;
		$this->CC_revievers='';
		$this->BCC_recievers='';
		$attachments='';
		$this->attachmentsplain='';
	}

	/**
	 * Mail zusammenbauen und senden
	 */
	public function send()
	{
		//wenn MAIL_DEBUG gesetzt ist dann alles an diese Adresse schicken
		if(MAIL_DEBUG!='')
		{
			$this->to = MAIL_DEBUG;
			$this->CC_recievers = ($this->CC_recievers!=''?MAIL_DEBUG:'');
			$this->BCC_recievers = ($this->BCC_recievers!=''?MAIL_DEBUG:'');
			$this->replyTo = ($this->replyTo!=''?MAIL_DEBUG:'');
		}

		$mime_boundary_alternative = 'ALT+'.md5(time());
		$mime_boundary_mixed = 'MIXD+'.md5(time());
		$eol="\n";

		if(defined('MAIL_FROM') && MAIL_FROM!='')
			$this->sender = MAIL_FROM;

		// Header
		$header = '';
  		$header .= "From: {$this->sender}".$eol;

  		if (!empty($this->CC_recievers))
			$header .= "CC: {$this->CC_recievers}".$eol;
		if (!empty($this->BCC_recievers))
			$header .= "BCC: {$this->BCC_recievers}".$eol;
		if (!empty($this->replyTo))
			$header .= "Reply-To: {$this->replyTo}".$eol;
		if (!empty($this->replyTo))
			$header .= "Return-Path: {$this->replyTo}".$eol;

		$header .= 'X-Mailer: FHComplete V1'.$eol;
	  	$header .= 'Mime-Version: 1.0'.$eol;
		$header .= 'Precedence: bulk'.$eol;
		$header .= 'Auto-Submitted: auto-generated'.$eol;
		$header .= "Content-Type: multipart/related; boundary=\"$mime_boundary_mixed\"".$eol;
		$header .= "Content-Transfer-Encoding: 8bit".$eol;

		// Body
		$mailbody = "";
		$mailbody .= $eol;
		$mailbody .= "--$mime_boundary_mixed".$eol;
		$mailbody .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary_alternative\"".$eol;
		$mailbody .= $eol;
		$mailbody .= "--$mime_boundary_alternative".$eol;
		$mailbody .= "Content-Type: text/plain; charset={$this->textContent[1]}".$eol;
		$mailbody .= "Content-Transfer-Encoding: {$this->textContent[2]}".$eol;
		$mailbody .= $eol;
		$mailbody .= $this->textContent[0];
		$mailbody .= $eol;
		$mailbody .= $eol;

		if (!empty($this->htmlContent[0]))
		{
			$mailbody .= "--$mime_boundary_alternative".$eol;
			$mailbody .= "Content-Type: text/html; charset={$this->htmlContent[1]}".$eol;
			$mailbody .= "Content-Transfer-Encoding: {$this->htmlContent[2]}".$eol;
			$mailbody .= $eol;
			$mailbody .= $this->htmlContent[0];
			$mailbody .= $eol;
			$mailbody .= $eol;
		}
		$mailbody .= "--{$mime_boundary_alternative}--".$eol;
		$mailbody .= $eol;
		$mailbody .= "--$mime_boundary_mixed";

		// Attachments Plain
		if (is_array($this->attachmentsplain) && (count($this->attachmentsplain) > 0))
		{
			foreach ($this->attachmentsplain as $attachment)
			{
				$dispo = 'attachment';
				$mailbody .= $eol;
				$mailbody .= "Content-Disposition: $dispo; filename={$attachment[2]}".$eol;
				$mailbody .= "Content-Type: {$attachment[1]}; name={$attachment[2]}".$eol;

				$mailbody .= 'Content-Transfer-Encoding: '.$attachment[3].$eol;
				$mailbody .= $eol;
				$mailbody .= $attachment[0];
				$mailbody .= $eol;
				$mailbody .= "--$mime_boundary_mixed";
			}
		}

		// Attachments Binary
		if (is_array($this->attachments) && (count($this->attachments) > 0))
		{
			foreach ($this->attachments as $attachment)
			{
				$dispo = empty($attachment[3]) ? 'attachment' : 'inline';
				$mailbody .= $eol;
				$mailbody .= "Content-Disposition: $dispo; filename={$attachment[2]}".$eol;
				$mailbody .= "Content-Type: {$attachment[1]}; name={$attachment[2]}".$eol;
				if (!empty($attachment[3]))
				{
					$mailbody .= "Content-ID: <{$attachment[3]}>".$eol;
				}
				$mailbody .= 'Content-Transfer-Encoding: base64'.$eol;
				$mailbody .= $eol;
				$mailbody .= $attachment[0];
				$mailbody .= $eol;
				$mailbody .= "--$mime_boundary_mixed";
			}
		}
		$mailbody .= "--".$eol;

		// Subject Encoding setzen
		$subject = "=?UTF-8?B?".base64_encode($this->subject)."?=";

		// Senden
		if(mail($this->to, $subject, $mailbody, $header))
			return true;
		else
			return false;
	}

	/**
	 * Setzt den Text fuer ein Mail
	 */
	public function setTextContent($text, $charset = 'UTF-8', $encoding = '8bit')
	{
		$this->textContent[0] = $text;
		$this->textContent[1] = $charset;
		$this->textContent[2] = $encoding;
		return true;
	}

	/**
	 * Setzt den HTMLText fuer ein Mail
	 */
	public function setHTMLContent($html, $charset = 'UTF-8', $encoding = '8bit')
	{
		$this->htmlContent[0] = $html;
		$this->htmlContent[1] = $charset;
		$this->htmlContent[2] = $encoding;
		if (empty($this->textContent[0]))
			$this->setTextContent(strip_tags($html), $charset, $encoding);

		return true;
	}

	/**
	 * Fuegt ein Binary Attachment zum Mail hinzu
	 * $file Dateiname des hinzuzufuegenden Files
	 * $type MIME Type "application/xls"
	 * $name Anzeigename des Files
	 * $ContentID die ContentID der Datei falls sie als inline-image genutzt wird
	 */
	public function addAttachmentBinary($file, $type, $name, $ContentID = "")
	{
		if (!file_exists($file))
		{
			$this->errormsg = 'Attachment wurde nicht gefunden';
			return false;
		}

		$handle = fopen($file,'rb');
		if (!$handle)
		{
			$this->errormsg = 'Fehler beim Oeffnen der Datei';
			return false;
		}

		$file_content = fread($handle,filesize($file));
		@fclose($handle);

		$attachment_string = chunk_split(base64_encode($file_content));
		$this->attachments[] = Array($attachment_string, $type, $name, $ContentID);
		return true;
	}

	/**
	 * Fuegt ein Bild zum Mail hinzu
	 * $image	image-URL
	 * $type	MIME Type "image/[image-type]", zB "image/jpg"
	 * $name	Anzeigename des Files
	 * $ContentID ContentID für inline-embedding (diese muss im script im tag <img src="cid:ContentID"> angegeben werden)
	 */
	public function addEmbeddedImage ($image, $type, $name = '', $ContentID)
	{
		$image_string = file_get_contents($image);
		if (!$image_string)
		{
			$this->errormsg = 'Fehler beim Einlesen der Datei';
			return false;
		}

		$image_b64 = chunk_split(base64_encode($image_string), 76, "\n");
		$this->attachments[] = Array($image_b64, $type, $name, $ContentID);
	}

	/**
	 * Fuegt ein Attachment zum Mail hinzu
	 * $content
	 * $type MIME Type "application/xls"
	 * $name Anzeigename des Files
	 * $ContentID die ContentID der Datei falls sie als inline-image genutzt wird
	 */
	public function addAttachmentPlain($content, $type, $name, $encoding='8bit')
	{
		$this->attachmentsplain[] = Array($content, $type, $name, $encoding);
		return true;
	}
	/**
	 * Setzt den ReplyTo
	 */
	public function setReplyTo($repl)
	{
		$this->replyTo = $repl;
		return true;
	}

	/**
	 * Setzt die CC Empfaenger
	 */
	public function setCCRecievers($rcvs)
	{
		$this->CC_recievers = '';
		if (is_array($rcvs))
		{
			foreach ($rcvs as $rcv)
				$this->CC_recievers .= ",$rcv";
			$this->CC_recievers = mb_substr($this->CC_recievers, 1);
		}
		else
		{
			$this->CC_recievers = $rcvs;
		}
		return true;
	}

	/**
	 * Setzt die BCC Empfaenger
	 */
	public function setBCCRecievers($rcvs)
	{
		$this->BCC_recievers = '';
		if (is_array($rcvs))
		{
			foreach ($rcvs as $rcv)
				$this->BCC_recievers .= ",$rcv";
			$this->BCC_recievers = mb_substr($this->BCC_recievers, 1);
		}
		else
		{
			$this->BCC_recievers = $rcvs;
		}
		return true;
	}
}
?>

<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Message extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function up()
    {
		$this->startUP();
		
		// Create table public.tbl_msg_message
		$fields = array(
			"message_id" => array(
				"type" => "serial"
			),
			"person_id" => array(
				"type" => "bigint"
			),
			"subject" => array(
				"type" => "varchar(256)",
				"null" => false
			),
			"body" => array(
				"type" => "text",
				"null" => false
			),
			"priority" => array(
				"type" => "smallint DEFAULT 0",
				"null" => false
			),
			"relationmessage_id" => array(
				"type" => "bigint",
				"null" => true
			),
			"oe_kurzbz" => array(
				"type" => "varchar(32)",
				"null" => true
			),
			"insertamum" => array(
				"type" => "timestamp DEFAULT NOW()",
				"null" => false
			),
			"insertvon" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("public", "tbl_msg_message", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_msg_message",
			"pk_tbl_msg_message",
			array("message_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_message",
			"fk_tbl_msg_message_person_id",
			"person_id",
			"public",
			"tbl_person",
			"person_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_message",
			"fk_tbl_msg_message_relationmessage_id",
			"relationmessage_id",
			"public",
			"tbl_msg_message",
			"message_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_message",
			"fk_tbl_msg_message_oe_kurzbz",
			"oe_kurzbz",
			"public",
			"tbl_organisationseinheit",
			"oe_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addCommentToColumn("public", "tbl_msg_message", "person_id", "Sender");
		$this->addCommentToColumn("public", "tbl_msg_message", "priority", "Codex in config/message.php");
		$this->grantTable("SELECT", "public", "tbl_msg_message", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_message", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_message", "vilesci");
		$this->grantSequence(array("SELECT", "UPDATE"), "public", "tbl_msg_message_message_id_seq", "web");
		$this->grantSequence(array("SELECT", "UPDATE"), "public", "tbl_msg_message_message_id_seq", "admin");
		$this->grantSequence(array("SELECT", "UPDATE"), "public", "tbl_msg_message_message_id_seq", "vilesci");
		
		// Create table public.tbl_msg_recipient
		$fields = array(
			"message_id" => array(
				"type" => "bigint",
				"null" => false
			),
			"person_id" => array(
				"type" => "bigint",
				"null" => false
			),
			"token" => array(
				"type" => "varchar(128)",
				"null" => true
			),
			"sent" => array(
				"type" => "timestamp DEFAULT NULL",
				"null" => true
			),
			"sentinfo" => array(
				"type" => "text DEFAULT NULL",
				"null" => true
			),
			"insertamum" => array(
				"type" => "timestamp DEFAULT NOW()",
				"null" => false
			),
			"insertvon" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("public", "tbl_msg_recipient", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_msg_recipient",
			"pk_tbl_msg_recipient",
			array("person_id", "message_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_recipient",
			"fk_tbl_msg_recipient_person_id",
			"person_id",
			"public",
			"tbl_person",
			"person_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_recipient",
			"fk_tbl_msg_recipient_message_id",
			"message_id",
			"public",
			"tbl_msg_message",
			"message_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addUniqueKey(
			"public",
			"tbl_msg_recipient",
			"uk_tbl_msg_recipient_token",
			array("token")
		);
		$this->addCommentToColumn("public", "tbl_msg_recipient", "person_id", "Receiver");
		$this->addCommentToColumn("public", "tbl_msg_recipient", "sent", "If NULL not sent, otherwise the shipping date");
		$this->grantTable("SELECT", "public", "tbl_msg_recipient", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_recipient", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_recipient", "vilesci");
		
		// Create table public.tbl_msg_status
		$fields = array(
			"message_id" => array(
				"type" => "bigint",
				"null" => false
			),
			"person_id" => array(
				"type" => "bigint",
				"null" => false
			),
			"status" => array(
				"type" => "smallint",
				"null" => false
			),
			"statusinfo" => array(
				"type" => "text",
				"null" => true
			),
			"insertamum" => array(
				"type" => "timestamp DEFAULT NOW()",
				"null" => false
			),
			"insertvon" => array(
				"type" => "varchar(32)",
				"null" => true
			),
			"updateamum" => array(
				"type" => "timestamp DEFAULT NOW()",
				"null" => false
			),
			"updatevon" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("public", "tbl_msg_status", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_msg_status",
			"pk_tbl_msg_status",
			array("message_id", "person_id", "status")
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_status",
			"fk_tbl_msg_status_person_id",
			"person_id",
			"public",
			"tbl_person",
			"person_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_status",
			"fk_tbl_msg_status_message_id",
			"message_id",
			"public",
			"tbl_msg_message",
			"message_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addCommentToColumn("public", "tbl_msg_status", "person_id", "Receiver");
		$this->grantTable("SELECT", "public", "tbl_msg_status", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_status", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_status", "vilesci");
		
		// Create table public.tbl_msg_attachment
		$fields = array(
			"attachment_id" => array(
				"type" => "serial"
			),
			"message_id" => array(
				"type" => "bigint",
				"null" => false
			),
			"name" => array(
				"type" => "text",
				"null" => true
			),
			"filename" => array(
				"type" => "text",
				"null" => true
			)
		);
		$this->createTable("public", "tbl_msg_attachment", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_msg_attachment",
			"pk_tbl_msg_attachment",
			array("attachment_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_msg_attachment",
			"fk_tbl_msg_attachment_message_id",
			"message_id",
			"public",
			"tbl_msg_message",
			"message_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "public", "tbl_msg_attachment", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_attachment", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_msg_attachment", "vilesci");
		$this->grantSequence(array("SELECT", "UPDATE"), "public", "tbl_msg_attachment_attachment_id_seq", "web");
		$this->grantSequence(array("SELECT", "UPDATE"), "public", "tbl_msg_attachment_attachment_id_seq", "admin");
		$this->grantSequence(array("SELECT", "UPDATE"), "public", "tbl_msg_attachment_attachment_id_seq", "vilesci");
		
		$this->endUP();
	}

	public function down()
	{
		$this->startDown();

		$this->dropTable("public", "tbl_msg_recipient");
		$this->dropTable("public", "tbl_msg_status");
		$this->dropTable("public", "tbl_msg_attachment");
		$this->dropTable("public", "tbl_msg_message");

		$this->endDown();
	}
}

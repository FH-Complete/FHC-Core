<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class MigrationLib extends CI_Migration
{
	private $MSG_PREFIX = "[-]";
	private $INFO_PREFIX = "[I]";
	private $ERROR_PREFIX = "[E]";
	private $SEPARATOR = "------------------------------";
	
	private $cli;
	
	public function __construct()
	{
		if ($this->input->is_cli_request())
		{
			$this->cli = true;
		}
		else
		{
			$this->cli = false;
		}
	}
	
	private function getEOL()
	{
		if ($this->cli === true)
		{
			return PHP_EOL;
		}
		else
		{
			return "<br>";
		}
	}
	
	private function printMessage($message)
	{
		printf("%s %s" . $this->getEOL(), $this->MSG_PREFIX, $message);
	}
	
	private function printInfo($info)
	{
		printf("%s %s" . $this->getEOL(), $this->INFO_PREFIX, $info);
	}
	
	private function printError($error)
	{
		printf("%s %s" . $this->getEOL(), $this->ERROR_PREFIX, $error);
	}
	
	protected function startUP()
	{
		$this->printInfo(sprintf("%s Start method up of class %s %s", $this->SEPARATOR, get_called_class(), $this->SEPARATOR));
	}
	
	protected function endUP()
	{
		$this->printInfo(sprintf("%s End method up of class %s %s", $this->SEPARATOR, get_called_class(), $this->SEPARATOR));
	}
	
	protected function columnExists($column, $schema, $table)
	{
		$query = sprintf("SELECT COUNT(%s) FROM %s.%s", $column, $schema, $table);
		
		if (! @$this->db->simple_query($query))
		{
			return false;
		}
		
		return true;
	}
	
    protected function addColumn($schema, $table, $column, $type)
	{
		if (!$this->columnExists($column, $schema, $table))
		{
			$query = sprintf("ALTER TABLE %s.%s ADD COLUMN %s %s", $schema, $table, $column, $type);
  			if (@$this->db->simple_query($query))
			{
				$this->printMessage(sprintf("Column %s.%s.%s of type %s added", $schema, $table, $column, $type));
			}
			else
			{
				$this->printError(sprintf("Error while adding column %s.%s.%s of type %s", $schema, $table, $column, $type));
			}
		}
		else
		{
			$this->printInfo(sprintf("Column %s.%s.%s already exists", $schema, $table, $column));
		}
	}
	
	protected function grantTable($permission, $schema, $table, $user)
	{
		$stringPermission = null;

		if (is_array($permission))
		{
			if (count($permission) > 0)
			{
				$stringPermission = "";
				for ($i = 0; $i < count($permission); $i++)
				{
					$stringPermission .= $permission[$i];
					if ($i != count($permission) - 1)
					{
						$stringPermission .= ", ";
					}
				}
				$query = sprintf("GRANT %s ON TABLE %s.%s TO %s", $stringPermission, $schema, $table, $user);
			}
		}
		else
		{
			$query = sprintf("GRANT %s ON TABLE %s.%s TO %s", $permission, $schema, $table, $user);
		}

		if (@$this->db->simple_query($query))
		{
			$this->printMessage(
				sprintf("Granted permissions %s on table %s.%s to user %s",
						is_null($stringPermission) ? $permission : $stringPermission,
						$schema,
						$table,
						$user
			));
		}
		else
		{
			$this->printError(
				sprintf("Granting permissions %s on table %s.%s to user %s",
						is_null($stringPermission) ? $permission : $stringPermission,
						$schema,
						$table,
						$user
			));
		}
	}
	
	protected function createTable($schema, $table, $fields)
	{
		if (! $this->db->table_exists($schema . "." . $table))
		{
			$query = sprintf("CREATE TABLE %s.%s (%s)", $schema, $table, $fields);
			
			if (@$this->db->simple_query($query))
			{
				$this->printMessage(sprintf("Table %s.%s created", $schema, $table));
			}
			else
			{
				$this->printError(sprintf("Creating table %s.%s", $schema, $table));
			}
		}
		else
		{
			$this->printInfo(sprintf("Table %s.%s already exists", $schema, $table));
		}
	}
	
	protected function execQuery($query)
	{
		if (! @$this->db->simple_query($query))
		{
			$this->printError($this->db->error());
		}
		
		$this->printInfo("Query correctly executed");
	}
}
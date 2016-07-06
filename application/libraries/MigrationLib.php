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
		parent::__construct();
		
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
	
    protected function addColumn($schema, $table, $fields)
	{
		foreach($fields as $name => $definition)
		{
			if (!$this->db->field_exists($name, $schema . '.' . $table))
			{
				if ($this->dbforge->add_column($schema . '.' . $table, array($name => $definition)))
				{
					$this->printMessage(sprintf("Column %s.%s.%s of type %s added", $schema, $table, $name, $definition["type"]));
				}
				else
				{
					$this->printError(sprintf("Error while adding column %s.%s.%s of type %s", $schema, $table, $name, $definition["type"]));
				}
			}
			else
			{
				$this->printInfo(sprintf("Column %s.%s.%s already exists", $schema, $table, $name));
			}
		}
	}
	
	protected function addPrimaryKey($schema, $table, $name, $fields)
	{
		$stringFields = null;

		if (is_array($fields))
		{
			if (count($fields) > 0)
			{
				$stringFields = "";
				for ($i = 0; $i < count($fields); $i++)
				{
					$stringFields .= $fields[$i];
					if ($i != count($fields) - 1)
					{
						$stringFields .= ", ";
					}
				}
				$query = sprintf("ALTER TABLE %s.%s ADD CONSTRAINT %s PRIMARY KEY (%s)", $schema, $table, $name, $stringFields);
			}
		}
		else
		{
			$query = sprintf("ALTER TABLE %s.%s ADD CONSTRAINT %s PRIMARY KEY (%s)", $schema, $table, $name, $fields);
		}
		
		if (@$this->db->simple_query($query))
		{
			$this->printMessage(sprintf("Added primary key %s on table %s.%s", $name, $schema, $table));
		}
		else
		{
			$this->printError(sprintf("Adding primary key %s on table %s.%s", $name, $schema, $table));
		}
	}
	
	protected function addForeingKey($schema, $table, $name, $field, $schemaDest, $tableDest, $fieldDest, $attributes)
	{
		$query = sprintf("ALTER TABLE %s.%s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s.%s (%s) %s",
						$schema, $table, $name, $field, $schemaDest, $tableDest, $fieldDest, $attributes);
		
		if (@$this->db->simple_query($query))
		{
			$this->printMessage(sprintf("Added foreign key %s on table %s.%s", $name, $schema, $table));
		}
		else
		{
			$this->printError(sprintf("Adding foreign key %s on table %s.%s", $name, $schema, $table));
		}
	}
	
	protected function grantTable($permissions, $schema, $table, $user)
	{
		$stringPermission = null;

		if (is_array($permissions))
		{
			if (count($permissions) > 0)
			{
				$stringPermission = "";
				for ($i = 0; $i < count($permissions); $i++)
				{
					$stringPermission .= $permissions[$i];
					if ($i != count($permissions) - 1)
					{
						$stringPermission .= ", ";
					}
				}
				$query = sprintf("GRANT %s ON TABLE %s.%s TO %s", $stringPermission, $schema, $table, $user);
			}
		}
		else
		{
			$query = sprintf("GRANT %s ON TABLE %s.%s TO %s", $permissions, $schema, $table, $user);
		}

		if (@$this->db->simple_query($query))
		{
			$this->printMessage(
				sprintf("Granted permissions %s on table %s.%s to user %s",
						is_null($stringPermission) ? $permissions : $stringPermission,
						$schema,
						$table,
						$user
			));
		}
		else
		{
			$this->printError(
				sprintf("Granting permissions %s on table %s.%s to user %s",
						is_null($stringPermission) ? $permissions : $stringPermission,
						$schema,
						$table,
						$user
			));
		}
	}
	
	protected function createTable($schema, $table, $fields)
	{
		$this->dbforge->add_field($fields);
		
		if ($this->dbforge->create_table($schema . '.' . $table, true))
		{
			$this->printMessage(sprintf("Table %s.%s created or existing", $schema, $table));
		}
		else
		{
			$this->printError(sprintf("Creating table %s.%s", $schema, $table));
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
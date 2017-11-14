<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Utility class to be used in the database migration process
 */
class MigrationLib extends CI_Migration
{
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads EPrintfLib
		$this->load->library('EPrintfLib');
	}

	/**
	 * Check if a column exists in a table and schema
	 */
	private function columnExists($name, $schema, $table)
	{
		$query = sprintf("SELECT %s FROM %s.%s LIMIT 1", $name, $schema, $table);

		if (@$this->db->simple_query($query))
		{
			return true;
		}

		return false;
	}

	/**
	 * Print an info about the starting of method up
	 */
	protected function startUP()
	{
		$this->eprintflib->printInfo(
			sprintf("%s Start method up of class %s %s", EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Print an info about the ending of method up
	 */
	protected function endUP()
	{
		$this->eprintflib->printInfo(
			sprintf("%s End method up of class %s %s", EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Print an info about the starting of method down
	 */
	protected function startDown()
	{
		$this->eprintflib->printInfo(
			sprintf("%s Start method down of class %s %s", EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Print an info about the ending of method down
	 */
	protected function endDown()
	{
		$this->eprintflib->printInfo(
			sprintf("%s End method down of class %s %s", EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Adds a column, with attributes, to a table and schema
	 */
    protected function addColumn($schema, $table, $fields)
	{
		foreach ($fields as $name => $definition)
		{
			if (!$this->columnExists($name, $schema, $table))
			{
				if ($this->dbforge->add_column($schema.'.'.$table, array($name => $definition)))
				{
					$this->eprintflib->printMessage(sprintf("Column %s.%s.%s of type %s added", $schema, $table, $name, $definition["type"]));
				}
				else
				{
					$this->eprintflib->printError(sprintf("Error while adding column %s.%s.%s of type %s", $schema, $table, $name, $definition["type"]));
				}
			}
			else
			{
				$this->eprintflib->printInfo(sprintf("Column %s.%s.%s already exists", $schema, $table, $name));
			}
		}
	}

	/**
	 * Modifies a column, and its attributes, of a table and schema
	 */
	protected function modifyColumn($schema, $table, $fields)
	{
		foreach ($fields as $name => $definition)
		{
			if ($this->columnExists($name, $schema, $table))
			{
				if ($this->dbforge->modify_column($schema.'.'.$table, array($name => $definition)))
				{
					$this->eprintflib->printMessage(sprintf("Column %s.%s.%s has been modified", $schema, $table, $name));
				}
				else
				{
					$this->eprintflib->printError(sprintf("Error while modifying column %s.%s.%s", $schema, $table, $name));
				}
			}
			else
			{
				$this->eprintflib->printInfo(sprintf("Column %s.%s.%s doesn't exist", $schema, $table, $name));
			}
		}
	}

	/**
	 * Drops a column from a table and schema
	 */
	protected function dropColumn($schema, $table, $field)
	{
		if ($this->columnExists($field, $schema, $table))
		{
			if ($this->dbforge->drop_column($schema.'.'.$table, $field))
			{
				$this->eprintflib->printMessage(sprintf("Column %s.%s.%s has been dropped", $schema, $table, $field));
			}
			else
			{
				$this->eprintflib->printError(sprintf("Error while dropping column %s.%s.%s", $schema, $table, $field));
			}
		}
		else
		{
			$this->eprintflib->printInfo(sprintf("Column %s.%s.%s doesn't exist", $schema, $table, $field));
		}
	}

	/**
	 * Sets a column as primary key of a table and schema
	 */
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
			$this->eprintflib->printMessage(sprintf("Added primary key %s on table %s.%s", $name, $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Adding primary key %s on table %s.%s", $name, $schema, $table));
		}
	}

	/**
	 * Sets a column as foreign key of a table and schema
	 */
	protected function addForeingKey($schema, $table, $name, $field, $schemaDest, $tableDest, $fieldDest, $attributes)
	{
		$query = sprintf(
			"ALTER TABLE %s.%s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s.%s (%s) %s",
			$schema,
			$table,
			$name,
			$field,
			$schemaDest,
			$tableDest,
			$fieldDest,
			$attributes
		);

		if (@$this->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf("Added foreign key %s on table %s.%s", $name, $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Adding foreign key %s on table %s.%s", $name, $schema, $table));
		}
	}

	/**
	 * Sets a column as unique key of a table and schema
	 */
	protected function addUniqueKey($schema, $table, $name, $fields)
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
				$query = sprintf("CREATE UNIQUE INDEX %s ON %s.%s (%s)", $name, $schema, $table, $stringFields);
			}
		}
		else
		{
			$query = sprintf("CREATE UNIQUE INDEX %s ON %s.%s (%s)", $name, $schema, $table, $fields);
		}

		if (@$this->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf("Added unique key %s on table %s.%s", $name, $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Adding unique key %s on table %s.%s", $name, $schema, $table));
		}
	}

	/**
	 * Grants permissions to a user on a table and schema
	 */
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
			$this->eprintflib->printMessage(
				sprintf(
					"Granted permissions %s on table %s.%s to user %s",
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$table,
					$user
				)
			);
		}
		else
		{
			$this->eprintflib->printError(
				sprintf(
					"Granting permissions %s on table %s.%s to user %s",
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$table,
					$user
				)
			);
		}
	}

	/**
	 * Creates a table in a schema with columns
	 */
	protected function createTable($schema, $table, $fields)
	{
		$this->dbforge->add_field($fields);

		if ($this->dbforge->create_table($schema.'.'.$table, true))
		{
			$this->eprintflib->printMessage(sprintf("Table %s.%s created or existing", $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Creating table %s.%s", $schema, $table));
		}
	}

	/**
	 * Drops a table from a schema
	 */
	protected function dropTable($schema, $table)
	{
		if ($this->dbforge->drop_table($schema.".".$table))
		{
			$this->eprintflib->printMessage(sprintf("Table %s.%s has been dropped", $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Dropping table %s.%s", $schema, $table));
		}
	}

	/**
	 * Initializes a sequence with the max value of a column
	 */
	protected function initializeSequence($schemaSrc, $sequence, $schemaDst, $table, $field)
	{
		$query = sprintf("SELECT SETVAL('%s.%s', (SELECT MAX(%s) FROM %s.%s))", $schemaSrc, $sequence, $field, $schemaDst, $table);

		if (@$this->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf("Sequence %s.%s has been initialized", $schemaSrc, $sequence));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Initializing sequence %s.%s", $schemaSrc, $sequence));
		}
	}

	/**
	 * Add comment to a column
	 */
	protected function addCommentToColumn($schema, $table, $field, $comment)
	{
		$query = sprintf("COMMENT ON COLUMN %s.%s.%s IS ?", $schema, $table, $field);

		if (@$this->db->query($query, array($comment)))
		{
			$this->eprintflib->printMessage(sprintf("Comment added to %s.%s.%s", $schema, $table, $field));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Error while adding comment to %s.%s.%s", $schema, $table, $field));
		}
	}

	/**
	 * Add comment to a table
	 */
	protected function addCommentToTable($schema, $table, $comment)
	{
		$query = sprintf("COMMENT ON TABLE %s.%s IS ?", $schema, $table, $field);

		if (@$this->db->query($query, array($comment)))
		{
			$this->eprintflib->printMessage(sprintf("Comment added to %s.%s", $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf("Error while adding comment to %s.%s", $schema, $table));
		}
	}
	/**
	 * Grants permissions to a user on a sequence
	 */
	protected function grantSequence($permissions, $schema, $sequence, $user)
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
				$query = sprintf("GRANT %s ON SEQUENCE %s.%s TO %s", $stringPermission, $schema, $sequence, $user);
			}
		}
		else
		{
			$query = sprintf("GRANT %s ON SEQUENCE %s.%s TO %s", $permissions, $schema, $sequence, $user);
		}

		if (@$this->db->simple_query($query))
		{
			$this->eprintflib->printMessage(
				sprintf(
					"Granted permissions %s on sequence %s.%s to user %s",
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$sequence,
					$user
				)
			);
		}
		else
		{
			$this->eprintflib->printError(
				sprintf(
					"Granting permissions %s on sequence %s.%s to user %s",
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$sequence,
					$user
				)
			);
		}
	}

	/**
	 * Executes the given query
	 */
	protected function execQuery($query)
	{
		if (! @$this->db->simple_query($query))
		{
			$error = $this->db->error();

			if (is_array($error) && isset($error["message"]))
			{
				$this->eprintflib->printError($error["message"]);
			}
			else
			{
				$this->eprintflib->printError("Error while executing a query");
			}
		}

		$this->eprintflib->printInfo(
			"Query correctly executed: ".
			substr(preg_replace("/\s+/", " ", trim($query)), 0, EPrintfLib::PRINT_QUERY_LEN).
			(strlen($query) > EPrintfLib::PRINT_QUERY_LEN ? "..." : "")
		);
	}
}

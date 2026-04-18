<?php

namespace LKSCore\Core;

class SchemaBuilder
{
    private $columns = [];
    private $currentColumn = null;

    public function id()
    {
        $this->columns['id'] = "INT AUTO_INCREMENT PRIMARY KEY";
        $this->currentColumn = 'id';
        return $this;
    }

    public function string($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "VARCHAR(255)";
        return $this;
    }

    public function integer($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "INT";
        return $this;
    }

    public function boolean($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "TINYINT(1)";
        return $this;
    }

    public function text($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "TEXT";
        return $this;
    }

    public function default($value)
    {
        if ($this->currentColumn && isset($this->columns[$this->currentColumn])) {
            $this->columns[$this->currentColumn] .= " DEFAULT " . $this->formatValue($value);
        }
        return $this;
    }

    public function unique()
    {
        if ($this->currentColumn && isset($this->columns[$this->currentColumn])) {
            $this->columns[$this->currentColumn] .= " UNIQUE";
        }
        return $this;
    }

    public function nullable()
    {
        if ($this->currentColumn && isset($this->columns[$this->currentColumn])) {
            $this->columns[$this->currentColumn] .= " NULL";
        }
        return $this;
    }

    public function timestamps()
    {
        $this->columns['created_at'] = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns['updated_at'] = "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function build($table)
    {
        $cols = [];
        foreach ($this->columns as $name => $type) {
            $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
            $cols[] = "`$name` $type";
        }

        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        $columnsSql = implode(", ", $cols);
        $sql = "CREATE TABLE IF NOT EXISTS `$table` ($columnsSql)";

        Database::getInstance()->exec($sql);
    }

    private function formatValue($value)
    {
        if ($value === null) return 'NULL';
        if (is_bool($value)) return $value ? '1' : '0';
        if (is_numeric($value)) return $value;
        
        // FIX: Use PDO::quote for safe string literal formatting in DDL
        return Database::getInstance()->quote($value);
    }
}
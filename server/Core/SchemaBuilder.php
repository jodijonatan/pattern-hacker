<?php

namespace LKSCore\Core;

class SchemaBuilder
{
    /**
     * format:
     * [
     *   "column_name" => "TYPE + MODIFIERS"
     * ]
     */
    private $columns = [];

    private $currentColumn = null;

    // =====================
    // BASIC COLUMNS
    // =====================

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

    public function longText($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "LONGTEXT";
        return $this;
    }

    public function float($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "FLOAT";
        return $this;
    }

    public function double($name)
    {
        $this->currentColumn = $name;
        $this->columns[$name] = "DOUBLE";
        return $this;
    }

    // =====================
    // MODIFIERS
    // =====================

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

    // =====================
    // TIMESTAMPS
    // =====================

    public function timestamps()
    {
        $this->columns['created_at'] =
            "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";

        $this->columns['updated_at'] =
            "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

        return $this;
    }

    // =====================
    // BUILD QUERY
    // =====================

    public function build($table)
    {
        $cols = [];

        foreach ($this->columns as $name => $type) {
            $cols[] = "`$name` $type";
        }

        $columns = implode(", ", $cols);

        $sql = "CREATE TABLE IF NOT EXISTS `$table` ($columns)";

$db = \LKSCore\Core\Database::getInstance();
        $db->query($sql);
    }

    // =====================
    // HELPER
    // =====================

    private function formatValue($value)
    {
        if (is_string($value)) {
            return "'" . $value . "'";
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }
}
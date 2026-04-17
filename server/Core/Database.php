<?php

namespace LKSCore\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private static $config = null;
    private $conn;

    public static function init(array $config)
    {
        self::$config = $config;
        self::$instance = null;
    }

    public function __construct()
    {
        $config = self::$config ?? [
            'host' => 'localhost',
            'db'   => 'lks_game_db',
            'user' => 'root',
            'pass' => ''
        ];

        try {
            $this->conn = new PDO(
                "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance->conn;
    }

    public static function table($table)
    {
        return new class($table) {
            private $db;
            private $tableName;
            private $joins = [];
            private $wheres = [];
            private $bindings = [];
            private $orderBy = null;
            private $limitVal = null;
            private $selects = '*';

            public function __construct($t)
            {
                $this->db = Database::getInstance();
                $this->tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $t);
            }

            public function select($columns)
            {
                $this->selects = is_array($columns) ? implode(', ', $columns) : $columns;
                return $this;
            }

            public function join($table, $first, $operator, $second)
            {
                $tableClean = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $this->joins[] = " JOIN `$tableClean` ON $first $operator $second";
                return $this;
            }

            public function where($column, $operator, $value = null)
            {
                if ($value === null) {
                    $value = $operator;
                    $operator = '=';
                }
                // Basic column sanitization for simple calls
                $cleanColumn = (strpos($column, '.') !== false) ? $column : "`$column`";
                $this->wheres[] = "$cleanColumn $operator ?";
                $this->bindings[] = $value;
                return $this;
            }

            public function orderBy($column, $direction = 'ASC')
            {
                $column = preg_replace('/[^a-zA-Z0-9_\.]/', '', $column);
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $this->orderBy = "$column $direction";
                return $this;
            }

            public function limit($count)
            {
                $this->limitVal = (int) $count;
                return $this;
            }

            private function buildWhere()
            {
                if (empty($this->wheres)) return '';
                return ' WHERE ' . implode(' AND ', $this->wheres);
            }

            private function buildSuffix()
            {
                $sql = '';
                if ($this->orderBy) $sql .= " ORDER BY {$this->orderBy}";
                if ($this->limitVal) $sql .= " LIMIT {$this->limitVal}";
                return $sql;
            }

            public function get()
            {
                return $this->all();
            }

            public function all()
            {
                $sql = "SELECT {$this->selects} FROM `{$this->tableName}`" . 
                       implode('', $this->joins) . 
                       $this->buildWhere() . 
                       $this->buildSuffix();
                $stmt = $this->db->prepare($sql);
                $stmt->execute($this->bindings);
                return $stmt->fetchAll();
            }

            public function first()
            {
                $results = $this->limit(1)->all();
                return $results[0] ?? null;
            }

            public function find($id)
            {
                return $this->where('id', $id)->first();
            }

            public function insert($data)
            {
                $keys = array_keys($data);
                $quotedKeys = array_map(fn($k) => "`$k`", $keys);
                $placeholders = array_fill(0, count($data), '?');
                
                $sql = "INSERT INTO `{$this->tableName}` (" . implode(',', $quotedKeys) . ") VALUES (" . implode(',', $placeholders) . ")";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(array_values($data));
                return $this->db->lastInsertId();
            }

            public function update($data)
            {
                $sets = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $sets[] = "`$key` = ?";
                    $values[] = $value;
                }
                $values = array_merge($values, $this->bindings);
                $sql = "UPDATE `{$this->tableName}` SET " . implode(', ', $sets) . $this->buildWhere();
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($values);
            }

            public function delete()
            {
                $sql = "DELETE FROM `{$this->tableName}`" . $this->buildWhere();
                $stmt = $this->db->prepare($sql);
                return $stmt->execute($this->bindings);
            }
        };
    }

    public static function query($sql, $params = [])
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function exec($sql)
    {
        return self::getInstance()->exec($sql);
    }
}

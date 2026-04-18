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
                    PDO::ATTR_EMULATE_PREPARES   => false, // FIX: Real prepared statements
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['message' => 'DB Connection failed']);
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

            private $allowedOperators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS', 'IS NOT'];

            public function __construct($t)
            {
                $this->db = Database::getInstance();
                $this->tableName = $this->sanitizeIdentifier($t);
            }

            private function sanitizeIdentifier($id)
            {
                // Only allow alphanumeric, underscore, and dot (for table.column)
                return preg_replace('/[^a-zA-Z0-9_\.]/', '', $id);
            }

            private function validateOperator($operator)
            {
                $operator = strtoupper(trim($operator));
                if (!in_array($operator, $this->allowedOperators)) {
                    throw new \InvalidArgumentException("Invalid SQL operator: $operator");
                }
                return $operator;
            }

            public function select($columns)
            {
                if (is_array($columns)) {
                    $cleaned = array_map([$this, 'sanitizeIdentifier'], $columns);
                    $this->selects = implode(', ', $cleaned);
                } else {
                    $this->selects = $this->sanitizeIdentifier($columns);
                }
                return $this;
            }

            public function join($table, $first, $operator, $second)
            {
                $table = $this->sanitizeIdentifier($table);
                $first = $this->sanitizeIdentifier($first);
                $second = $this->sanitizeIdentifier($second);
                $operator = $this->validateOperator($operator);

                $this->joins[] = " JOIN `$table` ON $first $operator $second";
                return $this;
            }

            public function where($column, $operator, $value = null)
            {
                if ($value === null) {
                    $value = $operator;
                    $operator = '=';
                }

                $column = $this->sanitizeIdentifier($column);
                $operator = $this->validateOperator($operator);

                $this->wheres[] = "$column $operator ?";
                $this->bindings[] = $value;
                return $this;
            }

            public function orderBy($column, $direction = 'ASC')
            {
                $column = $this->sanitizeIdentifier($column);
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $this->orderBy = "$column $direction";
                return $this;
            }

            public function limit($count)
            {
                $this->limitVal = (int)$count;
                return $this;
            }

            public function get()
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
                $res = $this->limit(1)->get();
                return $res[0] ?? null;
            }

            public function insert($data)
            {
                $keys = array_keys($data);
                $quotedKeys = array_map(fn($k) => "`" . $this->sanitizeIdentifier($k) . "`", $keys);
                $placeholders = array_fill(0, count($data), '?');
                
                $sql = "INSERT INTO `{$this->tableName}` (" . implode(',', $quotedKeys) . ") VALUES (" . implode(',', $placeholders) . ")";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(array_values($data));
                return $this->db->lastInsertId();
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
        };
    }

    public static function query($sql, $params = [])
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

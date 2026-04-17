<?php

namespace LKSCore\Core;

class Schema
{
    public static function create($table, $callback)
    {
        $builder = new SchemaBuilder();
        $callback($builder);
        $builder->build($table);
    }
}
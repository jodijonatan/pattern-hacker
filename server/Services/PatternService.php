<?php

class PatternService
{
    public static function generate($difficulty)
    {
        $types = ['arithmetic', 'increasing_gap', 'multiplicative'];
        $type = $types[array_rand($types)];

        switch ($type) {
            case 'arithmetic':
                return self::arithmetic($difficulty);
            case 'increasing_gap':
                return self::increasingGap($difficulty);
            case 'multiplicative':
                return self::multiplicative($difficulty);
        }
    }

    private static function arithmetic($difficulty)
    {
        $start = rand(1, 10);
        $diff = rand(1, 5 + $difficulty);

        $sequence = [];
        for ($i = 0; $i < 4; $i++) {
            $sequence[] = $start + ($i * $diff);
        }

        return [
            "sequence" => $sequence,
            "answer" => $start + 4 * $diff,
            "type" => "arithmetic"
        ];
    }

    private static function increasingGap($difficulty)
    {
        $current = rand(1, 10);
        $gap = rand(1, 3);

        $sequence = [$current];

        for ($i = 0; $i < 4; $i++) {
            $current += $gap;
            $sequence[] = $current;
            $gap++;
        }

        return [
            "sequence" => array_slice($sequence, 0, 4),
            "answer" => $current + $gap,
            "type" => "increasing_gap"
        ];
    }

    private static function multiplicative($difficulty)
    {
        $start = rand(1, 5);
        $factor = rand(2, 2 + $difficulty);

        $sequence = [];
        for ($i = 0; $i < 4; $i++) {
            $sequence[] = $start * pow($factor, $i);
        }

        return [
            "sequence" => $sequence,
            "answer" => $start * pow($factor, 4),
            "type" => "multiplicative"
        ];
    }
}
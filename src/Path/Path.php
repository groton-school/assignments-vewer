<?php

namespace GrotonSchool\Path;

/**
 * TODO deal with os-specific path separators
 * TODO truncate paths when subsequent portions start at root /foo/bar
 */
class Path
{
    public static function join(...$args): string
    {
        $trim = function ($value) {
            return trim($value, '/');
        };

        $filter = function ($value) {
            return !empty($value);
        };

        return join('/', array_filter(array_map($trim, $args), $filter));
    }
}

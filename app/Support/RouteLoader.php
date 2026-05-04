<?php

declare(strict_types=1);

namespace App\Support;

final class RouteLoader
{
    public static function load(string $pattern): void
    {
        $routeFiles = glob(base_path(mb_ltrim($pattern, '/\\')));

        if ($routeFiles === false) {
            return;
        }

        sort($routeFiles);

        foreach ($routeFiles as $routeFile) {
            require $routeFile;
        }
    }
}

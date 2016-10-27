<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {
        $router = self::createBaseRouter();
        $router[] = self::createAdminRouter('Admin', 'admin');

        return $router;
    }

    private static function createBaseRouter()
    {
        $router = new RouteList();

        return $router;
    }

    private static function createAdminRouter($moduleName, $routePrefix)
    {
        $router = new RouteList($moduleName);
        $router[] = self::route($routePrefix . '/<presenter>/<action>[/<model>]', 'Homepage:start');

        return $router;
    }

    private static function route($mask, $metadata = [], $flags = 0)
    {
        return new Route('[<locale=cs cs|en>/]' . $mask, $metadata, $flags);
    }
}

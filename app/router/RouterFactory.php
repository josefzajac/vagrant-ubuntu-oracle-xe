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
        $router   = self::createBaseRouter();
        $router[] = self::createAdminRouter('Admin', 'admin');
        $router[] = self::createFrontendRouter('Frontend');

        return $router;
    }

    private static function createBaseRouter()
    {
        $router   = new RouteList();

        $router[] = self::route('test', 'Test:');

        return $router;
    }

    private static function createAdminRouter($moduleName, $routePrefix)
    {
        $router   = new RouteList($moduleName);
        $router[] = self::route($routePrefix . '/<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }

    private static function createFrontendRouter($moduleName)
    {
        $router   = new RouteList($moduleName);
        $router[] = self::route('', 'Frontend:default');
        $router[] = self::route('aktualni', 'Frontend:current');
        $router[] = self::route('pripravovane', 'Frontend:next');
        $router[] = self::route('uzavrene', 'Frontend:old');
        $router[] = self::route('login', 'Login:login');
        $router[] = self::route('logout', 'Login:logout');
        $router[] = self::route('password-recovery', 'Login:passwordRecovery');
        $router[] = self::route('jak-na-to', 'Frontend:mainHowto');
        $router[] = self::route('o-projektu', 'Frontend:about');
        $router[] = self::route('seifertuv-zizkov/gash', 'Seifert:hash');
        $router[] = self::route('seifertuv-zizkov/vote/<participant_id>', 'Seifert:vote');
        $router[] = self::route('seifertuv-zizkov/registrace', 'Seifert:register');
        $router[] = self::route('seifertuv-zizkov/prihlaseni', 'Seifert:login');
        $router[] = self::route('seifertuv-zizkov/galerie', 'Seifert:gallery');
        $router[] = self::route('seifertuv-zizkov/pravidla', 'Seifert:rules');
        $router[] = self::route('seifertuv-zizkov/info', 'Seifert:info');
        $router[] = self::route('seifertuv-zizkov/jak-na-to', 'Seifert:howto');
        $router[] = self::route('seifertuv-zizkov/add', 'Seifert:add');
        $router[] = self::route('seifertuv-zizkov/', 'Seifert:landing');
        $router[] = self::route('<competition_slug>/gash', 'Competition:hash');
        $router[] = self::route('<competition_slug>/vote/<participant_id>', 'Competition:vote');
        $router[] = self::route('<competition_slug>/galerie', 'Competition:gallery');
        $router[] = self::route('<competition_slug>/pravidla', 'Competition:rules');
        $router[] = self::route('<competition_slug>/info', 'Competition:info');
        $router[] = self::route('<competition_slug>/jak-na-to', 'Competition:howto');
        $router[] = self::route('<competition_slug>/add', 'Competition:add');
        $router[] = self::route('<competition_slug>/', 'Competition:landing');

        return $router;
    }

    private static function route($mask, $metadata = [], $flags = 0)
    {
        return new Route('[<locale=cs cs|en>/]' . $mask, $metadata, $flags);
    }
}

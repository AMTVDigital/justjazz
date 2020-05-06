<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc1d6c79a2ab8c034d1c1abc5c70e08d4
{
    public static $prefixLengthsPsr4 = array (
        'j' => 
        array (
            'joshtronic\\' => 11,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'joshtronic\\' => 
        array (
            0 => __DIR__ . '/..' . '/joshtronic/php-loremipsum/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc1d6c79a2ab8c034d1c1abc5c70e08d4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc1d6c79a2ab8c034d1c1abc5c70e08d4::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

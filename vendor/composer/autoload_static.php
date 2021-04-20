<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit256a73115b96722060505506a2185113
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'Vimeo\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Vimeo\\' => 
        array (
            0 => __DIR__ . '/..' . '/vimeo/vimeo-api/src/Vimeo',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit256a73115b96722060505506a2185113::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit256a73115b96722060505506a2185113::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

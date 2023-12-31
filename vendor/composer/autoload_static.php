<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb1e7222f95bf5befb67c2720b2435595
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Classid\\TemplateReplacement\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Classid\\TemplateReplacement\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb1e7222f95bf5befb67c2720b2435595::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb1e7222f95bf5befb67c2720b2435595::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb1e7222f95bf5befb67c2720b2435595::$classMap;

        }, null, ClassLoader::class);
    }
}

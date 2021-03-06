<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit56344f08ca50dfe848ae9b8a750f8242
{
    public static $files = array (
        '11e87257e587b0e50e0a09e49cb9de7b' => __DIR__ . '/..' . '/ethnam/ethnam/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Ethnam\\Getopt\\' => 14,
            'Ethnam\\Generator\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ethnam\\Getopt\\' => 
        array (
            0 => __DIR__ . '/..' . '/ethnam/getopt/src',
        ),
        'Ethnam\\Generator\\' => 
        array (
            0 => __DIR__ . '/..' . '/ethnam/generator/src',
        ),
    );

    public static $classMap = array (
        'Config_File' => __DIR__ . '/..' . '/smarty/smarty/libs/Config_File.class.php',
        'Smarty' => __DIR__ . '/..' . '/smarty/smarty/libs/Smarty.class.php',
        'Smarty_Compiler' => __DIR__ . '/..' . '/smarty/smarty/libs/Smarty_Compiler.class.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit56344f08ca50dfe848ae9b8a750f8242::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit56344f08ca50dfe848ae9b8a750f8242::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit56344f08ca50dfe848ae9b8a750f8242::$classMap;

        }, null, ClassLoader::class);
    }
}

<?php

class autoloader
{

    /**
     * Enregistre l'autoloader
     * @return void
     */
    static function register(): void
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Inclut le fichier de la classe correspondante
     * @param $class
     * @return void
     */
    static function autoload($class): void
    {
        if (str_contains($class, 'Blog')) {
            $replacements = [
                '\\' => '/',
                'Blog/' => '',
            ];

            $filename = str_replace(array_keys($replacements), array_values($replacements), $class);

            require 'modules/' . $filename . '.php';
        } else {
            if (strpos($class, 'Exception')) {
                require '_assets/includes/exceptions/' . $class . '.php';
            } else {
                require '_assets/includes/' . $class . '.php';
            }
        }
    }

}
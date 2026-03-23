<?php
// Autoloader for subreg2abraflexi Debian package (pkg-php-tools)
// This file is generated for Debian packaging and should not be edited manually.

// Register dependencies installed to /usr/share/php
require_once '/usr/share/php/AbraFlexi/autoload.php';
require_once '/usr/share/php/Subreg/autoload.php';

// Register PSR-4 for this package (SpojeNet\SubregAbraFlexi)
spl_autoload_register(function ($class) {
    $prefix = 'SpojeNet\\SubregAbraFlexi\\';
    $base_dir = __DIR__ . '/SpojeNet/SubregAbraFlexi/';
    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

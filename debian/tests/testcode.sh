#!/bin/sh
set -e

cd /usr/share/subreg2abraflexi

# Verify PHP classes load correctly
php -r "require '/usr/lib/php/subreg2abraflexi/autoload.php'; echo class_exists('SpojeNet\SubregAbraFlexi\SubregPricelist') ? 'SubregPricelist OK' : 'SubregPricelist FAIL'; echo PHP_EOL;"
php -r "require '/usr/lib/php/subreg2abraflexi/autoload.php'; echo class_exists('SpojeNet\SubregAbraFlexi\Customers') ? 'Customers OK' : 'Customers FAIL'; echo PHP_EOL;"
php -r "require '/usr/lib/php/subreg2abraflexi/autoload.php'; echo class_exists('SpojeNet\SubregAbraFlexi\DomainCredit') ? 'DomainCredit OK' : 'DomainCredit FAIL'; echo PHP_EOL;"

echo "All class checks passed."

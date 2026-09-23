# DoceFlow — composer no cPanel (sem composer global)

```bash
cd ~/app.xd360.com.br   # ou pasta do doceflow, ex.: ~/doceflow.xd360.com.br

curl -sS https://getcomposer.org/installer -o composer-setup.php
/usr/local/bin/ea-php81 composer-setup.php --install-dir=. --filename=composer.phar
rm -f composer-setup.php
/usr/local/bin/ea-php81 composer.phar install --no-dev --no-interaction
```

Troque `ea-php81` por `php` se `php -v` já for 8.1+.

Confirme: `ls vendor/autoload.php`

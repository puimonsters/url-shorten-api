# Installation API

### Apache Config (httpd-vhosts.conf)

```

<VirtualHost *:80>
	ServerName url-shorten-api
	DocumentRoot "e:/project/api/url-shorten-api/public"
	<Directory  "e:/project/api/url-shorten-api/public/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
</VirtualHost>

```

### Environment Config (.env)

```

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test_rabbit_db
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6360

```

# Installation Database

```
create database

> php artisan migrate

dummy data

> php artisan db:seed --class=UrlShortensSeeder

```

```
sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `test_rabbit_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `test_rabbit_db`;

CREATE TABLE IF NOT EXISTS `url_shortens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hits` int(11) NOT NULL,
  `expiry` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

```

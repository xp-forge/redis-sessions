Redis Sessions
==============

[![Build status on GitHub](https://github.com/xp-forge/redis-sessions/workflows/Tests/badge.svg)](https://github.com/xp-forge/redis-sessions/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/redis-sessions/version.png)](https://packagist.org/packages/xp-forge/redis-sessions)

[Redis](https://redis.io/)-based sessions implementation

Example
-------

```php
use web\session\{Sessions, InRedis};

$inject->bind(Sessions::class, new InRedis('redis://localhost'));
``` 

To use authentication, pass it as username in the connection string, e.g. *redis://secret@localhost*.
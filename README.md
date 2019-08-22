Redis Sessions
==============

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/redis-sessions.svg)](http://travis-ci.org/xp-forge/redis-sessions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.6+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_6plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/redis-sessions/version.png)](https://packagist.org/packages/xp-forge/redis-sessions)

[Redis](https://redis.io/)-based sessions implementation

Example
-------

```php
use web\session\{Sessions, Redis};

$inject->bind(Sessions::class, new Redis('redis://localhost'));
``` 

To use authentication, pass it as username in the connection string, e.g. *redis://secret@localhost*.
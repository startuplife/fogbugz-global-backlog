# Backlog
Allows you to keep track of a global backlog across all milestones.

## Requirements
* [Redis](http://redis.io/) >= 2.6.13
* [PHPRedis](https://github.com/nicolasff/phpredis)
* [Composer](http://getcomposer.org/)

## Installation
Run Composer
```Bash
ckdarby@ckdarby-workstation:~/www/fogbugz-global-backlog$ composer install
```

Set up cronjob to run every 15 minutes
```Bash
ckdarby@ckdarby-workstation:$ crontab -e
*/15 * * * * php ~/www/fogbugz-global-backlog/app/console backlog:fogbugz
```

Manually pull from fogbugz
```Bash
ckdarby@ckdarby-workstation:~/www/fogbugz-global-backlog$ php app/console backlog:fogbugz
```

Manually push backlog order to fogbugz
```Bash
ckdarby@ckdarby-workstation:~/www/fogbugz-global-backlog$ php app/console backlog:fogbugz --push
```

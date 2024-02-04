Redis Sessions change log
=========================

## ?.?.? / ????-??-??

## 2.0.0 / 2024-02-04

* Implemented xp-framework/rfc#341: Drop XP <= 9 compatibility - @thekid
* Added PHP 8.4 to the test matrix - @thekid
* Merged PR #3: Migrate to new testing library - @thekid

## 1.1.1 / 2022-06-11

* Added compatibility with `xp-forge/sessions` version 3.0 - @thekid

## 1.1.0 / 2022-04-12

* Merged PR #2: Make naming consistent with other session implementations.
  The entry point class is now called `web.session.InRedis`
  (@thekid)

## 1.0.2 / 2021-10-24

* Made compatible with XP 11 as well as new major versions of
  `xp-framework/networking` and `xp-forge/sessions`
  (@thekid)

## 1.0.1 / 2020-04-10

* Made compatible with `xp-forge/uri` version 2.0.0 - @thekid

## 1.0.0 / 2020-04-05

* Implemented xp-framework/rfc#334: Drop PHP 5.6. The minimum required
  PHP version is now 7.0.0!
  (@thekid)
* Made compatible with XP 10 - @thekid

## 0.2.0 / 2019-08-25

* Merge PR #1: Extract Redis protocol implementation to its own library
  https://github.com/xp-forge/redis
  (@thekid)
* Implement session timeout properly - @thekid
* Fixed error message when authentication fails - @thekid

## 0.1.0 / 2019-08-23

* Hello World! First release - @thekid
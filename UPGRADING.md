# Upgrading

## From 1.x to 2.x

### High impact changes

#### PHP 8.4 requirement

The minimum PHP version is now 8.4.

### Low impact changes

#### Bump to `spatie/crawler` ^9.1

This version now uses `spatie/crawler` ^9.1, which implements response streaming.

If you have a large site and want to reduce memory usage during the export, you can enable streaming in your `config/export.php`:

```php
'use_streaming' => true,
```

By default, streaming is disabled.

#### Internal API changes

The `LocalClient` signature has changed. It is now an invokable class used as a Guzzle handler. If you were extending or using `LocalClient` directly, please review the updated implementation.

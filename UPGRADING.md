# Upgrading

## From 1.3 to 1.4

### PHP 8.4 requirement

The minimum PHP version is now 8.4.

### `spatie/crawler` ^9.1

This version uses `spatie/crawler` ^9.1, which supports response streaming.

If you have a large site and want to reduce memory usage during the export, you can enable streaming in `config/export.php`:

```php
'use_streaming' => true,
```

By default, streaming is disabled, so behaviour is unchanged for existing installations.

### Internal API changes

The `LocalClient` signature has changed. It is now an invokable class used as a Guzzle handler. If you were extending or using `LocalClient` directly, please review the updated implementation.

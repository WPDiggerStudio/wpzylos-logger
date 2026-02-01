# WPZylos Logger

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-WPDiggerStudio-181717?logo=github)](https://github.com/WPDiggerStudio/wpzylos-logger)

PSR-3 compliant logging for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/WPDiggerStudio/wpzylos-logger/issues)**

---

## ✨ Features

- **PSR-3 Compliant** — Standard logger interface
- **Multiple Channels** — File, error_log, WP Debug Bar
- **Log Levels** — Emergency to Debug levels
- **Context Support** — Structured logging with context
- **Log Rotation** — Automatic file rotation

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## 🚀 Installation

```bash
composer require wpdiggerstudio/wpzylos-logger
```

---

## 📖 Quick Start

```php
use WPZylos\Framework\Logger\Logger;

$logger = new Logger($context);

$logger->info('User logged in', ['user_id' => 123]);
$logger->error('Payment failed', ['order_id' => 456]);
$logger->debug('API response', ['body' => $response]);
```

---

## 🏗️ Core Features

### Log Levels

```php
$logger->emergency('System is unusable');
$logger->alert('Action must be taken immediately');
$logger->critical('Critical conditions');
$logger->error('Error conditions');
$logger->warning('Warning conditions');
$logger->notice('Normal but significant condition');
$logger->info('Informational messages');
$logger->debug('Debug-level messages');
```

### Context Data

```php
$logger->info('Order placed', [
    'order_id' => $order->id,
    'user_id' => $user->id,
    'total' => $order->total,
]);
```

### Log Files

```php
// Logs written to: wp-content/plugins/my-plugin/logs/debug.log
$logger->info('Application started');
```

---

## 📦 Related Packages

| Package                                                                | Description            |
| ---------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/WPDiggerStudio/wpzylos-core)         | Application foundation |
| [wpzylos-scaffold](https://github.com/WPDiggerStudio/wpzylos-scaffold) | Plugin template        |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

If you find this package helpful, consider buying me a coffee! Your support helps maintain and improve the WPZylos ecosystem.

<a href="https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC" target="_blank">
  <img src="https://img.shields.io/badge/Donate-PayPal-blue.svg?style=for-the-badge&logo=paypal" alt="Donate with PayPal" />
</a>

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [WPDiggerStudio](https://github.com/WPDiggerStudio)**

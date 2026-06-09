# WooCommerce Coupon Display Manager

[![WordPress Compatibility](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![WooCommerce Compatibility](https://img.shields.io/badge/WooCommerce-7.0%2B-purple.svg)](https://woocommerce.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-orange.svg)](LICENSE)

Control the position, style, and behavior of the WooCommerce coupon field on any checkout page — WooCommerce default, CartFlows, FunnelKit, and WooCommerce Blocks.

---

## 📖 Table of Contents

- [The Problem](#-the-problem)
- [The Solution](#-the-solution)
- [Key Features](#-key-features)
- [Compatibility](#-compatibility)
- [Installation](#-installation)
- [Configuration Settings](#-configuration-settings)
- [Developer Hooks & Filters](#-developer-hooks--filters)
- [Changelog](#-changelog)
- [License](#-license)

---

## ⚠️ The Problem

The default WooCommerce checkout page is notorious for causing two major issues that directly hurt checkout conversion rates:
1. **Primary Button Confusion:** Customers often mistake the prominent "Apply Coupon" button for the "Place Order" button, leading to checkout confusion and failed submissions.
2. **Cart Abandonment:** A highly visible coupon input field prompts customers to leave the checkout flow to search Google for "coupon codes," resulting in lost sales.

---

## 💡 The Solution

**WooCommerce Coupon Display Manager** resolves these issues by letting you customize exactly where, how, and when the coupon field is rendered. Move it below the primary CTA, style it as a secondary element, or offer one-click clickable coupon badges directly on your checkout screen.

---

## ✨ Key Features

- **Smart Repositioning:** Move the coupon field directly **above the Place Order button** or keep it in the default sidebar/order review.
- **Click-to-Apply Coupon Badges:** Display active, valid public coupons on checkout. Customers can apply them instantly with a single click.
- **Applied Coupon Success Pills:** Elegant, responsive success badges showing applied coupons with a quick `(Remove)` action.
- **Button Style Customization:**
  - Secondary Outline (Gray/Neutral)
  - Minimal Text Link
  - Custom Color Scheme (configure background, text, and hover states)
- **Single Coupon Mode:** Option to automatically replace active coupons when a new code is applied, preventing discount stacking.
- **Performance First:** Lightweight codebase. JS and CSS assets are only enqueued on checkout pages.
- **HPOS Compatible:** Built with modern High-Performance Order Storage support.

---

## 🔌 Compatibility

This plugin is designed to work seamlessly with:
- **WooCommerce Classic Checkout** (shortcode `[woocommerce_checkout]`)
- **WooCommerce Block Checkout** (React-rendered Cart & Checkout Blocks)
- **CartFlows** (Free & Pro checkout steps)
- **FunnelKit / WooFunnels** (Advanced checkout templates)
- All major page builders (Elementor, Divi, Bricks, etc.)

---

## 🚀 Installation

1. Clone or download this repository.
2. Upload the `coupon-display-manager-for-woocommerce` directory to your WordPress plugins directory (usually `/wp-content/plugins/`).
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Go to **WooCommerce** -> **Coupon Display** to configure your options.

---

## ⚙️ Configuration Settings

Located under **WooCommerce -> Coupon Display**:

| Setting | Type | Description |
| :--- | :--- | :--- |
| **Activated** | Checkbox | Toggle custom coupon styling and repositioning. |
| **Layout Position** | Select | Choose between `Above Payment Button` or `WooCommerce Default / Sidebar`. |
| **Coupon Display Mode** | Select | Select `Manual Input Only`, `Clickable Coupon List Only`, or `Both`. |
| **Select Coupons to Display** | Grid | Filter which coupons appear in the clickable badge grid. |
| **Apply Button Text** | Text | Modify the CTA text shown on the apply button. |
| **Button Style** | Select | Choose `Secondary`, `Link`, or `Custom` color scheme. |
| **Single Coupon Mode** | Checkbox | Limit to one active coupon per order. |

---

## 🪝 Developer Hooks & Filters

Extend the plugin programmatically using these filters:

### Modify the Settings Array
```php
apply_filters( 'woocommerce_coupon_display_manager_settings', $settings );
```

---

## 📄 License

This project is licensed under the GNU General Public License v2.0 or later. See the [LICENSE](license.txt) file for details.

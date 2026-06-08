===  WooCommerce Coupon Display Manager ===
Contributors:      era ai
Tags:              woocommerce, coupon, checkout, cartflows, funnelkit
Requires at least: 6.0
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Control the position, style, and behavior of the WooCommerce coupon field on any checkout page — WooCommerce default, CartFlows, FunnelKit, and more.

== Description ==

**WooCommerce Coupon Display Manager** solves a common UX problem: customers mistake the prominent "Apply" coupon button for the "Place Order" payment button, causing confusion, drop-offs, and support tickets.

The plugin modifies the *presentation layer only* — no changes to WooCommerce payment logic or coupon calculations.

= Works Globally =

* **WooCommerce Classic Checkout** (shortcode `[woocommerce_checkout]`)
* **WooCommerce Block Checkout** (Cart & Checkout Blocks)
* **CartFlows** (free and pro)
* **FunnelKit / WooFunnels** Advanced Checkout Pages
* **Any page builder** embedding the WooCommerce checkout shortcode (Elementor, Divi, etc.)

= Three Display Modes =

* **Mode A — Collapsible (recommended):** The coupon field is hidden behind a discreet "Have a coupon?" link. Customers with codes can expand it; everyone else never sees it.
* **Mode B — Reposition:** Moves the coupon field to *below* the Place Order button, so the payment CTA is always the most prominent element.
* **Mode C — Restyle Only:** Keeps the field in its original position but restyled as a secondary/outline button so it no longer looks like the primary CTA.

= Key Features =

* Fully configurable from **WooCommerce → Coupon Display** — no code required.
* Custom text for the toggle link and the Apply button.
* Three Apply button style presets: Secondary (outline), Link (text only), or Custom Colors.
* Optional "Optional — only if you have a coupon" hint text.
* Optional **one-click coupon badges** — display valid coupons that customers can apply with a single click.
* Responsive on all screen sizes: 320 px → desktop.
* Zero JS / CSS loaded on non-checkout pages (performance safe).
* Clean uninstall — all options removed when the plugin is deleted.
* HPOS (High Performance Order Storage) compatible.
* Block Checkout compatible.

== Installation ==

1. Upload the `woo-coupon-display-manager` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Go to **WooCommerce → Coupon Display** to configure.

No WooCommerce coupon settings are changed — only the visual presentation on the checkout page.

== Frequently Asked Questions ==

= Does this affect how coupons work? =

No. All coupon logic (discount calculations, expiry, usage limits) remains 100% WooCommerce native. This plugin only changes how the coupon field *looks and where it appears*.

= Does it work with CartFlows? =

Yes. The plugin detects CartFlows checkout steps and loads its assets automatically. The collapsible toggle and restyle modes work with CartFlows' custom coupon field (`.wcf-custom-coupon-field`).

= Does it work with FunnelKit / WooFunnels? =

Yes. FunnelKit Funnel Builder and WooFunnels Advanced Checkout Pages are both detected. The plugin loads on those pages and applies the configured mode.

= What happens if I deactivate the plugin? =

The checkout reverts to the default WooCommerce coupon presentation immediately — no leftover CSS, DOM changes, or orphaned options (unless you choose to keep the settings; they are only deleted on full uninstall/Delete).

= Is the Block Checkout supported? =

Yes. Block Checkout (React-rendered) is handled by `blocks.js`, which uses a `MutationObserver` to apply customizations after React re-renders the component tree.

== Screenshots ==

1. Mode A — Collapsible: coupon field hidden under a link.
2. Mode B — Reposition: coupon field moved below Place Order.
3. Mode C — Restyle: secondary/outline Apply button.
4. Admin settings panel (WooCommerce → Coupon Display).
5. Applied coupon pill with one-click remove.

== Changelog ==

= 1.0.0 =
* Initial release. Features complete layout positioning, customized styling, and available coupon list badges compatible with WooCommerce Classic, Block Checkout, CartFlows, and FunnelKit.

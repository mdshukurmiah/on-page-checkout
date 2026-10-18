# On-Page Checkout for Elementor
A high-converting Elementor add-on to display WooCommerce products and enable seamless on-page checkout without redirects.

## Key Features

- **On-Page Checkout Widget**: Drag-and-drop checkout form directly into any Elementor page.
- **Product Countdown Widget**: Urgency countdown timer with target date, auto-reset (interval/daily), Bangla numbers, and complete style controls.
- **Product Scarcity Widget**: Stock urgency counter with pulsing SVG indicator, custom icons, count up/down modes, persistent localStorage counts, and custom styles.
- **Dedicated Elementor Category**: "On Page Checkout" category grouping all widgets in Elementor.
- **Single & Multi-Product Selection**: Choose between radio buttons and multi-select checkboxes.
- **Per-Card Quantity Selectors**: Individual quantity counters on each product card when multi-select is enabled.
- **Standalone Variation Cards**: Display variations as separate product cards with custom titles (`Title - Variation`) and variation-specific images.
- **Out of Stock Badges & Disabled State**: Out-of-stock items display an alert badge, dim card opacity, and prevent selection with dynamic variation dropdown updates.
- **Searchable Product Autocomplete**: Easily search and add products in the Elementor editor.
- **Mobile Optimized**: Collapsible order summary drawer toggle button on mobile devices.
- **Responsive Controls**: Customizable product card column grids and responsive gaps.
- **Field Customizer**: Enable, disable, and relabel checkout fields under `W3 OPC -> Checkout Fields`.

## Installation

1. Upload the `on-page-checkout` folder to `/wp-content/plugins/` or install via the WordPress Plugins menu.
2. Activate the plugin.
3. Ensure both **Elementor** and **WooCommerce** are installed and active.
4. Edit any page in Elementor, find the **On-Page Checkout** widgets under the **On Page Checkout** category, and drag them into your section.

## Changelog

### 1.0.2
- **Feature**: Added Countdown widget with target date/time, interval and daily auto-reset, Bangla number conversions, and full typography/box styling.
- **Feature**: Added Scarcity widget with pulsing SVG indicator, customizable icons, count up/down modes, persistent localStorage counts, and style controls.
- **Feature**: Added dedicated "On Page Checkout" Elementor widget category for all plugin widgets.
- **Feature**: Added multi-product selection support with customizable selection types (checkbox vs radio).
- **Feature**: Added individual per-card quantity selectors in multi-selection mode with responsive padding controls.
- **Feature**: Added option to display product variations as standalone separate product cards with custom title and variation images.
- **Feature**: Added out-of-stock badge display and disabled selection for out-of-stock products and variations, with dynamic in-card variation switching.
- **Feature**: Added searchable product autocomplete selection for easily adding products in Elementor.
- **Feature**: Added responsive column controls for product cards across desktop, tablet, and mobile.
- **Feature**: Added responsive gap settings between checkout form and order review table.
- **Feature**: Added styling controls for order review table header background.
- **Feature**: Added collapsible mobile order summary toggle button with configurable initial state.
- **Fix**: Resolved Elementor editor preview issue where the checkout form did not render for the default selected product.
- **Enhancement**: Performance optimization with on-demand conditional asset loading.

### 1.0.1
- **Fix**: Minor bug fixes and improvements.

### 1.0.0
- Initial release.



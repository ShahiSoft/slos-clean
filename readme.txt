=== Shahi LegalFlowSuite ===
Contributors: shahisoft
Tags: legal, gdpr, privacy, accessibility, wcag
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.5.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Professional legal operations management suite for WordPress with GDPR management, document generation, accessibility scanning, and automated WCAG fixes.

== Description ==

Shahi LegalFlowSuite is a comprehensive legal operations management plugin for WordPress. It provides tools to assist organizations with managing their legal operations, generating legal documents, handling data subject requests, and maintaining accessibility standards.

**⚠️ Legal Disclaimer:** This plugin provides tools and resources to help manage compliance-related tasks. It does not provide legal advice and does not guarantee compliance with any law or regulation. Consult with qualified legal counsel regarding your compliance obligations.

= Key Features =

**Privacy & Consent Management**
* GDPR management tools and consent management
* Cookie consent banner with customization options
* Data subject request (DSR) portal for GDPR, CCPA, and other privacy laws
* Consent tracking and audit logs

**Document Generation**
* Generate legal documents from templates
* Company profile management
* Document version control
* Professional document templates (Privacy Policy, Terms of Service, Cookie Policy, etc.)

**Data Subject Rights Portal**
* Handle access, deletion, portability, and rectification requests
* Email verification and secure request handling
* 30-day SLA tracking
* Export data in machine-readable formats

**Accessibility Scanner**
* WCAG 2.1 AA accessibility scanning
* Automated accessibility issue detection
* **Auto-Fix System** - 96 fixers automatically repair accessibility issues
* Focus indicators, touch targets, keyboard navigation fixes
* Animation pause controls, timing adjustments
* ARIA live regions, landmark structure
* Real-time progress popup with fixer status
* Detailed reports with remediation guidance
* Page-by-page accessibility scoring

**Modular Architecture**
* Enable/disable modules as needed
* Clean, modern admin interface
* Secure and performance-optimized
* Translation ready

= Who Is This For? =

* **Website Owners** - Ensure legal compliance and manage privacy requirements
* **Agencies** - Offer compliance services to clients
* **Legal Teams** - Streamline document generation and DSR management
* **Accessibility Officers** - Monitor and improve site accessibility

= Technical Highlights =

* PSR-4 autoloading with Composer
* WordPress Coding Standards compliant
* Secure: Input sanitization, output escaping, nonce verification
* Performance optimized with caching
* REST API for integrations
* AJAX-powered interfaces
* Fully translatable

= Privacy & Data Collection =

This plugin does NOT track any user data or send information to external servers. All data is stored locally in your WordPress database. The plugin includes tools to help YOU comply with privacy laws, but does not collect analytics or usage statistics itself.

= Documentation & Support =

For documentation, examples, and support, please visit the plugin's GitHub repository or contact support through WordPress.org forums.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress dashboard
2. Navigate to Plugins > Add New
3. Search for "Shahi LegalFlowSuite"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress dashboard
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin

= After Activation =

1. Navigate to SLOS in the admin menu
2. Complete the onboarding wizard to set up your company profile
3. Enable the modules you need (Compliance, Documents, DSR Portal, Accessibility)
4. Configure settings for each module
5. Start managing your legal compliance!

== Frequently Asked Questions ==

= Does this plugin require any external services? =

No. All functionality is self-contained within WordPress. No external API calls or third-party services are required.

= Is this plugin GDPR compliant? =

This plugin HELPS you achieve GDPR compliance by providing tools for consent management, data subject requests, and privacy documentation. However, GDPR compliance is a legal requirement that involves your entire organization's practices, not just a plugin.

= Can I use this on client sites? =

Yes! The plugin is licensed under GPLv3, which allows you to use it on unlimited sites, including client projects.

= Does it work with my theme? =

Yes! The plugin uses standard WordPress admin interfaces and does not affect your site's front-end theme. The consent banner can be customized to match your site's design.

= Can I disable modules I don't need? =

Absolutely! Navigate to SLOS > Modules to enable or disable: Privacy & Consent Management, Legal Documents, DSR Portal, and Accessibility Scanner.

= Is the plugin translated? =

The plugin is translation-ready with all strings properly wrapped for internationalization. You can translate it using standard WordPress translation tools like Loco Translate or Poedit.

= What happens to my data if I deactivate the plugin? =

Your data remains in the database. If you want to completely remove all plugin data, use the "Delete All Data" option in Settings before uninstalling.

= Does this create any database tables? =

Yes. The plugin creates several custom tables for consents, DSR requests, documents, and other features. These tables are properly indexed for performance.

== Privacy Policy ==

Shahi LegalFlowSuite does not:
* Collect any user data
* Send information to external servers
* Track user behavior or analytics
* Use cookies or local storage for tracking
* Connect to third-party services

All data generated by this plugin is stored locally in your WordPress database. The plugin helps YOU manage compliance, but does not report data to anyone.

== Screenshots ==

1. Main Dashboard - Overview of compliance status and quick actions
2. Module Management - Enable/disable features as needed
3. Compliance Dashboard - Consent management and tracking
4. Document Generator - Create legal documents from templates
5. DSR Portal - Handle data subject requests
6. Accessibility Scanner - WCAG accessibility scanning
7. Settings - Comprehensive configuration options

== Third Party Libraries ==

This plugin includes the following open source libraries in compliance with their respective licenses:

* **dompdf** (LGPL-2.1) - PDF generation library - https://github.com/dompdf/dompdf
* **Masterminds HTML5 Parser** (MIT) - HTML parsing - https://github.com/Masterminds/html5-php
* **Symfony CSS Selector** (MIT) - CSS selector parsing - https://symfony.com/components/CssSelector
* **Symfony DOM Crawler** (MIT) - DOM traversal - https://symfony.com/components/DomCrawler

All libraries are GPL-compatible and included with full source code.

== Changelog ==

= 3.4.0 - 2025-12-29 =
* NEW: 5 new accessibility fixer classes (LanguageChange, StatusMessage, ErrorIdentification, AnimationPause, TimingControl)
* NEW: Enhanced 7 existing fixers with actual auto-fix functionality
* NEW: JavaScript accessibility controls (keyboard traps, animation pause, timing controls)
* NEW: CSS accessibility styles (focus-visible, touch targets, high contrast support)
* NEW: Auto-Fix Progress Popup with real-time status tracking
* IMPROVED: Auto-fix coverage increased from 62% to 91%
* IMPROVED: 96 total fixers with comprehensive WCAG 2.1 AA coverage
* TESTED: 328 unit tests passing (100% pass rate)
* Performance: Average 1.65ms per fixer execution

= 3.3.0 - 2024-12-28 =
* BREAKING: Complete rebrand from "Shahi LegalOps Suite" to "Shahi LegalFlowSuite"
* Updated plugin slug, namespaces, text domain, and all identifiers
* Updated text domain from shahi-legalops-suite to shahi-legalflowsuite
* Updated PHP namespaces from ShahiLegalopsSuite to ShahiLegalFlowSuite
* Updated all constants from SHAHI_LEGALOPS_SUITE to SHAHI_LEGALFLOWSUITE
* Note: Fresh install required - not compatible with 3.2.x data

= 3.2.1 - 2024-12-28 =
* WordPress.org compliance improvements
* Removed "321" suffix from plugin name for clean slug
* Added legal disclaimer clarifying plugin provides tools, not legal advice
* Added comprehensive third-party libraries documentation
* Added privacy policy confirming no data collection or tracking
* Enhanced plugin description with specific features
* Updated minimum WordPress version to 6.0
* Added "Tested up to: 6.9" compatibility

= 3.1.1 - 2024-12-24 =
* WordPress.org compliance improvements
* Created comprehensive readme.txt in WordPress.org format
* Removed all external CDN dependencies (Chart.js)
* Removed premium/trialware UI elements (PRO badges, license activation)
* Improved menu highlighting and navigation
* Enhanced security and code quality
* Ready for WordPress.org directory submission

= 3.0.1 - 2024-12-24 =
* Removed analytics dashboard (non-functional)
* Improved admin menu highlighting
* Added icons to menu items
* Performance optimizations
* Bug fixes and stability improvements
* WordPress 6.7 compatibility

= 3.0.0 - 2024-12-01 =
* Major rewrite with modular architecture
* Added DSR Portal module
* Added Accessibility Scanner module
* Enhanced document generation
* New modern UI with dark theme
* Improved security and performance
* Full translation support

= 2.0.0 - 2024-06-15 =
* Added consent management
* Cookie scanner functionality
* Geolocation support
* Compliance dashboard

= 1.0.0 - 2024-01-10 =
* Initial release
* Basic compliance tools
* Document generation
* Company profile management

== Upgrade Notice ==

= 3.4.0 =
Major accessibility auto-fix enhancement. Adds 5 new fixer classes, enhances 7 existing fixers, and includes professional progress UI. Safe to update.

= 3.1.1 =
WordPress.org compliance release. Removed external dependencies and premium UI elements. Safe to update.

= 3.0.1 =
Performance improvements and bug fixes. Safe to update.

= 3.0.0 =
Major update with new modules. Please backup your database before upgrading. The plugin structure has been completely rewritten.

== Additional Info ==

= Credits =

* Developed by ShahiSoft
* Icons: Dashicons (bundled with WordPress)
* No external libraries required

= License =

This plugin is licensed under the GNU General Public License v3.0 or later.

= Support =

For support, please use the WordPress.org support forums. For bug reports and feature requests, visit our GitHub repository.

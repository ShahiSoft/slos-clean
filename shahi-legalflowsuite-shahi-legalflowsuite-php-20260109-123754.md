# Plugin Check Report

**Plugin:** Shahi LegalFlowSuite
**Generated at:** 2026-01-09 12:37:54


## `includes/Admin/Modules.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 74 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 195 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 195 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 195 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 199 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 199 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 200 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT module_key FROM $table WHERE is_enabled = 1&quot; |  |
| 221 | 71 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;shahi_modules_nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 221 | 71 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;shahi_modules_nonce&#039;] |  |
| 246 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 246 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 246 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 258 | 40 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;enabled_modules&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 270 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 270 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 272 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(*) FROM $table WHERE module_key = %s&quot; |  |
| 279 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 279 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 291 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 334 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 334 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 334 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 346 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 353 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 353 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |
| 404 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 404 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 422 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;module_key&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 462 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 476 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/API/Cookie_REST_Controller.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 319 | 22 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Core/I18n.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 129 | 20 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $text | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 129 | 27 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 142 | 28 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $text | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 142 | 35 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 155 | 9 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 155 | 13 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $text | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 155 | 20 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 168 | 21 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $text | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 168 | 28 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 183 | 20 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $text | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 183 | 27 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralContext | The $context parameter must be a single text string literal. Found: $context | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#disambiguation-by-context) |
| 183 | 37 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 197 | 28 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $text | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 197 | 35 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralContext | The $context parameter must be a single text string literal. Found: $context | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#disambiguation-by-context) |
| 197 | 45 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 212 | 20 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralSingle | The $single parameter must be a single text string literal. Found: $single | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 212 | 29 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralPlural | The $plural parameter must be a single text string literal. Found: $plural | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 212 | 47 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 228 | 21 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralSingle | The $single parameter must be a single text string literal. Found: $single | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 228 | 30 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralPlural | The $plural parameter must be a single text string literal. Found: $plural | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 228 | 48 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralContext | The $context parameter must be a single text string literal. Found: $context | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#disambiguation-by-context) |
| 228 | 58 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralDomain | The $domain parameter must be a single text string literal. Found: self::TEXT_DOMAIN | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |
| 373 | 13 | ERROR | PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound | load_plugin_textdomain() has been discouraged since WordPress version 4.6. When your plugin is hosted on WordPress.org, you no longer need to manually include this function call for translations under your plugin slug. WordPress will automatically load the translations for you as needed. | [Docs](https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/) |

## `includes/Core/Multilingual_Integration.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 173 | 20 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $default_value | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |

## `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 438 | 28 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_POST[&#039;attachment_id&#039;]. Check that the array index exists before using it. |  |
| 466 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 472 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 477 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 477 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 477 | 34 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 1245 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 1355 | 82 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$color'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 1427 | 38 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;issue_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 1499 | 47 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 1730 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to _n() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 1732 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 1843 | 26 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;enabled&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 2182 | 36 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;fixer_id&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 2184 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;content&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 2699 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 2699 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 2716 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 2742 | 31 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 2742 | 31 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 2744 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 2803 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 2803 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 2904 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;scan_post_types&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 2904 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;scan_post_types&#039;] |  |
| 2915 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;active_checkers&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 2915 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;active_checkers&#039;] |  |
| 3105 | 73 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;position&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 3112 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;color&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 3143 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 3342 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;text&#039;] |  |

## `includes/Modules/AccessibilityScanner/ConsentUxAutoScanner.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 208 | 17 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 219 | 17 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 229 | 17 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Modules/AccessibilityScanner/FixEngine/FixEngineAjaxHandler.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 94 | 21 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 94 | 43 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 117 | 23 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 118 | 36 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 118 | 36 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;fixer_id&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 203 | 24 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 204 | 23 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 204 | 89 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 204 | 89 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;fixer_ids&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 260 | 22 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 305 | 24 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 306 | 23 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 306 | 89 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 306 | 89 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;fixer_ids&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 334 | 22 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 335 | 22 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 364 | 21 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 364 | 50 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 399 | 38 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 400 | 38 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 402 | 38 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/AudioAccessibilityFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 95 | 28 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 99 | 66 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/EmptyLinkFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 220 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 225 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 232 | 33 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 252 | 13 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 257 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/IframeAccessibilityFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 128 | 13 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 131 | 29 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/TableCaptionFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 127 | 21 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 138 | 33 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Modules/AccessibilityScanner/Services/BackupService.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 102 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 131 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 131 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 131 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 132 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 133 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 134 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $backup_id |  |
| 162 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 162 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 162 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 163 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 164 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 165 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $post_id |  |
| 199 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 199 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 199 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 200 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 201 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 202 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $post_id |  |
| 203 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $limit |  |
| 257 | 21 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 284 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 284 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 284 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 285 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 286 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 287 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $date_threshold |  |
| 292 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 320 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 320 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 320 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 321 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 322 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 323 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $post_id |  |
| 340 | 41 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 340 | 48 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 340 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 340 | 63 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 340 | 90 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 341 | 41 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 341 | 48 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 341 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 341 | 63 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 341 | 111 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 342 | 41 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 342 | 48 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 342 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 342 | 63 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 342 | 105 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 343 | 41 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 343 | 48 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 343 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 343 | 63 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 343 | 97 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 344 | 41 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 344 | 48 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 344 | 54 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 344 | 63 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 344 | 97 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |

## `includes/PostTypes/PostTypeManager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 319 | 17 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 319 | 56 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 323 | 27 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 324 | 21 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 327 | 43 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to _n() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 328 | 43 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to _n() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 329 | 43 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to _n() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Services/Consent_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 496 | 27 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 499 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 499 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 499 | 30 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 564 | 27 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 567 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 567 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 567 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 582 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 582 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 584 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t FROM {$table}\n |  |
| 592 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 592 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 594 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t FROM {$table}\n |  |
| 602 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 602 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 604 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t FROM {$table}\n |  |
| 1209 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1209 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1340 | 33 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Services/DSR_Export_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 126 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 134 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 178 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 217 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 372 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 372 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 392 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 392 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 482 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 491 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 506 | 13 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fopen | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fopen(). |  |
| 524 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 699 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 741 | 57 | ERROR | WordPress.WP.AlternativeFunctions.unlink_unlink | unlink() is discouraged. Use wp_delete_file() to delete a file. |  |
| 744 | 10 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_rmdir | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: rmdir(). |  |
| 777 | 13 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to __() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |
| 802 | 17 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 802 | 45 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 806 | 25 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 807 | 50 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 835 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 846 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 846 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 861 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_readfile | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: readfile(). |  |
| 864 | 3 | ERROR | WordPress.WP.AlternativeFunctions.unlink_unlink | unlink() is discouraged. Use wp_delete_file() to delete a file. |  |

## `includes/Services/Profile_Validator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 479 | 24 | ERROR | WordPress.WP.I18n.NonSingularStringLiteralText | The $text parameter must be a single text string literal. Found: $this->field_labels[ $field ] | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#basic-strings) |

## `includes/Shortcodes/ModuleShortcode.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 93 | 17 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to esc_html__() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `templates/admin/modules.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 91 | 33 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to esc_html__() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `templates/admin/compliance/tabs/audit-logs.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 404 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 404 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 408 | 31 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 408 | 31 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 410 | 7 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$logs_table} at &quot;SELECT * FROM {$logs_table} ORDER BY created_at DESC LIMIT %d&quot; |  |
| 503 | 53 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 503 | 53 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 503 | 73 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$logs_table} at &quot;SELECT COUNT(*) FROM {$logs_table} WHERE 1 = %d&quot; |  |
| 506 | 21 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to esc_html__() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/Admin/DSRReports.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 166 | 24 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 166 | 67 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 166 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;end_date&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 167 | 24 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 167 | 69 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 167 | 69 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;start_date&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 715 | 59 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;_wpnonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 715 | 59 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;_wpnonce&#039;] |  |
| 724 | 69 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;start_date&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 725 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;end_date&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 738 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$content'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Admin/ThemeManager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 173 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 173 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 173 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 181 | 37 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 181 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;theme&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 257 | 44 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'wp_create_nonce'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Ajax/Compliance_Export_Ajax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 157 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 157 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 230 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 230 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 232 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE created_at &gt;= %s&quot; |  |
| 238 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 238 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 240 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT status, COUNT(*) as count FROM {$table} WHERE created_at &gt;= %s GROUP BY status&quot; |  |
| 247 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 247 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 249 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT type, COUNT(*) as count FROM {$table} WHERE created_at &gt;= %s GROUP BY type&quot; |  |
| 256 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 256 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 258 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT country_code, COUNT(*) as count FROM {$table} WHERE created_at &gt;= %s AND country_code IS NOT NULL GROUP BY country_code ORDER BY count DESC LIMIT 10&quot; |  |
| 388 | 60 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'number_format_i18n'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 399 | 60 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'number_format_i18n'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 411 | 60 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'number_format_i18n'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 423 | 60 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'number_format_i18n'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 433 | 32 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'number_format_i18n'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 437 | 32 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'number_format_i18n'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/API/Consent_Export_Controller.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 187 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$data'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Core/test-hooks.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 119 | 21 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'strlen'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 142 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"FAIL (Missing: $action)\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 172 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"FAIL (Missing: $filter)\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 197 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"FAIL (Missing example for: $hook_name)\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 205 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"FAIL (Missing example for: $hook_name)\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 222 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '" - $hook_name\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 226 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '" - $hook_name\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Core/validate-hooks.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 42 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '" ✓ $name\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 48 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '" ✓ $name\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/Admin/AccessibilitySettings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 23 | 3 | ERROR | PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing | Sanitization missing for register_setting(). | [Docs](https://developer.wordpress.org/reference/functions/register_setting/) |
| 24 | 3 | ERROR | PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing | Sanitization missing for register_setting(). | [Docs](https://developer.wordpress.org/reference/functions/register_setting/) |
| 25 | 3 | ERROR | PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing | Sanitization missing for register_setting(). | [Docs](https://developer.wordpress.org/reference/functions/register_setting/) |
| 26 | 3 | ERROR | PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing | Sanitization missing for register_setting(). | [Docs](https://developer.wordpress.org/reference/functions/register_setting/) |
| 27 | 3 | ERROR | PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing | Sanitization missing for register_setting(). | [Docs](https://developer.wordpress.org/reference/functions/register_setting/) |
| 41 | 17 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '__'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 42 | 17 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '__'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/FixEngine/AbstractFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 59 | 103 | ERROR | WordPress.Security.EscapeOutput.ExceptionNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$id'. | [Docs](https://developer.wordpress.org/apis/security/escaping/) |
| 222 | 25 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/CoverageMatrixAnalyzer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 142 | 34 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 191 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_mkdir | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: mkdir(). |  |
| 202 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_SERVER[&#039;SCRIPT_FILENAME&#039;]. Check that the array index exists before using it. |  |
| 202 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;SCRIPT_FILENAME&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 202 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;SCRIPT_FILENAME&#039;] |  |
| 210 | 30 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$results['legacy_count']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 211 | 33 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$results['fixengine_count']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 220 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"✓ Report saved: $filepath\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/FixEngine/DeterminismChecker.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 171 | 34 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 217 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_mkdir | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: mkdir(). |  |
| 228 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_SERVER[&#039;SCRIPT_FILENAME&#039;]. Check that the array index exists before using it. |  |
| 228 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;SCRIPT_FILENAME&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 228 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;SCRIPT_FILENAME&#039;] |  |
| 236 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Tested: {$results['tested']} fixers\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 237 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Deterministic: {$results['deterministic']}\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 238 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Non-Deterministic: {$results['non_deterministic']}\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 239 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Errors: {$results['errors']}\n\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 242 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"✓ Report saved: $filepath\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/FixEngine/FixerCollection.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 61 | 77 | ERROR | WordPress.Security.EscapeOutput.ExceptionNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$id'. | [Docs](https://developer.wordpress.org/apis/security/escaping/) |

## `includes/Modules/AccessibilityScanner/FixEngine/IntegrationTestSuite.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 55 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"\n[TEST] {$method}... "'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 64 | 37 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$result['reason']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 71 | 34 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 310 | 34 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 352 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_mkdir | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: mkdir(). |  |
| 363 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_SERVER[&#039;SCRIPT_FILENAME&#039;]. Check that the array index exists before using it. |  |
| 363 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;SCRIPT_FILENAME&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 363 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;SCRIPT_FILENAME&#039;] |  |
| 372 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Passed: {$results['passed']}/{$results['total']}\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 373 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Failed: {$results['failed']}/{$results['total']}\n\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 376 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"✓ Report saved: $filepath\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/FixEngine/PerformanceProfiler.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 94 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"Profiling {$id}... "'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 96 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"{$profile['avg_time']}s ({$profile['rating']})\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 176 | 34 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 249 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_mkdir | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: mkdir(). |  |
| 260 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_SERVER[&#039;SCRIPT_FILENAME&#039;]. Check that the array index exists before using it. |  |
| 260 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;SCRIPT_FILENAME&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 260 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;SCRIPT_FILENAME&#039;] |  |
| 272 | 10 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"✓ Report saved: $filepath\n"'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/FixEngine/Migrations/DatabaseSchemaMigration.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 28 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_mkdir | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: mkdir(). |  |
| 50 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '"\n[{$step}] "'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 61 | 35 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$result['error']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 80 | 58 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 92 | 42 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 92 | 49 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 92 | 55 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 92 | 64 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 92 | 88 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 98 | 38 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 98 | 45 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 98 | 51 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 98 | 60 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 98 | 80 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 158 | 50 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 158 | 57 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 158 | 63 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 158 | 72 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 158 | 94 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 164 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 164 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$index_name} at &quot;CREATE INDEX {$index_name} ON %i({$index[&#039;columns&#039;]})&quot; |  |
| 164 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$index[&#039;columns&#039;]} at &quot;CREATE INDEX {$index_name} ON %i({$index[&#039;columns&#039;]})&quot; |  |
| 167 | 35 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 194 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 194 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 194 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 196 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 203 | 25 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 203 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 203 | 38 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 203 | 47 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 203 | 79 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 209 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 209 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 223 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 223 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 223 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 225 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 230 | 34 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fk_sql |  |
| 234 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 252 | 26 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 255 | 26 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 256 | 26 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 269 | 24 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 286 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 286 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 286 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 287 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 287 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 288 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 289 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 289 | 12 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 289 | 18 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found posts |  |
| 295 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 295 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 295 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 296 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 297 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 317 | 42 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 317 | 49 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 317 | 55 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 317 | 64 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 317 | 91 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $fix_history_table |  |
| 349 | 34 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 366 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_SERVER[&#039;SCRIPT_FILENAME&#039;]. Check that the array index exists before using it. |  |
| 366 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;SCRIPT_FILENAME&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 366 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;SCRIPT_FILENAME&#039;] |  |
| 383 | 27 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$results['steps']['backup_tables']['backup_file']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Modules/AccessibilityScanner/Reporting/AccessibilityReporter.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 24 | 63 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;format&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 54 | 66 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;template&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 108 | 66 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;template&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 127 | 13 | WARNING | WordPress.DB.SlowDBQuery.slow_db_query_meta_query | Detected usage of meta_query, possible slow query. |  |
| 155 | 41 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 198 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 203 | 41 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 228 | 35 | ERROR | WordPress.Security.EscapeOutput.ExceptionNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '__'. | [Docs](https://developer.wordpress.org/apis/security/escaping/) |
| 248 | 59 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 355 | 71 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 551 | 18 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 562 | 41 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 570 | 45 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 570 | 54 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'date'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 585 | 32 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 585 | 41 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'date'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/PostTypes/Metaboxes.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 239 | 130 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$readonly'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 244 | 135 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$readonly'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 248 | 95 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$readonly'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 255 | 81 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$selected'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 263 | 93 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$checked'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 272 | 126 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$checked'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 300 | 72 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;shahi_item_details_nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 300 | 72 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;shahi_item_details_nonce&#039;] |  |
| 332 | 73 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;shahi_item_settings_nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 332 | 73 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;shahi_item_settings_nonce&#039;] |  |

## `includes/PostTypes/TemplateItem.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 310 | 47 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 312 | 50 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 313 | 49 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 314 | 49 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 319 | 47 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 321 | 50 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 322 | 54 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 323 | 56 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 324 | 55 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 329 | 47 | ERROR | WordPress.Security.EscapeOutput.UnsafePrintingFunction | All output should be run through an escaping function (like esc_html_e() or esc_attr_e()), found '_e'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-with-localization) |
| 346 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 346 | 45 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 347 | 16 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 356 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 356 | 43 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 357 | 35 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 357 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;shahi_status&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 362 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 363 | 33 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 363 | 33 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;shahi_item_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `includes/Services/PDF_Generator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 318 | 15 | ERROR | WordPress.WP.AlternativeFunctions.strip_tags_strip_tags | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |  |
| 635 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$pdf_data'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 649 | 14 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

## `includes/Widgets/QuickActionsWidget.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 64 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$args['before_widget']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 67 | 18 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$args['before_title']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 67 | 63 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$args['after_title']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 120 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$args['after_widget']'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/accessibility-settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 31 | 37 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'admin_url'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/dashboard.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 244 | 89 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$setup_progress'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 595 | 112 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'ShahiLegalFlowSuite'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Ajax/DashboardAjax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 68 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 68 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 69 | 60 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 71 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 71 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 22 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 85 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 85 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 89 | 1 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 132 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 136 | 28 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 168 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 168 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 178 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 184 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 184 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 185 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 185 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |

## `includes/API/AnalyticsController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 143 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 143 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 143 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 164 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 164 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 166 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 166 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at &quot;SELECT COUNT(*) FROM %i WHERE 1=1 $date_condition&quot; |  |
| 172 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 172 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 174 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at FROM $analytics_table \n |  |
| 175 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at WHERE 1=1 $date_condition \n |  |
| 182 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 182 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 183 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SELECT COUNT(DISTINCT user_id) FROM $analytics_table WHERE user_id &gt; 0 $date_condition&quot; |  |
| 183 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at &quot;SELECT COUNT(DISTINCT user_id) FROM $analytics_table WHERE user_id &gt; 0 $date_condition&quot; |  |
| 187 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 187 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 189 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at FROM $analytics_table \n |  |
| 190 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at WHERE 1=1 $date_condition \n |  |
| 224 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 224 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 240 | 34 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 240 | 34 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $where_clause at &quot;SELECT COUNT(*) FROM %i WHERE $where_clause&quot; |  |
| 242 | 35 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 242 | 35 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $where_clause at &quot;SELECT COUNT(*) FROM %i WHERE $where_clause&quot; |  |
| 244 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 244 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 244 | 28 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $total_query |  |
| 247 | 12 | WARNING | WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber | Incorrect number of replacements passed to $wpdb-&gt;prepare(). Found 1 replacement parameters, expected 3. |  |
| 248 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 248 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $where_clause at &quot;SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM %i WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d&quot; |  |
| 285 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 285 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 290 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 297 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 297 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |

## `includes/API/Config_REST_Controller.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 439 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_readfile | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: readfile(). |  |

## `includes/Core/Activator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 214 | 6 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 294 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 294 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 302 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 302 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 323 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 323 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 325 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 346 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 346 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 346 | 27 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 350 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Core/Security.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 344 | 24 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 378 | 19 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 378 | 78 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 631 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Database/DatabaseHelper.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 50 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 50 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 50 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 66 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 66 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 66 | 49 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 70 | 35 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 72 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 72 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 88 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 112 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 112 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 128 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 128 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 149 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 149 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 149 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 196 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 196 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 196 | 30 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 210 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;TRUNCATE TABLE {$table_name}&quot; |  |
| 224 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 224 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 224 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;OPTIMIZE TABLE {$table_name}&quot; |  |
| 244 | 37 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$column} at &quot;{$column} IN ({$placeholders})&quot; |  |
| 244 | 37 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$placeholders} at &quot;{$column} IN ({$placeholders})&quot; |  |
| 244 | 69 | WARNING | WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare | Replacement variables found, but no valid placeholders found in the query. |  |
| 246 | 37 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$column} at &quot;{$column} = %s&quot; |  |
| 268 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM {$table_name} \n |  |
| 278 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM {$table_name} \n |  |
| 286 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 286 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 286 | 30 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 378 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_CLIENT_IP&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 378 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_CLIENT_IP&#039;] |  |
| 380 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_X_FORWARDED_FOR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 380 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_X_FORWARDED_FOR&#039;] |  |
| 382 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotValidated | Detected usage of a possibly undefined superglobal array index: $_SERVER[&#039;REMOTE_ADDR&#039;]. Check that the array index exists before using it. |  |
| 382 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 382 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 396 | 27 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 410 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 412 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 412 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 414 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;DELETE FROM {$table_name} WHERE created_at &lt; %s&quot; |  |

## `includes/Database/QueryOptimizer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 46 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 47 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 60 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 60 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 73 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 76 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 76 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 78 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SELECT COUNT(*) FROM $table_name WHERE event_time BETWEEN %s AND %s&quot; |  |
| 85 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 85 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 87 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SELECT COUNT(DISTINCT user_id) FROM $table_name WHERE event_time BETWEEN %s AND %s&quot; |  |
| 94 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 94 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 96 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SELECT COUNT(*) FROM $table_name WHERE event_type = %s AND event_time BETWEEN %s AND %s&quot; |  |
| 107 | 25 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 108 | 25 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 109 | 25 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 133 | 39 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 133 | 71 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 142 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 142 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 146 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 151 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 156 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 161 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 166 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 172 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 173 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 176 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 176 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 178 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SELECT event_type, COUNT(*) as count FROM $table_name \n |  |
| 220 | 37 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 220 | 69 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 229 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 229 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 229 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SHOW TABLES LIKE &#039;$table_name&#039;&quot; |  |
| 234 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 235 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 238 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 238 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 240 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;SELECT page_url, COUNT(*) as views FROM $table_name \n |  |
| 274 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 275 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 307 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 307 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 49 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 49 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 50 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 60 | 36 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 60 | 36 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 69 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 69 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 93 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 93 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 93 | 29 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 95 | 6 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 106 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 113 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 139 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 139 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 149 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 149 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 149 | 28 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 151 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 162 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 185 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 185 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Database/Repositories/Base_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 98 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 114 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 114 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 114 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 115 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$this-&gt;primary_key} = %d&quot; |  |
| 115 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;primary_key} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$this-&gt;primary_key} = %d&quot; |  |
| 116 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $id |  |
| 143 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 143 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$args[&#039;order_by&#039;]} at &quot;SELECT * FROM {$this-&gt;table} ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 143 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$order} at &quot;SELECT * FROM {$this-&gt;table} ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 148 | 36 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 175 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$column} = %s ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 175 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$column} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$column} = %s ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 175 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$args[&#039;order_by&#039;]} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$column} = %s ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 175 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$order} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$column} = %s ORDER BY {$args[&#039;order_by&#039;]} {$order} LIMIT %d OFFSET %d&quot; |  |
| 181 | 36 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 207 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 229 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 256 | 36 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 259 | 38 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 271 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 271 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 271 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 272 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT COUNT(*) FROM {$this-&gt;table} WHERE {$this-&gt;primary_key} = %d&quot; |  |
| 272 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;primary_key} at &quot;SELECT COUNT(*) FROM {$this-&gt;table} WHERE {$this-&gt;primary_key} = %d&quot; |  |
| 273 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $id |  |

## `includes/Database/Repositories/Consent_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 84 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 84 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 84 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 85 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE user_id = %d AND status = &#039;accepted&#039; ORDER BY created_at DESC&quot; |  |
| 86 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $user_id |  |
| 101 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 101 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 101 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 102 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT COUNT(*) FROM {$this-&gt;table} WHERE user_id = %d AND type = %s AND status = &#039;accepted&#039;&quot; |  |
| 103 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $user_id |  |
| 104 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $type |  |
| 135 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT type, COUNT(*) as count FROM {$this-&gt;table} WHERE status = &#039;accepted&#039; GROUP BY type&quot; |  |
| 154 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT status, COUNT(*) as count FROM {$this-&gt;table} GROUP BY status&quot; |  |
| 311 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 311 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 313 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} \n |  |

## `includes/Database/Repositories/DSR_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 75 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 75 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |
| 95 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 111 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 111 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 111 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 112 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE verification_token = %s AND status = &#039;pending_verification&#039;&quot; |  |
| 113 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $token |  |
| 207 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT status, COUNT(*) as count FROM {$this-&gt;table} GROUP BY status&quot; |  |
| 225 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT request_type, COUNT(*) as count FROM {$this-&gt;table} GROUP BY request_type&quot; |  |
| 281 | 9 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 281 | 9 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 283 | 25 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_X_FORWARDED_FOR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 283 | 25 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_X_FORWARDED_FOR&#039;] |  |

## `includes/Database/Repositories/Legal_Doc_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 88 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 88 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 88 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 88 | 49 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 88 | 56 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found table |  |
| 92 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 92 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 92 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 92 | 49 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 92 | 56 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found versions_table |  |
| 166 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 166 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 166 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 167 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE doc_type = %s AND locale = %s LIMIT 1&quot; |  |
| 168 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $doc_type |  |
| 169 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $locale |  |
| 176 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 176 | 12 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 176 | 18 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 177 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE doc_type = %s AND locale = &#039;en_US&#039; LIMIT 1&quot; |  |
| 178 | 6 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $doc_type |  |
| 199 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 199 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 199 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 200 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE id = %d LIMIT 1&quot; |  |
| 201 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $id |  |
| 238 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where_clause} ORDER BY doc_type ASC, updated_at DESC&quot; |  |
| 238 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$where_clause} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where_clause} ORDER BY doc_type ASC, updated_at DESC&quot; |  |
| 238 | 96 | WARNING | WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare | Replacement variables found, but no valid placeholders found in the query. |  |
| 245 | 37 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 303 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 333 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 449 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 473 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 473 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 473 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 474 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;versions_table} at &quot;SELECT * FROM {$this-&gt;versions_table} \n |  |
| 478 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $doc_id |  |
| 479 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $limit |  |
| 501 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 501 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 501 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 502 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;versions_table} at &quot;SELECT * FROM {$this-&gt;versions_table} WHERE id = %d LIMIT 1&quot; |  |
| 503 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $version_id |  |
| 525 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 525 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 525 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 526 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;versions_table} at &quot;SELECT id FROM {$this-&gt;versions_table} \n |  |
| 530 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $doc_id |  |
| 531 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $keep_count |  |
| 563 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 563 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 563 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 564 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} \n |  |
| 568 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $current_profile_version |  |
| 607 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT status, COUNT(*) as count FROM {$this-&gt;table} GROUP BY status&quot; |  |
| 703 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 703 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 703 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 704 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;versions_table} at &quot;SHOW COLUMNS FROM {$this-&gt;versions_table} LIKE %s&quot; |  |
| 705 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $column |  |
| 712 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 712 | 12 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 712 | 18 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 713 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;versions_table} at &quot;SHOW COLUMNS FROM {$this-&gt;versions_table} LIKE %s&quot; |  |

## `includes/Modules/Module.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 329 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 329 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 332 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 358 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 358 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 361 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 361 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 403 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 403 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 407 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 415 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 415 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 427 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |

## `includes/Modules/ModuleManager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 323 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 323 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 326 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `includes/Modules/AccessibilityScanner/Compliance/AccessibilityStatementGenerator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 21 | 23 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 37 | 20 | WARNING | WordPress.WP.DeprecatedFunctions.get_page_by_titleFound | get_page_by_title() has been deprecated since WordPress version 6.2.0. Use WP_Query instead. |  |
| 53 | 10 | ERROR | PluginCheck.CodeAnalysis.Heredoc.NotAllowed | Use of heredoc syntax ( |  |

## `includes/Modules/AccessibilityScanner/FixEngine/FixHistoryRepository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 139 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 145 | 36 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 156 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 161 | 36 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 180 | 1 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 192 | 1 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 196 | 35 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 217 | 1 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 218 | 1 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 227 | 36 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 238 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 243 | 23 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 255 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 255 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 255 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 257 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 257 | 12 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found table_name |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/DownloadLinkFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 55 | 20 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/EmptyAltFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 170 | 46 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/ExternalLinkFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 24 | 103 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_HOST&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 24 | 103 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_HOST&#039;] |  |
| 25 | 16 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 47 | 18 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/FigureCaptionFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 85 | 28 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/GenericLinkTextFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 265 | 13 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/MissingAltFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 116 | 25 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 137 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 137 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 139 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 148 | 30 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 148 | 30 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 150 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/VideoAccessibilityFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 182 | 14 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 208 | 26 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 220 | 26 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Migrations/IdCanonicalizationMigration.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 127 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 127 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 129 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 198 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 198 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 203 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 203 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 203 | 50 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 216 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 216 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Modules/AccessibilityScanner/Fixes/AccessibilityFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 272 | 21 | ERROR | WordPress.WP.AlternativeFunctions.strip_tags_strip_tags | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |  |
| 324 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 330 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 334 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 341 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 345 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 351 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 352 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 355 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 364 | 6 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 382 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 526 | 105 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_HOST&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 526 | 105 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_HOST&#039;] |  |
| 527 | 18 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 534 | 20 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/Fixes/Fixers/LinkAndImageFixers.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 360 | 11 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 452 | 20 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 497 | 103 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_HOST&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 497 | 103 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_HOST&#039;] |  |
| 498 | 16 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 522 | 18 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/Scanner/Checkers/ColorRelianceCheck.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 31 | 11 | ERROR | WordPress.WP.AlternativeFunctions.strip_tags_strip_tags | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |  |

## `includes/Modules/AccessibilityScanner/Scanner/Checkers/ExternalLinkCheck.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 34 | 15 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 42 | 17 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/Scanner/Checkers/SkippedHeadingLevelCheck.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 47 | 27 | ERROR | WordPress.WP.AlternativeFunctions.strip_tags_strip_tags | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |  |

## `includes/Services/AnalyticsTracker.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 66 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 240 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[$key] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 240 | 29 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[$key] |  |
| 261 | 26 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 273 | 48 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_HOST&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 273 | 48 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_HOST&#039;] |  |
| 274 | 50 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REQUEST_URI&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 274 | 50 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REQUEST_URI&#039;] |  |
| 297 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 297 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 299 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(*) FROM $table WHERE created_at BETWEEN %s AND %s&quot; |  |
| 306 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 306 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 308 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(DISTINCT user_id) FROM $table WHERE created_at BETWEEN %s AND %s AND user_id IS NOT NULL&quot; |  |
| 315 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 315 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 317 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT event_type, COUNT(*) as count FROM $table WHERE created_at BETWEEN %s AND %s GROUP BY event_type ORDER BY count DESC&quot; |  |
| 348 | 18 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 350 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 350 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 352 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;DELETE FROM $table WHERE created_at &lt; %s&quot; |  |

## `includes/Services/Compliance_Score_Calculator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 787 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 787 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 787 | 55 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 802 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 802 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 804 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT DISTINCT country_code FROM {$table} WHERE country_code IS NOT NULL AND country_code != &#039;&#039; AND created_at &gt;= %s&quot; |  |
| 826 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 826 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 828 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT * FROM {$table} WHERE created_at &gt;= %s ORDER BY created_at DESC LIMIT %d&quot; |  |

## `includes/Services/Consent_Export_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 112 | 9 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fopen | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fopen(). |  |
| 154 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 368 | 9 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fopen | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fopen(). |  |
| 380 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 402 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |

## `includes/Services/DSR_Report_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 114 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 114 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 116 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s&quot; |  |
| 123 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 123 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 125 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s AND status = &#039;completed&#039;&quot; |  |
| 132 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 132 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 134 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s AND status NOT IN (&#039;completed&#039;, &#039;rejected&#039;)&quot; |  |
| 141 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 141 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 143 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s AND status = &#039;rejected&#039;&quot; |  |
| 173 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 173 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 176 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |
| 206 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 206 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 209 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |
| 239 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 239 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 242 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |
| 273 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 273 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 276 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |
| 286 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 286 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 289 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |
| 299 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 299 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 302 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |
| 331 | 34 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 331 | 34 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 333 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} \n |  |
| 342 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 342 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 344 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} \n |  |
| 357 | 36 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 357 | 36 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 359 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} \n |  |
| 611 | 4 | ERROR | WordPress.WP.AlternativeFunctions.unlink_unlink | unlink() is discouraged. Use wp_delete_file() to delete a file. |  |
| 640 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 640 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 644 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at \t\t\t\tFROM {$table} \n |  |

## `includes/Services/DSR_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 751 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 751 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 753 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 753 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $placeholders at &quot;SELECT COUNT(*) FROM %i WHERE status IN ($placeholders)&quot; |  |
| 759 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 759 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 759 | 50 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 762 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 762 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 764 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 772 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 772 | 26 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 774 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 785 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 785 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 786 | 20 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 797 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 797 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 799 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 813 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 813 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 815 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `shahi-legalflowsuite.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 71 | 3 | ERROR | PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound | load_plugin_textdomain() has been discouraged since WordPress version 4.6. When your plugin is hosted on WordPress.org, you no longer need to manually include this function call for translations under your plugin slug. WordPress will automatically load the translations for you as needed. | [Docs](https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/) |
| 384 | 3 | ERROR | PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent | Found call to wp_enqueue_script() with external resource. Offloading scripts to your servers or any remote service is disallowed. |  |
| 433 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Core/Assets.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1311 | 17 | ERROR | WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet | Stylesheets must be registered/enqueued via wp_enqueue_style() |  |
| 1332 | 18 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 1332 | 49 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 1349 | 18 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 1349 | 49 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `readme.txt`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | outdated_tested_upto_header | Tested up to: 6.7 < 6.9. The "Tested up to" value in your plugin is not set to the current version of WordPress. This means your plugin will not show up in searches, as we require plugins to be compatible and documented as tested up to the most recent version of WordPress. | [Docs](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/#readme-header-information) |
| 0 | 0 | WARNING | readme_parser_warnings_trimmed_short_description | The "Short Description" section is too long and was truncated. A maximum of 150 characters is supported. |  |

## `templates/admin/consent-logs.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 16 | 35 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 16 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;date_from&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 16 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;date_from&#039;] |  |
| 24 | 35 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 24 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;date_to&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 24 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;date_to&#039;] |  |
| 32 | 45 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 32 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;action&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 32 | 45 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;action&#039;] |  |
| 33 | 48 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 33 | 48 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;action&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 33 | 48 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;action&#039;] |  |
| 34 | 46 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 34 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;action&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 34 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;action&#039;] |  |
| 35 | 46 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 35 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;action&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 35 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;action&#039;] |  |
| 36 | 46 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 36 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;action&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 36 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;action&#039;] |  |
| 45 | 49 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 45 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;purpose&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 45 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;purpose&#039;] |  |
| 46 | 49 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 46 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;purpose&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 46 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;purpose&#039;] |  |
| 47 | 49 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 47 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;purpose&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 47 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;purpose&#039;] |  |
| 48 | 51 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 48 | 51 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;purpose&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 48 | 51 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;purpose&#039;] |  |
| 58 | 35 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 58 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;user_id&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 58 | 35 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;user_id&#039;] |  |

## `includes/Admin/AccessibilityMainPage.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 96 | 31 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 96 | 62 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `includes/Admin/ComplianceMainPage.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 104 | 25 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 104 | 56 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 396 | 31 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 396 | 62 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `includes/Admin/Document_Editor_Controller.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 57 | 22 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 57 | 46 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `includes/Admin/Document_Hub_Controller.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 792 | 66 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;doc_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 816 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 826 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 854 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;doc_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 855 | 74 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;overrides&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 855 | 74 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;overrides&#039;] |  |
| 897 | 71 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;doc_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 898 | 78 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;overrides&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 898 | 78 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;overrides&#039;] |  |
| 899 | 76 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;change_reason&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 930 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 940 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 976 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 976 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 979 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at FROM {$table}\n |  |
| 1031 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1031 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1033 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$versions_table} at &quot;SELECT content FROM {$versions_table} WHERE id = %d AND doc_id = %d&quot; |  |
| 1044 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1044 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1046 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$docs_table} at &quot;SELECT title, type, locale FROM {$docs_table} WHERE id = %d&quot; |  |
| 1112 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1112 | 16 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1114 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$docs_table} at &quot;SELECT title, content, status, version FROM {$docs_table} WHERE id = %d&quot; |  |
| 1177 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1177 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1179 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$versions_table} at &quot;SELECT id, content, change_reason, created_at FROM {$versions_table} WHERE id = %d&quot; |  |
| 1184 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1184 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1186 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$versions_table} at &quot;SELECT id, content, change_reason, created_at FROM {$versions_table} WHERE id = %d&quot; |  |
| 1258 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1258 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1259 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$docs_table} at &quot;SELECT COUNT(*) FROM {$docs_table} WHERE status = &#039;draft&#039;&quot; |  |
| 1272 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 1272 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 1314 | 62 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;title&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 1315 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;content&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `includes/Admin/DSR_Settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `includes/Admin/DSRMainPage.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 100 | 31 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 100 | 62 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `includes/Admin/DSRRequestDetail.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 107 | 24 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 107 | 56 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `includes/Admin/DSRRequests.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |
| 352 | 20 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 352 | 41 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 352 | 102 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 353 | 20 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 353 | 47 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 353 | 114 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 397 | 25 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 398 | 68 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 398 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;tab&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 398 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;tab&#039;] |  |
| 405 | 35 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 405 | 65 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 405 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;search&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 405 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;search&#039;] |  |
| 454 | 88 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 454 | 88 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;tab&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 454 | 88 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_GET[&#039;tab&#039;] |  |

## `includes/Admin/MenuManager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 396 | 18 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 396 | 57 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 396 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;page&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `includes/Admin/ModuleDashboard.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 325 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 325 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 325 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 333 | 25 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 333 | 67 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 333 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;module&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 335 | 18 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 335 | 18 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;enabled&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 335 | 18 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;enabled&#039;] |  |
| 368 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 368 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 368 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 376 | 25 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 376 | 67 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 376 | 67 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;module&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 399 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 399 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 399 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 407 | 21 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 407 | 68 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 407 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;action_type&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 408 | 21 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 408 | 51 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 408 | 51 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;modules&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 408 | 51 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;modules&#039;] |  |

## `includes/Admin/Onboarding.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 104 | 18 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 104 | 57 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 104 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;page&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 267 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 267 | 64 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 267 | 64 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 267 | 64 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 285 | 22 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 285 | 65 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 285 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;purpose&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 286 | 22 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 286 | 55 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 287 | 40 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 287 | 40 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;modules&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 289 | 22 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 289 | 56 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 290 | 6 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 290 | 6 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;settings&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 290 | 6 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;settings&#039;] |  |
| 344 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 344 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 344 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 350 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 350 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 352 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SELECT COUNT(*) FROM $table WHERE module_key = %s&quot; |  |
| 359 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 359 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 371 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 419 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 419 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 419 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 431 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 438 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 438 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |
| 455 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 455 | 64 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 455 | 64 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 455 | 64 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |

## `includes/Admin/Settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 87 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 95 | 24 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 95 | 62 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 95 | 62 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;tab&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 120 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 314 | 72 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;shahi_settings_nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 314 | 72 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;shahi_settings_nonce&#039;] |  |
| 339 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;active_tab&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 348 | 62 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;geolocation_provider&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 357 | 81 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;geolocation_override_region&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 364 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_template&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 369 | 65 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_position&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 374 | 62 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_theme&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 379 | 69 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_primary_color&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 382 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_accept_color&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 385 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_reject_color&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 393 | 73 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;banner_region_scope&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 405 | 64 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_heading&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 408 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_banner_message&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 411 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_accept_button_text&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 414 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_reject_button_text&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 417 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_settings_button_text&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 420 | 69 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;consent_privacy_policy_text&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 423 | 52 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;privacy_policy_url&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 426 | 48 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;learn_more_url&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 432 | 66 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;cookie_scanner_frequency&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 458 | 60 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;slos_export_format&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 464 | 63 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;slos_export_frequency&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 473 | 53 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;plugin_name&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 491 | 55 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;notification_email&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 512 | 58 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;ip_blacklist&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 523 | 49 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;api_key&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 544 | 53 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;license_key&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 591 | 17 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 591 | 55 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 591 | 55 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;tab&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 600 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 607 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 607 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |
| 701 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 701 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 701 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 727 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 727 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 727 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 736 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 740 | 25 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 740 | 25 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;settings&#039;] |  |
| 758 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 758 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 758 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 785 | 34 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 785 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 785 | 34 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |
| 808 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 808 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Ajax/AjaxHandler.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 140 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;nonce&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 140 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;nonce&#039;] |  |

## `includes/Ajax/AnalyticsAjax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 56 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 56 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 56 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 61 | 20 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 61 | 62 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 61 | 62 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;period&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 78 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 78 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 79 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SELECT COUNT(*) FROM $analytics_table WHERE 1=1 $date_condition&quot; |  |
| 79 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at &quot;SELECT COUNT(*) FROM $analytics_table WHERE 1=1 $date_condition&quot; |  |
| 83 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 83 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 85 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at FROM $analytics_table \n |  |
| 86 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at WHERE 1=1 $date_condition \n |  |
| 94 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 94 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 95 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SELECT COUNT(DISTINCT user_id) FROM $analytics_table WHERE user_id &gt; 0 $date_condition&quot; |  |
| 95 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $date_condition at &quot;SELECT COUNT(DISTINCT user_id) FROM $analytics_table WHERE user_id &gt; 0 $date_condition&quot; |  |
| 99 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 99 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 101 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at FROM $analytics_table \n |  |
| 133 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 133 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 133 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 138 | 24 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 138 | 70 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 138 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;start_date&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 139 | 24 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 139 | 68 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 139 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;end_date&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 152 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 152 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 153 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM $analytics_table WHERE $where_clause ORDER BY created_at DESC LIMIT 10000&quot; |  |
| 153 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $where_clause at &quot;SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM $analytics_table WHERE $where_clause ORDER BY created_at DESC LIMIT 10000&quot; |  |

## `includes/Ajax/ModuleAjax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 53 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 57 | 30 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 58 | 23 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 58 | 57 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 58 | 57 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;enabled&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 91 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 95 | 30 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 96 | 23 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 96 | 46 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 96 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;settings&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 96 | 46 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;settings&#039;] |  |
| 131 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 131 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 131 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 142 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 148 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 148 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 149 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 149 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |

## `includes/Ajax/OnboardingAjax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 53 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 57 | 19 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 58 | 18 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 58 | 37 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 58 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;data&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 58 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;data&#039;] |  |
| 90 | 18 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 90 | 37 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 90 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;data&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 90 | 37 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;data&#039;] |  |
| 124 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 124 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 124 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 135 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 141 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 141 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 142 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 142 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |

## `includes/Ajax/SettingsAjax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 53 | 17 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 57 | 15 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 57 | 15 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;settings&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 57 | 15 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_POST[&#039;settings&#039;] |  |
| 90 | 22 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 90 | 59 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 174 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 174 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 174 | 24 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $analytics_table at &quot;SHOW TABLES LIKE &#039;$analytics_table&#039;&quot; |  |
| 185 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 191 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 191 | 21 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 192 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 192 | 68 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |

## `includes/Core/Cron.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 174 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 185 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 258 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 268 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Database/MigrationManager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 96 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 173 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 182 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 264 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 301 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Database/Migrations/migration_2025_12_20_consent_logs_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 75 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 75 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 75 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 75 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_consent_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 71 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 71 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 71 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 71 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_dsr_logs_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 72 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 72 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 72 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_dsr_requests_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 74 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 74 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 74 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 74 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_form_issues_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 70 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 70 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 70 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 70 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_form_submissions_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 71 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 71 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 71 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 71 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_trackers_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 72 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 72 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 72 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_20_vendors_table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 72 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 72 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 72 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table_name at &quot;DROP TABLE IF EXISTS $table_name&quot; |  |
| 72 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_22_add_geo_rule_to_consent.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 42 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 42 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 54 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 54 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 54 | 43 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;SHOW COLUMNS FROM {$table_name}&quot; |  |
| 66 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 66 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 67 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD COLUMN geo_rule_id INT UNSIGNED NULL AFTER ip_hash&quot; |  |
| 67 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 76 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 76 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 77 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD COLUMN country_code VARCHAR(10) NULL AFTER geo_rule_id&quot; |  |
| 77 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 86 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 86 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 87 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD COLUMN region VARCHAR(20) NULL AFTER country_code&quot; |  |
| 87 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 95 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 95 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 95 | 43 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;SHOW INDEX FROM {$table_name}&quot; |  |
| 104 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 104 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 104 | 18 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD INDEX idx_geo_rule_id (geo_rule_id)&quot; |  |
| 104 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 108 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 108 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 108 | 18 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD INDEX idx_country_code (country_code)&quot; |  |
| 108 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 112 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 112 | 13 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 112 | 18 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD INDEX idx_region (region)&quot; |  |
| 112 | 27 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 130 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 130 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 142 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 142 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 142 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP INDEX IF EXISTS idx_region&quot; |  |
| 142 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 143 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 143 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 143 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP INDEX IF EXISTS idx_country_code&quot; |  |
| 143 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 144 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 144 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 144 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP INDEX IF EXISTS idx_geo_rule_id&quot; |  |
| 144 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 147 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 147 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 147 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP COLUMN IF EXISTS region&quot; |  |
| 147 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 148 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 148 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 148 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP COLUMN IF EXISTS country_code&quot; |  |
| 148 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 149 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 149 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 149 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP COLUMN IF EXISTS geo_rule_id&quot; |  |
| 149 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/Database/Migrations/migration_2025_12_30_add_version_columns_to_consent.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 42 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 42 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 54 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 54 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 54 | 43 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;SHOW COLUMNS FROM {$table_name}&quot; |  |
| 66 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 66 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 67 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD COLUMN banner_version VARCHAR(50) NULL AFTER metadata&quot; |  |
| 67 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 76 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 76 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 77 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} ADD COLUMN policy_version VARCHAR(50) NULL AFTER banner_version&quot; |  |
| 77 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 88 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 108 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 108 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 120 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 120 | 29 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 120 | 43 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;SHOW COLUMNS FROM {$table_name}&quot; |  |
| 132 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 132 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 133 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP COLUMN policy_version&quot; |  |
| 133 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 142 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 142 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 143 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;ALTER TABLE {$table_name} DROP COLUMN banner_version&quot; |  |
| 143 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 153 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Database/Migrations/Migration_Company_Profile.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 69 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 69 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 92 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 92 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 92 | 17 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_name} at &quot;DROP TABLE IF EXISTS {$table_name}&quot; |  |
| 111 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Database/Migrations/Runner.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 140 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 140 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 140 | 25 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |
| 171 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 171 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 171 | 25 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable $table at &quot;SHOW TABLES LIKE &#039;$table&#039;&quot; |  |

## `includes/Database/Repositories/Company_Profile_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 97 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} ORDER BY id DESC LIMIT 1&quot; |  |
| 129 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at \t\t\t FROM {$this-&gt;table} ORDER BY id DESC LIMIT 1&quot; |  |

## `includes/Database/Repositories/DSR_Audit_Log_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 87 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 131 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 131 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 132 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where} {$order_by} {$limit}&quot; |  |
| 132 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$where} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where} {$order_by} {$limit}&quot; |  |
| 132 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$order_by} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where} {$order_by} {$limit}&quot; |  |
| 132 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$limit} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where} {$order_by} {$limit}&quot; |  |
| 210 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 210 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 214 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 214 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 215 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where_clause} {$order_by} {$limit_clause}&quot; |  |
| 215 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$where_clause} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where_clause} {$order_by} {$limit_clause}&quot; |  |
| 215 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$order_by} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where_clause} {$order_by} {$limit_clause}&quot; |  |
| 215 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$limit_clause} at &quot;SELECT * FROM {$this-&gt;table} WHERE {$where_clause} {$order_by} {$limit_clause}&quot; |  |
| 254 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 254 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 267 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 267 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 287 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 287 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 289 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at &quot;SELECT * FROM {$this-&gt;table} ORDER BY created_at DESC LIMIT %d&quot; |  |
| 321 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 321 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 324 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table} at \t\t\t\tFROM {$this-&gt;table} \n |  |

## `includes/Helpers/Locale_Helper.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 183 | 15 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 184 | 47 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/FixEngine.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 225 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Logger.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 117 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Modules/AccessibilityScanner/Scanner/ScannerEngine.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 204 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Modules/AccessibilityScanner/Widget/AccessibilityWidget.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 24 | 21 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 24 | 62 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 24 | 62 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;event&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 25 | 21 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 25 | 64 | WARNING | WordPress.Security.NonceVerification.Missing | Processing form data without nonce verification. |  |
| 25 | 64 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;details&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `includes/Modules/DSR_Portal/DSR_Portal.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 296 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 307 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Services/Base_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 78 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 349 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_CLIENT_IP&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 349 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_CLIENT_IP&#039;] |  |
| 351 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_X_FORWARDED_FOR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 351 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_X_FORWARDED_FOR&#039;] |  |
| 353 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;REMOTE_ADDR&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 353 | 10 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;REMOTE_ADDR&#039;] |  |
| 366 | 70 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |

## `includes/Services/Consent_Audit_Logger.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 112 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 139 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 237 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 237 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 315 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 315 | 22 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 329 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table_name} at &quot;SELECT * FROM {$this-&gt;table_name} WHERE id = %d&quot; |  |
| 334 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 334 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 371 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table_name} at &quot;DELETE FROM {$this-&gt;table_name} WHERE created_at &lt; DATE_SUB(NOW(), INTERVAL %d DAY)&quot; |  |
| 376 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 376 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 397 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$this-&gt;table_name} at &quot;SELECT * FROM {$this-&gt;table_name} WHERE consent_id = %d ORDER BY created_at DESC&quot; |  |
| 402 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 402 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 483 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 483 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Services/Document_Generator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 322 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 323 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 324 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 343 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 436 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 436 | 48 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_print_r | print_r() found. Debug code should not normally be used in production. |  |
| 440 | 3 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 440 | 46 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_var_export | var_export() found. Debug code should not normally be used in production. |  |
| 444 | 4 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Services/Document_Hub_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 365 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 365 | 28 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 366 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$docs_table} at &quot;SELECT COUNT(*) FROM {$docs_table} WHERE status != &#039;not_generated&#039;&quot; |  |
| 374 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 374 | 23 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 375 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$docs_table} at &quot;SELECT COUNT(*) FROM {$docs_table} WHERE status = &#039;published&#039;&quot; |  |
| 408 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 408 | 21 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 411 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$docs_table} at FROM {$docs_table} \n |  |

## `includes/Services/DSR_Audit_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 70 | 25 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[&#039;HTTP_USER_AGENT&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 70 | 25 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[&#039;HTTP_USER_AGENT&#039;] |  |
| 475 | 11 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_SERVER[$key] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 475 | 11 | WARNING | WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | Detected usage of a non-sanitized input variable: $_SERVER[$key] |  |

## `includes/Services/DSR_Erasure_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 183 | 5 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |
| 396 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 396 | 14 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 425 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 425 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 441 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 441 | 17 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 499 | 17 | WARNING | WordPress.DB.SlowDBQuery.slow_db_query_meta_key | Detected usage of meta_key, possible slow query. |  |
| 500 | 17 | WARNING | WordPress.DB.SlowDBQuery.slow_db_query_meta_value | Detected usage of meta_value, possible slow query. |  |

## `includes/Services/Placeholder_Mapper.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 639 | 6 | WARNING | WordPress.PHP.DevelopmentFunctions.error_log_error_log | error_log() found. Debug code should not normally be used in production. |  |

## `includes/Services/Script_Blocker_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `includes/Shortcodes/Consent_Preferences_Shortcode.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `includes/Shortcodes/DSR_Verify_Shortcode.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 71 | 19 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 71 | 71 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |

## `templates/admin/accessibility-dashboard.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `templates/admin/accessibility-pages-attention.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `templates/admin/onboarding-modal.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `templates/admin/support.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `templates/admin/compliance/main.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | Internal.LineEndings.Mixed | File has mixed line endings; this may cause incorrect results |  |

## `uninstall.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 54 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 54 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 68 | 16 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_analytics} at &quot;TRUNCATE TABLE {$table_analytics}&quot; |  |
| 93 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 93 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 108 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 108 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 108 | 16 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_analytics} at &quot;DROP TABLE IF EXISTS {$table_analytics}&quot; |  |
| 108 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 109 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 109 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 109 | 16 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_modules} at &quot;DROP TABLE IF EXISTS {$table_modules}&quot; |  |
| 109 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |
| 110 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 110 | 5 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 110 | 16 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table_onboarding} at &quot;DROP TABLE IF EXISTS {$table_onboarding}&quot; |  |
| 110 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.SchemaChange | Attempting a database schema change is discouraged. |  |

## `includes/API/SystemController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 94 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 94 | 18 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `includes/Core/Deactivator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 58 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 58 | 9 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

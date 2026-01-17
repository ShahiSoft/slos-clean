# Plugin Check Report

**Plugin:** Shahi LegalFlowSuite
**Generated at:** 2026-01-17 11:09:16


## `templates/admin/documents/hub.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 145 | 21 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to _n() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `templates/admin/modules.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 91 | 33 | ERROR | WordPress.WP.I18n.MissingTranslatorsComment | A function call to esc_html__() with texts containing placeholders was found, but was not accompanied by a "translators:" comment on the line above to clarify the meaning of the placeholders. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#descriptions) |

## `includes/PostTypes/Metaboxes.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 263 | 93 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$checked'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 272 | 126 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$checked'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Services/PDF_Generator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 318 | 15 | ERROR | WordPress.WP.AlternativeFunctions.strip_tags_strip_tags | strip_tags() is discouraged. Use the more comprehensive wp_strip_all_tags() instead. |  |
| 635 | 14 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$pdf_data'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 649 | 14 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

## `templates/admin/accessibility-settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 31 | 37 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'admin_url'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/compliance/tabs/geo-rules.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 876 | 58 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$icon'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/dashboard.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 244 | 89 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$setup_progress'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/documents/partials/profile-banner.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 88 | 21 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$completeness'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |
| 102 | 21 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found '$completeness'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `templates/admin/settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 595 | 112 | ERROR | WordPress.Security.EscapeOutput.OutputNotEscaped | All output should be run through an escaping function (see the Security sections in the WordPress Developer Handbooks), found 'ShahiLegalFlowSuite'. | [Docs](https://developer.wordpress.org/apis/security/escaping/#escaping-functions) |

## `includes/Admin/Modules.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 201 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 275 | 6 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `includes/Ajax/Compliance_Export_Ajax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 160 | 35 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 170 | 13 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fopen | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fopen(). |  |

## `includes/Ajax/DashboardAjax.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 69 | 60 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 72 | 22 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 89 | 1 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `includes/API/AnalyticsController.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 164 | 26 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $date_condition used in $wpdb->get_var($wpdb->prepare(\n\t\t\t\t"SELECT COUNT(*) FROM %i WHERE 1=1 $date_condition",\n\t\t\t\t$analytics_table\n\t\t\t))\n$date_condition assigned unsafely at line 160:\n $date_condition = ''\n$date_condition assigned unsafely at line 148:\n $date_condition = ''\n$total_events assigned unsafely at line 164:\n $total_events = $wpdb->get_var(\n\t\t\t$wpdb->prepare(\n\t\t\t\t"SELECT COUNT(*) FROM %i WHERE 1=1 $date_condition",\n\t\t\t\t$analytics_table\n\t\t\t)\n\t\t)\n$analytics_table assigned unsafely at line 140:\n $analytics_table = $wpdb->prefix . 'shahi_analytics'\n$period assigned unsafely at line 139:\n $period = $request->get_param( 'period' )\n$request used without escaping. |  |
| 166 | 5 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 240 | 34 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 242 | 35 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |
| 244 | 19 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $total_query used in $wpdb->get_var($total_query)\n$total_query assigned unsafely at line 242:\n $total_query = $wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE $where_clause", array_merge( array( $analytics_table ), $values ) )\n$where_clause assigned unsafely at line 237:\n $where_clause = implode( ' AND ', $where )\n$where assigned unsafely at line 233:\n $where[] = 'event_type = %s'\n$values[] used without escaping. |  |
| 244 | 28 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $total_query |  |
| 248 | 4 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `includes/API/Config_REST_Controller.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 439 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_readfile | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: readfile(). |  |

## `includes/Database/DatabaseHelper.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 52 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |
| 69 | 24 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_var($sql)\n$sql assigned unsafely at line 68:\n $sql = "SELECT COUNT(*) FROM {$table_name}"\n$table_name assigned unsafely at line 65:\n $table_name = self::get_table_name( $table ) |  |
| 69 | 33 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 75 | 23 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_var($sql)\n$sql assigned unsafely at line 73:\n $sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}"\n$table_name assigned unsafely at line 65:\n $table_name = self::get_table_name( $table )\n$where_clause assigned unsafely at line 72:\n $where_clause = self::build_where_clause( $where ) |  |
| 75 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 155 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 157 | 17 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_row($sql)\n$sql assigned unsafely at line 155:\n $sql = $wpdb->prepare( $sql, 1 )\n$sql assigned unsafely at line 152:\n $sql = "SELECT {$select_clause} FROM {$table_name} WHERE {$where_clause} LIMIT %d"\n$select_clause assigned unsafely at line 149:\n $select_clause = ! empty( $columns ) ? implode( ', ', $columns ) : '*'\n$table_name assigned unsafely at line 146:\n $table_name = self::get_table_name( $table )\n$where_clause assigned unsafely at line 147:\n $where_clause = self::build_where_clause( $where )\n$columns used without escaping. |  |
| 157 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 211 | 27 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 216 | 17 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_results($sql)\n$sql assigned unsafely at line 211:\n $sql = $wpdb->prepare( $sql, ...$prepare_values )\n$sql assigned unsafely at line 195:\n $sql .= " ORDER BY {$orderby} {$order}"\n$orderby assigned unsafely at line 194:\n $orderby = sanitize_key( $args['order_by'] )\nNote: sanitize_key() is not a safe escaping function.\n$order assigned unsafely at line 193:\n $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC'\n$args['order_by'] used without escaping. |  |
| 216 | 30 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 308 | 17 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_results($sql)\n$sql assigned unsafely at line 297:\n $sql = $wpdb->prepare(\n\t\t\t\t"SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM {$table_name} \n WHERE created_at BETWEEN %s AND %s \n ORDER BY created_at DESC",\n\t\t\t\t$start_date,\n\t\t\t\t$end_date\n\t\t\t)\n$table_name assigned unsafely at line 284:\n $table_name = self::get_table_name( 'analytics' ) |  |
| 308 | 30 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 432 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

## `includes/Database/QueryOptimizer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 46 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 47 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 72 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 73 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 107 | 25 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 108 | 25 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 109 | 25 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 133 | 39 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 133 | 71 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 146 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 151 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 156 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 161 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 166 | 17 | ERROR | WordPress.WP.AlternativeFunctions.rand_rand | rand() is discouraged. Use the far less predictable wp_rand() instead. |  |
| 172 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 173 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 220 | 37 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 220 | 69 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 234 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 235 | 21 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 274 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 275 | 17 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

## `includes/Database/Repositories/Consent_Repository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 84 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 84 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 84 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 86 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $user_id |  |
| 101 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 101 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 101 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 103 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $user_id |  |
| 104 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $type |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Fixers/VideoAccessibilityFixer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 182 | 14 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 208 | 26 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |
| 220 | 26 | ERROR | WordPress.WP.AlternativeFunctions.parse_url_parse_url | parse_url() is discouraged because of inconsistency in the output across PHP versions; use wp_parse_url() instead. |  |

## `includes/Modules/AccessibilityScanner/FixEngine/FixHistoryRepository.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 140 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 140 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 140 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 142 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $post_id |  |
| 143 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $limit |  |
| 157 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 157 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 157 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 159 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $session_id |  |
| 174 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 174 | 12 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 174 | 18 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 183 | 6 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $post_id |  |
| 219 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 219 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 219 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 226 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $limit |  |
| 240 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 240 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 240 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 242 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $days |  |
| 256 | 4 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 256 | 11 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found wpdb |  |
| 256 | 17 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found prepare |  |
| 258 | 5 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $this |  |
| 258 | 12 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found table_name |  |

## `includes/Modules/AccessibilityScanner/FixEngine/Migrations/IdCanonicalizationMigration.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 202 | 21 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter _escape( $table ) ) ) used in $wpdb->get_results(sprintf( 'SELECT id, fixer_id FROM %s', $wpdb->_escape( $table ) )) |  |
| 202 | 90 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |

## `includes/Modules/AccessibilityScanner/Reporting/AccessibilityReporter.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 209 | 41 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 254 | 59 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 361 | 71 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 557 | 18 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 568 | 41 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 576 | 55 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |
| 591 | 42 | ERROR | WordPress.DateTime.RestrictedFunctions.date_date | date() is affected by runtime timezone changes which can cause date/time to be incorrectly displayed. Use gmdate() instead. |  |

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

## `includes/Modules/Module.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 332 | 44 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |
| 406 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |

## `includes/Modules/ModuleManager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 326 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $table |  |

## `includes/Services/Compliance_Score_Calculator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 787 | 55 | ERROR | WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder | The %i modifier is only supported in WP 6.2 or higher. Found: "%i". |  |

## `includes/Services/Consent_Export_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 112 | 9 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fopen | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fopen(). |  |
| 154 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 368 | 9 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fopen | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fopen(). |  |
| 380 | 4 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |
| 402 | 3 | ERROR | WordPress.WP.AlternativeFunctions.file_system_operations_fclose | File operations should use WP_Filesystem methods instead of direct PHP filesystem calls. Found: fclose(). |  |

## `includes/Services/Consent_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 495 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 497 | 17 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_results($sql)\n$sql assigned unsafely at line 495:\n $sql = $wpdb->prepare( $sql, ...$values )\n$sql assigned unsafely at line 485:\n $sql = "SELECT c.*, u.display_name as user_name, u.user_email \n\t\t\t\tFROM {$table} c \n\t\t\t\tLEFT JOIN {$wpdb->users} u ON c.user_id = u.ID \n\t\t\t\tWHERE {$where_sql} \n\t\t\t\tORDER BY c.{$order_by} {$order} \n\t\t\t\tLIMIT %d OFFSET %d"\n$table assigned unsafely at line 426:\n $table = $wpdb->prefix . 'slos_consent'\n$where_sql assigned unsafely at line 473:\n $where_sql = implode( ' AND ', $where )\n$order_by assigned unsafely at line 480:\n $order_by = in_array( $pagination['order_by'] ?? '', array( 'id', 'type', 'status', 'created_at', 'region', 'country_code' ) )\n\t\t\t? $pagination['order_by']\n\t\t\t: 'created_at'\n$order assigned unsafely at line 483:\n $order = strtoupper( $pagination['order'] ?? 'DESC' ) === 'ASC' ? 'ASC' : 'DESC'\n$where assigned unsafely at line 469:\n $where[] = 'country_code = %s'\n$pagination['order_by'] used without escaping.\n$filters['country_code'] used without escaping. |  |
| 497 | 30 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 561 | 26 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |
| 563 | 23 | ERROR | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $sql used in $wpdb->get_var($sql)\n$sql assigned unsafely at line 561:\n $sql = $wpdb->prepare( $sql, ...$values )\n$sql assigned unsafely at line 559:\n $sql = "SELECT COUNT(*) FROM {$table}" . ( ! empty( $where_sql ) ? " WHERE {$where_sql}" : '' )\n$where_sql assigned unsafely at line 557:\n $where_sql = implode( ' AND ', $where )\n$where assigned unsafely at line 553:\n $where[] = 'country_code = %s'\n$filters['country_code'] used without escaping. |  |
| 563 | 32 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $sql |  |

## `includes/Services/DSR_Service.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 753 | 20 | ERROR | WordPress.DB.PreparedSQL.NotPrepared | Use placeholders and $wpdb->prepare(); found $query |  |

## `shahi-legalflowsuite.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 72 | 3 | ERROR | PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound | load_plugin_textdomain() has been discouraged since WordPress version 4.6. When your plugin is hosted on WordPress.org, you no longer need to manually include this function call for translations under your plugin slug. WordPress will automatically load the translations for you as needed. | [Docs](https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/) |

## `readme.txt`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | no_plugin_readme | The plugin readme.txt does not exist. |  |

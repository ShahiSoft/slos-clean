# Legal Docs Integration Reference

_Reference for: IMPLEMENTATION-PLAN.md → Phase 1.1_
_Replaces: LEGAL-DOCS-REFERENCE.md (which assumed building from scratch)_

---

## Context: Existing LegalDocs Module

The plugin already has a comprehensive Legal Documents module:

| Component | Location | Purpose |
|-----------|----------|---------|
| `LegalDocuments` | `Modules/LegalDocs/LegalDocuments.php` | Module registration and menu |
| `Document_Hub_Controller` | `Admin/Document_Hub_Controller.php` | Admin UI, AJAX handlers for generate/publish/export |
| `Document_Generator` | `Services/Document_Generator.php` | Core generation logic |
| `Document_Hub_Service` | `Services/Document_Hub_Service.php` | Business logic for document cards, categories, stats |
| `Template_Manager` | `Services/Template_Manager.php` | Template loading and placeholder handling |
| `Export_Manager` | `Services/Export_Manager.php` | PDF/DOCX export |
| `Company_Profile_Repository` | `Database/Repositories/` | Company data for templates |
| `Legal_Doc_Repository` | `Database/Repositories/` | Document storage |

**Admin URL:** `admin.php?page=slos-documents`

---

## Integration Goals

Instead of creating a parallel "Legal Docs" tab inside Compliance, we will:

1. **Surface document status** in the Compliance dashboard (no duplicate UI).
2. **Bind cookie scanner data** to existing document templates.
3. **Cross-link** between Compliance and Document Hub.
4. **Trigger staleness flags** when underlying data (cookies, profile) changes.

---

## 1. Cookie Data Binding to Templates

### Current Placeholder System

`Document_Generator` and `Template_Manager` use placeholders like `{{company_name}}`, `{{effective_date}}`, etc., populated from `Company_Profile_Repository`.

### New Placeholders for Cookie Data

> **Note:** Cookie data is stored in `slos_cookie_inventory` option (per `Cookie_Scanner_Service.php`), not `slos_detected_cookies`.

| Placeholder | Description | Source |
|-------------|-------------|--------|
| `{{cookie_table_all}}` | Full table of all detected cookies | `slos_cookie_inventory` option |
| `{{cookie_table_necessary}}` | Table of necessary cookies only | Filtered by category |
| `{{cookie_table_analytics}}` | Table of analytics cookies only | Filtered by category |
| `{{cookie_table_marketing}}` | Table of marketing cookies only | Filtered by category |
| `{{cookie_table_functional}}` | Table of functional cookies only | Filtered by category |
| `{{cookie_count}}` | Total number of detected cookies | Count |
| `{{cookie_categories}}` | Comma-separated list of categories with cookies | Derived |
| `{{last_cookie_scan_date}}` | Date of last scan | `slos_cookie_scan_time` option |

### Implementation in `Placeholder_Mapper.php`

> **Note:** The existing `Placeholder_Mapper.php` already handles company profile placeholders and has cookie fields from the profile (`essential_cookies`, `analytics_cookies`, etc.). The new `get_cookie_placeholders()` method will pull **scanner-detected** cookies from `slos_cookie_inventory` for real-time accuracy.

```php
// In Placeholder_Mapper.php - NEW method to add

public function get_cookie_placeholders(): array {
    // Use the correct option key per Cookie_Scanner_Service
    $cookies = get_option( 'slos_cookie_inventory', [] );
    $scan_time = get_option( 'slos_cookie_scan_time', null );

    $by_category = [];
    foreach ( $cookies as $cookie ) {
        $cat = $cookie['category'] ?? 'unknown';
        $by_category[ $cat ][] = $cookie;
    }

    return [
        'cookie_table_all'       => $this->render_cookie_table( $cookies ),
        'cookie_table_necessary' => $this->render_cookie_table( $by_category['necessary'] ?? [] ),
        'cookie_table_analytics' => $this->render_cookie_table( $by_category['analytics'] ?? [] ),
        'cookie_table_marketing' => $this->render_cookie_table( $by_category['marketing'] ?? [] ),
        'cookie_table_functional'=> $this->render_cookie_table( $by_category['functional'] ?? [] ),
        'cookie_count'           => count( $cookies ),
        'cookie_categories'      => implode( ', ', array_keys( $by_category ) ),
        'last_cookie_scan_date'  => $scan_time ? date_i18n( get_option('date_format'), strtotime( $scan_time ) ) : __( 'Never', 'shahi-legalflowsuite' ),
    ];
}

private function render_cookie_table( array $cookies ): string {
    if ( empty( $cookies ) ) {
        return '<p>' . __( 'No cookies in this category.', 'shahi-legalflowsuite' ) . '</p>';
    }

    $html = '<table class="slos-cookie-table"><thead><tr>';
    $html .= '<th>' . __( 'Name', 'shahi-legalflowsuite' ) . '</th>';
    $html .= '<th>' . __( 'Provider', 'shahi-legalflowsuite' ) . '</th>';
    $html .= '<th>' . __( 'Purpose', 'shahi-legalflowsuite' ) . '</th>';
    $html .= '<th>' . __( 'Duration', 'shahi-legalflowsuite' ) . '</th>';
    $html .= '</tr></thead><tbody>';

    foreach ( $cookies as $c ) {
        $html .= '<tr>';
        $html .= '<td>' . esc_html( $c['name'] ?? '' ) . '</td>';
        $html .= '<td>' . esc_html( $c['domain'] ?? $c['provider'] ?? '' ) . '</td>';
        $html .= '<td>' . esc_html( $c['purpose'] ?? $c['description'] ?? '' ) . '</td>';
        $html .= '<td>' . esc_html( $c['duration'] ?? $c['expiry'] ?? '' ) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    return $html;
}
```

### Registering Placeholders

Extend the existing placeholder registration (likely in `Document_Generator::get_all_placeholders()` or similar):

```php
$placeholders = array_merge(
    $this->get_company_placeholders(),
    $this->get_date_placeholders(),
    $this->get_cookie_placeholders(), // NEW
);
```

---

## 2. Cookie Table Shortcode

For users who want to embed cookie tables in arbitrary pages (not just Document Hub templates).

### Shortcode: `[slos_cookie_table]`

**File:** `includes/Shortcodes/Cookie_Table_Shortcode.php` (new)

**Attributes:**

| Attribute | Default | Description |
|-----------|---------|-------------|
| `category` | `all` | Filter by category: `all`, `necessary`, `analytics`, `marketing`, `functional`, or comma-separated |
| `columns` | `name,provider,purpose,duration` | Columns to show (comma-separated) |
| `class` | `slos-cookie-table` | CSS class for the table |
| `empty_message` | `No cookies found.` | Message when no cookies match |

**Examples:**

```
[slos_cookie_table]
[slos_cookie_table category="analytics,marketing"]
[slos_cookie_table category="necessary" columns="name,purpose"]
```

**Implementation Skeleton:**

```php
namespace ShahiLegalFlowSuite\Shortcodes;

class Cookie_Table_Shortcode {
    public function __construct() {
        add_shortcode( 'slos_cookie_table', [ $this, 'render' ] );
    }

    public function render( $atts ) {
        $atts = shortcode_atts( [
            'category'      => 'all',
            'columns'       => 'name,provider,purpose,duration',
            'class'         => 'slos-cookie-table',
            'empty_message' => __( 'No cookies found.', 'shahi-legalflowsuite' ),
        ], $atts );

        // Use correct option key per Cookie_Scanner_Service
        $cookies = get_option( 'slos_cookie_inventory', [] );
        
        // Filter by category
        if ( $atts['category'] !== 'all' ) {
            $cats = array_map( 'trim', explode( ',', $atts['category'] ) );
            $cookies = array_filter( $cookies, fn($c) => in_array( $c['category'] ?? '', $cats, true ) );
        }

        if ( empty( $cookies ) ) {
            return '<p class="slos-cookie-empty">' . esc_html( $atts['empty_message'] ) . '</p>';
        }

        // Render table with specified columns
        $columns = array_map( 'trim', explode( ',', $atts['columns'] ) );
        return $this->build_table( $cookies, $columns, $atts['class'] );
    }

    private function build_table( array $cookies, array $columns, string $class ): string {
        // ... table HTML generation
    }
}
```

---

## 3. Compliance Dashboard Integration

### Document Status Card

In `templates/admin/compliance/tabs/dashboard.php`, add a "Legal Documents" card in the Quick Actions or sidebar:

```php
<?php
// Fetch document statuses from Document_Hub_Service
$hub_service = new \ShahiLegalFlowSuite\Services\Document_Hub_Service();
$doc_cards = $hub_service->get_document_cards();

// Filter to key compliance docs
$compliance_docs = array_filter( $doc_cards, fn($d) => in_array( $d['slug'] ?? '', [
    'cookie-policy',
    'privacy-policy',
    'accessibility-statement',
], true ) );
?>

<div class="slos-card">
    <div class="slos-card-header">
        <h3>
            <span class="dashicons dashicons-media-document"></span>
            <?php esc_html_e( 'Legal Documents', 'shahi-legalflowsuite' ); ?>
        </h3>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-documents' ) ); ?>" class="slos-btn-ghost">
            <?php esc_html_e( 'Manage', 'shahi-legalflowsuite' ); ?>
        </a>
    </div>
    <div class="slos-card-body">
        <?php foreach ( $compliance_docs as $doc ) : ?>
            <div class="slos-doc-status-row">
                <span class="slos-doc-name"><?php echo esc_html( $doc['title'] ); ?></span>
                <span class="slos-status-badge <?php echo esc_attr( $doc['status'] ?? 'missing' ); ?>">
                    <?php echo esc_html( ucfirst( $doc['status'] ?? 'missing' ) ); ?>
                </span>
            </div>
        <?php endforeach; ?>
        <?php if ( empty( $compliance_docs ) ) : ?>
            <p class="slos-empty-hint"><?php esc_html_e( 'No compliance documents configured.', 'shahi-legalflowsuite' ); ?></p>
        <?php endif; ?>
    </div>
</div>
```

### Readiness Score Dimension

In `Compliance_Score_Calculator`, the `LEGAL_DOCS` dimension should query existing doc status:

```php
private function calculate_legal_docs_score(): int {
    $hub_service = new Document_Hub_Service();
    $cards = $hub_service->get_document_cards();

    $required = [ 'cookie-policy', 'privacy-policy', 'accessibility-statement' ];
    $published = 0;

    foreach ( $cards as $card ) {
        if ( in_array( $card['slug'], $required, true ) && ( $card['status'] ?? '' ) === 'published' ) {
            $published++;
        }
    }

    return count( $required ) > 0 ? round( ( $published / count( $required ) ) * 100 ) : 100;
}
```

---

## 4. Staleness Detection Hook

When cookie scanner data changes, mark Cookie Policy as needing regeneration.

### Hook into Scanner

```php
// In Cookie_Scanner_Service or wherever scan completes
do_action( 'slos_cookies_updated', $detected_cookies );
```

### Listen in Document_Hub_Service

```php
// In Document_Hub_Service::__construct() or init
add_action( 'slos_cookies_updated', [ $this, 'mark_cookie_policy_stale' ] );

public function mark_cookie_policy_stale( $cookies ) {
    // Find Cookie Policy doc ID
    $doc_id = $this->get_doc_id_by_slug( 'cookie-policy' );
    if ( $doc_id ) {
        update_post_meta( $doc_id, '_slos_needs_regeneration', true );
        update_post_meta( $doc_id, '_slos_stale_reason', 'cookie_data_changed' );
    }
}
```

### Surface Staleness in Dashboard

In the "Legal Documents" card, show a warning badge if `_slos_needs_regeneration` is set:

```php
<?php if ( get_post_meta( $doc['id'], '_slos_needs_regeneration', true ) ) : ?>
    <span class="slos-stale-badge" title="<?php esc_attr_e( 'Data has changed since last generation', 'shahi-legalflowsuite' ); ?>">
        ⚠️ <?php esc_html_e( 'Outdated', 'shahi-legalflowsuite' ); ?>
    </span>
<?php endif; ?>
```

---

## 5. Cross-Links

| From | To | Method |
|------|----|--------|
| Compliance Dashboard → Legal Docs | Document Hub | Link in card header |
| Document Hub → Cookie Scanner | Compliance tab | Link in Cookie Policy card when stale |
| Cookie Scanner tab → Cookie Policy | Document Hub | "Update Cookie Policy" button after scan |

---

## Migration Notes

- No new tables or major migrations required; we reuse existing `Legal_Doc_Repository` and `wp_posts`.
- The only new DB artifact is post meta flags (`_slos_needs_regeneration`, `_slos_stale_reason`).

---

## Files to Modify

| File | Change |
|------|--------|
| `Services/Placeholder_Mapper.php` or `Template_Manager.php` | Add `get_cookie_placeholders()` method |
| `Services/Document_Generator.php` | Merge cookie placeholders into `get_all_placeholders()` |
| `Shortcodes/Cookie_Table_Shortcode.php` | New file for `[slos_cookie_table]` |
| `Admin/ComplianceMainPage.php` | (minor) Expose doc status in stats if not already |
| `templates/admin/compliance/tabs/dashboard.php` | Add Legal Documents card |
| `Services/Compliance_Score_Calculator.php` | Implement `LEGAL_DOCS` dimension using `Document_Hub_Service` |
| `Services/Cookie_Scanner_Service.php` | Fire `slos_cookies_updated` action |
| `Services/Document_Hub_Service.php` | Listen for cookie updates; mark docs stale |

---

_End of Reference_

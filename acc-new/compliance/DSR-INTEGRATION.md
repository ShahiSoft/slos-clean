context # DSR Integration Reference

_Reference for: IMPLEMENTATION-PLAN.md → Phase 2.2_

---

## Context: Existing DSR Module

The plugin already has a comprehensive DSR (Data Subject Request) module:

| Component | Location | Purpose |
|-----------|----------|---------|
| `DSR_Service` | `Services/DSR_Service.php` | Core business logic; all 7 GDPR rights |
| `DSR_Audit_Service` | `Services/DSR_Audit_Service.php` | Audit trail for DSR actions |
| `DSR_Email_Service` | `Services/DSR_Email_Service.php` | Verification and notification emails |
| `DSR_Export_Service` | `Services/DSR_Export_Service.php` | Data export for access/portability |
| `DSR_Erasure_Service` | `Services/DSR_Erasure_Service.php` | Data erasure handling |
| `DSR_Report_Service` | `Services/DSR_Report_Service.php` | Reporting and analytics |
| `DSR_Repository` | `Database/Repositories/DSR_Repository.php` | Data access layer |
| `DSR_Audit_Log_Repository` | `Database/Repositories/DSR_Audit_Log_Repository.php` | Audit log storage |
| `DSRMainPage` | `Admin/DSRMainPage.php` | Tabbed admin UI (Requests, Reports, Settings) |
| `DSRRequestDetail` | `Admin/DSRRequestDetail.php` | Single request detail view |

**Admin URL:** `admin.php?page=slos-dsr`

**Supported Request Types:**
- `access` (Article 15)
- `rectification` (Article 16)
- `erasure` (Article 17)
- `portability` (Article 20)
- `restriction` (Article 18)
- `object` (Article 21)
- `automated_decision` (Article 22)

---

## Integration Goals

Link the **Consent** module with the **DSR** module so that:

1. When processing a DSR, admins can see the requester's **consent history**.
2. DSR **data exports** (access/portability) include consent records.
3. The **Compliance Ops Dashboard** shows DSR metrics alongside consent stats.
4. DSR fulfillment can reference **audit logs** for evidence.

---

## 1. Consent Lookup by Email

### New Method in `Consent_Service`

```php
// In Consent_Service.php

/**
 * Get all consents for a given email address.
 *
 * @param string $email Email to search (will be hashed for comparison if IP hashing is used).
 * @return array Array of consent records.
 */
public function get_consents_by_email( string $email ): array {
    return $this->repository->find_by_email( $email );
}
```

### New Method in `Consent_Repository`

```php
// In Consent_Repository.php

/**
 * Find consent records by email.
 *
 * Since consents may be associated with:
 * - Logged-in users (user_id > 0) → look up by user email
 * - Guests (user_id = 0) → may have email in metadata
 *
 * @param string $email
 * @return array
 */
public function find_by_email( string $email ): array {
    global $wpdb;

    $sanitized_email = sanitize_email( $email );
    if ( empty( $sanitized_email ) ) {
        return [];
    }

    // Find user ID by email (if exists)
    $user = get_user_by( 'email', $sanitized_email );
    $user_id = $user ? $user->ID : 0;

    // Query: match by user_id OR by email in metadata
    $sql = $wpdb->prepare(
        "SELECT * FROM {$this->table_name}
         WHERE user_id = %d
            OR metadata LIKE %s
         ORDER BY created_at DESC",
        $user_id,
        '%' . $wpdb->esc_like( $sanitized_email ) . '%'
    );

    return $wpdb->get_results( $sql, ARRAY_A );
}
```

### Related Audit Logs

Also expose consent audit logs for the same user:

```php
// In Consent_Audit_Logger.php

public function get_logs_by_email( string $email ): array {
    $user = get_user_by( 'email', $email );
    if ( ! $user ) {
        return [];
    }
    return $this->search( [ 'user_id' => $user->ID, 'limit' => 500 ] );
}
```

---

## 2. Consent History in DSR Detail View

### UI Modification

In `DSRRequestDetail.php` or the corresponding template (`templates/admin/dsr/detail.php`), add a collapsible section:

```php
<?php
// Fetch consent history for the requester email
$consent_service = new \ShahiLegalFlowSuite\Services\Consent_Service();
$consents = $consent_service->get_consents_by_email( $request['email'] );
?>

<div class="slos-dsr-section slos-consent-history">
    <h3 class="slos-section-toggle" data-target="consent-history-content">
        <span class="dashicons dashicons-shield"></span>
        <?php esc_html_e( 'Consent History', 'shahi-legalflowsuite' ); ?>
        <span class="slos-badge"><?php echo count( $consents ); ?></span>
        <span class="dashicons dashicons-arrow-down-alt2 toggle-icon"></span>
    </h3>
    
    <div id="consent-history-content" class="slos-section-content" style="display: none;">
        <?php if ( ! empty( $consents ) ) : ?>
            <table class="slos-data-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Date', 'shahi-legalflowsuite' ); ?></th>
                        <th><?php esc_html_e( 'Type', 'shahi-legalflowsuite' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></th>
                        <th><?php esc_html_e( 'Source', 'shahi-legalflowsuite' ); ?></th>
                        <th><?php esc_html_e( 'Region', 'shahi-legalflowsuite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $consents as $consent ) : ?>
                        <tr>
                            <td><?php echo esc_html( date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $consent['created_at'] ) ) ); ?></td>
                            <td><span class="slos-type-badge"><?php echo esc_html( ucfirst( $consent['type'] ) ); ?></span></td>
                            <td><span class="slos-status-badge <?php echo esc_attr( $consent['status'] ); ?>"><?php echo esc_html( ucfirst( $consent['status'] ) ); ?></span></td>
                            <td><?php echo esc_html( $consent['metadata']['source'] ?? 'website' ); ?></td>
                            <td><?php echo esc_html( $consent['country_code'] ?? '-' ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="slos-empty-hint"><?php esc_html_e( 'No consent records found for this email.', 'shahi-legalflowsuite' ); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
// Simple toggle
document.querySelectorAll('.slos-section-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function() {
        var target = document.getElementById(this.dataset.target);
        var icon = this.querySelector('.toggle-icon');
        if (target.style.display === 'none') {
            target.style.display = 'block';
            icon.classList.replace('dashicons-arrow-down-alt2', 'dashicons-arrow-up-alt2');
        } else {
            target.style.display = 'none';
            icon.classList.replace('dashicons-arrow-up-alt2', 'dashicons-arrow-down-alt2');
        }
    });
});
</script>
```

---

## 3. Include Consent Data in DSR Exports

### Existing Implementation

> **Good News:** `DSR_Export_Service.php` already registers a `consent` data provider (lines 75-79) via `register_core_providers()`:
> ```php
> $providers['consent'] = array(
>     'label'    => __( 'Consent Records', 'shahi-legalflowsuite' ),
>     'callback' => array( $this, 'collect_consent_data' ),
>     'priority' => 30,
> );
> ```

### Enhancement: Ensure Comprehensive Consent Collection

The existing `collect_consent_data()` method should be verified to:

1. Pull consent records using `Consent_Repository::find_by_email()`
2. Include consent audit logs via `Consent_Audit_Logger::get_logs_by_email()`

If the existing implementation only does basic collection, enhance it:

```php
// In DSR_Export_Service.php - verify/enhance collect_consent_data()

public function collect_consent_data( string $email ): array {
    $consent_service = new Consent_Service();
    $audit_logger = new Consent_Audit_Logger();
    
    return [
        'consent_records' => $consent_service->get_consents_by_email( $email ),
        'consent_audit_logs' => $audit_logger->get_logs_by_email( $email ),
    ];
}
```

### JSON Export Structure

```json
{
  "user_profile": { ... },
  "comments": [ ... ],
  "consent_records": [
    {
      "id": 123,
      "type": "analytics",
      "status": "accepted",
      "created_at": "2025-12-15 10:30:00",
      "country_code": "DE",
      "metadata": { "source": "banner", "language": "en" }
    },
    ...
  ],
  "consent_audit_logs": [
    {
      "id": 456,
      "action": "grant",
      "purpose": "analytics",
      "created_at": "2025-12-15 10:30:00"
    },
    ...
  ]
}
```

---

## 4. DSR Metrics in Compliance Ops Dashboard

### Add DSR Dimension to Compliance Score (Optional)

If desired, add a `DSR_OPERATIONS` dimension to the readiness score:

```php
// In Compliance_Score_Calculator.php

private function calculate_dsr_operations_score(): int {
    $dsr_service = new DSR_Service();
    
    // Get open requests
    $open_requests = $dsr_service->count_by_status( [ 'pending_verification', 'verified', 'in_progress', 'on_hold' ] );
    
    // Get overdue requests (past SLA)
    $overdue = $dsr_service->count_overdue();
    
    // Simple scoring: penalize overdue requests
    if ( $open_requests === 0 ) {
        return 100; // No open requests = perfect
    }
    
    $overdue_ratio = $overdue / $open_requests;
    return max( 0, round( 100 - ( $overdue_ratio * 100 ) ) );
}
```

### Dashboard Card

In the Compliance Ops Dashboard (Phase 2.1), include a DSR summary card:

```php
<?php
$dsr_service = new \ShahiLegalFlowSuite\Services\DSR_Service();
$dsr_stats = [
    'open'      => $dsr_service->count_by_status( [ 'pending_verification', 'verified', 'in_progress', 'on_hold' ] ),
    'completed' => $dsr_service->count_by_status( [ 'completed' ] ),
    'overdue'   => $dsr_service->count_overdue(),
];
?>

<div class="slos-ops-card">
    <div class="slos-card-header">
        <h3>
            <span class="dashicons dashicons-clipboard"></span>
            <?php esc_html_e( 'Data Subject Requests', 'shahi-legalflowsuite' ); ?>
        </h3>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-dsr' ) ); ?>" class="slos-btn-ghost">
            <?php esc_html_e( 'Manage', 'shahi-legalflowsuite' ); ?>
        </a>
    </div>
    <div class="slos-card-body">
        <div class="slos-stat-row">
            <span class="slos-stat-label"><?php esc_html_e( 'Open Requests', 'shahi-legalflowsuite' ); ?></span>
            <span class="slos-stat-value"><?php echo esc_html( $dsr_stats['open'] ); ?></span>
        </div>
        <div class="slos-stat-row">
            <span class="slos-stat-label"><?php esc_html_e( 'Completed (All Time)', 'shahi-legalflowsuite' ); ?></span>
            <span class="slos-stat-value"><?php echo esc_html( $dsr_stats['completed'] ); ?></span>
        </div>
        <?php if ( $dsr_stats['overdue'] > 0 ) : ?>
            <div class="slos-stat-row slos-alert">
                <span class="slos-stat-label"><?php esc_html_e( 'Overdue (Past SLA)', 'shahi-legalflowsuite' ); ?></span>
                <span class="slos-stat-value slos-danger"><?php echo esc_html( $dsr_stats['overdue'] ); ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>
```

---

## 5. Cross-Links

| From | To | Method |
|------|----|--------|
| DSR Detail → Consent Records | Inline collapsible section | In-page expansion |
| Compliance Dashboard → DSR | DSR card with "Manage" link | Link to `slos-dsr` |
| DSR Reports → Consent Analytics | Future enhancement | Could correlate consent rates with DSR volumes |

---

## Files to Modify

| File | Change |
|------|--------|
| `Services/Consent_Service.php` | Add `get_consents_by_email()` method |
| `Database/Repositories/Consent_Repository.php` | Add `find_by_email()` method |
| `Services/Consent_Audit_Logger.php` | Add `get_logs_by_email()` method |
| `Admin/DSRRequestDetail.php` | Include consent history section |
| `templates/admin/dsr/detail.php` | (if separate template) Add consent history markup |
| `Services/DSR_Export_Service.php` | Include consent data in exports |
| `Services/Compliance_Score_Calculator.php` | (optional) Add `DSR_OPERATIONS` dimension |
| `templates/admin/compliance/tabs/dashboard.php` | Add DSR summary card |

---

## Privacy Considerations

- Consent records exposed in DSR detail are limited to **that specific email**.
- Export packages should be delivered securely (existing DSR flow handles this).
- Audit logs shown are already filtered by user ID.

---

_End of Reference_

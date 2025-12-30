# Geo Rules & Regional Presets Reference

_Reference for: IMPLEMENTATION-PLAN.md → Phase 1.2_

---

## Context: Existing Geo Services

The plugin already has robust geo-targeting infrastructure:

| Component | Location | Purpose |
|-----------|----------|---------|
| `Geo_Service` | `Services/Geo_Service.php` | IP detection via multiple providers (ipapi, ipinfo, ip-api), region mapping, caching |
| `Geo_Rule_Matcher` | `Services/Geo_Rule_Matcher.php` | Priority-based rule matching, EU/EEA country lists, framework-to-template mapping |

**Existing Capabilities:**
- Rules stored in `slos_geo_rules` option (not a database table)
- Priority-based matching: Exact country → Parent country → Region group (EU-ALL, EEA-ALL) → Global fallback
- Built-in EU (27) and EEA (30) country code arrays
- Framework mapping to banner templates (GDPR → eu, CCPA → ccpa, LGPD → advanced)
- Banner config extraction from rules (`consent_mode`, `show_reject`, `require_explicit`, etc.)

---

## Enhancement Goals

This reference defines **enhancements to the existing system**, not a rebuild:

1. Add opinionated **regional presets** (EU, US-CA, UK, BR, ROW) that populate `slos_geo_rules` with sensible defaults.
2. Extend the admin UI with preset selection buttons.
3. Bind presets to required legal docs for the Compliance Score Calculator.
4. Improve visual representation in the Geo Rules admin tab.

---

## Core Concepts

| Term | Definition |
|------|------------|
| **Region** | A logical grouping of countries/states (e.g., "EU" covers all EEA members). |
| **Geo Rule** | A configuration object that binds a region to a consent model, banner template, and optional legal doc requirements. |
| **Consent Model** | How consent is handled: `opt-in` (explicit acceptance required), `opt-out` (implicit acceptance, user can reject), `notice-only` (informational, no blocking). |
| **Banner Template** | Visual + behavioral template for the consent banner (e.g., `eu`, `ccpa`, `simple`, `advanced`). |
| **Preset** | A pre-defined Geo Rule for common jurisdictions, ready to activate with one click. |

---

## Regional Presets Data Structure

```php
// config/geo-presets.php

return [
    'EU' => [
        'label'         => 'European Union / EEA',
        'countries'     => ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO'],
        'consent_model' => 'opt-in',
        'template'      => 'eu',
        'legal_docs'    => ['privacy_policy', 'cookie_policy'],
        'description'   => 'Strict opt-in consent per GDPR and ePrivacy Directive.',
    ],
    'UK' => [
        'label'         => 'United Kingdom',
        'countries'     => ['GB'],
        'consent_model' => 'opt-in',
        'template'      => 'eu',
        'legal_docs'    => ['privacy_policy', 'cookie_policy'],
        'description'   => 'UK GDPR mirrors EU requirements.',
    ],
    'US-CA' => [
        'label'         => 'California (CCPA/CPRA)',
        'countries'     => ['US'],
        'states'        => ['CA'],
        'consent_model' => 'opt-out',
        'template'      => 'ccpa',
        'legal_docs'    => ['privacy_policy'],
        'description'   => 'Opt-out model with "Do Not Sell" link required.',
    ],
    'BR' => [
        'label'         => 'Brazil (LGPD)',
        'countries'     => ['BR'],
        'consent_model' => 'opt-in',
        'template'      => 'advanced',
        'legal_docs'    => ['privacy_policy', 'cookie_policy'],
        'description'   => 'LGPD requires explicit consent for personal data processing.',
    ],
    'ROW' => [
        'label'         => 'Rest of World',
        'countries'     => ['*'],  // wildcard
        'consent_model' => 'notice-only',
        'template'      => 'simple',
        'legal_docs'    => ['privacy_policy'],
        'description'   => 'Informational notice; no blocking. Use where no strict law applies.',
    ],
];
```

---

## Existing Storage & Schema

> **Important:** Rules are stored in the `slos_geo_rules` WordPress option as an array, **not** a database table. This is the existing implementation in `Geo_Rule_Matcher.php`.

### Current Rule Structure (from existing code)

```php
// Stored in: get_option( 'slos_geo_rules', [] )

$rule = [
    'id'               => 1,                    // Unique identifier
    'name'             => 'European Union',     // Display name
    'countries'        => ['EU-ALL'],           // Country codes or 'EU-ALL', 'EEA-ALL', 'GLOBAL', '*'
    'active'           => true,                 // Is rule active
    'show_banner'      => true,
    'consent_mode'     => 'opt-in',             // 'opt-in', 'opt-out'
    'show_reject'      => true,
    'require_explicit' => true,
    'record_proof'     => true,
    'allow_withdraw'   => true,
    'framework'        => 'GDPR',               // Maps to template via Geo_Rule_Matcher::map_framework_to_template()
];
```

### Proposed Enhancements to Rule Schema

To support regional presets and legal doc requirements, extend the existing rule structure:

```php
// Enhanced rule structure (backward compatible)
$rule = [
    // ... existing fields ...
    
    // New fields for presets integration:
    'preset_key'       => 'EU',                 // Optional: links to preset for UI
    'legal_docs'       => ['privacy_policy', 'cookie_policy'], // Required docs for Compliance Score
    'description'      => 'Strict opt-in consent per GDPR.',   // Help text
    'states'           => null,                 // For US state-level rules: ['CA', 'VA', 'CO']
];
```

### Field Notes

- **countries**: Existing support for `EU-ALL`, `EEA-ALL`, `GLOBAL`, `*` wildcards via `Geo_Rule_Matcher`
- **framework**: Already maps to banner templates via `map_framework_to_template()` method
- **legal_docs**: New field for Compliance Score Calculator integration
- **preset_key**: New field to identify preset-derived rules in UI

---

## Preset Application Logic

When a user clicks a preset button, the system should:

```php
/**
 * Apply a regional preset to geo rules.
 * 
 * @param string $preset_key One of: EU, UK, US-CA, BR, ROW
 */
public function apply_preset( string $preset_key ): void {
    $presets = include SLOS_PLUGIN_DIR . 'config/geo-presets.php';
    
    if ( ! isset( $presets[ $preset_key ] ) ) {
        return;
    }
    
    $preset = $presets[ $preset_key ];
    $existing_rules = get_option( 'slos_geo_rules', [] );
    
    // Check if a rule for this preset already exists
    $existing_index = array_search( $preset_key, array_column( $existing_rules, 'preset_key' ) );
    
    $new_rule = [
        'id'               => $existing_index !== false ? $existing_rules[ $existing_index ]['id'] : $this->get_next_rule_id(),
        'name'             => $preset['label'],
        'countries'        => $preset['countries'],
        'states'           => $preset['states'] ?? null,
        'active'           => true,
        'consent_mode'     => $preset['consent_model'],
        'framework'        => $this->consent_model_to_framework( $preset['consent_model'], $preset_key ),
        'show_banner'      => true,
        'show_reject'      => $preset['consent_model'] === 'opt-in',
        'require_explicit' => $preset['consent_model'] === 'opt-in',
        'record_proof'     => true,
        'allow_withdraw'   => true,
        'preset_key'       => $preset_key,
        'legal_docs'       => $preset['legal_docs'],
        'description'      => $preset['description'],
    ];
    
    if ( $existing_index !== false ) {
        $existing_rules[ $existing_index ] = $new_rule;
    } else {
        $existing_rules[] = $new_rule;
    }
    
    update_option( 'slos_geo_rules', $existing_rules );
    
    // Clear matcher cache
    ( new Geo_Rule_Matcher() )->clear_cache();
}
```

---

## Rule Matching (Existing Implementation)

The `Geo_Rule_Matcher::find_matching_rule()` method already implements priority-based matching:

1. **Exact match**: US-CA matches US-CA visitors
2. **Parent country match**: US matches US-CA visitors if no state-specific rule
3. **Region group match**: EU-ALL matches any EU country via `is_eu_country()`
4. **Global/wildcard match**: GLOBAL or * matches everything

**No changes needed** to the matching algorithm; presets simply populate the rules option with well-structured data.

---

## Admin UI Specification

### Geo Rules Tab Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│ Geo Rules                                                    [+ Add]│
├─────────────────────────────────────────────────────────────────────┤
│ Quick Presets: [EU] [UK] [US-CA] [Brazil] [Rest of World]           │
│ Click a preset to apply its default configuration.                  │
├─────────────────────────────────────────────────────────────────────┤
│ Active Rules (ordered by priority)                                  │
│ ┌─────────────────────────────────────────────────────────────────┐ │
│ │ 1. European Union / EEA                                         │ │
│ │    Countries: 30 | Model: Opt-In | Template: EU                 │ │
│ │    [Edit] [Disable] [↑] [↓]                                     │ │
│ ├─────────────────────────────────────────────────────────────────┤ │
│ │ 2. California (CCPA/CPRA)                                       │ │
│ │    Countries: US (CA only) | Model: Opt-Out | Template: CCPA    │ │
│ │    [Edit] [Disable] [↑] [↓]                                     │ │
│ ├─────────────────────────────────────────────────────────────────┤ │
│ │ 3. Rest of World                                                │ │
│ │    Countries: * | Model: Notice-Only | Template: Simple         │ │
│ │    [Edit] [Disable] [↑] [↓]                                     │ │
│ └─────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
```

### Rule Editor Modal

- Name (text)
- Description (textarea)
- Countries (multi-select or tag input with search)
- States (conditional, shown if single country selected)
- Consent Model (radio: Opt-In / Opt-Out / Notice-Only)
- Banner Template (dropdown)
- Required Legal Docs (checkboxes)
- Priority (number input)
- Enabled (toggle)

### Visual Region Map (Optional Enhancement)

- Interactive world map where countries are color-coded by their assigned rule.
- Clicking a country shows which rule applies and allows quick reassignment.

---

## REST API Endpoints

### GET `/wp-json/slos/v1/geo/rules`

Returns all geo rules (for admin UI).

### POST `/wp-json/slos/v1/geo/rules`

Create a new rule.

### PUT `/wp-json/slos/v1/geo/rules/{id}`

Update an existing rule.

### DELETE `/wp-json/slos/v1/geo/rules/{id}`

Delete a rule.

### GET `/wp-json/slos/v1/geo/region`

Detect visitor's region and return the matched rule.

**Response:**

```json
{
  "data": {
    "country_code": "DE",
    "state_code": null,
    "region": "EU",
    "rule_id": 1,
    "consent_model": "opt-in",
    "template": "eu"
  }
}
```

### POST `/wp-json/slos/v1/geo/presets/{preset_key}/apply`

Apply a preset (creates or updates the rule for that preset).

---

## Banner Integration

The consent banner JS (`consent-banner.js`) already calls `/geo/region` on init. The enhanced flow:

1. Banner JS calls `/geo/region`.
2. Backend runs `Geo_Rule_Matcher::match()` using visitor's IP-derived location.
3. Response includes `consent_model` and `template`.
4. Banner JS selects the appropriate template and configures behavior:
   - `opt-in`: Block non-essential cookies until explicit consent.
   - `opt-out`: Allow cookies by default; show "Do Not Sell" and preference options.
   - `notice-only`: Informational banner; no blocking.

---

## Migration from Existing Geo Data

If the current system stores geo rules in a different format (e.g., simple country → template mapping), create a migration script that:

1. Reads existing rules from options or legacy table.
2. Maps them to the new `Geo_Rule` schema.
3. Inserts into `{prefix}_slos_geo_rules`.
4. Deprecates the old storage.

---

## Testing Scenarios

| Visitor Location | Expected Rule | Consent Model | Template |
|------------------|---------------|---------------|----------|
| Germany (DE) | EU | opt-in | eu |
| UK (GB) | UK | opt-in | eu |
| California (US-CA) | US-CA | opt-out | ccpa |
| Texas (US-TX) | ROW (or US fallback if configured) | notice-only | simple |
| Brazil (BR) | BR | opt-in | advanced |
| Australia (AU) | ROW | notice-only | simple |

---

## Security Considerations

- Geo detection via IP should use a privacy-respecting service (MaxMind local DB or similar).
- Do not expose full IP addresses in REST responses or logs.
- Admin endpoints for rule management require `manage_options` capability.

---

_End of Reference_

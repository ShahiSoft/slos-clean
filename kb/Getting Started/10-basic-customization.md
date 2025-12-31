# Basic Customization

## Personalize Your Compliance Experience

### Banner Customization

#### Step 1: Choose Banner Style
```
SLOS → Consent Management → Banner Settings → Appearance
```

**Style Options:**
```json
{
  "theme": "modern_dark",
  "position": "bottom",
  "layout": "full_width",
  "animation": "slide_up",
  "border_radius": "8px",
  "shadow": "medium",
  "max_width": "1200px"
}
```

#### Step 2: Customize Colors
```
SLOS → Consent Management → Banner Settings → Colors
```

**Color Scheme:**
```json
{
  "primary_background": "#1a1a1a",
  "secondary_background": "#2a2a2a",
  "text_color": "#ffffff",
  "button_primary": "#007cba",
  "button_secondary": "#6c757d",
  "button_text": "#ffffff",
  "link_color": "#4dabf7",
  "border_color": "#404040"
}
```

#### Step 3: Update Banner Text
```
SLOS → Consent Management → Banner Settings → Content
```

**Custom Text:**
```json
{
  "title": "We Value Your Privacy",
  "description": "We use cookies to enhance your experience and provide personalized content. You can manage your preferences anytime.",
  "accept_all_text": "Accept All",
  "reject_all_text": "Reject All",
  "customize_text": "Customize",
  "read_more_text": "Learn More",
  "read_more_link": "/privacy-policy"
}
```

### Portal Customization

#### Step 1: Access Portal Settings
```
SLOS → DSR Portal → Settings → Appearance
```

**Portal Branding:**
```json
{
  "portal_title": "Privacy Request Portal",
  "company_logo": "https://yourcompany.com/logo.png",
  "primary_color": "#007cba",
  "secondary_color": "#f8f9fa",
  "font_family": "Inter, sans-serif",
  "favicon": "https://yourcompany.com/favicon.ico"
}
```

#### Step 2: Customize Portal Pages
```
SLOS → DSR Portal → Settings → Pages → Edit
```

**Welcome Page:**
```html
<div class="portal-welcome">
    <h1>Your Privacy Rights</h1>
    <p>Manage your personal data and privacy preferences with {{company_name}}.</p>
    <div class="quick-actions">
        <a href="#access-data" class="btn-primary">Access My Data</a>
        <a href="#delete-data" class="btn-secondary">Delete My Data</a>
        <a href="#update-preferences" class="btn-secondary">Update Preferences</a>
    </div>
</div>
```

### Document Customization

#### Step 1: Access Document Templates
```
SLOS → Legal Documents → Templates → Customize
```

**Company Information:**
```json
{
  "company_name": "Your Company Name",
  "company_address": "123 Business St, City, State 12345",
  "company_phone": "(555) 123-4567",
  "company_email": "privacy@yourcompany.com",
  "company_website": "https://yourcompany.com",
  "data_protection_officer": "Jane Doe, DPO",
  "registration_number": "Company Registration #123456"
}
```

#### Step 2: Customize Document Content
```
SLOS → Legal Documents → Templates → Edit Content
```

**Privacy Policy Custom Sections:**
```markdown
## Our Commitment to Privacy

At {{company_name}}, we are committed to protecting your privacy and being transparent about our data practices. This privacy policy explains how we collect, use, and protect your personal information.

## Contact Information

If you have any questions about this privacy policy or our data practices, please contact us:

- Email: {{company_email}}
- Phone: {{company_phone}}
- Address: {{company_address}}
- Data Protection Officer: {{data_protection_officer}}
```

### Dashboard Customization

#### Step 1: Configure Dashboard Widgets
```
SLOS → Dashboard → Customize → Widgets
```

**Widget Layout:**
```json
{
  "layout": "grid",
  "columns": 3,
  "widgets": [
    {
      "type": "consent_overview",
      "position": {"x": 0, "y": 0, "width": 2, "height": 1},
      "title": "Consent Overview"
    },
    {
      "type": "dsr_requests",
      "position": {"x": 2, "y": 0, "width": 1, "height": 1},
      "title": "DSR Requests"
    },
    {
      "type": "compliance_status",
      "position": {"x": 0, "y": 1, "width": 1, "height": 1},
      "title": "Compliance Status"
    },
    {
      "type": "recent_activity",
      "position": {"x": 1, "y": 1, "width": 2, "height": 1},
      "title": "Recent Activity"
    }
  ]
}
```

#### Step 2: Set Dashboard Preferences
```
SLOS → Dashboard → Settings → Preferences
```

**Display Options:**
```json
{
  "theme": "auto",
  "language": "en",
  "timezone": "America/New_York",
  "date_format": "MM/DD/YYYY",
  "number_format": "en-US",
  "items_per_page": 25,
  "auto_refresh": true,
  "refresh_interval": 300
}
```

### Custom CSS and Styling

#### Step 1: Access Custom CSS
```
SLOS → Settings → Appearance → Custom CSS
```

**Banner Customization:**
```css
/* Custom banner styling */
.slos-consent-banner {
    font-family: 'Inter', sans-serif;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.slos-consent-banner .banner-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.slos-consent-banner .banner-description {
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.slos-consent-banner .banner-buttons .btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.slos-consent-banner .banner-buttons .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
```

#### Step 2: Portal Styling
```css
/* Custom portal styling */
.slos-dsr-portal {
    font-family: 'Inter', sans-serif;
}

.slos-dsr-portal .portal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.slos-dsr-portal .portal-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.slos-dsr-portal .request-form {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.slos-dsr-portal .form-group {
    margin-bottom: 1.5rem;
}

.slos-dsr-portal .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.slos-dsr-portal .form-group input,
.slos-dsr-portal .form-group select,
.slos-dsr-portal .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.slos-dsr-portal .btn-submit {
    background: #007cba;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.slos-dsr-portal .btn-submit:hover {
    background: #005a87;
}
```

### Language and Localization

#### Step 1: Configure Languages
```
SLOS → Settings → Languages → Configure
```

**Language Settings:**
```json
{
  "default_language": "en",
  "available_languages": [
    {"code": "en", "name": "English", "flag": "🇺🇸"},
    {"code": "es", "name": "Español", "flag": "🇪🇸"},
    {"code": "fr", "name": "Français", "flag": "🇫🇷"},
    {"code": "de", "name": "Deutsch", "flag": "🇩🇪"}
  ],
  "auto_detect_language": true,
  "fallback_language": "en"
}
```

#### Step 2: Customize Translations
```
SLOS → Settings → Languages → Translations → Edit
```

**Custom Translations:**
```json
{
  "en": {
    "consent_banner_title": "We Value Your Privacy",
    "accept_all_button": "Accept All Cookies",
    "customize_button": "Customize Preferences"
  },
  "es": {
    "consent_banner_title": "Valoramos Tu Privacidad",
    "accept_all_button": "Aceptar Todas las Cookies",
    "customize_button": "Personalizar Preferencias"
  }
}
```

### User Role Permissions

#### Step 1: Configure User Roles
```
SLOS → Settings → Users → Roles → Customize
```

**Role Permissions:**
```json
{
  "administrator": {
    "full_access": true,
    "manage_users": true,
    "view_all_data": true,
    "export_data": true,
    "delete_data": true
  },
  "privacy_officer": {
    "consent_management": true,
    "dsr_portal": true,
    "reports": true,
    "legal_documents": true,
    "user_management": false
  },
  "editor": {
    "content_editing": true,
    "basic_reports": true,
    "consent_viewing": true,
    "dsr_viewing": false
  }
}
```

#### Step 2: Set Access Restrictions
```
SLOS → Settings → Security → Access Control
```

**Access Rules:**
```json
{
  "ip_restrictions": ["192.168.1.0/24", "10.0.0.0/8"],
  "two_factor_required": ["administrator", "privacy_officer"],
  "session_timeout": 3600,
  "password_policy": {
    "min_length": 12,
    "require_uppercase": true,
    "require_numbers": true,
    "require_symbols": true
  }
}
```

### Integration Customization

#### Step 1: Configure Third-Party Integrations
```
SLOS → Integrations → Settings → Customize
```

**Integration Options:**
```json
{
  "google_analytics": {
    "enabled": true,
    "tracking_id": "GA_MEASUREMENT_ID",
    "anonymize_ip": true,
    "respect_do_not_track": true
  },
  "facebook_pixel": {
    "enabled": true,
    "pixel_id": "FACEBOOK_PIXEL_ID",
    "advanced_matching": false
  },
  "hotjar": {
    "enabled": false,
    "site_id": "HOTJAR_SITE_ID"
  }
}
```

#### Step 2: Custom Webhooks
```
SLOS → Integrations → Webhooks → Configure
```

**Webhook Endpoints:**
```json
{
  "consent_events": {
    "url": "https://api.yourcompany.com/webhooks/consent",
    "events": ["consent_granted", "consent_updated", "consent_withdrawn"],
    "secret": "your_webhook_secret",
    "retry_attempts": 3
  },
  "dsr_events": {
    "url": "https://api.yourcompany.com/webhooks/dsr",
    "events": ["request_received", "request_completed", "data_exported"],
    "secret": "your_webhook_secret",
    "retry_attempts": 3
  }
}
```

### Backup and Maintenance

#### Step 1: Configure Automated Backups
```
SLOS → Settings → Backups → Schedule
```

**Backup Settings:**
```json
{
  "frequency": "daily",
  "time": "02:00",
  "retention_days": 30,
  "include_files": true,
  "include_database": true,
  "compression": "gzip",
  "encryption": true,
  "remote_storage": {
    "provider": "aws_s3",
    "bucket": "your-backup-bucket",
    "region": "us-east-1"
  }
}
```

#### Step 2: Maintenance Schedule
```
SLOS → Settings → Maintenance → Schedule
```

**Maintenance Tasks:**
```json
{
  "database_optimization": {
    "frequency": "weekly",
    "day": "sunday",
    "time": "03:00"
  },
  "log_cleanup": {
    "frequency": "monthly",
    "retention_days": 90
  },
  "cache_clearing": {
    "frequency": "daily",
    "time": "01:00"
  }
}
```

### Testing Customizations

#### Step 1: Preview Changes
```
SLOS → Settings → Appearance → Preview
```

**Preview Options:**
- [x] Banner preview
- [x] Portal preview
- [x] Document preview
- [x] Mobile responsiveness
- [x] Cross-browser compatibility

#### Step 2: User Testing
```javascript
// Test customization in different scenarios
const testScenarios = [
    { name: 'Desktop Chrome', viewport: '1920x1080' },
    { name: 'Mobile Safari', viewport: '375x667' },
    { name: 'Tablet Firefox', viewport: '768x1024' }
];

testScenarios.forEach(scenario => {
    console.log(`Testing ${scenario.name}...`);
    // Run automated tests for each scenario
    runCustomizationTests(scenario);
});
```

### Reverting Changes

#### Step 1: Backup Current Settings
```
SLOS → Settings → Backup → Create Backup
```

**Backup Configuration:**
```json
{
  "backup_name": "pre_customization_backup",
  "include_settings": true,
  "include_customizations": true,
  "include_templates": true,
  "timestamp": "2025-12-31T10:00:00Z"
}
```

#### Step 2: Restore Defaults
```
SLOS → Settings → Appearance → Restore Defaults
```

**Restore Options:**
- [x] Restore banner defaults
- [x] Restore portal defaults
- [x] Restore color scheme
- [x] Restore templates
- [x] Keep custom content

### Advanced Customization

#### Step 1: Custom JavaScript
```
SLOS → Settings → Advanced → Custom JavaScript
```

**Custom Scripts:**
```javascript
// Custom banner behavior
document.addEventListener('slos_banner_loaded', function() {
    // Add custom functionality
    const banner = document.querySelector('.slos-consent-banner');

    // Add custom button
    const customBtn = document.createElement('button');
    customBtn.textContent = 'More Info';
    customBtn.onclick = function() {
        window.open('/privacy-details', '_blank');
    };
    banner.querySelector('.banner-buttons').appendChild(customBtn);
});

// Custom consent logic
document.addEventListener('slos_consent_granted', function(event) {
    const categories = event.detail.categories;

    // Custom tracking based on consent
    if (categories.includes('analytics')) {
        // Initialize analytics
        initCustomAnalytics();
    }

    if (categories.includes('marketing')) {
        // Load marketing pixels
        loadMarketingPixels();
    }
});
```

#### Step 2: API Customizations
```
SLOS → Advanced → API → Custom Endpoints
```

**Custom API Endpoints:**
```php
// Custom API endpoint for consent data
add_action('rest_api_init', function() {
    register_rest_route('slos/v1', '/custom-consent', array(
        'methods' => 'GET',
        'callback' => 'get_custom_consent_data',
        'permission_callback' => 'check_user_permission'
    ));
});

function get_custom_consent_data($request) {
    // Custom consent data logic
    $user_id = get_current_user_id();
    $consent_data = get_user_consent_data($user_id);

    // Add custom processing
    $consent_data['custom_field'] = 'additional_data';

    return new WP_REST_Response($consent_data, 200);
}
```

### Support Resources

#### Documentation
- [Banner Customization](../How-tos/01-setup-cookie-banner.md)
- [Portal Configuration](../Features/DSR-Portal/configuration.md)
- [Advanced Customization](../Advanced/02-hooks-filters.md)

#### Help
- Customization FAQ
- CSS styling guide
- Theme compatibility
- Technical support
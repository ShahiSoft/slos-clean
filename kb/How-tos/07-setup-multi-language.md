# Set Up Multi-Language Support

## Configure Multi-Language Cookie Banners & Documents

### Enable Multi-Language Support

#### Step 1: Install Language Plugins
Choose one of these multilingual plugins:

**WPML (Recommended)**
```
WordPress Admin → Plugins → Add New
Search: "WPML Multilingual CMS"
Install and activate
```

**Polylang**
```
WordPress Admin → Plugins → Add New
Search: "Polylang"
Install and activate
```

**TranslatePress**
```
WordPress Admin → Plugins → Add New
Search: "TranslatePress"
Install and activate
```

#### Step 2: Configure Languages
```
SLOS → Settings → Languages
```

Add your supported languages:
- English (en) - Primary
- Spanish (es)
- French (fr)
- German (de)
- Portuguese (pt)
- Italian (it)

### Configure Cookie Banner Translations

#### Step 1: Access Banner Translation
```
SLOS → Consent Management → Banner Settings → Translations
```

#### Step 2: Translate Banner Text

**Main Banner Text:**
```json
{
  "en": {
    "title": "We use cookies",
    "description": "We use cookies to enhance your experience...",
    "accept_all": "Accept All",
    "reject_all": "Reject All",
    "customize": "Customize"
  },
  "es": {
    "title": "Usamos cookies",
    "description": "Usamos cookies para mejorar tu experiencia...",
    "accept_all": "Aceptar Todo",
    "reject_all": "Rechazar Todo",
    "customize": "Personalizar"
  }
}
```

#### Step 3: Translate Cookie Categories

**Essential Cookies:**
- EN: "Essential Cookies"
- ES: "Cookies Esenciales"
- FR: "Cookies Essentiels"
- DE: "Notwendige Cookies"

**Analytics Cookies:**
- EN: "Analytics Cookies"
- ES: "Cookies de Analítica"
- FR: "Cookies d'Analyse"
- DE: "Analyse-Cookies"

**Marketing Cookies:**
- EN: "Marketing Cookies"
- ES: "Cookies de Marketing"
- FR: "Cookies de Marketing"
- DE: "Marketing-Cookies"

**Preferences Cookies:**
- EN: "Preferences Cookies"
- ES: "Cookies de Preferencias"
- FR: "Cookies de Préférences"
- DE: "Präferenz-Cookies"

### Set Up Geo-Language Detection

#### Step 1: Configure Geo-Detection
```
SLOS → Consent Management → Geo-Targeting → Language Settings
```

#### Step 2: Map Countries to Languages

```javascript
const geoLanguageMap = {
  // Spanish-speaking countries
  'ES': 'es', 'MX': 'es', 'AR': 'es', 'CO': 'es', 'PE': 'es',
  'VE': 'es', 'CL': 'es', 'EC': 'es', 'GT': 'es', 'CU': 'es',
  'BO': 'es', 'DO': 'es', 'HN': 'es', 'PY': 'es', 'SV': 'es',
  'NI': 'es', 'CR': 'es', 'PA': 'es', 'UY': 'es', 'GQ': 'es',

  // French-speaking countries
  'FR': 'fr', 'BE': 'fr', 'CH': 'fr', 'CA': 'fr', 'LU': 'fr',
  'MC': 'fr', 'SN': 'fr', 'CI': 'fr', 'ML': 'fr', 'NE': 'fr',

  // German-speaking countries
  'DE': 'de', 'AT': 'de', 'LI': 'fr', // Liechtenstein uses French

  // Portuguese-speaking countries
  'PT': 'pt', 'BR': 'pt', 'AO': 'pt', 'MZ': 'pt', 'CV': 'pt',
  'GW': 'pt', 'ST': 'pt', 'TL': 'pt',

  // Italian-speaking countries
  'IT': 'it', 'SM': 'it', 'VA': 'it', // Vatican City

  // Default fallback
  'default': 'en'
};
```

#### Step 3: Browser Language Fallback
Enable automatic browser language detection as secondary fallback.

### Configure Legal Document Translations

#### Step 1: Access Document Translation
```
SLOS → Legal Documents → Templates → Translations
```

#### Step 2: Translate Document Templates

**Privacy Policy Sections:**
- Data Collection (Recopilación de Datos)
- Legal Basis (Base Legal)
- Data Subject Rights (Derechos del Interesado)
- Contact Information (Información de Contacto)

**Cookie Policy Sections:**
- What are Cookies (Qué son las Cookies)
- How We Use Cookies (Cómo Usamos las Cookies)
- Managing Cookies (Gestionar Cookies)
- Third-party Cookies (Cookies de Terceros)

#### Step 3: Auto-Translation Setup
For initial translations, use:
- Google Translate API
- DeepL Pro API
- Manual translation services

### Set Up DSR Portal Multi-Language

#### Step 1: Portal Language Configuration
```
SLOS → DSR Portal → Settings → Languages
```

#### Step 2: Translate Portal Interface

**Request Form Labels:**
```json
{
  "request_type": {
    "en": "Request Type",
    "es": "Tipo de Solicitud",
    "fr": "Type de Demande",
    "de": "Anfragetyp"
  },
  "personal_info": {
    "en": "Personal Information",
    "es": "Información Personal",
    "fr": "Informations Personnelles",
    "de": "Persönliche Informationen"
  }
}
```

#### Step 3: Translate Response Templates

**Approval Responses:**
- EN: "Your request has been approved..."
- ES: "Su solicitud ha sido aprobada..."
- FR: "Votre demande a été approuvée..."
- DE: "Ihr Antrag wurde genehmigt..."

**Rejection Responses:**
- EN: "Your request cannot be fulfilled..."
- ES: "Su solicitud no puede ser cumplida..."
- FR: "Votre demande ne peut pas être satisfaite..."
- DE: "Ihr Antrag kann nicht erfüllt werden..."

### Configure Analytics Multi-Language

#### Step 1: Event Translation
```
SLOS → Analytics Integration → Event Settings → Translations
```

#### Step 2: Translate Custom Events

**Consent Events:**
```json
{
  "consent_granted": {
    "en": "Consent Granted",
    "es": "Consentimiento Otorgado",
    "fr": "Consentement Accordé",
    "de": "Einwilligung Erteilt"
  }
}
```

#### Step 3: Language-Specific Dashboards
Create separate analytics dashboards for each language to track:
- Consent rates by language
- Popular request types by region
- Document downloads by language
- Portal usage by language

### Implement Language Switching

#### Step 1: Add Language Switcher
```php
// Add to theme functions.php
function slos_language_switcher() {
    if (function_exists('icl_get_languages')) {
        // WPML language switcher
        do_action('wpml_language_switcher');
    } elseif (function_exists('pll_the_languages')) {
        // Polylang language switcher
        pll_the_languages(array('show_flags' => 1));
    }
}
add_action('wp_footer', 'slos_language_switcher');
```

#### Step 2: Cookie Banner Language Sync
Ensure banner language matches site language:

```javascript
// Sync banner language with site language
document.addEventListener('DOMContentLoaded', function() {
    const siteLang = document.documentElement.lang || 'en';
    if (window.SLOS && window.SLOS.setLanguage) {
        window.SLOS.setLanguage(siteLang);
    }
});
```

### Test Multi-Language Setup

#### Step 1: Language Testing Checklist

**Banner Testing:**
- [ ] Banner appears in correct language for each region
- [ ] All buttons and text translate properly
- [ ] Cookie categories display in correct language
- [ ] Consent choices save correctly across languages

**Document Testing:**
- [ ] Legal documents generate in selected language
- [ ] Templates translate all sections correctly
- [ ] PDF export maintains language formatting
- [ ] Links and references work in all languages

**Portal Testing:**
- [ ] Request form displays in user's language
- [ ] Email notifications send in correct language
- [ ] Status updates maintain language consistency
- [ ] Document exports respect language selection

#### Step 2: Cross-Language Consistency
- Test consent status carries across language switches
- Verify cookie settings persist across languages
- Check analytics tracking works with all languages
- Ensure accessibility features work in all languages

### Advanced Multi-Language Features

#### Step 1: Custom Language Packs
Create custom language files for industry-specific terms:

```php
// Custom language pack for healthcare
$healthcare_terms = array(
    'en' => array(
        'medical_data' => 'Medical Data',
        'health_records' => 'Health Records'
    ),
    'es' => array(
        'medical_data' => 'Datos Médicos',
        'health_records' => 'Registros de Salud'
    )
);
```

#### Step 2: Dynamic Content Translation
Set up real-time translation for user-generated content:
- DSR request descriptions
- Custom document sections
- Support ticket communications
- Audit log entries

#### Step 3: RTL Language Support
For right-to-left languages (Arabic, Hebrew):

```css
/* RTL language support */
[dir="rtl"] .slos-consent-banner {
    text-align: right;
}

[dir="rtl"] .banner-buttons {
    flex-direction: row-reverse;
}
```

### Monitor Multi-Language Performance

#### Step 1: Language Analytics
Track usage by language:
- Most popular languages
- Consent rates by language
- Document downloads by language
- Portal usage by language

#### Step 2: Translation Quality
Monitor translation effectiveness:
- User feedback on translations
- Error rates in translated content
- Support tickets about translations
- A/B testing of translation variations

### Maintenance and Updates

#### Step 1: Regular Translation Updates
- Review translations quarterly
- Update for new regulations
- Add new language support
- Improve translation quality

#### Step 2: Language Pack Management
- Keep language packs updated
- Backup custom translations
- Version control translations
- Document translation changes

### Troubleshooting Multi-Language Issues

#### Common Problems

**Language Not Detected:**
- Check geo-detection service
- Verify browser language settings
- Test with different IP addresses
- Check language fallback settings

**Translation Missing:**
- Verify language pack installation
- Check for incomplete translations
- Update translation files
- Use fallback language

**Consent Not Syncing:**
- Check cross-language cookie settings
- Verify language switcher integration
- Test consent persistence
- Debug cookie domain settings

#### Support Resources
- Translation service providers
- Language plugin documentation
- Browser language detection guides
- Internationalization best practices
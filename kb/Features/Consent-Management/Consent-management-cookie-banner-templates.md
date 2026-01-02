# Cookie Banner Templates

## Overview

The Consent Management module provides 4 professionally designed cookie banner templates that automatically adapt to different regulatory requirements and user preferences.

## Available Templates

### 1. Simple Template

**Best For:** Basic websites, minimal compliance needs

**Features:**
- Clean, minimal design
- Single "Accept All" button
- Optional "Settings" link
- Small, unobtrusive banner
- Quick dismissal

**Compliance:** Basic consent collection

**Customization Options:**
- Banner position (top, bottom, corner)
- Background color
- Text color
- Button styling
- Font size

### 2. GDPR Template

**Best For:** EU websites, comprehensive GDPR compliance

**Features:**
- Detailed consent categories
- Granular consent options
- "Accept All", "Reject All", "Customize" buttons
- Cookie category breakdown
- Privacy Policy link
- Data processing information

**Compliance:** Full GDPR Article 7 requirements

**Cookie Categories:**
- Essential (always required)
- Analytics & Performance
- Marketing & Advertising
- Functional & Preferences
- Social Media

### 3. CCPA Template

**Best For:** California businesses, CCPA compliance

**Features:**
- "Do Not Sell My Personal Information" link
- Opt-out preference signals
- Business purpose disclosures
- Data sharing information
- Consumer rights information

**Compliance:** California Consumer Privacy Act

**Key Elements:**
- Clear privacy notice
- Opt-out mechanisms
- Data collection categories
- Business purposes
- Third-party sharing

### 4. Advanced Template

**Best For:** Enterprise websites, maximum customization

**Features:**
- Fully customizable consent matrix
- Advanced cookie categorization
- Vendor management
- Consent duration settings
- Advanced analytics integration
- Multi-language support

**Compliance:** GDPR, CCPA, LGPD, PIPEDA

**Advanced Features:**
- Cookie-by-cookie consent
- Vendor-specific consents
- Consent validity periods
- Withdrawal mechanisms
- Audit trail integration

## Template Selection Logic

### Automatic Selection
The system automatically selects the appropriate template based on:

1. **Geo Location Detection**
   - EU → GDPR Template
   - California → CCPA Template
   - Brazil → LGPD Template
   - Other → Simple Template

2. **Manual Override**
   - Admin can force specific template
   - Per-region configuration
   - A/B testing variants

3. **Dynamic Switching**
   - Template changes based on user location
   - Seamless transition
   - Consent preservation

## Customization Options

### Visual Customization

**Colors:**
- Primary color (buttons, links)
- Secondary color (accents)
- Background color
- Text color
- Border colors

**Typography:**
- Font family
- Font size
- Font weight
- Line height
- Text alignment

**Layout:**
- Banner position
- Banner width
- Button layout
- Content spacing
- Mobile responsiveness

### Content Customization

**Text Content:**
- Main message
- Cookie categories
- Button labels
- Privacy policy links
- Legal text

**Links:**
- Privacy Policy URL
- Cookie Policy URL
- Terms of Service URL
- Contact information

**Legal Text:**
- Data controller information
- Processing purposes
- Retention periods
- User rights information

## A/B Testing

### Variant Creation

1. **Create Variants**
   - Duplicate existing template
   - Modify colors, text, layout
   - Different consent flows

2. **Traffic Distribution**
   - Percentage-based splitting
   - Geographic targeting
   - Device-based targeting

3. **Performance Tracking**
   - Consent rates
   - Bounce rates
   - Conversion impact
   - User engagement

### Testing Metrics

**Primary Metrics:**
- Consent acceptance rate
- Banner dismissal rate
- Time to consent
- Consent withdrawal rate

**Secondary Metrics:**
- Page load impact
- Mobile conversion
- Cross-device consistency
- Legal compliance rate

## Mobile Optimization

### Responsive Design

**Breakpoint Adaptation:**
- Desktop (>1024px)
- Tablet (768px-1023px)
- Mobile (<767px)

**Mobile-Specific Features:**
- Touch-friendly buttons (44px minimum)
- Optimized text size
- Swipe gestures
- Bottom sheet presentation
- Full-screen overlay option

### Performance Considerations

**Mobile Optimization:**
- Reduced JavaScript bundle
- Lazy loading
- Minimal animations
- Battery-friendly
- Network-efficient

## Accessibility Features

### WCAG 2.1 AA Compliance

**Keyboard Navigation:**
- Tab order through all elements
- Enter/Space to activate
- Escape to dismiss
- Focus indicators

**Screen Reader Support:**
- ARIA labels and roles
- Semantic HTML structure
- Alternative text
- Live regions for updates

**Visual Accessibility:**
- High contrast options
- Large text support
- Reduced motion support
- Color-blind friendly

## Integration Points

### WordPress Integration

**Theme Compatibility:**
- Works with all themes
- No theme modifications needed
- CSS isolation
- JavaScript namespacing

**Plugin Compatibility:**
- Tested with popular plugins
- No conflicts with caching plugins
- Compatible with page builders
- CDN-friendly

### External Services

**Analytics Integration:**
- Google Analytics 4
- Google Tag Manager
- Facebook Pixel
- Custom analytics

**Consent Management Platforms:**
- OneTrust integration
- Cookiebot compatibility
- Custom CMP support

## Template Management

### Creating Custom Templates

1. **Base Template Selection**
   - Start from existing template
   - Modify HTML structure
   - Customize CSS styling
   - Add JavaScript logic

2. **Template Registration**
   - Register via hooks
   - Add to template list
   - Configure options
   - Test functionality

3. **Validation & Testing**
   - Cross-browser testing
   - Accessibility validation
   - Performance testing
   - Legal compliance review

### Template Updates

**Version Management:**
- Template versioning
- Backward compatibility
- Update notifications
- Migration paths

**Change Tracking:**
- Modification history
- Approval workflows
- Testing requirements
- Rollback capabilities

## Best Practices

### Design Principles

1. **Clarity:** Clear, concise language
2. **Transparency:** Explain data usage
3. **Choice:** Easy consent options
4. **Control:** Simple withdrawal
5. **Trust:** Professional appearance

### Implementation Tips

1. **Test Thoroughly:** All devices and browsers
2. **Monitor Performance:** Impact on page speed
3. **Regular Updates:** Keep legal text current
4. **User Feedback:** Monitor consent patterns
5. **Compliance Review:** Regular legal audits

### Common Mistakes

- **Overly Complex:** Too many options confuse users
- **Poor Visibility:** Banner too subtle
- **Blocking Content:** Interferes with user experience
- **Outdated Text:** Legal requirements change
- **No Testing:** Not tested on real users

## Troubleshooting

### Template Not Loading

**Common Causes:**
- JavaScript disabled
- CSS conflicts
- Theme interference
- Plugin conflicts

**Solutions:**
- Check browser console
- Disable other plugins
- Test in incognito mode
- Verify theme compatibility

### Consent Not Recording

**Common Causes:**
- Database connection issues
- JavaScript errors
- Form validation failures
- Server-side processing errors

**Solutions:**
- Check server logs
- Verify database tables
- Test form submission
- Review error logs

### Mobile Display Issues

**Common Causes:**
- CSS media queries
- Theme overrides
- Plugin interference
- Viewport settings

**Solutions:**
- Test on actual devices
- Check responsive design
- Override theme styles
- Use browser dev tools

## Related Documentation

- [Consent Management Overview](overview.md)
- [Cookie Scanner](cookie-scanner.md)
- [Consent Tracking](consent-tracking.md)
- [Regional Compliance](regional-compliance.md)
- [Configuration Guide](configuration.md)
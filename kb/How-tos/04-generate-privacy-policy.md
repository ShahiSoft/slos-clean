# How to Generate a Privacy Policy

## Overview

This guide shows how to generate a compliant privacy policy for your website using the Legal Document Generator.

## Prerequisites

- Legal Documents module enabled
- Company profile completed
- Admin access to WordPress

## Step 1: Complete Company Profile

Before generating documents, set up your company details:

1. Go to **SLOS** → **Legal Documents** → **Company Profile**
2. Fill in all fields:

**Required Information:**
- Company Name (legal name)
- Website URL
- Contact Email
- Business Address (street, city, state, zip, country)
- Phone Number
- Business Type (select from dropdown)
- Industry

**Optional Information:**
- Data Protection Officer email
- Company logo
- Registration number
- Business hours
- Privacy Officer

3. Click **Save Profile**

## Step 2: Generate New Privacy Policy

1. Go to **SLOS** → **Legal Documents** → **Generate New**
2. Select **Privacy Policy**
3. Choose template:
   - **Standard** - Works for most sites
   - **E-Commerce** - Includes payment data
   - **SaaS** - Includes user accounts
   - **Agency/Consultant** - Client data focus
   - **Publisher/Media** - Content user data

4. Choose language:
   - English (default)
   - Spanish
   - French
   - German
   - Portuguese
   - Italian
   - Dutch

5. Click **Generate**

## Step 3: Review Generated Content

The policy automatically includes:

### 1. Introduction
- Company name and contact
- Policy effective date
- Last updated date
- Statement of commitment to privacy

### 2. Information We Collect

**Automatically Populated:**
- Personal data types collected
- Collection methods (forms, cookies, tracking)
- Required vs optional data
- Special categories of data (if applicable)

**Includes:**
- Contact information (name, email, phone)
- Account data (username, password)
- Device information (IP, browser, OS)
- Behavioral data (pages visited, time spent)
- Cookie data (types, purposes, vendors)

### 3. How We Use Your Data

**Auto-included Based on:**
- Consent Management settings
- Cookie scanner results
- Analytics enabled
- Marketing tools
- Third-party integrations

**Common Sections:**
- Providing services
- Improving website
- Marketing purposes
- Legal compliance
- Fraud prevention
- Analytics

### 4. Legal Basis for Processing

**Automatically Includes:**
- Consent (cookie consent)
- Legitimate interest
- Contractual necessity
- Legal obligation
- Vital interests
- Public task

### 5. Data Retention

**Auto-filled with:**
- Retention periods per data type
- Consent record retention
- Deletion procedures
- Archival practices
- Legal hold exceptions

### 6. Your Rights

**Based on Regulations Selected:**

**GDPR (EU):**
- Right of access
- Right to rectification
- Right to erasure
- Right to restrict
- Right to portability
- Right to object
- Rights related to automated processing

**CCPA (California):**
- Right to know
- Right to delete
- Right to opt-out of sale
- Right to non-discrimination

**LGPD (Brazil):**
- Right to access
- Right to correction
- Right to deletion
- Right to portability
- Right to revoke consent

### 7. Cookie Policy Section

**Auto-populated with:**
- Cookie types detected by scanner
- Cookie vendors
- Cookie purposes
- Retention periods
- User controls

### 8. Third-Party Sharing

Lists any third parties:
- Analytics providers
- Marketing platforms
- Payment processors
- Hosting providers
- Integrations
- Data processors

### 9. Data Security

Describes:
- Encryption practices
- Access controls
- Security measures
- Breach notification
- Incident response

### 10. International Transfers

If applicable:
- EU adequacy decisions
- Standard contractual clauses
- Binding corporate rules
- Consent for transfers

### 11. Contact Information

**Auto-filled:**
- Contact email
- Mailing address
- Data Protection Officer (if provided)
- Response procedures
- SLA for responses

### 12. Cookie Consent Notice (if using Consent Management)

- Links to cookie banner
- Lists cookie types
- Explains purposes
- Links to preferences

## Step 4: Customize Policy

### Edit Sections

1. Click **Edit Policy**
2. Select section to edit:
   - Click section heading
   - Content becomes editable
   - Make changes
   - Save automatically

### Common Customizations

**Add Company-Specific Details:**
```
Example: "We use analytics to understand how customers use our 
e-commerce platform to improve product recommendations."
```

**Add Internal Policies:**
```
Example: "Our internal retention policy deletes inactive account data 
after 2 years of no login."
```

**Emphasize Security:**
```
Example: "We use industry-standard 256-bit encryption for all 
personal data in transit and at rest."
```

**Special Categories:**
```
If you process sensitive data:
- Health information
- Biometric data
- Racial or ethnic origin
- Political opinions
- Add to "Special Categories" section
```

### Add Variables

Use template variables:
- `{company_name}` - Auto-fills
- `{website_url}` - Auto-fills
- `{contact_email}` - Auto-fills
- `{dpo_email}` - DPO contact
- `{effective_date}` - Auto-fills
- `{data_retention_days}` - Your number

Example:
```
"As of {effective_date}, {company_name} (www.{website_url}) 
collects personal data to provide services. Contact us at {contact_email}."
```

## Step 5: Verify Compliance

### GDPR Checklist

If targeting EU visitors:
- ✓ Identifies company (controller)
- ✓ Lists lawful bases for processing
- ✓ Explains data subject rights
- ✓ Specifies retention periods
- ✓ Mentions data processor agreements
- ✓ Covers international transfers
- ✓ Includes breach notification process
- ✓ Mentions data protection authority
- ✓ Notes right to lodge complaints

### CCPA Checklist

If California visitors:
- ✓ States collection of personal information
- ✓ Lists business purposes
- ✓ Explains do not sell option
- ✓ Describes consumer rights
- ✓ Includes contact for requests
- ✓ References privacy practices
- ✓ Mentions shine the light compliance

### LGPD Checklist

If Brazilian visitors:
- ✓ Identifies data controller
- ✓ Specifies processing purposes
- ✓ Lists lawful bases
- ✓ Explains data subject rights
- ✓ Mentions data protection authority
- ✓ References retention period
- ✓ Covers international transfers

## Step 6: Get Legal Review

**IMPORTANT:** Before publishing:

1. Download draft (see Step 7)
2. Send to legal counsel
3. Have lawyer review for:
   - Jurisdiction compliance
   - Business accuracy
   - Completeness
   - Risk areas
4. Incorporate feedback
5. Get approval

## Step 7: Export or Publish

### Export Options

**Option 1: PDF Export**

1. Click **Export as PDF**
2. File downloads to computer
3. Share with legal team
4. Print if needed

**Option 2: HTML Export**

1. Click **Export as HTML**
2. Get HTML code
3. Paste into page editor
4. Customize formatting

**Option 3: Word Document**

1. Click **Export as DOCX**
2. Opens in Microsoft Word
3. Edit in Word
4. Add company branding
5. Print or PDF

### Publish to Website

**Option A: Auto-Create Page**

1. Click **Publish to Website**
2. Creates new page: "Privacy Policy"
3. Policy auto-published as draft
4. Review in page editor
5. Click Publish when ready

**Option B: Manual Page Creation**

1. Export as HTML
2. Create new WordPress page
3. Go to **Pages** → **Add New**
4. Title: "Privacy Policy"
5. Paste HTML into editor
6. Customize formatting
7. Publish

## Step 8: Create Privacy Policy Page

### Page Setup

1. **Page Title:** "Privacy Policy"
2. **URL Slug:** "/privacy-policy/"
3. **Status:** Published
4. **Visibility:** Public
5. **Menu:** Add to footer menu (optional)

### Page Content

1. Add policy content
2. Format with headings
3. Add company logo (optional)
4. Add last updated date
5. Add contact info
6. Save and publish

### Link from Banner

1. Go to **Consent Management** → **Settings**
2. In banner links section:
   - Privacy Policy URL: `/privacy-policy/`
3. Save settings
4. Banner now links to page

## Step 9: Keep Policy Current

### Set Reminders

1. Go to **Legal Documents** → **Policies** → **Edit**
2. Set **Review Date:** Annually
3. Reminders sent to admin email
4. Review and update as needed

### Version Control

Track changes:
1. **Major Update** - Change in practices
   - Increment major version
   - Mark as new "effective date"
   - Send notification to users

2. **Minor Update** - Clarification
   - Increment minor version
   - Update in place
   - Note change

### Documentation

Keep change log:
```
Version 1.2 - Dec 31, 2025
- Added CCPA section for California visitors
- Expanded cookie descriptions
- Added DSR request process

Version 1.1 - Jan 15, 2025
- Added AI/ML processing disclosure
- Clarified third-party sharing
- Updated security practices

Version 1.0 - Jan 1, 2025
- Initial policy creation
```

## Step 10: Notify Users (Optional)

### For Major Changes

1. If material changes made:
   - Send email to users
   - Post banner on site
   - Add notification in privacy settings

2. Timing:
   - Notify 15-30 days before effective
   - Request re-consent if using cookies
   - Document acknowledgment

## Troubleshooting

### Policy Missing Sections

**Solution:**
- Verify all company profile fields completed
- Re-generate policy
- Manually add missing sections
- Update company profile

### Formatting Issues

**Solution:**
- Export as HTML
- Check in page editor
- Fix formatting manually
- Re-save page

### Compliance Questions

**Solution:**
- Use verification checklists
- Consult legal resources
- Contact lawyer if uncertain
- Get professional review

## Best Practices

1. **Legal Review First** - Have lawyer review before publishing
2. **Keep Current** - Update annually minimum
3. **Version Control** - Track all changes
4. **Communicate Changes** - Notify users of major updates
5. **Archive Old Versions** - Keep historical copies
6. **Mobile Friendly** - Ensure readable on mobile
7. **Accessible** - Follow WCAG guidelines
8. **Plain Language** - Avoid legal jargon

## Next Steps

1. Complete company profile
2. Generate privacy policy
3. Customize for your business
4. Get legal review
5. Publish to website
6. Link from consent banner
7. Set annual review reminder

## Related Articles

- [Generate Cookie Policy](07-generate-cookie-policy.md)
- [Create Terms of Service](08-create-terms.md)
- [Accessibility Statement Generation](09-create-accessibility-statement.md)

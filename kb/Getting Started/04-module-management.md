# Module Management

## What are Modules?

Modules are independent, feature-complete components of Shahi LegalFlowSuite. Enable only the modules you need.

## Available Modules

1. **Consent Management** - Cookie consent and compliance
2. **Accessibility Scanner** - WCAG 2.1 AA scanning
3. **Legal Documents** - Document generation
4. **DSR Portal** - Data Subject Rights handling

## Enabling/Disabling Modules

### Via Admin Panel
1. Go to **SLOS** → **Settings** → **Modules**
2. Toggle each module on/off
3. Click **Save Changes**

### Module Toggle Details
Each module shows:
- **Status** - Enabled/Disabled indicator
- **Description** - What the module does
- **Features** - Key capabilities
- **Configuration** - Link to module settings
- **Documentation** - Link to help docs

## Module-Specific Settings

Each enabled module has its own settings page:

### Consent Management Settings
- Banner templates and customization
- Cookie scanner configuration
- Geo-targeting rules
- Analytics event tracking

### Accessibility Scanner Settings
- Auto-fix options
- Scanning schedule
- Report preferences
- Fixer configuration

### Legal Documents Settings
- Company profile
- Document templates
- PDF export options
- Version control

### DSR Portal Settings
- Portal visibility and access
- Email verification
- SLA configuration
- Data export format

## Database Impact

When you disable a module:
- ❌ Module features stop working
- ✓ Data remains in database
- ✓ Settings are preserved
- ✓ You can re-enable later

When you uninstall the plugin:
- ❌ Database tables are deleted
- ❌ All data is lost
- ℹ️ Use **Settings** → **Export Data** before uninstalling

## Performance

Disabled modules don't consume resources:
- No database queries
- No PHP code loaded
- No frontend assets
- Minimal admin memory overhead

## Best Practices

1. **Activate Only What You Need** - Reduces database load
2. **Test Before Disabling** - Ensure no dependent features
3. **Backup First** - Export data before major changes
4. **Enable Gradually** - Set up one module at a time
5. **Monitor Performance** - Check site speed after enabling

## Troubleshooting Module Issues

**Module won't enable?**
- Check database permissions
- Review error log
- Ensure compatibility with WordPress version

**Module data disappeared?**
- Data doesn't delete when module is disabled
- Check if module is actually disabled
- Verify database tables exist via phpMyAdmin

**Module causing performance issues?**
- Disable and test site speed
- Check for configuration conflicts
- Review database indexes

## Next Steps

After setting up modules:
1. Review each module's documentation
2. Configure module-specific settings
3. Test functionality thoroughly
4. Set up backups
5. Train team on usage

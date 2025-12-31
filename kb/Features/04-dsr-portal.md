# Data Subject Rights (DSR) Portal

## What is the DSR Portal?

The Data Subject Rights (DSR) Portal enables users to submit and manage requests for their personal data under GDPR, CCPA, LGPD, and other privacy regulations. Includes access, deletion, portability, and rectification requests with automated handling.

## Key Features

### 1. Request Types

**Access Request (GDPR Art. 15)**
- User requests copy of their personal data
- 30-day legal deadline
- Export in machine-readable format (JSON)
- Summary of data processing

**Deletion Request (GDPR Art. 17)**
- User requests erasure of personal data
- "Right to be forgotten"
- Applies unless legal obligation exists
- Confirmation sent upon completion

**Portability Request (GDPR Art. 20)**
- User requests data in transferable format
- JSON or CSV export
- Must include all personal data
- Sent within 30 days

**Rectification Request (GDPR Art. 16)**
- User requests correction of incorrect data
- Can update own information
- Reviewed by admin
- Confirmation sent

**Restriction Request**
- Request to restrict processing
- Data retained but not processed
- User notified when restriction lifted

### 2. Portal Features

**Public Submission Portal**
- Accessible from website footer or dedicated page
- No WordPress account required
- Privacy-first design
- Clear request instructions

**Email Verification**
- Verification email sent
- User confirms email address
- Security against fraudulent requests
- Token expires in 24 hours

**Request Tracking**
- Users track their request status
- Real-time status updates
- Estimated completion date
- Download exported data

**SLA Management**
- 30-day legal deadline (GDPR)
- Dashboard shows pending requests
- Auto-alerts when approaching deadline
- Priority indicators

**Admin Dashboard**
- View all DSR requests
- Filter by type and status
- Assign to team members
- Add internal notes
- Track completion

### 3. Data Export

**Supported Formats**
- **JSON** - Machine-readable, structured data
- **CSV** - Spreadsheet format
- **PDF** - Human-readable format
- **ZIP** - Compressed archive for large exports

**Included Data**
- User account information
- Comment history
- Transaction records
- Behavioral data (if applicable)
- Consent history
- Cookie consent records
- DSR request history

**Third-Party Data**
- Can include data from external services
- Via hook system for integration
- Centralized export with permission

### 4. Request Workflow

#### User Submits Request
1. Visit portal
2. Select request type
3. Enter email address
4. Provide identifier information
5. Submit request

#### Verification Email
1. Verification email sent
2. User clicks verification link
3. Portal generates unique request token
4. Admin notified

#### Admin Review
1. Admin reviews request
2. Verifies identity if needed
3. Collects personal data
4. Prepares export file
5. Sends to user

#### User Downloads
1. User receives download link
2. Link expires after 7 days
3. Data downloaded once (logged)
4. Request marked complete

### 5. Privacy & Security

**Data Protection**
- End-to-end encryption
- Secure storage
- No third-party exposure
- Local database only

**Identity Verification**
- Email verification required
- Optional additional verification
- Prevents unauthorized access
- Fraud detection

**Download Security**
- Unique download tokens
- Time-limited links (7 days)
- One-time download option
- IP address logging

**Audit Trail**
- All actions logged
- Request history
- Admin actions
- Download history

## Admin Dashboard

### DSR Request Management

Access via: **SLOS** → **DSR Portal** → **Requests**

Shows:
- **Pending Requests** - Awaiting action
- **In Progress** - Being processed
- **Completed** - Sent to user
- **Rejected** - Denied (with reason)
- **Expired** - No action taken

### Filters & Search
- Filter by request type
- Filter by status
- Search by email
- Date range filtering
- Priority sorting

### Request Details
- Request type
- Submission date
- Verification status
- Requester email
- Request description
- Attached documentation

### Bulk Actions
- Approve multiple requests
- Reject with template reason
- Change status in bulk
- Assign to staff member
- Add tags for organization

### Reporting

Generate reports showing:
- Total requests by type
- Average processing time
- Compliance rate
- Response times
- Request volume trends
- Denial reasons

## Configuration

### Portal Settings
1. Go to **SLOS** → **DSR Portal** → **Settings**
2. **Enable Portal** - Toggle on/off
3. **Portal URL** - Where to display
4. **Auto-Response Email** - Message sent on submission
5. **Admin Notification** - Who gets notified

### SLA Settings
- **Default Deadline** - 30 days (GDPR)
- **Custom Deadlines** - By regulation
- **Reminder Interval** - When to alert admin
- **Escalation** - Action if deadline approaching

### Data Export Settings
- **Default Format** - JSON, CSV, PDF
- **Export Method** - Manual or automatic
- **Include Third-Party** - Toggle third-party data
- **Encryption** - Encrypt exported files
- **Retention** - How long to keep exports

### Email Notifications
- **Auto-Response Email** - After submission
- **Verification Email** - Verification link
- **Status Update Email** - Status changes
- **Completion Email** - Data ready
- **Admin Email** - New request alert

## Privacy Compliance

### GDPR (EU)
- **Art. 15** - Access right supported
- **Art. 17** - Deletion right supported
- **Art. 20** - Portability right supported
- **30-Day Deadline** - Enforced with alerts

### CCPA (California)
- **Access Right** - Supported
- **Deletion Right** - Supported
- **Opt-Out Right** - Consumer disclosure
- **45-Day Deadline** - Configurable

### LGPD (Brazil)
- **Data Access** - Supported
- **Data Deletion** - Supported
- **Data Portability** - Supported
- **30-Day Deadline** - Configurable

## User Portal

### Public-Facing Portal

Accessible at: `/privacy-requests/` (configurable)

**Features:**
- Submit new request
- Track existing request
- Download exported data
- View request status
- Verify email
- Cancel request (if unprocessed)

**Design:**
- Mobile responsive
- Privacy-first styling
- Clear instructions
- Simple forms
- WCAG 2.1 AA accessible

## API Integration

### Hooks for Custom Data

Add custom data to exports via hooks:

```php
add_filter('slos_dsr_export_data', function($data) {
    // Add custom data sources
    $data['custom_data'] = get_custom_user_data($user_id);
    return $data;
});
```

### Third-Party Services

Collect data from connected services:
- Send export request to service
- Collect data in standard format
- Include in final export
- Manage via admin API

## Performance

Resource usage:
- CPU: <1% for portal operations
- Memory: 5MB per request
- Database: ~1MB per 100 requests
- Export generation: 2-10 seconds

## Scheduling

### Automatic Processing
- **Auto-Process Requests** - Toggle on/off
- **Process Time** - Admin approval required
- **Bulk Processing** - Process multiple at once
- **Scheduled Processing** - Nightly batch processing

## Deletion Fulfillment

When deletion request approved:
1. Collect all personal data
2. Generate backup/archive (compliance)
3. Delete from database
4. Delete from third-parties (if integrated)
5. Confirm deletion with user
6. Log deletion action

## Data Retention

**Request Records Retained:**
- Request metadata (type, date)
- Completion status
- User email (anonymized)
- Audit trail

**User Data Retained:**
- Deleted per user request
- Archive for compliance (30 days)
- Backup retention policy
- Legal hold exceptions

## Export Verification

Ensure exports are accurate:
1. **Completeness Check** - All data included
2. **Accuracy Review** - Data is correct
3. **Format Verification** - Proper format
4. **Security Verification** - Encryption working
5. **Delivery Test** - Download link works

## Best Practices

1. **Verify Identity** - Don't skip verification
2. **Document Everything** - Keep detailed audit trail
3. **Meet SLA** - Track and meet deadlines
4. **Secure Data** - Encrypt exports
5. **Archive Records** - Keep compliance archives
6. **Regular Testing** - Test portal functionality
7. **Staff Training** - Educate team on DSR

## Troubleshooting

**Portal not appearing?**
- Verify module enabled
- Check portal URL setting
- Clear WordPress cache
- Verify user permissions

**Email not sending?**
- Test SMTP configuration
- Check WordPress mail setup
- Review error logs
- Verify email provider

**Export not generating?**
- Check database permissions
- Verify PHP memory limit
- Check disk space
- Review error logs

## Next Steps

1. Enable DSR Portal module
2. Configure portal settings
3. Test submission process
4. Set up email notifications
5. Train staff on handling requests

## Support

For detailed guides:
- [Set Up DSR Portal](../How-tos/10-setup-dsr-portal.md)
- [Process DSR Requests](../How-tos/11-process-dsr-requests.md)
- [Export User Data](../How-tos/12-export-data.md)

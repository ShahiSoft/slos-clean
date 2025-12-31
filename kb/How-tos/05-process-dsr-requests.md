# How to Process DSR Requests

## Overview

This guide walks through the complete process of handling Data Subject Rights (DSR) requests from submission to completion.

## Request Types

### Access Request
User wants copy of their data
- 30-day legal deadline
- Export in machine-readable format
- Full scope of processing

### Deletion Request
User wants data erased
- "Right to be forgotten"
- Permanent deletion
- May have legal exceptions

### Portability Request
User wants data in transferable format
- JSON or CSV export
- Complete data transfer
- User can move to another service

### Rectification Request
User wants to correct incorrect data
- Update personal information
- Admin review required
- Limited scope

## Step 1: Request Submission

### User Submits Request

1. **User visits DSR Portal** at `/privacy-requests/`
2. **Selects request type:** Access, Delete, Portability, Rectification
3. **Enters email address:** Must match account or receive verification
4. **Provides details:**
   - Requestor name
   - Identifying information
   - Specific data requested
   - Explanation (optional)
5. **Submits request**

### Verification Email Sent

System automatically:
1. Sends verification email
2. Includes confirmation link
3. Link valid for 24 hours
4. Tokens are unique and expire

### User Verifies Identity

1. Opens email
2. Clicks verification link
3. System marks as verified
4. Shows request confirmation
5. Provides tracking reference

## Step 2: Admin Notification

### Admin Dashboard Alert

1. **New request notification** sent to admin email
2. **SLOS Dashboard** shows new request badge
3. **Pending Requests** queue updated
4. **Email contains:**
   - Request type
   - Requester email
   - Submission time
   - Link to review

### Check Request Details

1. Go to **SLOS** → **DSR Portal** → **Requests**
2. Click **Pending** tab
3. Locate request in list
4. Click to open details
5. Review all information

**Request Details Show:**
- Request type (access, deletion, etc.)
- Requester email and name
- Submission time
- Verification status
- Deadline (30 days or custom)
- Days remaining
- Priority level (auto-calculated)
- Assigned user

## Step 3: Verification & Assignment

### Verify Requester Identity

1. Open request details
2. Scroll to **Verification Status**
3. Review verification:
   - ✓ Email verified (automatic)
   - ⊗ Manual verification needed (optional)

For high-risk requests:

1. Add **Internal Notes:**
   ```
   Additional verification completed via phone.
   Confirmed identity with last 4 digits of SSN: 1234
   ```
2. Update status to verified
3. Mark complete date

### Assign to Team Member

1. Click **Assign To** dropdown
2. Select team member responsible
3. Choose from admin users
4. Can reassign later if needed
5. Assignee receives notification

### Set Priority

1. **Auto-priority** based on:
   - Request type
   - Deadline urgency
   - Requester profile
   - Special notes

2. **Manual priority:**
   - Click **Priority** dropdown
   - Select: High, Normal, Low
   - High priority shows first in queue

## Step 4: Data Collection

### Automatic Collection

For most requests, data auto-collects from:
- User account data
- Post history
- Comment history
- Metadata
- Consent records
- Scan results

### Manual Data Identification

For complex requests:

1. **Identify data sources:**
   - User profile
   - Posts/pages authored
   - Comments
   - Custom fields
   - Media files
   - Transactions
   - Support tickets
   - Analytics records
   - Third-party services

2. **Check external sources:**
   - CRM systems
   - Email services
   - Analytics platforms
   - Payment processors
   - Backup storage

### Collect Third-Party Data

If you use external services:

1. Go to **DSR Portal** → **Settings**
2. Enable **Third-Party Integration**
3. For each integrated service:
   - Send data request
   - Collect response
   - Include in export

## Step 5: Process Request

### For Access Requests

1. **Collect all data:**
   - WordPress user data
   - Post history
   - Comments
   - Site analytics
   - Tracking records
   - Consent history

2. **Prepare export:**
   - Select format (JSON, CSV, PDF)
   - Verify completeness
   - Test download link
   - Ensure readability

3. **Review for accuracy:**
   - Verify all data correct
   - Check no sensitive internal notes
   - Confirm personal data complete

4. **Mark as ready:**
   - Change status to "Ready for Export"
   - Generate download link
   - Set expiration (7 days recommended)
   - Send to requester

### For Deletion Requests

⚠️ **IMPORTANT: Follow careful process**

1. **Create backup:**
   - Export user data before deletion
   - Store in secure location
   - Document timestamp
   - Keep for compliance (30 days minimum)

2. **Review for exceptions:**
   - Legal obligations (taxes, contracts)?
   - Active disputes?
   - Ongoing litigation?
   - Regulatory holds?
   - Document any exceptions

3. **Prepare deletion:**
   - List data to be deleted
   - Verify scope with admin/legal
   - Check for dependencies
   - Plan deletion sequence

4. **Execute deletion:**
   - Delete user account (soft delete first)
   - Delete authored content (if approved)
   - Delete comments
   - Delete personal data
   - Clear tracking cookies
   - Update third-party services

5. **Confirm deletion:**
   - Document completion time
   - Verify data gone (spot check)
   - Archive backup
   - Send confirmation email
   - Update request status

### For Portability Requests

1. **Collect all data:**
   - Same as access request
   - Comprehensive data gather
   - Include third-party data
   - All formats possible

2. **Format for transfer:**
   - Use standard format (JSON preferred)
   - Portable structure
   - Complete metadata
   - Clear documentation

3. **Verify completeness:**
   - All user data included
   - Accurate and current
   - Properly formatted
   - Can be imported elsewhere

4. **Prepare export:**
   - Create ZIP file
   - Include all formats
   - Add documentation
   - Secure transfer method

### For Rectification Requests

1. **Review correction request:**
   - What data is incorrect?
   - What should it be?
   - Request evidence?

2. **Verify accuracy:**
   - Is request valid?
   - Have records?
   - Can we verify?

3. **Make correction:**
   - Update user profile
   - Update metadata
   - Log change
   - Document date/time

4. **Notify requester:**
   - Send confirmation
   - Show updated data
   - Explain changes made
   - Ask for verification

## Step 6: Quality Assurance

### Review Export Before Sending

1. **Completeness Check:**
   - All categories included
   - No data truncated
   - All attachments present
   - Complete records

2. **Accuracy Check:**
   - Data is current
   - No errors
   - Dates correct
   - No sensitive internal info

3. **Privacy Check:**
   - No internal notes exposed
   - No other users' data included
   - No business secrets
   - Only user's own data

4. **Format Check:**
   - Proper formatting
   - File not corrupted
   - Readable structure
   - Test download

### Test Download Link

1. Copy download link
2. Open in incognito browser
3. Verify download works
4. Check file size reasonable
5. Verify content complete

## Step 7: Send to Requester

### Generate Download Link

1. Go to request details
2. Click **Generate Download**
3. System creates unique link
4. Link expires in 7 days
5. One-time download option available

### Send Notification Email

1. Click **Send Download Notification**
2. System sends automated email with:
   - Unique download link
   - Instructions for accessing data
   - Link expiration date
   - Contact info for questions
   - Confirmation of completion
   - SLA confirmation

### Document Completion

1. Update **Request Status** to "Completed"
2. Add **Completion Notes:**
   ```
   Export generated 2025-01-15
   Format: JSON
   Size: 2.4MB
   Download notified via email
   ```
3. Record **Completion Date**
4. Save changes

## Step 8: Compliance Documentation

### Keep Audit Trail

1. **Document everything:**
   - Request date
   - Requester identity
   - Verification method
   - Data collected
   - Completion date
   - Export method
   - Notification sent
   - Download status

2. **SLOS auto-documents:**
   - All actions logged
   - Timestamps recorded
   - User actions tracked
   - Email copies saved

### Generate Compliance Report

1. Go to **SLOS** → **DSR Portal** → **Reports**
2. Select date range
3. Choose report type:
   - All Requests
   - Response Times
   - SLA Compliance
   - By Request Type
   - By Status

4. Download PDF/CSV

### SLA Compliance

Monitor 30-day deadline:

1. **Dashboard shows:**
   - Days remaining
   - Color coded urgency
   - Approaching deadline alerts
   - Overdue flags

2. **Alerts:**
   - 7 days before deadline
   - 1 day before deadline
   - After deadline (escalation)

## Step 9: Follow-Up

### After Completion

1. **Check for follow-up:**
   - User has 7 days to download
   - Monitor download status
   - Follow up if not downloaded

2. **Archive:**
   - Move to completed folder
   - Keep records for audit
   - Annual retention review
   - Delete per policy

### Deletion Request Special Follow-Up

1. **30-day hold:**
   - Keep backup 30 days
   - Verify deletion complete
   - Check no data reappears

2. **Spot checks:**
   - Verify user can't login
   - Verify profile deleted
   - Verify data not accessible
   - Check third-parties removed

3. **Final deletion:**
   - After 30 days delete backup
   - Document final deletion
   - Update status to "Permanently Deleted"
   - Archive record

## Troubleshooting

### Data Not Exporting

1. Check file permissions
2. Verify disk space
3. Review error log
4. Increase PHP memory
5. Try smaller export first

### Email Not Sending

1. Verify SMTP working
2. Check email address valid
3. Look for bounces
4. Add to allowed list
5. Test mail function

### User Can't Download

1. Verify link not expired
2. Check IP not blocked
3. Test link yourself
4. Resend with new link
5. Offer manual delivery

### SLA Deadline Approaching

1. Prioritize request
2. Assign to senior staff
3. Expedite data collection
4. Request extension if necessary
5. Document any delays

## Best Practices

1. **Act Promptly** - Start within 24 hours
2. **Stay Organized** - Use SLOS tracking
3. **Document Well** - Detailed audit trail
4. **Verify Data** - Quality assurance check
5. **Secure Delivery** - Protect data in transit
6. **Meet SLA** - 30-day deadline (GDPR)
7. **Train Staff** - Team knows process
8. **Regular Audits** - Review quarterly

## Legal Considerations

⚠️ **Important:**
- Consult with legal team
- Understand applicable laws
- Document all decisions
- Exceptions require justification
- Follow regulatory guidance
- Have retention policy

## Next Steps

1. Set up DSR Portal
2. Train team on procedures
3. Create documentation
4. Test with sample requests
5. Monitor SLA compliance
6. Adjust as needed

## Related Articles

- [Setup DSR Portal](05-setup-dsr-portal.md)
- [Export User Data](06-export-data.md)
- [Privacy Compliance](../Features/04-dsr-portal.md)

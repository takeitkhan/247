# Withdrawal System Update - Deployment Checklist

## Pre-Deployment Checklist

- [ ] **Code Changes Reviewed**
  - [ ] PayPalAPI.php checked for balance refund logic
  - [ ] PayoutSystem.php checked for status update enhancement
  - [ ] withdrawal-form.php checked for UI changes
  - [ ] payout-balance.php reviewed

- [ ] **Database Verified**
  - [ ] `wp_withdrawal_requests` table has `admin_notes` column
  - [ ] `wp_payout_audit_log` table exists
  - [ ] Backups taken

- [ ] **Backup Created**
  - [ ] Database backup taken
  - [ ] File backups taken (PayPalAPI.php, PayoutSystem.php)
  - [ ] Backup location noted: `__________________`

## Deployment Steps

### Step 1: Upload Files
```bash
Upload the modified files to production:
/wp-content/themes/247empowerment/inc/PayPalAPI.php
/wp-content/themes/247empowerment/inc/PayoutSystem.php
/wp-content/themes/247empowerment/template-custom/frontend/withdrawal-form.php
/wp-content/themes/247empowerment/inc/payout-balance.php
```

- [ ] PayPalAPI.php uploaded
- [ ] PayoutSystem.php uploaded
- [ ] withdrawal-form.php uploaded
- [ ] payout-balance.php uploaded

### Step 2: Verify Permissions
```bash
ls -la /wp-content/themes/247empowerment/inc/PayPalAPI.php
# Should be readable and writable by web server
```

- [ ] File permissions verified (644 or 755)
- [ ] Web server can read files

### Step 3: Test System Functionality

#### Test 1: Admin Dashboard Access
```
Go to: Admin Dashboard → Payouts → Withdrawal Requests
```

- [ ] Admin page loads without errors
- [ ] Withdrawal list displays correctly
- [ ] New "Details" column visible for failed withdrawals

#### Test 2: Create Test Withdrawal
```
User-side: Request small withdrawal (e.g., $0.25)
Admin-side: Click "Approve" button
```

- [ ] Withdrawal can be submitted
- [ ] Admin approval works
- [ ] Withdrawal list updates

#### Test 3: Simulate PayPal Error (Optional)
```php
// Temporarily modify PayPalAPI.php to return an error
// Then approve a withdrawal
// Expected: Status becomes "failed", balance is restored
```

- [ ] Error handling works
- [ ] Balance is refunded
- [ ] Error message is stored
- [ ] Admin and user can see error details

#### Test 4: Frontend User View
```
Go to: Withdrawal Request page (user-facing)
```

- [ ] Balance displays correctly
- [ ] Recent withdrawals table shows all fields
- [ ] Failed withdrawal shows "Failed - Click for details"
- [ ] Clicking reveals error reason

### Step 4: Check Log Files
```bash
# Check WordPress debug log
tail -f /wp-content/debug.log
```

- [ ] No fatal errors in debug log
- [ ] Withdrawal actions logged correctly
- [ ] Balance refund logged with reason

### Step 5: Database Verification
```sql
-- Check a failed withdrawal record
SELECT id, status, admin_notes, transaction_id 
FROM wp_withdrawal_requests 
WHERE status = 'failed' 
ORDER BY created_at DESC LIMIT 1;
```

- [ ] Failed withdrawals have `admin_notes` populated
- [ ] Successful withdrawals have `transaction_id` populated
- [ ] No NULL status or invalid data

### Step 6: Audit Logs Check
```sql
-- Check audit trail
SELECT * FROM wp_payout_audit_log 
ORDER BY created_at DESC LIMIT 10;
```

- [ ] Audit entries created for all actions
- [ ] Error details logged when failures occur
- [ ] Timestamps are accurate

## Post-Deployment Verification

### User-Facing Changes
- [ ] Users can see withdrawal history
- [ ] Failed withdrawals show error details when clicked
- [ ] Message "Your balance has been restored" displays
- [ ] Balance updates correctly

### Admin-Facing Changes
- [ ] Admin can see error reasons for failed withdrawals
- [ ] Admin can see transaction IDs for successful withdrawals
- [ ] Details column shows appropriate information
- [ ] Admin buttons work correctly

### System Health
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug log
- [ ] Database queries execute without errors
- [ ] Email notifications sent (if configured)

## Rollback Plan

If critical issues occur, follow these steps:

### Option 1: Restore from Backup
```bash
# Restore files from backup
cp backup/PayPalAPI.php /wp-content/themes/247empowerment/inc/
cp backup/PayoutSystem.php /wp-content/themes/247empowerment/inc/
cp backup/withdrawal-form.php /wp-content/themes/247empowerment/template-custom/frontend/

# Restore database if schema was modified
mysql -u username -p database_name < backup/database.sql
```

### Option 2: Manual File Revert
```bash
# Using git
git revert HEAD

# Or manually restore old versions
```

### Verification After Rollback
```
1. Clear browser cache
2. Verify admin page loads
3. Check withdrawal system still functions
4. Review debug logs
```

---

## Performance Considerations

### Test Response Times
- [ ] Withdrawal approval response time < 2 seconds
- [ ] Balance refund response time < 1 second
- [ ] Database query performance acceptable

### Optional Optimization
```sql
-- Add indexes for frequently queried fields
ALTER TABLE wp_withdrawal_requests ADD INDEX idx_user_status (user_id, status);
ALTER TABLE wp_withdrawal_requests ADD INDEX idx_status (status);
```

---

## Monitoring Setup

### Key Metrics to Monitor
```
1. Failed withdrawal rate:
   SELECT COUNT(*) FROM wp_withdrawal_requests 
   WHERE status = 'failed' AND DATE(created_at) = CURDATE();

2. Average processing time:
   SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) 
   FROM wp_withdrawal_requests WHERE status = 'paid';

3. Balance accuracy:
   Verify sum of all user balances matches total referral_commission
```

### Alert Setup
- [ ] Set alert if failed withdrawal count > 5 per day
- [ ] Set alert if processing time > 5 minutes
- [ ] Set alert if database backup fails

---

## Documentation Updates

- [ ] README updated with new features
- [ ] CHANGELOG updated with fix details
- [ ] Support documentation updated
- [ ] FAQ updated with failure reason explanations

---

## Stakeholder Notification

### Internal Team
- [ ] Developers notified of changes
- [ ] Support team trained on new error messages
- [ ] Admin users trained on new Details column

### External Communication
- [ ] Consider notifying users about the fix
- [ ] Update support pages with troubleshooting guide

---

## Sign-Off

- [ ] **Developer**: __________________ Date: __________
- [ ] **QA Tester**: _________________ Date: __________
- [ ] **DevOps**: __________________ Date: __________
- [ ] **Project Manager**: __________ Date: __________

---

## Deployment Timeline

| Step | Estimated Time | Actual Time | Status |
|------|----------------|-------------|--------|
| File upload | 15 min | ____ | __ |
| Testing | 30 min | ____ | __ |
| Database check | 10 min | ____ | __ |
| Monitoring | 15 min | ____ | __ |
| Documentation | 20 min | ____ | __ |
| **Total** | **90 min** | ______ | __ |

---

## Support Contacts

For deployment issues:
- **Primary**: [Development Lead Email]
- **Secondary**: [DevOps Email]
- **Emergency**: [Manager Email]

---

## Common Issues & Solutions

### Issue 1: "admin_notes column not found"
**Solution:** Verify column exists:
```sql
DESC wp_withdrawal_requests;
```
If missing, add it:
```sql
ALTER TABLE wp_withdrawal_requests ADD COLUMN admin_notes LONGTEXT DEFAULT NULL;
```

### Issue 2: Balance not refunding
**Solution:** Verify `payout_refund_withdrawal()` is called in PayPalAPI.php around line 167.

### Issue 3: Error details not showing
**Solution:** Clear WordPress object cache:
```php
wp_cache_flush();
```

### Issue 4: Admin page slow
**Solution:** Add database indexes:
```sql
CREATE INDEX idx_user_status ON wp_withdrawal_requests(user_id, status);
CREATE INDEX idx_status ON wp_withdrawal_requests(status);
```

---

## Success Criteria

Deployment is successful when:

✅ Admin can see withdrawal list without errors  
✅ Failed withdrawals display error reasons  
✅ User balance is automatically restored on failure  
✅ Audit logs record all transactions  
✅ No errors in WordPress debug log  
✅ All database queries execute correctly  
✅ System processes withdrawals reliably  

---

## Post-Deployment Support

After successful deployment:

1. Monitor for 24-48 hours for any issues
2. Collect feedback from admin and support team
3. Document any edge cases discovered
4. Plan for optional enhancements (email notifications, retry mechanism)

See related documentation:
- [WITHDRAWAL_SOLUTION_SUMMARY.md](WITHDRAWAL_SOLUTION_SUMMARY.md)
- [WITHDRAWAL_ISSUE_FIX.md](WITHDRAWAL_ISSUE_FIX.md)
- [WITHDRAWAL_DEVELOPER_GUIDE.md](WITHDRAWAL_DEVELOPER_GUIDE.md)

# pfSense CIDR Calculator - Test Cases

<!--------------------------------------------------------------------------
-                                                                          -
-       888888    888888              88     888888                        -
-         88      88                  88     88   oo                       -
-         88      88                  88     88                            -
-         88      8888 .d8b.   .d8b.  88     8888 88 8888b.  .d8b.         -
-         88      88  d8P Y8b d8P Y8b 88     88   88 88  8b d8P Y8b        -
-         88      88  8888888 8888888 88     88   88 88  88 8888888        -
-         88      88  Y8b.    Y8b.    88     88   88 88  88 Y8b.           -
-       888888    88   ºY888P  ºY888P 88     88   88 88  88  ºY888P        -
-                                                                          -
-                                               (c) 2025 I Feel Fine, Inc. -
----------------------------------------------------------------------------
- Licensed under the Apache License, Version 2.0 (the "License");          -
- you may not use this file except in compliance with the License.         -
- You may obtain a copy of the License at                                  -
-                                                                          -
-     http://www.apache.org/licenses/LICENSE-2.0                           -
-                                                                          -
- Unless required by applicable law or agreed to in writing, software      -
- distributed under the License is distributed on an "AS IS" BASIS,        -
- WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. -
- See the License for the specific language governing permissions and      -
- limitations under the License.                                           -
--------------------------------------------------------------------------->

## Manual Test Procedures

### Test Environment Setup

**Prerequisites:**
- pfSense 2.7.0 or later installed
- Admin access to pfSense web interface
- Package installed and accessible

---

## Category 1: IPv4 Calculator Tests

### Test 1.1: Basic IPv4 Calculation
**Objective:** Verify correct calculation of standard /24 network

**Steps:**
1. Navigate to **Diagnostics > CIDR Calculator**
2. Ensure **IPv4** tab is selected
3. Enter IP: `192.168.1.100`
4. Select CIDR: `24`

**Expected Results:**
- Network Address: `192.168.1.0`
- CIDR Route: `192.168.1.0/24`
- Subnet Range: `192.168.1.0 - 192.168.1.255`
- Wildcard Mask: `0.0.0.255`
- Broadcast Address: `192.168.1.255`
- Assignable Addresses: `192.168.1.1 - 192.168.1.254 (254 hosts)`
- Maximum Subnets: `16,777,216`
- Maximum Addresses: `256`

**Status:** ☐ Pass ☐ Fail

---

### Test 1.2: /32 Network (Single Host)
**Objective:** Verify special handling for /32 networks

**Steps:**
1. Enter IP: `10.0.0.1`
2. Select CIDR: `32`

**Expected Results:**
- Network Address: `10.0.0.1`
- Broadcast Address: `10.0.0.1`
- Assignable Addresses: `10.0.0.1 - 10.0.0.1 (1 hosts)`
- Maximum Addresses: `1`

**Status:** ☐ Pass ☐ Fail

---

### Test 1.3: /31 Network (Point-to-Point)
**Objective:** Verify RFC 3021 /31 handling

**Steps:**
1. Enter IP: `192.168.1.0`
2. Select CIDR: `31`

**Expected Results:**
- Network Address: `192.168.1.0`
- Broadcast Address: `192.168.1.1`
- Assignable Addresses: `192.168.1.0 - 192.168.1.1 (2 hosts)`
- Maximum Addresses: `2`

**Status:** ☐ Pass ☐ Fail

---

### Test 1.4: Large Network (/8)
**Objective:** Verify calculations for large networks

**Steps:**
1. Enter IP: `10.0.0.0`
2. Select CIDR: `8`

**Expected Results:**
- Network Address: `10.0.0.0`
- Subnet Mask: `255.0.0.0`
- Wildcard Mask: `0.255.255.255`
- Broadcast Address: `10.255.255.255`
- Assignable Addresses: `10.0.0.1 - 10.255.255.254 (16,777,214 hosts)`
- Maximum Addresses: `16,777,216`

**Status:** ☐ Pass ☐ Fail

---

### Test 1.5: Invalid IPv4 Input
**Objective:** Verify input validation

**Test Cases:**
1. Enter IP: `999.999.999.999` → Should normalize to `255.255.255.255`
2. Enter IP: `abc.def.ghi.jkl` → Should clear results
3. Enter IP: `192.168.1` → Should clear results (incomplete)
4. Enter IP: `192.168.1.1.1` → Should clear results (too many octets)

**Expected:** Invalid inputs should either normalize or clear results without errors

**Status:** ☐ Pass ☐ Fail

---

### Test 1.6: Subnet Mask Selector Sync
**Objective:** Verify dropdown synchronization

**Steps:**
1. Select CIDR: `16`
2. Observe Subnet Mask dropdown

**Expected:** Automatically selects `255.255.0.0`

**Steps:**
1. Change Subnet Mask to: `255.255.255.192`
2. Observe CIDR dropdown

**Expected:** Automatically changes to `26`

**Status:** ☐ Pass ☐ Fail

---

### Test 1.7: Real-time Calculation
**Objective:** Verify responsive updates

**Steps:**
1. Enter IP: `172.16.0.0`
2. Change CIDR from `12` to `16` to `20` to `24`

**Expected:** Results update immediately on each change without clicking any button

**Status:** ☐ Pass ☐ Fail

---

## Category 2: IPv6 Calculator Tests

### Test 2.1: Basic IPv6 Calculation
**Objective:** Verify IPv6 expansion and calculation

**Steps:**
1. Click **IPv6** tab
2. Enter Address: `2001:db8::`
3. Select Prefix: `64`

**Expected Results:**
- Full Address: `2001:0db8:0000:0000:0000:0000:0000:0000`
- Network Address: Starts with `2001:0db8:0000:0000:`
- /64 Subnets Available: `1`

**Status:** ☐ Pass ☐ Fail

---

### Test 2.2: IPv6 Short Form Expansion
**Objective:** Verify compressed address expansion

**Test Cases:**
1. `::1` → `0000:0000:0000:0000:0000:0000:0000:0001`
2. `2001:db8::1` → `2001:0db8:0000:0000:0000:0000:0000:0001`
3. `fe80::` → `fe80:0000:0000:0000:0000:0000:0000:0000`

**Status:** ☐ Pass ☐ Fail

---

### Test 2.3: /64 Subnet Calculation
**Objective:** Verify subnet count logic

**Test Cases:**
1. Prefix `48` → /64 Subnets: `65,536`
2. Prefix `56` → /64 Subnets: `256`
3. Prefix `60` → /64 Subnets: `16`
4. Prefix `64` → /64 Subnets: `1`
5. Prefix `80` → /64 Subnets: `1`
6. Prefix `128` → /64 Subnets: `1`

**Status:** ☐ Pass ☐ Fail

---

### Test 2.4: Invalid IPv6 Input
**Objective:** Verify IPv6 validation

**Test Cases:**
1. `gggg::1` → Should show "Invalid IPv6 address format"
2. `2001:db8:::1` → Should show error
3. `192.168.1.1` → Should show error
4. Empty field → Should clear results

**Status:** ☐ Pass ☐ Fail

---

### Test 2.5: IPv6 Real-time Updates
**Objective:** Verify responsive calculation

**Steps:**
1. Enter: `2001:db8::`
2. Change prefix from `48` to `56` to `64` to `80`

**Expected:** Results update on each change

**Status:** ☐ Pass ☐ Fail

---

## Category 3: Dashboard Widget Tests

### Test 3.1: Widget Installation
**Objective:** Verify widget appears in dashboard

**Steps:**
1. Navigate to **Dashboard**
2. Click **+** (Add Widget)
3. Look for "CIDR Calculator" in list

**Expected:** Widget available in list

**Status:** ☐ Pass ☐ Fail

---

### Test 3.2: Widget IPv4 Calculation
**Objective:** Verify widget IPv4 functionality

**Steps:**
1. Add CIDR Calculator widget to dashboard
2. Ensure IPv4 checkbox is checked
3. Enter: `10.0.0.0/8`

**Expected Results:**
- Network: `10.0.0.0/8`
- Mask: `255.0.0.0`
- Range: `10.0.0.1 - 10.255.255.254`
- Broadcast: `10.255.255.255`
- Usable Hosts: `16,777,214`

**Status:** ☐ Pass ☐ Fail

---

### Test 3.3: Widget IPv6 Calculation
**Objective:** Verify widget IPv6 functionality

**Steps:**
1. Ensure IPv6 checkbox is checked
2. Enter Address: `2001:db8::`
3. Select Prefix: `48`

**Expected Results:**
- Full: `2001:0db8:0000:0000:0000:0000:0000:0000`
- Network: Calculated correctly
- /64 Subnets: `65,536`

**Status:** ☐ Pass ☐ Fail

---

### Test 3.4: Widget Toggle Functionality
**Objective:** Verify show/hide toggles work

**Steps:**
1. Uncheck IPv4 checkbox
2. Verify IPv4 section disappears
3. Uncheck IPv6 checkbox
4. Verify IPv6 section disappears
5. Refresh dashboard
6. Verify settings persist

**Expected:** Sections hide/show correctly and preferences persist

**Status:** ☐ Pass ☐ Fail

---

### Test 3.5: Widget Title Link
**Objective:** Verify widget title links to main page

**Steps:**
1. Click widget title "CIDR Calculator"

**Expected:** Redirects to `/diagnostics_cidr_calculator.php`

**Status:** ☐ Pass ☐ Fail

---

### Test 3.6: Widget Error Handling
**Objective:** Verify widget error messages

**Test Cases:**
1. IPv4: Enter `invalid/input` → Shows error message
2. IPv6: Enter `invalid::address::` → Shows error message

**Expected:** User-friendly error messages in red text

**Status:** ☐ Pass ☐ Fail

---

## Category 4: Privilege & Security Tests

### Test 4.1: Dashboard Access Privilege
**Objective:** Verify access control

**Steps:**
1. Create test user with **WebCfg - Dashboard (all)** privilege only
2. Log in as test user
3. Navigate to **Diagnostics > CIDR Calculator**

**Expected:** User can access calculator

**Status:** ☐ Pass ☐ Fail

---

### Test 4.2: No Dashboard Privilege
**Objective:** Verify access denial

**Steps:**
1. Create test user without dashboard privilege
2. Log in as test user
3. Attempt to access `/diagnostics_cidr_calculator.php`

**Expected:** Access denied

**Status:** ☐ Pass ☐ Fail

---

### Test 4.3: XSS Prevention
**Objective:** Verify no script injection

**Test Cases:**
1. IPv4: Enter `<script>alert('xss')</script>` as IP
2. Widget: Enter `192.168.1.1/<img src=x onerror=alert(1)>`

**Expected:** No script execution, input sanitized or rejected

**Status:** ☐ Pass ☐ Fail

---

## Category 5: Cross-Browser Tests

### Test 5.1: Browser Compatibility
**Objective:** Verify functionality across browsers

**Browsers to Test:**
- ☐ Firefox (latest)
- ☐ Chrome (latest)
- ☐ Safari (latest)
- ☐ Edge (latest)

**Test:** Perform Tests 1.1, 2.1, 3.2 in each browser

**Expected:** Full functionality in all browsers

---

## Category 6: Installation/Uninstallation Tests

### Test 6.1: Clean Installation
**Objective:** Verify package installs correctly

**Steps:**
1. Install package via Package Manager
2. Check files exist:
   - `/usr/local/www/diagnostics_cidr_calculator.php`
   - `/usr/local/www/widgets/cidr_calculator.widget.php`
   - `/etc/inc/priv/cidr_calc.inc`
   - `/usr/local/pkg/cidr_calc.inc`
   - `/usr/local/pkg/cidr_calc.xml`
3. Verify menu entry appears in Diagnostics
4. Verify widget available on dashboard

**Expected:** All files present, menu entry visible, widget available

**Status:** ☐ Pass ☐ Fail

---

### Test 6.2: Clean Uninstallation
**Objective:** Verify package removes cleanly

**Steps:**
1. Uninstall package
2. Verify files removed:
   - `/usr/local/www/diagnostics_cidr_calculator.php`
   - `/usr/local/www/widgets/cidr_calculator.widget.php`
3. Verify privilege entries removed
4. Verify widget configuration removed from config.xml

**Expected:** All package files removed, no orphaned configuration

**Status:** ☐ Pass ☐ Fail

---

## Test Summary

**Total Tests:** 28  
**Passed:** ___  
**Failed:** ___  
**Skipped:** ___  

**Tested By:** ___________________  
**Date:** ___________________  
**pfSense Version:** ___________________  
**Package Version:** ___________________  

**Notes:**

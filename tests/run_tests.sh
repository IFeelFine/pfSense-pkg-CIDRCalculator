#!/bin/bash
#
# Automated test suite for pfSense-pkg-CIDRCalculator
# Run this before submitting pull requests or creating releases

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "======================================"
echo "pfSense CIDR Calculator Test Suite"
echo "======================================"
echo ""

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Helper functions
pass() {
	echo "${GREEN}✓${NC} $1"
	TESTS_PASSED=$((TESTS_PASSED + 1))
}

fail() {
	echo "${RED}✗${NC} $1"
	TESTS_FAILED=$((TESTS_FAILED + 1))
}

warn() {
	echo "${YELLOW}⚠${NC} $1"
}

# ===========================================
# Test 1: Check Required Files Exist
# ===========================================
echo "Test 1: Checking required files..."

required_files=(
	"Makefile"
	"pkg-desc"
	"pkg-plist"
	"files/pkg-install.in"
	"files/pkg-deinstall.in"
	"files/etc/inc/priv/cidr_calc.priv.inc"
	"files/usr/local/pkg/cidr_calc.inc"
	"files/usr/local/pkg/cidr_calc.xml"
	"files/usr/local/share/pfSense-pkg-CIDRCalculator/info.xml"
	"files/usr/local/www/diag_cidr_calculator.php"
	"files/usr/local/www/widgets/widgets/cidr_calculator.widget.php"
)

for file in "${required_files[@]}"; do
	if [ -f "$file" ]; then
		pass "Found: $file"
	else
		fail "Missing: $file"
	fi
done

echo ""

# ===========================================
# Test 2: Validate PHP Syntax
# ===========================================
echo "Test 2: Validating PHP syntax..."

if ! command -v php > /dev/null 2>&1; then
	warn "PHP not installed - skipping PHP syntax validation"
else
	find files -name "*.php" -o -name "*.inc" | while read -r file; do
		if php -l "$file" > /dev/null 2>&1; then
			pass "PHP syntax valid: $file"
		else
			fail "PHP syntax error: $file"
			php -l "$file"
		fi
	done
fi

echo ""

# ===========================================
# Test 3: Validate XML Syntax
# ===========================================
echo "Test 3: Validating XML syntax..."

if ! command -v xmllint > /dev/null 2>&1; then
	warn "xmllint not installed - skipping XML validation"
else
	find files -name "*.xml" | while read -r file; do
		if xmllint --noout "$file" 2>&1; then
			pass "XML valid: $file"
		else
			fail "XML invalid: $file"
		fi
	done
fi

echo ""

# ===========================================
# Test 4: Check for Security Anti-Patterns
# ===========================================
echo "Test 4: Scanning for security issues..."

# Check for eval()
if grep -rn "eval(" files/ 2>/dev/null; then
	fail "Found eval() usage (security risk)"
else
	pass "No eval() usage found"
fi

# Check for system()
if grep -rn "system(" files/ --include="*.php" --include="*.inc" 2>/dev/null; then
	fail "Found system() calls (review needed)"
else
	pass "No system() calls found"
fi

# Check for shell_exec()
if grep -rn "shell_exec(" files/ --include="*.php" --include="*.inc" 2>/dev/null; then
	fail "Found shell_exec() calls (review needed)"
else
	pass "No shell_exec() calls found"
fi

# Check for unescaped HTML output
if grep -rn "echo.*\$_" files/ --include="*.php" | grep -v "htmlspecialchars\|gettext"; then
	warn "Potential unescaped output found (review manually)"
else
	pass "No obvious unescaped output"
fi

echo ""

# ===========================================
# Test 5: Validate pkg-plist Format
# ===========================================
echo "Test 5: Validating pkg-plist..."

# Check 1: Find absolute paths (starting with /) except for etc/
# Note: pkg-plist uses relative paths, but etc/ is relative to system root
invalid_paths=$(grep "^/" pkg-plist 2>/dev/null | grep -v "^/etc/" || true)
if [ -n "$invalid_paths" ]; then
	fail "Absolute paths found in pkg-plist (only etc/ allowed to start with /):"
	echo "$invalid_paths" | while read -r line; do
		echo "  - $line"
	done
else
	pass "pkg-plist paths are correct (no invalid absolute paths)"
fi

# Check 2: Find spaces in paths (but allow @dir entries which have space after @dir)
# Valid: "@dir /some/path" or "@dir some/path"
# Invalid: "path with spaces/file.txt"
invalid_spaces=$(grep " " pkg-plist 2>/dev/null | grep -v "^@dir " || true)
if [ -n "$invalid_spaces" ]; then
	fail "Spaces found in pkg-plist entries (outside @dir directives):"
	echo "$invalid_spaces" | while read -r line; do
		echo "  - $line"
	done
else
	pass "No invalid spaces in pkg-plist entries"
fi

# Check 3: Find tabs in pkg-plist (tabs should never be used)
if grep -qP "\t" pkg-plist 2>/dev/null; then
	fail "Tabs found in pkg-plist (use spaces or no whitespace)"
	grep -nP "\t" pkg-plist | while read -r line; do
		echo "  Line: $line"
	done
else
	pass "No tabs in pkg-plist"
fi

echo ""

# ===========================================
# Test 6: Check for Untranslated Strings
# ===========================================
echo "Test 6: Checking for untranslated strings..."

# Look for echo statements with quotes not wrapped in gettext()
if grep -rn 'echo ["\x27]' files/ --include="*.php" | grep -v "gettext\|<?\|?>\|^$" | grep -v "//"; then
	warn "Potential untranslated strings found (review manually)"
else
	pass "All visible strings appear to be wrapped in gettext()"
fi

echo ""

# ===========================================
# Test 7: Validate Makefile Syntax
# ===========================================
echo "Test 7: Validating Makefile..."

if ! grep -q "PORTNAME=" Makefile; then
	fail "Makefile missing PORTNAME"
else
	pass "Makefile has PORTNAME"
fi

if ! grep -q "PORTVERSION=" Makefile; then
	fail "Makefile missing PORTVERSION"
else
	pass "Makefile has PORTVERSION"
fi

if ! grep -q ".include <bsd.port.mk>" Makefile; then
	fail "Makefile missing .include <bsd.port.mk>"
else
	pass "Makefile includes bsd.port.mk"
fi

# Check for tabs (Makefiles require tabs)
if ! grep -P "\t" Makefile > /dev/null 2>&1; then
	warn "Makefile may be missing tabs (required for make)"
else
	pass "Makefile appears to use tabs"
fi

echo ""

# ===========================================
# Test 8: Check JavaScript Syntax
# ===========================================
echo "Test 8: Checking JavaScript syntax..."

if ! command -v node > /dev/null 2>&1; then
	warn "Node.js not installed - skipping JavaScript validation"
else
	# Extract JavaScript from PHP files and validate
	find files -name "*.php" | while read -r file; do
		# Extract script blocks
		sed -n '/<script[^>]*>/,/<\/script>/p' "$file" | \
		sed 's|//<!\[CDATA\[||g' | \
		sed 's|//\]\]>||g' | \
		grep -v "^<script" | \
		grep -v "^</script" > /tmp/extracted_js_$$.js 2>/dev/null || true
		
		if [ -s /tmp/extracted_js_$$.js ]; then
			if node --check /tmp/extracted_js_$$.js 2>&1; then
				pass "JavaScript valid in: $file"
			else
				fail "JavaScript syntax error in: $file"
			fi
		fi
		rm -f /tmp/extracted_js_$$.js
	done
fi

echo ""

# ===========================================
# Test 9: Validate CSRF Protection
# ===========================================
echo "Test 9: Checking CSRF protection..."

# Check widget has csrf_check()
if grep -q "csrf_check()" files/usr/local/www/widgets/widgets/cidr_calculator.widget.php; then
	pass "Widget has CSRF protection"
else
	fail "Widget missing csrf_check() call"
fi

# Check for csrf.magic usage in JavaScript
if grep -q "csrf.magic" files/usr/local/www/widgets/widgets/cidr_calculator.widget.php; then
	pass "Widget JavaScript uses csrf.magic"
else
	warn "Widget may not be using CSRF token in AJAX"
fi

echo ""

# ===========================================
# Test 10: Check for XSS Vulnerabilities
# ===========================================
echo "Test 10: Checking for potential XSS issues..."

# Check for innerHTML usage (potential XSS)
if grep -rn "innerHTML" files/ --include="*.php"; then
	warn "innerHTML usage found - verify XSS protection"
else
	pass "No innerHTML usage found"
fi

# Check for proper escaping in PHP
if grep -rn 'echo.*\$' files/ --include="*.php" | grep -v "htmlspecialchars\|gettext\|<?\|<?php"; then
	warn "Potential XSS - verify all output is escaped"
else
	pass "PHP output appears properly escaped"
fi

echo ""

# ===========================================
# Summary
# ===========================================
echo "======================================"
echo "Test Summary"
echo "======================================"
echo "${GREEN}Passed: $TESTS_PASSED${NC}"
echo "${RED}Failed: $TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
	echo "${GREEN}All tests passed!${NC}"
	exit 0
else
	echo "${RED}Some tests failed - review output above${NC}"
	exit 1
fi

# pfSense CIDR Calculator Package
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

[![Build Status](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/actions/workflows/build-package.yml/badge.svg)](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/actions)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![pfSense](https://img.shields.io/badge/pfSense-2.7%2B-blue)](https://www.pfsense.org)

A responsive IPv4 and IPv6 CIDR subnet calculator for pfSense, accessible under **Diagnostics** and as an optional **Dashboard widget**. Perfect for quick subnet calculations and network planning directly from your pfSense firewall.

---

## Features

### IPv4 Calculator

- **Real-time subnet calculations** with instant results
- **CIDR to subnet mask conversion** and vice versa
- **Network and broadcast address** calculation
- **Usable host range** with automatic /31 and /32 handling
- **Wildcard mask** calculation for ACLs
- **Maximum subnets and addresses** calculation
- **Special handling** for point-to-point (/31) and host (/32) networks
- **Copy-to-clipboard** functionality for all calculated values
- **Input validation** with automatic IP address normalization

### IPv6 Calculator

- **IPv6 address expansion** from compressed notation
- **Full address display** with proper formatting
- **Network address calculation** with prefix length
- **Address range** (start and end addresses)
- **Total addresses** calculation
- **/64 subnet calculation** for network planning
- **Copy-to-clipboard** functionality

### Dashboard Widget

- **Configurable display** - show IPv4, IPv6, or both
- **Compact interface** optimized for dashboard use
- **Settings persistence** across sessions
- **Real-time calculations** without page reload
- **Responsive design** adapts to widget size

### Security & Architecture

- **Client-side calculations** - all processing in browser, no server load
- **No data transmission** - calculations never sent to server
- **CSRF protection** on all state-changing operations
- **Input sanitization** and validation
- **XSS-safe output** using DOM manipulation
- **Privilege-based access** tied to dashboard permissions
- **Audit logging** for unauthorized access attempts

### User Experience

- **Responsive design** works on desktop, tablet, and mobile
- **Theme-aware** - adapts to pfSense light/dark themes
- **Accessibility features** - ARIA labels, keyboard navigation, screen reader support
- **Browser compatibility** - supports all modern browsers with fallbacks
- **Inline help text** for complex fields
- **Visual feedback** for copy operations and errors

### Technical

- **Version-agnostic** - compatible with pfSense CE and Plus 2.7+
- **HA/CARP safe** - no state dependencies or shared data
- **Zero external dependencies** - self-contained package
- **FreeBSD ports compliant** - ready for upstream submission
- **Automated testing** - comprehensive test suite
- **CI/CD pipeline** - automated validation and releases

---

## Installation

### Method 1: Package Manager (Recommended)

Once accepted into official pfSense package repository:

1. Navigate to **System > Package Manager > Available Packages**
2. Search for **"CIDR Calculator"**
3. Click **Install**

### Method 2: Manual Installation (Development)

1. Download the latest release

   ```shell
   fetch https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/releases/latest/download/pfSense-pkg-CIDRCalculator-0.1.0.tar.gz
   Verify checksum (optional)
   
   fetch https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/releases/latest/download/checksums.txt
   sha256 -c checksums.txt
   Install package
   
   pkg add pfSense-pkg-CIDRCalculator-0.1.0.tar.gz
   ```

### Method 3: From Source

1. Clone repository

   ```shell
   git clone https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator.git
   cd pfSense-pkg-CIDRCalculator
2. Run tests (optional but recommended)

   ```shell
   ./tests/run_tests.sh
   Build and install (requires FreeBSD build environment)
   
   make install
   ```

---

## Usage

### Diagnostics Page

1. Navigate to **Diagnostics > CIDR Calculator**
2. Choose **IPv4** or **IPv6** tab
3. Enter IP address and CIDR/prefix length
4. Results update automatically
5. Click **clipboard icons** to copy values

**IPv4 Example:**

- Input: `192.168.1.0/24`
- Output: Network, broadcast, usable range, wildcard mask, etc.

**IPv6 Example:**

- Input: `2001:db8::1/64`
- Output: Full address, network, range, /64 subnets available

### Dashboard Widget

1. Navigate to **Dashboard**
2. Click **+ (Add Widget)**
3. Select **CIDR Calculator**
4. Toggle **IPv4/IPv6** visibility as needed
5. Results update in real-time

**Widget Features:**

- Compact display optimized for dashboard
- Inline results without page reload
- Settings saved automatically
- Shows network, mask, range, and host count

## Testing

### Automated Test Suite

The package includes a comprehensive automated test suite that validates:

- ✅ **File structure** - all required files present
- ✅ **PHP syntax** - linting all PHP files
- ✅ **XML validation** - schema compliance
- ✅ **JavaScript syntax** - static analysis
- ✅ **Security scanning** - checks for dangerous functions
- ✅ **XSS protection** - output escaping validation
- ✅ **CSRF protection** - token usage verification
- ✅ **Makefile format** - FreeBSD ports compliance
- ✅ **pkg-plist format** - path validation
- ✅ **Code standards** - gettext usage, escaping, etc.

### Running Tests Locally

1. Make script executable (first time only)

   ```shell
   chmod +x tests/run_tests.sh
   ```

2. Run full test suite

   ```shell
   ./tests/run_tests.sh # Expected output:
   ======================================
   pfSense CIDR Calculator Test Suite
   ======================================
   Test 1: Checking required files...
   ✓ Found: Makefile
   ...
   ======================================
   Test Summary
   ======================================
   Passed: 45
   Failed: 0
   All tests passed!
   ```

### Continuous Integration

All commits and pull requests automatically trigger:

1. **Automated tests** via GitHub Actions
2. **Security scanning** for vulnerabilities
3. **Code validation** (PHP, XML, JavaScript)
4. **Package building** and artifact creation
5. **Release automation** on version tags

View test results: [GitHub Actions](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/actions)

### Manual Testing Checklist

While automated tests cover code quality, these require manual verification:

**IPv4 Calculations:**

- [ ] Standard /24 network: `192.168.1.0/24`
- [ ] /31 point-to-point: `10.0.0.0/31` (should show 2 usable)
- [ ] /32 host route: `192.168.1.1/32` (should show 1 host)
- [ ] Large network: `10.0.0.0/8`
- [ ] Edge case: `255.255.255.254/31`
- [ ] Invalid input: `300.0.0.1/24` (should normalize or error)
- [ ] Invalid CIDR: `192.168.1.0/33` (should reject)

**IPv6 Calculations:**

- [ ] Standard /64: `2001:db8::/64`
- [ ] /128 host: `2001:db8::1/128`
- [ ] /48 site: `2001:db8:1234::/48`
- [ ] Compressed: `::1/128`
- [ ] Invalid: `gggg::1/64` (should error)

**Widget Functionality:**

- [ ] Add widget to dashboard
- [ ] Toggle IPv4/IPv6 visibility
- [ ] Verify settings persist after reload
- [ ] Remove widget, verify cleanup

**Cross-Browser:**

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)

**Copy-to-Clipboard:**

- [ ] All copy buttons work
- [ ] "Copied!" feedback appears
- [ ] Correct values copied

---

## Development

### Project Structure

```plaintext
pfSense-pkg-CIDRCalculator/
├─┬ .github/workflows/
│ └── build-package.yml                 # CI/CD pipeline
├── Makefile                            # FreeBSD port Makefile
├── pkg-desc                            # Package description
├── pkg-plist                           # File manifest
├─┬ files/
│ ├── pkg-install.in                    # Installation script
│ ├── pkg-deinstall.in                  # Deinstallation script
│ ├─┬ etc/inc/priv/
│ │ └── cidr_calc.priv.inc              # Privilege definitions
│ └─┬ usr/local/
│   ├─┬ pkg/
│   │ ├── cidr_calc.inc                 # Package functions
│   │ └── cidr_calc.xml                 # Package configuration
│   ├─┬ share/pfSense-pkg-CIDRCalculator/
│   │ └── info.xml                      # Package metadata
│   └─┬ www/
│     ├── diag_cidr_calculator.php      # Main calculator page
│     └─┬ widgets/widgets/
│       └── cidr_calculator.widget.php  # Dashboard widget
└─┬ tests/
  └── run_tests.sh                      # Automated test suite
```

### Adding New Test Cases

Tests are organized in `tests/run_tests.sh` using numbered sections:

```shell
===========================================
Test N: Description of Test
===========================================

echo "Test N: What we're testing..."

if [condition]; then
pass "Success message"
else
fail "Failure message"
fi
For non-critical issues:

if [potential_issue]; then
warn "Warning message"
fi

echo "" # Blank line separator
```

> **Example - Adding a new test:**
>
> ```shell
> ===========================================
> Test 11: Check for TODO Comments
> ===========================================
> 
> echo "Test 11: Checking for TODO comments..."
> 
> if grep -rn "TODO|FIXME" files/ --include=".php" --include=".inc"; then
> warn "Found TODO/FIXME comments - review before release"
> else
> pass "No TODO/FIXME comments found"
> fi
> 
> echo ""
> ```


### Contributing

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/my-feature`
3. **Make** your changes
4. **Run** tests: `./tests/run_tests.sh`
5. **Commit** with clear message: `git commit -m "Add feature: description"`
6. **Push** to your fork: `git push origin feature/my-feature`
7. **Open** a Pull Request

All pull requests must:
- Pass automated tests
- Follow pfSense coding standards
- Include test coverage for new features
- Update documentation as needed

---

## Security Considerations

### Architecture
- **Client-side only** - No server-side processing reduces attack surface
- **No data persistence** - Calculations are ephemeral
- **No network requests** - All computation in browser

### Input Validation
- **IP address validation** - Strict format checking
- **CIDR range validation** - Prevents invalid values
- **Output escaping** - All user input sanitized before display
- **DOM manipulation** - XSS-safe element creation

### Access Control
- **Privilege-based** - Requires dashboard access
- **CSRF protection** - All state changes protected
- **Audit logging** - Unauthorized attempts logged
- **Session validation** - pfSense session management

### Browser Security
- **No eval()** - No dynamic code execution
- **No inline event handlers** - Uses addEventListener
- **Content Security Policy** - Compatible with CSP
- **No external dependencies** - No third-party CDNs

---

## Troubleshooting

### Calculator not loading
- **Check JavaScript is enabled** in browser
- **Verify privilege access** - user needs dashboard permission
- **Check browser console** for JavaScript errors
- **Clear browser cache** and reload

### Widget not appearing
- **Check widget is enabled** in dashboard settings
- **Verify configuration saved** - check `/cf/conf/config.xml`
- **Reload dashboard** after installation
- **Check PHP error log**: `/var/log/system.log`

### Copy-to-clipboard not working
- **Browser compatibility** - requires modern browser
- **HTTPS required** - clipboard API needs secure context
- **Permission granted** - some browsers require clipboard permission
- **Fallback used** - older browsers use `execCommand()`

### Installation fails
- **Check FreeBSD version** - pfSense 2.7+ required
- **Verify package integrity** - check SHA256 checksum
- **Review install log**: `/var/log/pkg_install.log`
- **Check disk space**: `df -h`

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

---

## Roadmap

### Version 0.2.0 (Planned)
- [ ] IPv4 subnet splitting calculator
- [ ] IPv6 subnet aggregation
- [ ] Network overlap detection
- [ ] VLSM calculator
- [ ] Export results as CSV/JSON
- [ ] Localization support (i18n)

### Version 0.3.0 (Future)
- [ ] Batch IP calculations
- [ ] Integration with pfSense aliases
- [ ] Auto-populate from existing interfaces
- [ ] Visual subnet diagrams
- [ ] Historical calculation storage (optional)

### Upstream Submission
- [ ] Complete all automated tests
- [ ] Manual testing on multiple pfSense versions
- [ ] Documentation review
- [ ] Submit to pfSense FreeBSD ports
- [ ] Inclusion in official package repository

---

## License

This project is licensed under the **Apache License 2.0** - see the [LICENSE](LICENSE) file for details.

Portions of this software are part of pfSense® software:
- Copyright (c) 2015-2025 Rubicon Communications, LLC (Netgate)
- All rights reserved.

---

## Credits

### Author

**I Feel Fine, Inc.**

- Website: [https://ifeelfine.ca](https://ifeelfine.ca)
- GitHub: [@IFeelFine](https://github.com/IFeelFine)
- Contact: 13614128+imdebating@users.noreply.github.com

### Acknowledgments

- **Netgate/pfSense Team** - For the excellent pfSense platform
- **FreeBSD Project** - For the robust OS foundation
- **Community Contributors** - For testing and feedback

### References

- [pfSense Package Development Guide](https://docs.netgate.com/pfsense/en/latest/development/develop-packages.html)
- [FreeBSD Porter's Handbook](https://docs.freebsd.org/en/books/porters-handbook/)
- [RFC 4632 - CIDR](https://www.rfc-editor.org/rfc/rfc4632)
- [RFC 3021 - Point-to-Point /31](https://www.rfc-editor.org/rfc/rfc3021)
- [RFC 4291 - IPv6 Addressing](https://www.rfc-editor.org/rfc/rfc4291)

---

## Support
<!--
### Documentation

- [Installation Guide](docs/INSTALL.md) *(coming soon)*
- [User Guide](docs/USER_GUIDE.md) *(coming soon)*
- [Developer Guide](docs/DEVELOPER.md) *(coming soon)*

### Getting Help

- **Issues**: [GitHub Issues](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/issues)
- **Discussions**: [GitHub Discussions](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/discussions)
- **pfSense Forums**: [Netgate Forum](https://forum.netgate.com/)
-->

### Reporting Bugs

When reporting bugs, please include:

1. pfSense version and architecture
2. Browser and version (for UI issues)
3. Steps to reproduce
4. Expected vs actual behavior
5. Screenshots (if applicable)
6. Relevant log entries

### Feature Requests

Feature requests are welcome! Please:

1. Check existing issues first
2. Describe the use case
3. Explain expected behavior
4. Consider backward compatibility

---

**Made with ❤️ for the pfSense community**

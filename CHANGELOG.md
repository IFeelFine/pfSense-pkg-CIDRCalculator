# Changelog

All notable changes to pfSense-pkg-CIDRCalculator will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.1] - 2025-12-06

### Fixed

- Update [pkg-install.in](files/pkg-install.in) [pkg-deinstall.in](files/pkg-deinstall.in) files removing any service interaction
- Change [Makefile](Makefile) DATADIR variable and directory locations

## [0.1.0] - 2025-12-05

### Added

#### Core Functionality

- IPv4 CIDR calculator with real-time subnet calculations
- IPv6 CIDR calculator with prefix length support
- Dashboard widget with configurable IPv4/IPv6 display
- Diagnostics menu integration under **Diagnostics > CIDR Calculator**
- Copy-to-clipboard functionality for all calculated values
- Browser fallback for older clipboard APIs (document.execCommand for legacy browsers)

#### Calculations - IPv4

- Network address calculation from IP/CIDR
- Broadcast address calculation
- Subnet mask conversion (CIDR ↔ dotted decimal)
- Wildcard mask calculation for ACL usage
- Usable host range with first/last addresses
- Total and usable host counts
- Special handling for /31 networks (RFC 3021 point-to-point)
- Special handling for /32 networks (single host)
- Maximum subnets calculation
- Maximum addresses per subnet calculation

#### Calculations - IPv6

- Full address expansion from compressed notation
- Network address calculation with prefix length
- End address calculation for network range
- Total addresses calculation (with scientific notation for large values)
- /64 subnet availability calculation
- Support for :: compression and zero-padding

#### User Interface

- Tabbed interface (IPv4/IPv6) on diagnostics page
- Responsive design for mobile, tablet, and desktop
- Real-time calculation updates on input change
- Input validation with helpful error messages
- Inline help text for complex fields
- Visual feedback for copy operations
- Loading states and error handling
- ARIA labels and roles for screen readers
- Keyboard navigation support

#### Widget Features

- Compact calculator optimized for dashboard
- Toggle IPv4/IPv6 visibility independently
- Settings persistence across sessions
- Inline results without page reload

#### Security & Architecture

- Client-side only calculations (zero server load)
- No data transmission to server
- XSS-safe output using DOM manipulation (not innerHTML with strings)
- Input sanitization and validation
- Privilege-based access control
- Session-based rate limiting on widget configuration updates
  - Maximum 10 configuration saves per 60 seconds per session
  - Returns HTTP 429 (Too Many Requests) when limit exceeded
  - Logs rate limit violations for security monitoring
- Audit logging for unauthorized access attempts

#### Package Infrastructure

- FreeBSD ports-compliant Makefile
- Proper pkg-plist manifest with correct paths
- Package installation/deinstallation scripts (pkg-install.in, pkg-deinstall.in)
- Privilege definitions (cidr_calc.priv.inc)
- Package lifecycle functions (install, deinstall, upgrade)
- Configuration management for widget settings
- Package metadata (info.xml)

#### Testing & CI/CD

- Comprehensive automated test suite (shell script-based `[tests/run_tests.sh](/tests/run_tests.sh)`)
- GitHub Actions workflow for continuous integration
- Automated PHP syntax validation
- Automated XML schema validation
- JavaScript syntax checking
- Security scanning for dangerous functions
- pkg-plist format validation
- Makefile compliance checking
- Code standards enforcement (gettext, escaping)
- Automated package building and release creation on release version tags
- Checksum generation (SHA256, MD5)

#### Documentation

- Comprehensive README with installation instructions
- Testing guide for developers
- Manual test checklist for functional testing

### Developer Notes

#### Breaking Changes

None - this is the initial release.

#### Deprecations

None - all features are new.

#### Migration Guide

Not applicable - fresh installation.

#### Known Issues

- Copy-to-clipboard may not work on non-HTTPS connections (browser security)
- IPv6 compressed notation may not display optimally in all cases
- Some older browsers may not support all features (graceful degradation provided)

#### Upgrade Notes

Not applicable - initial release.

---

## Release Checklist

### Pre-Release (v0.1.0)

- [x] All automated tests passing
- [x] Security scan clean
- [x] PHP syntax validated
- [x] XML schemas validated
- [x] JavaScript syntax checked
- [x] pkg-plist format correct
- [x] Makefile compliant with FreeBSD ports
- [x] Documentation complete
- [x] CHANGELOG updated
- [x] Version numbers synchronized

## Links

- [GitHub Repository](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator)
- [Issue Tracker](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/issues)
- [Releases](https://github.com/IFeelFine/pfSense-pkg-CIDRCalculator/releases)
- [pfSense Documentation](https://docs.netgate.com/pfsense/en/latest/)
- [FreeBSD Ports](https://www.freebsd.org/ports/)

---

## Credits

### Contributors

- **David** (I Feel Fine, Inc.) - Initial development, testing, documentation

### Special Thanks

- **Netgate/pfSense Team** - For the excellent platform and development resources
- **FreeBSD Project** - For the robust operating system foundation
- **Community Reviewers** - For comprehensive code review and security analysis

### References

- [RFC 4632 - Classless Inter-domain Routing (CIDR)](https://www.rfc-editor.org/rfc/rfc4632)
- [RFC 3021 - Using 31-Bit Prefixes on IPv4 Point-to-Point Links](https://www.rfc-editor.org/rfc/rfc3021)
- [RFC 4291 - IP Version 6 Addressing Architecture](https://www.rfc-editor.org/rfc/rfc4291)
- [pfSense Package Development Guide](https://docs.netgate.com/pfsense/en/latest/development/develop-packages.html)
- [FreeBSD Porter's Handbook](https://docs.freebsd.org/en/books/porters-handbook/)

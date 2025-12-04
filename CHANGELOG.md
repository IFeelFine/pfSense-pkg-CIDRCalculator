# Changelog

All notable changes to pfSense-pkg-CIDRCalculator will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - Unreleased (2025-12-04)

### Added

- CSRF protection for widget configuration
- Accessibility improvements (ARIA labels, keyboard navigation)
- Copy-to-clipboard functionality
- IPv6 full address expansion

### Fixed

- JavaScript syntax errors in widget
- XSS vulnerabilities in result display
- IPv6 end address calculation
- pkg-plist absolute path issues

### Security

- Proper CSRF token handling
- Input sanitization and output escaping

### Added

- Initial release
- IPv4 CIDR calculator with subnet mask conversion
- IPv6 CIDR calculator with prefix length support
- Dashboard widget with configuration options
- Diagnostics menu integration

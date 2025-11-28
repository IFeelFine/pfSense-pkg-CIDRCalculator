# pfSense CIDR Calculator Package

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![pfSense](https://img.shields.io/badge/pfSense-2.8+-orange.svg)](https://www.pfsense.org/)

An interactive IPv4 and IPv6 CIDR subnet calculator for pfSense with dashboard widget support.

## Features

- **Dual-Stack Support**: Comprehensive IPv4 and IPv6 CIDR calculations
- **Tabbed Interface**: Clean, organized layout with separate tabs for IPv4 and IPv6
- **Dashboard Widget**: Compact calculator widget for the pfSense dashboard
- **Real-time Calculations**: Instant results as you type or change selections
- **Comprehensive Results**: 
  - IPv4: Network address, broadcast, usable range, wildcard mask, and more
  - IPv6: Full address expansion, network address, /64 subnet counts
- **User-Friendly**: Accessible from Diagnostics menu to users with dashboard privileges

## Requirements

- pfSense 2.7.0 or later
- FreeBSD ports system (for building from source)

## Installation

### Via Package Manager (Recommended)

1. Navigate to **System > Package Manager > Available Packages**
2. Search for "CIDR Calculator"
3. Click **Install**

### Manual Installation

1. Download the latest release package
2. Navigate to **Diagnostics > Command Prompt**
3. Install via command:

```shell
pkg add /path/to/pfSense-pkg-CIDRCalculator-0.1.pkg
```

### Build from Source

```shell
git clone https://github.com/ifeelfine/pfSense-pkg-CIDRCalculator.git
cd pfSense-pkg-CIDRCalculator
make clean package
```

## Usage

### Main Calculator Page

1. Navigate to **Diagnostics > CIDR Calculator**
2. Select **IPv4** or **IPv6** tab
3. Enter IP address and CIDR/prefix length
4. Results update automatically

### Dashboard Widget

1. Navigate to **Dashboard**
2. Click **+** (Add Widget)
3. Select **CIDR Calculator**
4. Toggle IPv4/IPv6 display using checkboxes

## File Structure

```
pfSense-pkg-CIDRCalculator/
├── Makefile                          # FreeBSD port Makefile
├── pkg-desc                          # Package description
├── pkg-plist                         # File manifest
├── pkg-install.in                    # Installation script
├── pkg-deinstall.in                  # Deinstallation script
└── files/
  ├── etc/inc/priv/
  │ └── cidr_calc.inc                 # Privilege definitions
  ├── usr/local/pkg/
  │ ├── cidr_calc.inc                 # Package functions
  │ └── cidr_calc.xml                 # Package configuration
  ├── usr/local/share/pfSense-pkg-CIDRCalculator/
  │ └── info.xml                      # Package metadata
  └── usr/local/www/
  ├── diagnostics_cidr_calculator.php # Main calculator page
  └── widgets/
    └── cidr_calculator.widget.php    # Dashboard widget
```


## Security Considerations

- **Client-side Calculations**: All calculations performed in JavaScript (no server-side processing)
- **Input Validation**: Comprehensive IP address and CIDR validation
- **No External Dependencies**: Self-contained package with no third-party libraries
- **Privilege-based Access**: Tied to dashboard access privilege
- **CSRF Protection**: Widget uses `$nocsrf = true` (appropriate for non-critical settings)

## IPv4 Calculator Features

- IP address validation and normalization
- CIDR to subnet mask conversion
- Network and broadcast address calculation
- Usable host range calculation
- Wildcard mask calculation
- Maximum subnets and addresses calculation
- Special handling for /31 and /32 networks

## IPv6 Calculator Features

- IPv6 address validation
- Short-form to full expansion (e.g., `2001:db8::` → `2001:0db8:0000:0000:0000:0000:0000:0000`)
- Network address calculation
- /64 subnet count calculation (with special logic for prefixes ≥64)
- Start and end address display

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow pfSense coding standards
- Use `gettext()` for all user-facing strings
- Validate all user inputs
- Comment complex logic
- Test on pfSense 2.7+ before submitting

## Testing

See [TEST_CASES.md](TEST_CASES.md) for comprehensive testing procedures.

## Known Issues

- IPv6 start/end address calculations are simplified (network address used for both)
- Very large subnet counts (>2^53) may lose precision due to JavaScript number limits

## License

Copyright (c) 2025 I Feel Fine, Inc.

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

## Author

**David Bates**  
Email: 13614128+imdebating@users.noreply.github.com  
GitHub: [@ifeelfine](https://github.com/ifeelfine)

## Acknowledgments

- pfSense project and community
- Netgate for the pfSense platform

## Changelog

### Version 0.1 (Initial Release)
- IPv4 CIDR calculator with full feature set
- IPv6 CIDR calculator with /64 subnet counting
- Dashboard widget with toggle options
- Privilege-based access control
- Tabbed interface for IPv4/IPv6
# Nscam - IP Geolocation & Scam Tracker

<p align="left">
  <a href="https://github.com/dogesenic/nscam/releases/latest">
    <img src="https://img.shields.io/github/v/release/dogesenic/nscam?include_prereleases&style=flat" alt="Version">
  </a>
  <a href="https://github.com/dogesenic/nscam/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/dogesenic/nscam?style=flat" alt="License">
  </a>
  <a href="https://github.com/dogesenic/nscam/actions">
    <img src="https://img.shields.io/github/actions/workflow/status/dogesenic/nscam/build?style=flat" alt="Build">
  </a>
</p>

Open source IP geolocation tool to trace IP addresses, domains, and detect scammers.

## Features

- **IP & Domain Lookup** - Trace any IP address or domain
- **Geolocation** - Country, Region, City, ZIP, Timezone
- **ISP & Organization** - Get ISP name and AS number
- **Network Detection** - Detect Proxy/VPN, Hosting, Mobile
- **Google Maps** - Direct link to coordinates

## Installation

### Command Line Interface (Rust)

#### Linux/macOS
```bash
wget -O nscam https://github.com/dogesenic/nscam/releases/latest/nscam-x86_64-unknown-linux-gnu
chmod +x nscam
./nscam <target>
```

#### Windows
```powershell
# Download .exe from Releases
.\nscam-x86_64-pc-windows-gnu.exe <target>
```

#### macOS (Apple Silicon)
```bash
wget -O nscam https://github.com/dogesenic/nscam/releases/latest/nscam-aarch64-apple-darwin
chmod +x nscam
./nscam <target>
```

Build from source:
```bash
git clone https://github.com/dogesenic/nscam.git
cd nscam/cli
cargo build --release
./target/release/nscam <target>
```

---

### Website (PHP)

#### Quick Start (Localhost)
```bash
cd web
php -S localhost:8080
# Open http://localhost:8080
# or Open http://nscam.gt.tc/
```

#### Hosting (000webhost, InfinityFree, etc.)
1. Upload `index.php` to public_html
2. Open your site URL

## Usage

### Command Line Interface Examples
```bash
# Trace IP
./nscam 8.8.8.8

# Trace Domain
./nscam google.com

# Trace Website
./nscam pornhub.com
```

### Website Interface
1. Open website
2. Enter IP or Domain
3. Click "Trace"

## Output Example

```
TARGET INFORMATION
-----------------------------------
Target:       8.8.8.8
Type:         Residential IP
ISP:          Google LLC
Organization: Google Public DNS
AS Number:    15169

GEOLOCATION
-----------------------------------
Country:      United States (US)
Region:       California
City:         Mountain View
ZIP:          94043
Timezone:     America/Los_Angeles

MAPS
-----------------------------------
Coordinates:  37.3861, -122.0840
Google Maps:  https://www.google.com/maps/search/?api=1&query=37.3861,-122.0840
```

## Download

| Platform | Download |
|----------|----------|
| Windows x64 | [nscam-x86_64-pc-windows-gnu.exe](https://github.com/dogesenic/nscam/releases/latest/nscam-x86_64-pc-windows-gnu.exe) |
| Linux x64 | [nscam-x86_64-unknown-linux-gnu](https://github.com/dogesenic/nscam/releases/latest/nscam-x86_64-unknown-linux-gnu) |
| macOS Intel | [nscam-x86_64-apple-darwin](https://github.com/dogesenic/nscam/releases/latest/nscam-x86_64-apple-darwin) |
| macOS Apple | [nscam-aarch64-apple-darwin](https://github.com/dogesenic/nscam/releases/latest/nscam-aarch64-apple-darwin) |

## Tech Stack

| Version | Language | Framework |
|---------|----------|-----------|
| CLI | Rust | Tokio, Reqwest, Clap |
| Web | PHP | Native |

## License

MIT License - See [LICENSE](LICENSE)

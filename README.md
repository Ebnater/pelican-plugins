# Ebnater Pelican Plugins

This repository contains my Pelican plugins in one place.

Repository: https://github.com/Ebnater/pelican-plugins

## Installation

1. Download or clone this repository.
2. Copy the plugin folder you want to use into your Pelican panel `plugins` directory.
3. Make sure the folder name matches the plugin id exactly.
4. Open the Pelican admin panel, go to the plugins page, and install the plugin.
5. Check the plugin-specific README for additional requirements or setup steps.

> [!IMPORTANT]
> The plugin folder name must match the plugin id exactly.
> For example, `pocketid-provider` must stay `pocketid-provider`.

## Available Plugins

- [Backup Templates](backup-templates) - Create reusable backup ignore presets for specific servers.
- [PocketID Provider](pocketid-provider) - Use PocketID as an OAuth provider in Pelican.

## Plugin Notes

### Backup Templates

Adds reusable backup presets per server so ignored paths can be filled automatically during backup creation.

### PocketID Provider

Registers PocketID as an OAuth provider and integrates it into the existing OAuth settings in Pelican.

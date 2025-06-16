# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-06-16
### Added
- User profile management system
- Browser session management
- Account deletion functionality
- Laravel language files for i18n support
- Job type field to applications

### Changed
- Split Document model into separate Resume and Cover Letter models
- Enhanced application listing with improved information hierarchy
- Refactored Filament resources for better organization
- Updated dependencies (Filament to v3.3.21, Laravel to v12.18.0)
- Improved file upload handling and storage

### Removed
- Obsolete Document model and related files

## [1.0.0] - 2025-06-12
### Added
- Initial stable release of Laravel Job Tracker
- Job application management (details, status, description, salary, location, dates)
- Document management with PDF/Word support (10MB limit)
- Contact management system
- Rich text note taking with categories
- Task management
- Filament 3 admin interface
- User authentication
- Data import/export functionality

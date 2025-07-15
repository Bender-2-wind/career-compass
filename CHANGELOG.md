# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2025-07-15
### Added
- Complete redesign of the user profile management system
  - Added skill management with manual entry
  - Integrated resume upload functionality directly in profile settings
  - Added automatic profile population from resume uploads

### Changed
- Improved user profile management interface
  - Enhanced data entry options (manual vs. AI-assisted)
  - Streamlined profile setup process

## [1.2.1] - 2025-06-22
### Fixed
- Fixed PostgreSQL/MySQL compatibility issue with notifications table JSON column

## [1.2.0] - 2025-06-19
### Added
- Enhanced document management with separate Resume and Cover Letter handling
- Improved file organization with dedicated storage directories

### Changed
- Refactored document storage structure for better scalability
- Updated dependencies to their latest compatible versions

### Fixed
- Resolved issues with file downloads in the document management system
- Fixed display issues in the application list view
- Addressed minor UI/UX improvements and bug fixes

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

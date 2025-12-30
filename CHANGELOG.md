# Changelog

All notable changes to XBuilder will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.4.2] - 2025-12-30

### Fixed
- **Uploaded documents not reaching AI**: Critical bug where PDFs were cleared if extraction < 100 chars
  - Root cause: Over-aggressive client-side validation in v0.4.1
  - Documents with incomplete extraction were set to `null` before sending to AI
  - Changed: Now ALWAYS sends extracted content to AI, even if imperfect
  - Lowered threshold: 100 chars → 50 chars (more lenient)
  - Adds note for AI: "[Note: PDF extraction may be incomplete...]"
  - Let AI decide if content is usable vs discarding client-side
  - Users see: "Sending to AI anyway..." instead of complete rejection
- **Version badge positioning**: Fixed floating version number in chat interface
  - Changed to `position: fixed !important` with `z-index: 9999`
  - Added `pointer-events: none` to prevent interaction blocking
  - Now properly stays in bottom-left corner
- **Added comprehensive logging**: Debug logs throughout document and LinkedIn workflows
  - Track document flow: upload → chat API → AI
  - Track LinkedIn fetch: URL validation → HTML fetch → parsing
  - Helps diagnose issues without server access

## [0.4.1] - 2025-12-30

### Fixed
- **PDF text extraction returning corrupted data**: Critical bug causing AI to receive garbage instead of readable text
  - Changed `exec()` to `shell_exec()` for proper pdftotext output capture
  - Added `-enc UTF-8` flag for better character encoding
  - Try extraction with and without `-layout` flag (some PDFs work better without)
  - Validate extracted text (minimum length, no error messages, readable characters)
  - Improved fallback method to skip binary data
  - Added comprehensive logging at each extraction stage
  - Client-side validation detects suspicious content (< 100 chars, no text)
  - Warn users if PDF extraction fails with suggestion to use DOCX
  - Upload area now shows: "DOCX recommended for CVs"
  - Auto-clear bad content instead of sending garbage to AI
  - Better error messages explaining what to do

## [0.4.0] - 2025-12-30

### Added
- **LinkedIn profile fetching**: Paste LinkedIn URL to auto-fetch profile data
  - New `/xbuilder/api/linkedin.php` endpoint
  - Automatically detects LinkedIn URLs in chat messages
  - Extracts name, headline, location from public profiles
  - Parses JSON-LD structured data and Open Graph meta tags
  - No API key required - works with public LinkedIn profiles
  - Shows real-time status: "Fetching LinkedIn profile..."
  - Fallback handling for private/inaccessible profiles
  - Seamlessly combines with uploaded CV data

### Changed
- **Improved deployment clarity**: Publish button and messaging now crystal clear
  - Button renamed: "Publish" → "Publish to Live Site"
  - Success message shows: Live URL, Admin Panel URL, file location
  - Extended success display (3s → 5s)
  - Better globe icon for publish button
  - Makes it obvious the site publishes to root domain, not a subfolder

### Fixed
- **Version display bug**: Fixed hardcoded fallback versions in all UI views
  - chat.php, login.php, setup.php all had `0.2.0` fallback
  - Now correctly fall back to `0.4.0` (current version)
  - Version number now displays accurately across all pages

## [0.3.3] - 2025-12-30

### Fixed
- **Document upload not working**: AI was not receiving uploaded CV/document content
  - Installed poppler-utils (pdftotext) for proper PDF text extraction
  - Added comprehensive logging throughout upload and chat pipeline
  - Frontend now shows word count and extraction preview
  - Clear visual feedback: "Document ready - will be sent with your next message"
  - Console logging helps debug document sending process
  - Users can now successfully upload CVs and have AI read the content

## [0.3.2] - 2025-12-30

### Fixed
- **Gemini API model compatibility**: Updated to current stable production model
  - Changed from `gemini-1.5-flash` to `gemini-2.5-flash`
  - Gemini 1.5 models are no longer available in Google's v1 API
  - Using gemini-2.5-flash (current stable production model as of December 2025)
  - Resolves error: "models/gemini-1.5-flash is not found for API version v1"
  - Gemini integration now uses correct, supported model

## [0.3.1] - 2025-12-30

### Fixed
- **Gemini API model name**: Attempted fix with incorrect model name
  - Changed from `gemini-1.5-flash-latest` to `gemini-1.5-flash`
  - This fix was incomplete as Gemini 1.5 models are deprecated in v1 API

## [0.3.0] - 2025-12-30

### Added
- **Version display in UI**: Current version now visible in all admin pages
  - Setup wizard: bottom-right corner
  - Login page: bottom-right corner
  - Chat interface: bottom-left corner
- **Automated version bump script**: `bump-version.sh` for easy version management
  - Interactive prompts for major/minor/patch bumps
  - Automatically updates VERSION, README.md badges
  - Creates git commits and tags
  - Pushes to remote with confirmation
- **Complete version history**: All 7 releases now documented in README.md
- **Version tracking in Config.php**: getAppVersion() and getInstalledVersion() methods

### Changed
- README.md: Enhanced "Versioning & Changelog" section with full history
- All UI pages now read VERSION file dynamically

### Developer Experience
- Added `bump-version.sh` script for streamlined version management
- Version number is now single source of truth (VERSION file)
- All documentation auto-updates from VERSION file

## [0.2.0] - 2025-12-30

### Added
- **Multi-server support**: Full compatibility with Apache, Nginx, and OpenLiteSpeed
- `nginx.conf`: Production-ready Nginx configuration with security headers and caching
- `.htaccess.litespeed`: OpenLiteSpeed optimized configuration with LiteSpeed Cache
- `DEPLOYMENT.md`: Comprehensive deployment guide for all three web servers
- SSL/HTTPS configuration examples for all servers
- Troubleshooting guides for common deployment issues
- `/xbuilder/index.php`: Entry point for direct directory access

### Fixed
- **403 Forbidden error** when accessing `/xbuilder/` directory after setup
- Apache rewrite conditions now properly handle directory access

### Changed
- README.md: Expanded web server configuration section with detailed instructions
- Storage directory protection now implemented across all three web servers

## [0.1.4] - 2025-12-30

### Fixed
- **Gemini API compatibility issue**: Updated API endpoint from `v1beta` to `v1` (stable)
- Model changed to `gemini-1.5-flash-latest` for better reliability
- Resolves error: "models/gemini-1.5-flash is not found for API version v1beta"

## [0.1.3] - 2025-12-30

### Changed
- **License updated to AGPL-3.0**: Switched from MIT to GNU Affero General Public License v3.0
- Added personal attribution "Made with ❤️ by Asif Rahman" to README.md
- Standard AGPL-3.0 license text from official GNU source

### Fixed
- Attribution placement: Moved personal touch from LICENSE to README (best practice)

## [0.1.2] - 2025-12-28

### Changed
- Unified codebase: Merged best features from parallel development branches
- Enhanced core classes with improved features:
  - **Security.php**: Session namespacing, CSRF expiry, secure key deletion
  - **Generator.php**: Backup before publish, ZIP export, HTML validation
  - **AI.php**: Enhanced system prompt, API key testing, DI support
  - **Config.php**: Dot notation for nested values, version tracking
  - **Conversation.php**: Session namespacing, archiving, metadata storage

### Fixed
- Resolved code duplication from Claude web synchronization
- Consolidated instance-based architecture across all core classes

## [0.1.1] - 2025-12-28

### Added
- Initial code unification from multiple development streams
- Storage directory protection with `.htaccess`
- Session-based authentication with 24-hour expiry
- CSRF token protection with 1-hour expiry

### Changed
- Converted all core classes to instance-based pattern for better dependency injection
- Implemented Argon2id password hashing with configurable parameters
- Rate limiting on failed login attempts (0.5s delay)

## [0.1.0] - 2025-12-28

### Added
- **Initial XBuilder Phase 1 Release** 🎉
- AI-powered website generation with natural conversation interface
- Support for three AI providers:
  - Claude (Anthropic) - claude-sonnet-4-20250514
  - Gemini (Google) - gemini-1.5-flash
  - ChatGPT (OpenAI) - gpt-4o-mini
- Document upload support (PDF, DOCX, DOC, TXT, MD, JSON)
- CV/resume parsing and content extraction
- Complete admin interface at `/xbuilder/`
- Setup wizard for initial configuration
- Secure API key storage with AES-256-CBC encryption
- Single-file HTML output (pure static sites)
- Tailwind CSS integration via CDN
- Google Fonts support
- Mobile-responsive designs
- Conversation history management
- Site preview and publishing system
- Core architecture:
  - `AI.php`: Multi-provider AI abstraction
  - `Security.php`: Encryption, authentication, CSRF protection
  - `Config.php`: Application configuration management
  - `Generator.php`: HTML generation and site publishing
  - `Conversation.php`: Chat history and context management
- Apache support with `.htaccess` configuration
- Modern dark theme admin interface
- Emoji favicon trick for personalization
- Production-ready code structure

### Security
- AES-256-CBC encryption for API keys
- Argon2id password hashing
- Session-based authentication
- CSRF token protection
- Storage directory protected from web access
- Input validation and sanitization
- Secure file upload handling

## [0.0.1] - 2025-12-28

### Added
- Initial project structure
- Basic file organization
- Repository initialization

---

## Version History Summary

- **0.4.2** - Critical bug fix (documents not reaching AI, version badge position)
- **0.4.1** - Critical bug fix (PDF extraction returning corrupted data)
- **0.4.0** - New feature (LinkedIn profile fetching, deployment UX improvements)
- **0.3.3** - Bug fix (document upload - PDF extraction and AI integration)
- **0.3.2** - Bug fix (Gemini API model updated to 2.5-flash)
- **0.3.1** - Incomplete fix (used deprecated Gemini 1.5 model)
- **0.3.0** - Platform enhancements (version display, automation)
- **0.2.0** - Multi-server support (Apache, Nginx, OpenLiteSpeed)
- **0.1.4** - Gemini API compatibility fix (v1beta → v1)
- **0.1.3** - License update to AGPL-3.0
- **0.1.2** - Codebase unification and enhancements
- **0.1.1** - Security improvements and architecture refactoring
- **0.1.0** - Initial working release with complete Phase 1 features
- **0.0.1** - Project initialization

---

## Upgrade Notes

### From 0.1.x to 0.2.0
- No breaking changes
- If deploying on Nginx or OpenLiteSpeed, use the new configuration files
- Apache users: no action required, existing `.htaccess` continues to work

### From 0.0.x to 0.1.x
- First stable release
- Complete setup wizard on first access
- All API keys need to be configured via setup

---

## Roadmap

### Planned for 0.3.0
- [ ] Multiple pages support (About, Contact, Projects)
- [ ] Image upload and optimization
- [ ] Custom domain configuration helper
- [ ] SEO meta tag editor
- [ ] Analytics integration (Google Analytics, Plausible)

### Planned for 0.4.0
- [ ] Site export as ZIP file
- [ ] Template/style presets (optional starting points)
- [ ] AI-powered SEO suggestions
- [ ] Performance optimization hints
- [ ] Dark/light mode toggle for admin

### Planned for 1.0.0 (Stable Release)
- [ ] Multi-site support (multiple websites per installation)
- [ ] User accounts and multi-tenancy
- [ ] Plugin system for extensions
- [ ] Theme marketplace integration
- [ ] CLI tool for deployment
- [ ] Docker support
- [ ] Comprehensive testing suite

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## Links

- **Repository**: [github.com/Asif2BD/xBuilderCMS](https://github.com/Asif2BD/xBuilderCMS)
- **Issues**: [github.com/Asif2BD/xBuilderCMS/issues](https://github.com/Asif2BD/xBuilderCMS/issues)
- **Discussions**: [github.com/Asif2BD/xBuilderCMS/discussions](https://github.com/Asif2BD/xBuilderCMS/discussions)

---

**Maintained by Asif Rahman** | *Powered by xCloud.host*

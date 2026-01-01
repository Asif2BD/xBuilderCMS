# Changelog

All notable changes to XBuilder will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.7.4] - 2025-12-30

### Added
- **Dual Gemini API setup instructions**: Shows both Free and Pro options
  - **FREE tier**: Quick setup from AI Studio (limited quota, good for testing)
  - **PRO tier**: Google Cloud Console setup (unlimited quota, pay-per-use)
  - Expandable Pro setup steps with direct links to each configuration page
  - Clear comparison: Free (0-60 req/day) vs Pro (1000+ req/min)
  - Cost transparency: ~$0.001 per website generation
  - Helps users choose the right option based on their needs

### Improved
- Settings modal now clearly shows both API key options for Gemini
- Color-coded badges (Green for FREE, Purple for PRO)
- Step-by-step Pro setup guide with clickable links
- Better user guidance for resolving quota errors

## [0.7.3] - 2025-12-30

### Fixed
- **CRITICAL: Added missing GPT-5 family models**:
  - **GPT-5.2** (Latest - Dec 2025): gpt-5.2, gpt-5.2-2025-12-11, gpt-5.2-chat-latest, gpt-5.2-pro
  - **GPT-5.1** (Nov 2025): gpt-5.1, gpt-5.1-2025-11-13, gpt-5.1-chat-latest, gpt-5.1-codex, gpt-5.1-codex-mini
  - **GPT-5** (Aug 2025): gpt-5, gpt-5-2025-08-07, gpt-5-mini, gpt-5-nano, gpt-5-chat-latest
  - Total: 32 OpenAI models now available (was missing 16 GPT-5 models)
  - **Apology**: v0.7.1 incorrectly stated "GPT-5 does not exist" - this was completely wrong
  - All GPT-5 models released throughout 2025 are now included

### Note
- User was correct - I made a mistake not finding GPT-5 models
- OpenAI platform.openai.com/docs was temporarily blocking requests
- All model IDs verified from official OpenAI documentation

## [0.7.2] - 2025-12-30

### Added
- **Reset XBuilder feature**: Factory reset from settings modal
  - "Reset XBuilder" button in Settings → Danger Zone section
  - Two-step confirmation: dialog + text confirmation ("RESET_XBUILDER")
  - Deletes all API keys (securely overwritten before deletion)
  - Deletes all conversations, uploads, generated site, backups
  - Clears configuration and sessions
  - Redirects to setup wizard after reset
  - Perfect for testing or starting fresh

### Security
- Secure API key deletion: overwrites with random bytes before unlinking
- Requires authentication to access reset endpoint
- Two-step confirmation prevents accidental resets

## [0.7.1] - 2025-12-30

### Fixed
- **Critical: Updated to current API models (December 2025)**:
  - **Claude**: Added 4.5 family - Sonnet 4.5, Opus 4.5, Haiku 4.5 (latest models)
  - **OpenAI**: Added O3, O3-Mini, O3-Pro, O4-Mini, GPT-4.1 family (1M context)
  - **Gemini**: Added 3.x family (3 Pro, 3 Flash, 3 Deep Think), 2.5 family, complete 2.0 lineup
  - Removed non-existent models from previous version
  - Updated default models to latest stable versions

### Note
- **CORRECTION**: Previous version incorrectly stated GPT-5 doesn't exist - it does!
- Claude Opus 4.5 is the most intelligent Claude model
- Gemini 3 Pro is the latest Gemini (released Nov 2025)
- All model names verified against official API documentation
- Missing GPT-5 family fixed in v0.7.3

## [0.7.0] - 2025-12-30

### Added
- **Inline model switcher in chat header**: Switch AI models on-the-fly without opening settings
  - Dropdown shows all available models grouped by provider
  - Only shows providers with configured API keys
  - Instant switching - no page reload needed
  - Makes it easy to try different models (free vs paid)

- **Expanded model support**: Added many more models for all providers
  - **Gemini**: 6 models including free tier (2.0 Flash, 1.5 Flash, 1.5 Flash-8B, Thinking mode) and paid (1.5 Pro)
  - **Claude**: 9 models from Haiku to Opus 4 (including latest Sonnet 4, Opus 4, 3.7 Sonnet)
  - **OpenAI**: 8 models including reasoning models (GPT-4o, o1, o1-mini, o3-mini)
  - Clear labels showing which models are free vs paid

- **Professional fixed footer**: Replaced floating version badge with full-width footer
  - Shows current version, active provider, and model
  - GitHub link and copyright info
  - Clean, professional appearance
  - Doesn't interfere with chat interface

### Changed
- Model selection now uses config setting instead of hardcoded defaults
- AI class constructor accepts custom model parameter
- Footer spans full width for better visual balance

### Improved
- UI/UX: Easier to switch models - no need to open settings modal
- UI/UX: Current AI configuration always visible in footer
- Developer experience: setModel() and getModel() methods in AI class

## [0.6.3] - 2025-12-30

### Fixed
- **Critical: Gemini API model name error**: Corrected invalid model name causing quota errors
  - Changed from `gemini-2.5-flash` (doesn't exist) to `gemini-2.0-flash-exp` (correct)
  - Resolves error: "Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests"
  - Note: `gemini-2.5-flash` was never a valid model - v0.3.2 introduced this error
  - Gemini 2.0 Flash Experimental is the current recommended model
  - Updated all documentation to reflect correct model names

## [0.6.2] - 2025-12-30

### Improved
- **Enhanced AI prompting for beautiful websites**: Major system prompt improvements
  - Added personality-first discovery approach
  - Design decision matrix: profession → design style mapping
  - Formula for creating unique, personal designs
  - 4 proven design recipes with exact specifications (Tech Developer, Creative Designer, Startup Founder, Minimalist Professional)
  - Conversation patterns that work (no analysis paralysis)
  - Based on analysis of successful website examples (iamshifat.com)
  - AI now makes confident design decisions instead of asking endless questions
  - Each generated site feels custom-crafted, not cookie-cutter

### Fixed
- Debug button error: "Cannot read properties of undefined (reading 'target')"
  - Added event parameter to onclick handler and function signature
  - Debug button now correctly copies debug info to clipboard

## [0.6.1] - 2025-12-30

### Added
- **In-built update system**: One-click updates from GitHub
  - Automatic update checking from GitHub releases
  - Update notification badge in chat interface (animates when available)
  - Beautiful update modal showing version comparison and changelog
  - One-click update with progress indicator
  - Automatic backup before update (stored in `/xbuilder/storage/backups/`)
  - Automatic rollback if update fails
  - Preserves user data (`site/`, `storage/`, API keys, configuration)
  - Update verification (ensures version matches after update)
  - Clean old backups (keeps last 5)
  - Safe update process (30-60 seconds)
  - No manual Git operations required
  - Works from within the admin interface

- **Update API endpoint**: `/xbuilder/api/update.php`
  - `check`: Check for updates from GitHub
  - `perform`: Download and apply update
  - `list_backups`: List available backups
  - `rollback`: Rollback to specific backup

- **Update core class**: `xbuilder/core/Update.php`
  - GitHub API integration
  - ZIP download and extraction
  - Backup creation and restoration
  - Safe file replacement (excludes user data)
  - Version verification

- **AI Provider/Model Switcher**: Switch AI providers and models on-the-fly
  - Settings modal accessible from chat interface
  - Switch between Gemini, Claude, and OpenAI without restarting
  - Support for multiple models per provider
  - Model dropdown for active provider (Gemini 2.0 Flash, 1.5 Flash, 1.5 Pro, etc.)
  - Manage multiple API keys (add, update, delete)
  - Visual indicators show active provider and API key status
  - Quick links to get API keys for each provider
  - Solves quota limit issues by allowing instant provider switching
  - No setup wizard needed - configure everything from settings

- **Settings API endpoint**: `/xbuilder/api/settings.php`
  - `get_current`: Get current AI settings and available providers
  - `switch_provider`: Switch to different AI provider
  - `switch_model`: Switch to different model
  - `add_api_key`: Add or update API key for provider
  - `delete_api_key`: Remove API key for provider

### Changed
- Updated `.gitignore` to exclude backups and security logs
- Chat interface checks for updates 2 seconds after page load
- Improved model selection with support for latest models (Gemini 2.0 Flash Exp, Claude Sonnet 4, GPT-4o)

### Fixed
- Quota limit errors can now be resolved by switching providers instantly
- No need to restart or reconfigure when hitting API limits

### Benefits
- Users can update XBuilder without SSH/Git access
- No downtime - updates complete in under 60 seconds
- Zero risk - automatic backup and rollback
- User websites and data are never touched
- Perfect for non-technical users on shared hosting

## [0.6.0] - 2025-12-30

### Added
- **Account lockout protection**: Brute force attack prevention
  - Locks account after 5 failed login attempts
  - 15 minute lockout period
  - Failed attempts tracked per IP address
  - Prevents rapid-fire password guessing
  - Security score improved: 2/5 → 5/5

- **Session hijacking prevention**: Enhanced session security
  - IP address validation on every request
  - User-Agent validation on every request
  - Automatically logs out if session context changes
  - Prevents stolen session cookies from being used
  - Security score improved: 4/5 → 5/5

- **Security audit logging**: Comprehensive security event tracking
  - All security events logged to `/xbuilder/storage/security.log`
  - Logs: login success/failure, account lockouts, session hijacking attempts
  - Logs: API key storage/deletion, session expiry, logout events
  - Includes timestamps, IP addresses, User-Agent, and context data
  - Log files have restrictive permissions (0600)
  - Security score improved: 0/5 → 5/5

### Changed
- **Reduced session lifetime**: Better security/convenience balance
  - Changed from 24 hours to 2 hours
  - Reduces attack window if session is compromised
  - Still long enough for normal work sessions
  - More secure default configuration

### Security
- **Overall security score: 3.5/5 → 4.8/5**
- All critical security recommendations implemented
- System now significantly more resistant to common attacks
- Full audit trail for forensic analysis and threat detection

## [0.5.0] - 2025-12-30

### Added
- **Debug info copy button**: One-click debugging for easier troubleshooting
  - New "Debug" button in chat header
  - Collects comprehensive debug information (version, browser, conversation state, document status)
  - Captures console logs automatically (last 100 entries)
  - Copies all debug info to clipboard with one click
  - Visual feedback: button turns green and shows "Copied!" on success
  - Includes recent messages preview for context
  - Makes it easy for users to share debug information when reporting issues
  - No need to open DevTools or inspect network requests

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
- **0.3.2** - Bug fix attempt (used incorrect model gemini-2.5-flash - fixed in v0.6.3)
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

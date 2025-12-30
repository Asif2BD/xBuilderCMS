# CLAUDE.md - XBuilder Project Context

> This document captures the complete research, discussions, and decisions made during the creation of XBuilder. Use this to understand the project's vision, architecture, and future direction.

**IMPORTANT: Read the [Versioning & Release Process](#versioning--release-process) section before making any code changes!**

---

## 🎯 Project Vision

**XBuilder** is an open-source, AI-powered static website generator that lets anyone create stunning, unique websites through natural conversation with AI.

### The Problem We're Solving

| User Segment | Pain Points | Our Solution |
|--------------|-------------|--------------|
| Non-technical users | Don't know HTML/CSS, intimidated by builders | Chat naturally, get a website |
| Busy professionals | No time to build portfolio from scratch | Upload CV → instant personalized site |
| Privacy-conscious | Don't trust SaaS with their data | Self-hosted, own your data |
| Cost-sensitive | Monthly fees for website builders | One-time setup, no recurring costs |

### Core Philosophy

```
❌ NOT template-based (all sites look same)
❌ NOT restrictive (limited options)
❌ NOT SaaS (recurring fees, vendor lock-in)

✅ AI-driven creativity (unique sites every time)
✅ Conversation-guided (AI asks the right questions)
✅ Data-aware (CV, LinkedIn, documents as input)
✅ Self-hosted (your server, your data)
✅ Pure static output (blazing fast, host anywhere)
```

---

## 🏗️ Architecture Decisions

### Why Pure PHP?

We evaluated three approaches:

| Approach | Pros | Cons | Decision |
|----------|------|------|----------|
| **Pure PHP** | Works on ANY hosting ($2/mo), no build tools, your expertise | No hot reload preview | ✅ CHOSEN |
| **PHP + Node.js** | Hot reload, React output | Complex setup, VPS required | ❌ |
| **Pure Node.js** | Single runtime | Needs VPS, not familiar territory | ❌ |

**Key insight**: The generated output can be just as beautiful with pure HTML/CSS/JS as with React. Modern CSS (Tailwind, animations, Grid, Flexbox) makes this possible. The difference is developer experience, not end-user experience.

### Why No Templates?

Traditional builders use templates → all sites look the same. Instead:

- **Prompt engineering is the product** - The AI system prompt is carefully crafted to generate unique designs
- AI asks discovery questions (profession, vibe, colors, audience)
- AI creates custom color palettes, typography, layouts for each user
- Every generated site feels custom-crafted, not cookie-cutter

### Why Single HTML File Output?

- Simpler to generate and iterate
- No build step required
- Instant preview
- Easy to publish (just copy one file)
- Tailwind CSS via CDN handles styling
- JavaScript embedded for interactivity

---

## 📁 Project Structure

```
xBuilderCMS/
├── index.php                    # Main entry point & router
├── .htaccess                    # Apache URL rewriting
├── .gitignore                   # Excludes storage/, site/*
├── LICENSE                      # MIT License
├── README.md                    # User documentation
├── CLAUDE.md                    # This file - AI context
│
├── xbuilder/                    # Admin application
│   ├── router.php               # Admin route handler
│   │
│   ├── api/                     # API endpoints
│   │   ├── setup.php            # Initial configuration
│   │   ├── login.php            # Authentication
│   │   ├── chat.php             # AI conversation
│   │   ├── upload.php           # File upload (CV, docs)
│   │   └── publish.php          # Publish to live site
│   │
│   ├── core/                    # Core classes
│   │   ├── AI.php               # AI provider abstraction
│   │   ├── Security.php         # Encryption & auth
│   │   ├── Config.php           # App configuration
│   │   ├── Conversation.php     # Chat history
│   │   └── Generator.php        # HTML file writer
│   │
│   ├── views/                   # UI templates
│   │   ├── setup.php            # Setup wizard
│   │   ├── login.php            # Login page
│   │   └── chat.php             # Main interface
│   │
│   └── storage/                 # Private data (gitignored)
│       ├── keys/                # Encrypted API keys
│       ├── conversations/       # Chat history JSON
│       └── uploads/             # Uploaded documents
│
└── site/                        # Generated website (public)
    └── index.html               # The generated site
```

---

## 🔐 Security Implementation

### API Key Storage

```php
// Keys encrypted with AES-256-CBC
// Server-specific encryption key derived from:
$entropy = __DIR__ . php_uname() . microtime(true) . random_bytes(32);
$key = hash('sha256', $entropy, true);

// Stored outside webroot conceptually, protected by .htaccess
// Location: /xbuilder/storage/keys/{provider}.key
```

### Password Hashing

```php
// Using Argon2id (strongest available)
password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 3
]);
```

### Protection Layers

1. `.htaccess` denies access to `/xbuilder/storage/`
2. API keys encrypted at rest
3. Session-based authentication with 24hr expiry
4. CSRF tokens on forms
5. Rate limiting on login (0.5s delay on failure)

---

## 🤖 AI Integration

### Supported Providers

| Provider | Model | Cost | Notes |
|----------|-------|------|-------|
| **Gemini** | gemini-1.5-flash | Free tier | Recommended for testing |
| **Claude** | claude-sonnet-4-20250514 | ~$0.03/site | Best quality |
| **OpenAI** | gpt-4o-mini | ~$0.02/site | Good balance |

### The System Prompt (Core of the Product)

The system prompt in `AI.php` is critical. Key elements:

1. **Role Definition**: "You are XBuilder, an expert web designer..."
2. **Design Philosophy**: Uniqueness, modern aesthetics, personality
3. **Design Principles**: Typography, color, layout, animation guidelines
4. **Conversation Flow**: Discovery → Data Gathering → Design Direction → Generation → Iteration
5. **Output Format**: Must use ```xbuilder-html code blocks
6. **Technical Requirements**: Tailwind, Google Fonts, semantic HTML, accessibility
7. **Anti-patterns**: Never generic, never Arial/Inter/Roboto, never template-looking

### Output Extraction

```php
// AI responses are parsed for HTML:
// 1. Look for ```xbuilder-html ... ```
// 2. Fallback to ```html ... ```
// 3. Fallback to <!DOCTYPE html> ... </html>
```

---

## 👤 User Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER JOURNEY                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. DEPLOY                                                       │
│     User uploads XBuilder to their PHP hosting                  │
│     Or: One-click install via xCloud.host                       │
│                                                                  │
│  2. FIRST VISIT → SETUP WIZARD                                  │
│     - Choose AI provider (Gemini recommended - free)            │
│     - Enter API key                                             │
│     - Create admin password                                     │
│                                                                  │
│  3. CHAT INTERFACE                                              │
│     - AI greets: "What kind of website would you like?"         │
│     - User can type OR upload CV/document                       │
│     - AI asks discovery questions                               │
│     - AI proposes design direction                              │
│                                                                  │
│  4. GENERATION                                                  │
│     - AI generates complete HTML/CSS/JS                         │
│     - Live preview appears in iframe                            │
│     - Code view available in tab                                │
│                                                                  │
│  5. ITERATION                                                   │
│     - "Make the hero bigger"                                    │
│     - "Change colors to teal"                                   │
│     - "Add a testimonials section"                              │
│     - Each change regenerates the site                          │
│                                                                  │
│  6. PUBLISH                                                     │
│     - One click → site live at domain root                      │
│     - User can continue editing anytime at /xbuilder/           │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Design Generation Guidelines

The AI should generate sites that are:

### Typography
- Use Google Fonts (Space Grotesk, Outfit, Syne, Clash Display, Playfair Display)
- NEVER use Arial, Inter, Roboto, system fonts
- Clear hierarchy with font sizes
- Line height 1.5-1.7

### Colors
- Unique palettes, not generic blue/gray
- Consider user's industry and personality
- Deep purples, warm terracottas, sage greens, electric blues
- Ensure accessibility contrast

### Layout
- Embrace whitespace
- CSS Grid and Flexbox
- Occasional asymmetry
- Mobile-first responsive

### Animation
- Subtle entrance animations (fade, slide)
- Smooth hover transitions
- Scroll-triggered reveals (Intersection Observer)
- Never overwhelming

### Visual Elements
- Gradient backgrounds
- Glassmorphism when appropriate
- SVG patterns
- Grain/noise textures for depth

---

## 📄 Document Upload Support

### Supported Formats
- PDF (via pdftotext or basic parsing)
- DOCX (via ZipArchive - it's XML inside)
- DOC (via antiword or text extraction)
- TXT, MD, JSON (direct read)

### Processing Flow
```
Upload → Validate (10MB max) → Extract Text → Clean → Store → Send to AI
```

### Text Extraction Code
- PDF: Uses `pdftotext` CLI if available, falls back to basic stream parsing
- DOCX: Opens as ZIP, reads `word/document.xml`, strips tags
- DOC: Uses `antiword` if available, falls back to ASCII extraction

---

## 🚀 xCloud.host Integration

### Planned Features
- One-click app install from xCloud dashboard
- Fork-based deployment from GitHub
- Automatic domain/subdomain setup
- Potential branding as "XBuilder" in xCloud marketplace

### Manifest File (To Create)
```json
{
  "name": "XBuilder",
  "description": "AI-powered website generator",
  "version": "1.0.0",
  "repository": "https://github.com/Asif2BD/xBuilderCMS",
  "requirements": {
    "php": ">=8.0",
    "extensions": ["curl", "json", "openssl", "zip"]
  },
  "setup": {
    "redirect": "/xbuilder/setup"
  }
}
```

---

## 🛠️ Development Commands

### Local Testing
```bash
# Start PHP built-in server
php -S localhost:8000

# Visit http://localhost:8000
# Will redirect to /xbuilder/setup on first visit
```

### Testing AI Integration
```bash
# Get free Gemini API key from:
# https://makersuite.google.com/app/apikey

# Test with curl:
curl -X POST http://localhost:8000/xbuilder/api/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "I want a portfolio site"}'
```

---

## 📋 TODO / Future Enhancements

### Phase 1: Polish (Current)
- [ ] Test all three AI providers thoroughly
- [ ] Improve error handling and user feedback
- [ ] Add loading states and progress indicators
- [ ] Test PDF/DOCX upload on various files
- [ ] Mobile responsive admin interface

### Phase 2: Features
- [ ] Export site as ZIP file
- [ ] Multiple pages support (About, Contact, etc.)
- [ ] Image upload and integration
- [ ] Custom domain instructions
- [ ] SEO meta tag editor
- [ ] Analytics integration helper

### Phase 3: xCloud Integration
- [ ] Create xCloud manifest
- [ ] One-click deployment
- [ ] Branded version for xCloud marketplace
- [ ] Usage analytics dashboard

### Phase 4: Advanced
- [ ] Multiple sites per installation
- [ ] User accounts (multi-tenant)
- [ ] Theme/style presets (optional)
- [ ] AI-powered SEO suggestions
- [ ] Performance optimization hints

---

## 👨‍💻 Creator Context

**Asif Rahman**
- 20 years WordPress ecosystem experience
- Companies: WPDeveloper, Templately, Storeware, xCloud.host, easy.jobs (Startise Group)
- Focus: AI integration with WordPress, MENA region
- Location: Dubai, UAE
- Currently exploring: Multi-agent AI workflows, SaaS platforms

### Related Projects
- xCloud.host - WordPress hosting platform (deployment target)
- WPDeveloper - WordPress plugins
- Exploring AI chatbot platforms, document automation

---

## 🔗 Repository

**GitHub**: https://github.com/Asif2BD/xBuilderCMS

### Branching Strategy
- `main` - Stable releases
- `develop` - Active development
- Feature branches as needed

### Contributing
1. Fork the repository
2. Create feature branch
3. Make changes
4. Submit PR

---

## 💡 Key Insights from Development

1. **Pure PHP was the right choice** - Maximizes deployment flexibility, minimizes complexity

2. **Prompt engineering > Templates** - The AI system prompt is the secret sauce

3. **Single HTML output simplifies everything** - No build step, instant preview, easy publish

4. **Security must be built-in** - API keys are sensitive, encryption is non-negotiable

5. **User experience matters** - The chat interface should feel like talking to a designer

6. **Iteration is key** - Users will want to refine their sites through conversation

---

## 🆘 Troubleshooting

### "API key invalid"
- Check key format (Claude: sk-ant-*, OpenAI: sk-*, Gemini: ~39 chars)
- Verify key has correct permissions
- Check API account has credits/quota

### "PDF text extraction failed"
- Install pdftotext: `apt install poppler-utils`
- Or upload DOCX/TXT instead

### "Site not updating after publish"
- Clear browser cache
- Check file permissions on /site/ directory
- Verify .htaccess is working

### "Session expired"
- Sessions last 24 hours
- Login again at /xbuilder/login

---

## 📞 Support

- **Issues**: GitHub Issues
- **Documentation**: README.md
- **AI Context**: This file (CLAUDE.md)

---

---

## 📋 Versioning & Release Process

**CRITICAL**: XBuilder follows **Semantic Versioning (SemVer)** and has an automated version management system.

### Current Version System

- **VERSION file**: Single source of truth (currently v0.3.0)
- **Automatic UI display**: Version shown in setup, login, and chat interfaces
- **Git tags**: Each version has a corresponding git tag (e.g., v0.3.0)
- **CHANGELOG.md**: Detailed release notes for every version
- **README.md**: Version badge and history sync with VERSION file

### When Claude Makes Changes

**IMPORTANT RULE**: If you make changes to 2+ files that add features or fix bugs, you MUST bump the version!

#### Version Bump Rules:

**PATCH (0.3.0 → 0.3.1) - Bug Fixes Only**
```bash
./bump-version.sh patch
```
Use when:
- Fixing bugs
- Security patches
- Performance improvements
- Documentation fixes (code-related)

**MINOR (0.3.0 → 0.4.0) - New Features**
```bash
./bump-version.sh minor
```
Use when:
- Adding new features
- Adding new AI provider support
- Adding new export formats
- Multi-server support
- UI improvements

**MAJOR (0.9.0 → 1.0.0) - Breaking Changes**
```bash
./bump-version.sh major
```
Use when:
- Breaking API changes
- Database schema changes
- Configuration format changes
- First stable release (0.x → 1.0.0)

### Automated Workflow

The `bump-version.sh` script handles everything:

1. **Updates VERSION file** (e.g., 0.3.0 → 0.4.0)
2. **Updates README.md** (badges and version references)
3. **Prompts for CHANGELOG.md update** (you must add release notes)
4. **Creates git commit** with message "chore: bump version to X.X.X"
5. **Creates git tag** (e.g., v0.4.0)
6. **Pushes to remote** (branch + tag)

### How to Use (Step-by-Step)

**After making changes:**

```bash
# 1. Determine version bump type
# If you added features → minor
# If you fixed bugs → patch
# If breaking changes → major

# 2. Run the script
./bump-version.sh minor

# 3. Confirm the version bump
# Current version: 0.3.0
# New version: 0.4.0
# Bump version from 0.3.0 to 0.4.0? (y/n) → y

# 4. Update CHANGELOG.md when prompted
# Add your changes under:
## [0.4.0] - 2025-12-30

### Added
- Feature description here

### Fixed
- Bug fix description here

# Press Enter when done

# 5. Confirm commit
# Commit version bump? (y/n) → y

# 6. Create tag
# Create git tag v0.4.0? (y/n) → y

# 7. Push to remote
# Push to remote (branch + tag)? (y/n) → y
```

### Manual Version Bump (If Script Unavailable)

If the script doesn't work, manually update:

1. **VERSION file**: `echo "0.4.0" > VERSION`
2. **README.md**: Update badge `version-0.4.0-blue` and "Current Version: 0.4.0"
3. **CHANGELOG.md**: Add new section `## [0.4.0] - 2025-12-30`
4. **xbuilder/core/Config.php**: Update fallback to `return '0.4.0';`
5. **Commit**: `git commit -m "chore: bump version to 0.4.0"`
6. **Tag**: `git tag -a v0.4.0 -m "Release version 0.4.0"`
7. **Push**: `git push origin main && git push origin v0.4.0`

### What NOT to Bump For

Don't bump version for:
- ❌ README-only updates
- ❌ CLAUDE.md updates
- ❌ Comment changes
- ❌ Code formatting (no logic change)
- ❌ .gitignore changes
- ❌ CI/CD config only

### Examples from Recent Work

**v0.3.0** (MINOR bump)
- Added version display in UI
- Added bump-version.sh script
- Added complete version history
= New features → MINOR bump ✅

**v0.2.0** (MINOR bump)
- Multi-server support (Nginx, OpenLiteSpeed)
- Deployment guides
- Fixed 403 error
= New features + bug fix → MINOR bump (higher category wins) ✅

**v0.1.4** (PATCH bump)
- Fixed Gemini API compatibility
= Bug fix only → PATCH bump ✅

### Version Display in UI

All admin pages read VERSION file and display:
```php
<?php
$versionFile = dirname(__DIR__, 2) . '/VERSION';
$version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '0.3.0';
?>
<!-- In footer -->
<div>XBuilder v<?php echo htmlspecialchars($version); ?></div>
```

Users see current version in:
- Setup wizard (bottom-right corner)
- Login page (bottom-right corner)
- Chat interface (bottom-left corner)

### GitHub Actions

On push to main with VERSION file change:
- Automatically creates git tag
- Creates GitHub Release
- Links to CHANGELOG.md

### Quick Reference

```bash
# Check current version
cat VERSION

# Bump version (interactive)
./bump-version.sh [patch|minor|major]

# View version history
cat CHANGELOG.md | head -50

# See version in README
grep "Current Version" README.md
```

### For Future Claude Sessions

**Before committing any code changes:**

1. ✅ Check if you modified 2+ files
2. ✅ Determine if changes are features (MINOR) or fixes (PATCH)
3. ✅ Run `./bump-version.sh [type]`
4. ✅ Update CHANGELOG.md with your changes
5. ✅ Confirm all prompts
6. ✅ Version is automatically bumped, tagged, and pushed!

**Never forget**: If you add features or fix bugs, the version MUST be bumped. It's not optional!

See [VERSIONING.md](VERSIONING.md) for complete documentation.

---

*Last updated: December 2025*
*Generated from Claude.ai conversation with Asif Rahman*

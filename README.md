# 🚀 XBuilder - AI-Powered Website Builder

[![Version](https://img.shields.io/badge/version-0.6.1-blue.svg)](https://github.com/Asif2BD/xBuilderCMS/blob/main/CHANGELOG.md)
[![License](https://img.shields.io/badge/license-AGPL--3.0-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://www.php.net/)

**Create stunning, unique websites through conversation with AI.**

XBuilder is an open-source, self-hosted website builder that uses AI (Claude, Gemini, or ChatGPT) to generate beautiful, production-ready static websites. Just chat with AI, describe what you want, upload your CV - and get a professional website in minutes.

> 📋 **[View Changelog](CHANGELOG.md)** | 🚀 **Current Version: 0.6.1** | 📖 **[Deployment Guide](DEPLOYMENT.md)**

![XBuilder Demo](https://via.placeholder.com/800x400?text=XBuilder+Demo)

## ✨ Features

- **🤖 Multi-AI Support** - Choose from Claude (Anthropic), Gemini (Google), or ChatGPT (OpenAI)
- **💬 Conversational Interface** - Build your website through natural conversation
- **📄 Document Upload** - Upload CV/resume (PDF, DOCX, TXT) and AI extracts the content
- **🎨 Unique Designs** - No templates! AI creates custom, beautiful designs every time
- **⚡ Pure Static Output** - Generated sites are pure HTML/CSS/JS - blazing fast
- **🔒 Self-Hosted** - Your data stays on your server
- **📱 Mobile Responsive** - All generated sites work perfectly on mobile
- **🔄 Iterative** - Keep chatting to refine and update your site

## 🖥️ Requirements

- PHP 8.0 or higher
- Web server (Apache or Nginx)
- cURL extension enabled
- JSON extension enabled
- An API key from Claude, Gemini, or OpenAI

## 🚀 Quick Start

### Option 1: Simple Upload (Any PHP Host)

1. Download or clone this repository
2. Upload all files to your web hosting
3. Ensure `.htaccess` is uploaded (enable "show hidden files")
4. Visit `yourdomain.com` and follow the setup wizard

### Option 2: Git Clone

```bash
git clone https://github.com/yourusername/xbuilder.git
cd xbuilder
```

Then point your web server to the directory.

### Option 3: xCloud.host (One-Click)

1. Login to your xCloud dashboard
2. Create new site → Select "XBuilder"
3. Follow the setup wizard

## 📁 Directory Structure

```
xbuilder/
├── index.php              # Main entry point
├── .htaccess              # Apache rewrite rules
├── xbuilder/              # Admin application
│   ├── router.php         # Admin routing
│   ├── api/               # API endpoints
│   │   ├── chat.php       # AI conversation
│   │   ├── upload.php     # File uploads
│   │   ├── publish.php    # Publish site
│   │   ├── setup.php      # Initial setup
│   │   └── login.php      # Authentication
│   ├── core/              # Core classes
│   │   ├── AI.php         # AI provider abstraction
│   │   ├── Security.php   # Encryption & auth
│   │   ├── Config.php     # Configuration
│   │   ├── Generator.php  # Site generation
│   │   └── Conversation.php # Chat history
│   ├── views/             # HTML templates
│   │   ├── setup.php      # Setup wizard
│   │   ├── login.php      # Login page
│   │   └── chat.php       # Main interface
│   └── storage/           # Private data (gitignored)
│       ├── keys/          # Encrypted API keys
│       ├── conversations/ # Chat history
│       └── uploads/       # Uploaded files
└── site/                  # Generated website (public)
    └── index.html         # Your generated site
```

## 🔑 Getting API Keys

### Gemini (Free Tier Available!)
1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Click "Create API Key"
3. Copy your key

### Claude (Anthropic)
1. Go to [Anthropic Console](https://console.anthropic.com/settings/keys)
2. Create a new API key
3. Copy your key

### ChatGPT (OpenAI)
1. Go to [OpenAI Platform](https://platform.openai.com/api-keys)
2. Create a new secret key
3. Copy your key

## 🔒 Security

XBuilder takes security seriously:

- **API keys are encrypted** using AES-256-CBC with a server-specific key
- **Passwords are hashed** using Argon2id
- **Storage directory is protected** from web access
- **CSRF protection** on all forms
- **No external dependencies** - everything runs on your server

## 💡 Usage Tips

### Creating a Portfolio
```
You: I want a portfolio website
AI: What do you do professionally?
You: I'm a software engineer with 5 years experience
AI: What vibe are you going for - minimal, bold, creative?
You: Minimal and dark theme
AI: [Generates beautiful dark minimal portfolio]
```

### Uploading Your CV
1. Click the 📎 button in the chat
2. Upload your CV (PDF, DOCX, or TXT)
3. AI will extract your information and ask follow-up questions
4. Your personalized website is generated!

### Iterating on Design
```
You: Make the hero section bigger
AI: [Updates the hero section]

You: Change the accent color to teal
AI: [Changes colors throughout the site]

You: Add a testimonials section
AI: [Adds a new testimonials section]
```

## 🛠️ Web Server Configuration

XBuilder works with **Apache**, **Nginx**, and **OpenLiteSpeed**. Configuration files are included for all three.

### Apache (Recommended for Shared Hosting)

✅ **No configuration needed!** The included `.htaccess` file handles everything automatically.

Just upload all files and visit your domain.

### Nginx

1. Copy the included `nginx.conf` file contents
2. Add to your Nginx server block (usually in `/etc/nginx/sites-available/`)
3. Update the paths:
   - `root /var/www/xBuilderCMS;` (your installation path)
   - `server_name yourdomain.com;` (your domain)
   - `fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;` (your PHP-FPM socket)
4. Test config: `sudo nginx -t`
5. Reload Nginx: `sudo systemctl reload nginx`

**Quick Setup:**
```bash
sudo nano /etc/nginx/sites-available/xbuilder
# Paste the nginx.conf contents and adjust paths
sudo ln -s /etc/nginx/sites-available/xbuilder /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

See the `nginx.conf` file for the complete configuration.

### OpenLiteSpeed

1. Rename `.htaccess.litespeed` to `.htaccess`
2. Or copy `.htaccess.litespeed` contents to your existing `.htaccess`
3. OpenLiteSpeed automatically reads `.htaccess` files

**Includes LiteSpeed Cache optimization for better performance!**

### Testing Your Setup

After configuring your web server:
1. Visit `http://yourdomain.com`
2. You should be redirected to `/xbuilder/setup`
3. Complete the setup wizard
4. Start building your website!

## 📋 Versioning & Changelog

XBuilder follows [Semantic Versioning](https://semver.org/) (SemVer):
- **MAJOR** version for incompatible API changes
- **MINOR** version for new features (backward compatible)
- **PATCH** version for bug fixes (backward compatible)

**Current Version: 0.5.0**

### Version History

**v0.5.0** (2025-12-30) - Debug Tools Enhancement
- ✅ Added Debug button for one-click troubleshooting
- ✅ Captures comprehensive debug info (version, browser, state, logs)
- ✅ Auto-captures console.log/warn/error (last 100 entries)
- ✅ Copies all debug data to clipboard with visual feedback
- ✅ Makes reporting issues easier for users without DevTools access

**v0.4.2** (2025-12-30) - Document Upload Critical Fix
- ✅ Fixed: Uploaded documents not reaching AI (cleared if < 100 chars)
- ✅ Now sends all extracted content to AI, even if imperfect
- ✅ Fixed version badge positioning in chat interface
- ✅ Added comprehensive debug logging

**v0.4.1** (2025-12-30) - PDF Extraction Improvements
- ✅ Improved pdftotext execution with UTF-8 encoding
- ✅ Better validation and error handling
- ✅ Recommends DOCX format for better results

**v0.4.0** (2025-12-30) - LinkedIn Integration & UX Improvements
- ✅ LinkedIn profile fetching - paste URL to auto-extract profile data
- ✅ No API key required - works with public LinkedIn profiles
- ✅ Improved deployment clarity - "Publish to Live Site" button
- ✅ Clear success messaging shows root domain URL
- ✅ Fixed version display bug in UI

**v0.3.3** (2025-12-30) - Document Upload Fix
- ✅ Fixed PDF/CV upload - AI now receives document content
- ✅ Installed pdftotext for proper PDF extraction
- ✅ Added word count and preview feedback
- ✅ Comprehensive logging and debugging

**v0.3.2** (2025-12-30) - Gemini API Compatibility Fix
- ✅ Updated to gemini-2.5-flash (current stable production model)
- ✅ Gemini 1.5 models are deprecated in Google's v1 API
- ✅ Gemini integration now fully functional

**v0.3.1** (2025-12-30) - Incomplete Fix
- ⚠️ Attempted Gemini fix with deprecated model name
- ❌ Used gemini-1.5-flash which is no longer available in v1 API

**v0.3.0** (2025-12-30) - Platform Enhancements
- ✅ Version display in all UI pages (Setup, Login, Chat)
- ✅ Automated version bump script (bump-version.sh)
- ✅ Complete version history in README
- ✅ Enhanced documentation and changelog system

**v0.2.0** (2025-12-30) - Multi-Server Support
- ✅ Full compatibility with Apache, Nginx, and OpenLiteSpeed
- ✅ Production-ready configurations for all three servers
- ✅ Comprehensive deployment guide (DEPLOYMENT.md)
- ✅ Fixed 403 directory access error

**v0.1.4** (2025-12-30) - Gemini API Fix
- ✅ Fixed Gemini API compatibility (v1beta → v1)
- ✅ Updated to gemini-1.5-flash-latest model

**v0.1.3** (2025-12-30) - License Update
- ✅ Updated license to AGPL-3.0
- ✅ Added personal attribution to README

**v0.1.2** (2025-12-28) - Codebase Unification
- ✅ Merged best features from parallel development branches
- ✅ Enhanced Security, Generator, AI, Config classes
- ✅ Unified instance-based architecture

**v0.1.1** (2025-12-28) - Security Enhancements
- ✅ Session namespacing and CSRF protection
- ✅ Argon2id password hashing
- ✅ Rate limiting on failed logins

**v0.1.0** (2025-12-28) - Initial Release 🎉
- 🚀 AI-powered website generation
- 🤖 Multi-provider support (Claude, Gemini, OpenAI)
- 📄 Document upload and parsing
- 🔒 Secure authentication and encryption
- 🎨 Unique, template-free designs

**v0.0.1** (2025-12-28) - Project Initialization
- 📦 Initial project structure

📖 **[View Full Changelog →](CHANGELOG.md)** for detailed changes, upgrade notes, and roadmap.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please read [CHANGELOG.md](CHANGELOG.md) to understand our versioning system.

## 📝 License

This project is licensed under the **GNU Affero General Public License v3.0** - see the [LICENSE](LICENSE) file for details.

The AGPL-3.0 license ensures that if you modify and deploy XBuilder on a server, you must make your source code available to users.

## 🙏 Acknowledgments

- [Tailwind CSS](https://tailwindcss.com/) - For the utility-first CSS framework
- [Google Fonts](https://fonts.google.com/) - For beautiful typography
- [Anthropic](https://anthropic.com/), [Google](https://ai.google/), [OpenAI](https://openai.com/) - For AI APIs

## 📧 Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/xbuilder/issues)
- **Documentation**: [Wiki](https://github.com/yourusername/xbuilder/wiki)

---

**Made with ❤️ by Asif Rahman**

*Powered by xCloud.host*

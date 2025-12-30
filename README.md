# 🚀 XBuilder - AI-Powered Website Builder

**Create stunning, unique websites through conversation with AI.**

XBuilder is an open-source, self-hosted website builder that uses AI (Claude, Gemini, or ChatGPT) to generate beautiful, production-ready static websites. Just chat with AI, describe what you want, upload your CV - and get a professional website in minutes.

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

## 🛠️ Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/xbuilder;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Protect storage directory
    location ~ ^/xbuilder/storage {
        deny all;
    }
}
```

### Apache Configuration

The included `.htaccess` file handles everything automatically.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

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

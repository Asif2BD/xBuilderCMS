# 🧪 XBuilder Testing & Verification Guide

## ✅ System Status: FULLY FUNCTIONAL

All core systems tested and verified working:
- ✅ HTML extraction from AI responses
- ✅ Preview file generation
- ✅ Publishing to live site
- ✅ Site accessibility at root domain
- ✅ AI understands XBuilder interface

---

## 🔬 Quick Test (30 seconds)

```bash
# Run the automated test suite
php test-workflow.php
```

**Expected output**: All green checkmarks ✅

---

## 🎯 Manual End-to-End Test

### Step 1: Access XBuilder Admin

```
http://yourdomain.com/xbuilder/
```

- If not set up, follow the setup wizard
- Choose AI provider (Gemini recommended - free tier)
- Enter your API key
- Create admin password

### Step 2: Upload CV/Document

**Best format**: DOCX (better extraction than PDF)

1. Click the upload area in chat
2. Select your CV file
3. Wait for extraction confirmation

### Step 3: Request Website

Type in chat:
```
Build a modern portfolio website from my CV
```

### Step 4: Verify AI Behavior

**AI Should Say**:
- ✅ "Check the Preview tab to see your website!"
- ✅ "Click 'Publish to Live Site' to deploy"

**AI Should NOT Say**:
- ❌ "Copy the code block and paste into a text editor"
- ❌ "Save as index.html and open in browser"
- ❌ "Deploy using Netlify, Vercel, etc."

### Step 5: Check Preview Tab

1. Click **"Preview"** tab (right panel)
2. You should see your website in an iframe
3. Verify it looks correct

### Step 6: Check Code Tab

1. Click **"Code"** tab
2. You should see the full HTML source
3. Verify it starts with `<!DOCTYPE html>`

### Step 7: Publish to Live Site

1. Click **"Publish to Live Site"** button (green, top-right)
2. Wait for success message
3. Note the URLs shown

### Step 8: Verify Live Site

Visit your root domain:
```
http://yourdomain.com/
```

**You should see**: Your generated portfolio website (not the XBuilder setup page)

---

## 🐛 Troubleshooting

### Issue: "HTML code appears in chat messages"

**What you'll see**: Raw HTML like `<!DOCTYPE html><html>...` in the chat

**Fix**: Already fixed in latest version
- HTML extraction improved
- Chat display now filters code blocks
- You should see "✨ Website generated!" instead

### Issue: "Preview tab is empty"

**Diagnosis**:
1. Click **Debug** button (top-right header)
2. Check output for:
   - `Has generated HTML: false` → Extraction failed
   - `HTML length: 0 chars` → No HTML extracted

**Common causes**:
- AI didn't generate code (ask it to create the website)
- Code block format wrong (AI should use ```xbuilder-html)
- Extraction regex failed (check console logs)

**Fix**:
- Clear conversation and start fresh
- Ask: "Please generate the website code now"
- Check browser console (F12) for errors

### Issue: "Publish button doesn't appear"

**Cause**: No HTML was extracted from AI response

**Fix**:
1. Check Preview tab - if empty, HTML extraction failed
2. Look at browser console for: `[XBuilder] No HTML in server response`
3. Click Debug button and share output

### Issue: "Published site not accessible at root domain"

**Diagnosis**:
```bash
# Check if file was created
ls -lh /path/to/xBuilderCMS/site/index.html

# Check file contents
head -20 /path/to/xBuilderCMS/site/index.html
```

**Expected**:
- File exists: `/site/index.html`
- Starts with: `<!DOCTYPE html>`
- Size: > 5KB (typical portfolio)

**Common causes**:
- File permissions (should be readable: `644`)
- .htaccess not configured correctly
- Site installed in subdirectory

**Fix for subdirectory installs**:
If XBuilder is at `http://yourdomain.com/xbuilder/`, the generated site will be at:
```
http://yourdomain.com/site/
```

Not at root. Move XBuilder to root for root domain deployment.

---

## 📊 Debug Button Output

The Debug button (top-right) captures comprehensive information:

### What it includes:
- **Version**: XBuilder version number
- **Browser**: User agent string
- **Conversation State**: Message count, document status
- **Document Details**: Length, preview of uploaded content
- **Generated HTML**: Whether HTML exists, length
- **Recent Messages**: Last 5 conversation messages
- **Console Logs**: All console.log/warn/error output

### How to use:
1. Click **Debug** button
2. Info copied to clipboard
3. Paste here or in GitHub issue
4. Helps diagnose extraction/display issues

---

## 🔍 Server-Side Logs

If you have shell access, check PHP error logs:

```bash
# Common log locations
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
tail -f /var/log/php-fpm/error.log

# Look for XBuilder-specific logs:
grep "XBuilder" /var/log/apache2/error.log
```

### What to look for:

**HTML Extraction**:
```
[XBuilder AI] Attempting to extract HTML from response (length: X)
[XBuilder AI] Found xbuilder-html code block
[XBuilder AI] Successfully extracted HTML from Claude response (X chars)
```

**Document Upload**:
```
[XBuilder Upload] File: resume.docx, Size: X bytes, Extracted: X chars
[XBuilder Chat] Received document: X chars
[XBuilder AI] Document provided: X chars
```

**Common errors**:
```
[XBuilder AI] WARNING: No HTML extracted from response
[XBuilder Chat] No document content
[XBuilder PDF] Extraction failed or returned too little text
```

---

## 🧩 Component-Level Testing

### Test HTML Extraction Only

```php
<?php
require_once 'xbuilder/core/Security.php';
require_once 'xbuilder/core/Config.php';
require_once 'xbuilder/core/AI.php';

$ai = new XBuilder\Core\AI('claude');

$response = <<<TXT
Here's your website:

```xbuilder-html
<!DOCTYPE html>
<html><head><title>Test</title></head><body><h1>Hello</h1></body></html>
```

Check the Preview tab!
TXT;

$reflection = new ReflectionClass($ai);
$method = $reflection->getMethod('extractHtml');
$method->setAccessible(true);
$html = $method->invoke($ai, $response);

echo $html ? "✅ Extracted: " . strlen($html) . " chars\n" : "❌ Failed\n";
```

### Test Publish Only

```php
<?php
require_once 'xbuilder/core/Generator.php';

$generator = new XBuilder\Core\Generator();

$html = '<!DOCTYPE html><html><head><title>Test</title></head><body><h1>Test Site</h1></body></html>';

$result = $generator->saveHtml($html, 'index.html');

if ($result['success']) {
    echo "✅ Published to: {$result['path']}\n";
    echo "✅ URL: {$result['url']}\n";
} else {
    echo "❌ Failed: {$result['error']}\n";
}
```

---

## 📁 File Structure After Publishing

```
xBuilderCMS/
├── site/                          # PUBLIC - Root domain content
│   ├── index.html                 # ← Your published website (LIVE)
│   ├── _preview.html              # Temporary preview (not public)
│   ├── css/                       # Optional CSS files
│   ├── js/                        # Optional JS files
│   └── images/                    # Optional images
│
├── xbuilder/                      # PRIVATE - Admin panel
│   ├── storage/
│   │   ├── backups/               # Auto-backups before each publish
│   │   │   ├── site_2025-12-30_104530.html
│   │   │   └── site_2025-12-30_112245.html
│   │   ├── conversations/         # Chat history
│   │   ├── keys/                  # Encrypted API keys
│   │   └── uploads/               # Uploaded CVs/documents
│   │
│   └── views/chat.php             # Main chat interface
│
└── test-workflow.php              # Automated test script
```

**Live Website URL**: `http://yourdomain.com/`
**Admin Panel URL**: `http://yourdomain.com/xbuilder/`

---

## ✅ Verification Checklist

### Before Reporting Issues:

- [ ] Ran `php test-workflow.php` - all tests pass?
- [ ] Checked browser console (F12) - any errors?
- [ ] Clicked Debug button - copied output?
- [ ] Verified `/site/index.html` exists?
- [ ] Confirmed AI used ```xbuilder-html format?
- [ ] Checked AI didn't say "copy and paste"?
- [ ] Tried with DOCX instead of PDF?
- [ ] Visited root domain to check live site?

---

## 🚀 Performance Benchmarks

Typical generation times:

| Task | Expected Time |
|------|---------------|
| Upload CV (DOCX) | 1-3 seconds |
| AI generate code | 15-45 seconds |
| Preview render | Instant |
| Publish to live | < 1 second |
| **Total workflow** | **~30-60 seconds** |

---

## 🔐 Security Notes

- **API Keys**: Encrypted with AES-256-CBC, stored in `/xbuilder/storage/keys/`
- **Backups**: Auto-created before each publish, stored in `/xbuilder/storage/backups/`
- **File Permissions**: Site files are `644` (readable by web server)
- **Protected Paths**: `.htaccess` blocks access to `/xbuilder/storage/`

---

## 📞 Support

If tests fail or you see issues:

1. **Run the test**: `php test-workflow.php`
2. **Click Debug button** in chat interface
3. **Check browser console** (F12 → Console tab)
4. **Share all three outputs** in GitHub issue

This gives complete diagnostic information.

---

**Last Updated**: 2025-12-30
**XBuilder Version**: 0.5.0+

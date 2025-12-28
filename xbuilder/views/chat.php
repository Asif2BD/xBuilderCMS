<?php
/**
 * XBuilder Chat Interface View
 *
 * Main interface for chatting with AI and building websites.
 */

use XBuilder\Core\Security;
use XBuilder\Core\Config;
use XBuilder\Core\Conversation;
use XBuilder\Core\Generator;
use XBuilder\Core\AI;

$csrfToken = Security::generateCsrfToken();
$conversation = new Conversation();
$messages = $conversation->getMessages();
$generatedHtml = $conversation->getGeneratedHtml();
$hasPreview = Generator::hasPreview();
$isPublished = Generator::isPublished();
$provider = Config::getAiProvider();
$providerName = AI::getProviderName($provider);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XBuilder - Create Your Website</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Space Grotesk', sans-serif; }
        code, pre, .code { font-family: 'JetBrains Mono', monospace; }

        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chat-container {
            height: calc(100vh - 180px);
        }

        .message {
            animation: messageIn 0.3s ease forwards;
        }

        @keyframes messageIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user-message {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .ai-message {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .typing-indicator span {
            animation: typing 1.4s infinite;
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            margin: 0 2px;
        }

        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.3; }
            30% { transform: translateY(-5px); opacity: 1; }
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }

        .btn-success:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }

        textarea:focus, input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .preview-frame {
            background: white;
            border-radius: 8px;
        }

        .tab-active {
            border-bottom: 2px solid #3b82f6;
            color: #3b82f6;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }

        /* Code block styling */
        .code-block {
            background: #1e1e2e;
            border-radius: 8px;
            overflow: hidden;
        }

        .code-block pre {
            padding: 1rem;
            overflow-x: auto;
            font-size: 0.875rem;
            line-height: 1.5;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <!-- Header -->
    <header class="glass border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-xl font-bold">
                    <span class="text-blue-400">X</span>Builder
                </h1>
                <span class="text-xs text-gray-500 hidden sm:inline">Powered by <?php echo Security::sanitize($providerName); ?></span>
            </div>

            <div class="flex items-center space-x-3">
                <?php if ($hasPreview): ?>
                <button onclick="publishSite()" id="publishBtn" class="btn-success px-4 py-2 rounded-lg text-sm font-medium flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Publish</span>
                </button>
                <?php endif; ?>

                <button onclick="clearConversation()" class="btn-secondary px-3 py-2 rounded-lg text-sm" title="Start New">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>

                <a href="/xbuilder/logout" class="btn-secondary px-3 py-2 rounded-lg text-sm" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 chat-container">
            <!-- Chat Panel -->
            <div class="glass rounded-2xl flex flex-col overflow-hidden">
                <!-- Messages -->
                <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <?php if (empty($messages)): ?>
                    <!-- Welcome Message -->
                    <div class="ai-message message rounded-2xl p-4 max-w-[85%]">
                        <p class="text-gray-300">
                            Hello! I'm XBuilder, your AI-powered website designer. I'll help you create a stunning, unique website through our conversation.
                        </p>
                        <p class="text-gray-300 mt-3">
                            To get started, tell me:
                        </p>
                        <ul class="text-gray-400 mt-2 space-y-1 text-sm">
                            <li>What type of website do you want? (portfolio, business, personal)</li>
                            <li>Or upload your CV/resume and I'll create a professional portfolio for you!</li>
                        </ul>
                    </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="<?php echo $msg['role'] === 'user' ? 'user-message ml-auto' : 'ai-message'; ?> message rounded-2xl p-4 max-w-[85%]">
                            <div class="whitespace-pre-wrap"><?php echo nl2br(Security::sanitize($msg['content'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Typing Indicator (hidden by default) -->
                    <div id="typingIndicator" class="ai-message message rounded-2xl p-4 max-w-[85%] hidden">
                        <div class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-4 border-t border-white/10">
                    <form id="chatForm" class="flex space-x-2">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <!-- File Upload Button -->
                        <label class="btn-secondary p-3 rounded-xl cursor-pointer flex-shrink-0" title="Upload CV/Resume">
                            <input type="file" id="fileInput" class="hidden" accept=".pdf,.doc,.docx,.txt,.md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </label>

                        <!-- Message Input -->
                        <textarea
                            id="messageInput"
                            name="message"
                            class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 resize-none"
                            placeholder="Describe your website..."
                            rows="1"
                        ></textarea>

                        <!-- Send Button -->
                        <button type="submit" id="sendBtn" class="btn-primary p-3 rounded-xl flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </form>

                    <!-- File upload indicator -->
                    <div id="fileIndicator" class="hidden mt-2 text-sm text-gray-400 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span id="fileName"></span>
                        <button type="button" onclick="clearFile()" class="ml-2 text-red-400 hover:text-red-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="glass rounded-2xl flex flex-col overflow-hidden">
                <!-- Tabs -->
                <div class="flex border-b border-white/10">
                    <button onclick="showTab('preview')" id="tabPreview" class="tab-active px-6 py-3 text-sm font-medium">
                        Preview
                    </button>
                    <button onclick="showTab('code')" id="tabCode" class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-white">
                        Code
                    </button>
                    <?php if ($isPublished): ?>
                    <a href="/" target="_blank" class="ml-auto px-4 py-3 text-sm text-blue-400 hover:text-blue-300 flex items-center">
                        View Live Site
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Preview Content -->
                <div id="previewContent" class="flex-1 overflow-hidden">
                    <?php if ($hasPreview): ?>
                    <iframe id="previewFrame" src="/xbuilder/preview" class="w-full h-full preview-frame"></iframe>
                    <?php else: ?>
                    <div class="h-full flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p>Your website preview will appear here</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Code Content (hidden by default) -->
                <div id="codeContent" class="flex-1 overflow-auto hidden">
                    <?php if ($generatedHtml): ?>
                    <div class="code-block h-full">
                        <pre class="text-gray-300 h-full"><code id="codeDisplay"><?php echo Security::sanitize($generatedHtml); ?></code></pre>
                    </div>
                    <?php else: ?>
                    <div class="h-full flex items-center justify-center text-gray-500">
                        <p>No code generated yet</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 transform translate-y-20 opacity-0 transition-all duration-300">
        <div class="glass rounded-xl px-6 py-4 flex items-center space-x-3">
            <span id="toastIcon"></span>
            <span id="toastMessage"></span>
        </div>
    </div>

    <script>
        // State
        let selectedFile = null;
        let isLoading = false;

        // Elements
        const messagesEl = document.getElementById('messages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const fileInput = document.getElementById('fileInput');
        const typingIndicator = document.getElementById('typingIndicator');

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });

        // Enter to send (Shift+Enter for new line)
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        // File selection
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                selectedFile = this.files[0];
                document.getElementById('fileName').textContent = selectedFile.name;
                document.getElementById('fileIndicator').classList.remove('hidden');
            }
        });

        function clearFile() {
            selectedFile = null;
            fileInput.value = '';
            document.getElementById('fileIndicator').classList.add('hidden');
        }

        // Tab switching
        function showTab(tab) {
            const previewContent = document.getElementById('previewContent');
            const codeContent = document.getElementById('codeContent');
            const tabPreview = document.getElementById('tabPreview');
            const tabCode = document.getElementById('tabCode');

            if (tab === 'preview') {
                previewContent.classList.remove('hidden');
                codeContent.classList.add('hidden');
                tabPreview.classList.add('tab-active');
                tabCode.classList.remove('tab-active');
                tabCode.classList.add('text-gray-400');
            } else {
                previewContent.classList.add('hidden');
                codeContent.classList.remove('hidden');
                tabCode.classList.add('tab-active');
                tabPreview.classList.remove('tab-active');
                tabPreview.classList.add('text-gray-400');
            }
        }

        // Add message to chat
        function addMessage(role, content) {
            const div = document.createElement('div');
            div.className = `${role === 'user' ? 'user-message ml-auto' : 'ai-message'} message rounded-2xl p-4 max-w-[85%]`;
            div.innerHTML = `<div class="whitespace-pre-wrap">${escapeHtml(content)}</div>`;
            messagesEl.insertBefore(div, typingIndicator);
            scrollToBottom();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }

        function scrollToBottom() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function showTyping() {
            typingIndicator.classList.remove('hidden');
            scrollToBottom();
        }

        function hideTyping() {
            typingIndicator.classList.add('hidden');
        }

        // Toast notifications
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');

            const icons = {
                success: '<svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                error: '<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                info: '<svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            };

            icon.innerHTML = icons[type] || icons.info;
            msg.textContent = message;

            toast.classList.remove('translate-y-20', 'opacity-0');

            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Form submission
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isLoading) return;

            const message = messageInput.value.trim();
            if (!message && !selectedFile) return;

            isLoading = true;
            sendBtn.disabled = true;

            // Add user message
            if (message) {
                addMessage('user', message);
            }
            if (selectedFile) {
                addMessage('user', `[Uploaded: ${selectedFile.name}]`);
            }

            messageInput.value = '';
            messageInput.style.height = 'auto';
            showTyping();

            try {
                let response;

                if (selectedFile) {
                    // Upload file first
                    const uploadData = new FormData();
                    uploadData.append('file', selectedFile);
                    uploadData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

                    const uploadResponse = await fetch('/xbuilder/api/upload', {
                        method: 'POST',
                        body: uploadData
                    });

                    const uploadResult = await uploadResponse.json();

                    if (!uploadResult.success) {
                        throw new Error(uploadResult.error || 'Upload failed');
                    }

                    clearFile();

                    // Now send message with uploaded content
                    const chatData = new FormData();
                    chatData.append('message', message || 'Please create a website based on my uploaded document.');
                    chatData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

                    response = await fetch('/xbuilder/api/chat', {
                        method: 'POST',
                        body: chatData
                    });
                } else {
                    // Just send message
                    const formData = new FormData();
                    formData.append('message', message);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

                    response = await fetch('/xbuilder/api/chat', {
                        method: 'POST',
                        body: formData
                    });
                }

                const data = await response.json();
                hideTyping();

                if (data.success) {
                    addMessage('assistant', data.content);

                    // Update preview if HTML was generated
                    if (data.hasHtml) {
                        updatePreview(data.html);
                        showToast('Website preview updated!', 'success');

                        // Show publish button if not already visible
                        if (!document.getElementById('publishBtn')) {
                            location.reload();
                        }
                    }
                } else {
                    throw new Error(data.error || 'Failed to get response');
                }
            } catch (error) {
                hideTyping();
                addMessage('assistant', 'Sorry, something went wrong: ' + error.message);
                showToast(error.message, 'error');
            }

            isLoading = false;
            sendBtn.disabled = false;
            messageInput.focus();
        });

        function updatePreview(html) {
            const previewContent = document.getElementById('previewContent');
            const codeContent = document.getElementById('codeContent');

            // Update preview iframe
            let iframe = document.getElementById('previewFrame');
            if (!iframe) {
                previewContent.innerHTML = '<iframe id="previewFrame" src="/xbuilder/preview" class="w-full h-full preview-frame"></iframe>';
                iframe = document.getElementById('previewFrame');
            }
            iframe.src = '/xbuilder/preview?' + Date.now();

            // Update code display
            const codeDisplay = document.getElementById('codeDisplay');
            if (codeDisplay) {
                codeDisplay.textContent = html;
            } else {
                codeContent.innerHTML = `<div class="code-block h-full"><pre class="text-gray-300 h-full"><code id="codeDisplay">${escapeHtml(html)}</code></pre></div>`;
            }
        }

        // Publish site
        async function publishSite() {
            const btn = document.getElementById('publishBtn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            try {
                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

                const response = await fetch('/xbuilder/api/publish', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Website published successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Publish failed');
                }
            } catch (error) {
                showToast(error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Publish</span>';
            }
        }

        // Clear conversation
        async function clearConversation() {
            if (!confirm('Start a new conversation? This will clear the current chat and preview.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

                await fetch('/xbuilder/api/clear', {
                    method: 'POST',
                    body: formData
                });

                location.reload();
            } catch (error) {
                showToast('Failed to clear conversation', 'error');
            }
        }

        // Scroll to bottom on load
        scrollToBottom();
    </script>
</body>
</html>

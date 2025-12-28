<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XBuilder - AI Website Builder</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Space Grotesk', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        dark: {
                            900: '#0a0a0f',
                            800: '#12121a',
                            700: '#1a1a25',
                            600: '#252533',
                            500: '#32324a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: #0a0a0f;
        }
        
        /* Chat messages */
        .message {
            animation: messageIn 0.3s ease-out;
        }
        
        @keyframes messageIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Typing indicator */
        .typing-dot {
            animation: typingBounce 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        /* Loading spinner */
        .loading-spinner {
            border: 2px solid rgba(255,255,255,0.1);
            border-top-color: #6366f1;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Code syntax highlighting (basic) */
        .code-block {
            background: #1a1a25;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .code-block pre {
            padding: 1rem;
            overflow-x: auto;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        /* Preview iframe */
        #previewFrame {
            background: white;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #32324a;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4a4a6a;
        }
        
        /* Drag and drop */
        .drop-zone.drag-over {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }
        
        /* Tab active state */
        .tab-btn.active {
            color: white;
            border-color: #6366f1;
        }
    </style>
</head>
<body class="font-sans text-white h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-dark-800 border-b border-dark-600 px-4 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🚀</span>
            <h1 class="text-lg font-semibold">XBuilder</h1>
        </div>
        
        <div class="flex items-center gap-3">
            <button onclick="startNewConversation()" 
                    class="px-3 py-1.5 text-sm bg-dark-700 hover:bg-dark-600 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New
            </button>
            
            <a href="/xbuilder/logout" 
               class="px-3 py-1.5 text-sm text-gray-400 hover:text-white transition">
                Logout
            </a>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Chat Panel -->
        <div class="w-1/2 flex flex-col border-r border-dark-600">
            <!-- Messages -->
            <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4">
                <!-- Welcome message will be added by JS -->
            </div>
            
            <!-- Upload Area (hidden by default) -->
            <div id="uploadArea" class="hidden px-4 pb-2">
                <div class="drop-zone border-2 border-dashed border-dark-500 rounded-xl p-4 text-center transition-colors">
                    <input type="file" id="fileInput" class="hidden" accept=".pdf,.doc,.docx,.txt,.json">
                    <label for="fileInput" class="cursor-pointer">
                        <div class="text-3xl mb-2">📄</div>
                        <p class="text-sm text-gray-400">
                            Drop your CV/document here or <span class="text-indigo-400">click to browse</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, TXT supported</p>
                    </label>
                </div>
                <div id="uploadStatus" class="hidden mt-2 text-sm"></div>
            </div>
            
            <!-- Input Area -->
            <div class="p-4 border-t border-dark-600 shrink-0">
                <div class="flex gap-2">
                    <button onclick="toggleUpload()" 
                            class="p-3 bg-dark-700 hover:bg-dark-600 rounded-xl transition" 
                            title="Upload document">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    </button>
                    
                    <div class="flex-1 relative">
                        <textarea id="userInput" 
                                  rows="1"
                                  class="w-full px-4 py-3 bg-dark-700 border border-dark-600 rounded-xl focus:border-indigo-500 focus:outline-none resize-none"
                                  placeholder="Describe the website you want to create..."
                                  onkeydown="handleKeydown(event)"
                                  oninput="autoResize(this)"></textarea>
                    </div>
                    
                    <button onclick="sendMessage()" id="sendBtn"
                            class="p-3 bg-indigo-600 hover:bg-indigo-700 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Preview Panel -->
        <div class="w-1/2 flex flex-col bg-dark-900">
            <!-- Tabs -->
            <div class="flex border-b border-dark-600 shrink-0">
                <button onclick="showTab('preview')" 
                        class="tab-btn active px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-white transition"
                        data-tab="preview">
                    Preview
                </button>
                <button onclick="showTab('code')" 
                        class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-white transition"
                        data-tab="code">
                    Code
                </button>
                
                <div class="flex-1"></div>
                
                <button onclick="publishSite()" id="publishBtn"
                        class="hidden m-2 px-4 py-1.5 text-sm bg-green-600 hover:bg-green-700 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Publish
                </button>
            </div>
            
            <!-- Preview Content -->
            <div id="previewTab" class="flex-1 overflow-hidden">
                <div id="previewPlaceholder" class="h-full flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="text-6xl mb-4 opacity-50">🎨</div>
                        <p>Your website preview will appear here</p>
                        <p class="text-sm mt-2">Start chatting to generate your site</p>
                    </div>
                </div>
                <iframe id="previewFrame" class="hidden w-full h-full border-0"></iframe>
            </div>
            
            <!-- Code Content -->
            <div id="codeTab" class="hidden flex-1 overflow-hidden">
                <div id="codePlaceholder" class="h-full flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <div class="text-6xl mb-4 opacity-50">📝</div>
                        <p>Generated code will appear here</p>
                    </div>
                </div>
                <div id="codeContent" class="hidden h-full overflow-auto">
                    <pre class="p-4 font-mono text-sm text-gray-300 whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // State
        let conversationHistory = [];
        let generatedHtml = null;
        let isLoading = false;
        let uploadedDocument = null;
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadConversation();
            setupDropZone();
        });
        
        // Load existing conversation
        async function loadConversation() {
            try {
                const response = await fetch('/xbuilder/api/chat?action=load');
                const data = await response.json();
                
                if (data.messages && data.messages.length > 0) {
                    conversationHistory = data.messages;
                    data.messages.forEach(msg => {
                        addMessageToUI(msg.role, msg.content, false);
                    });
                    
                    if (data.generated_html) {
                        generatedHtml = data.generated_html;
                        showPreview(generatedHtml);
                    }
                } else {
                    // Show welcome message
                    addMessageToUI('assistant', getWelcomeMessage(), false);
                }
            } catch (error) {
                addMessageToUI('assistant', getWelcomeMessage(), false);
            }
        }
        
        function getWelcomeMessage() {
            return `Hey! 👋 I'm excited to help you create a website.

What are we building today - a portfolio, a business site, or something else?

If you have a **CV** or **LinkedIn profile**, feel free to share it and I'll craft something that really captures who you are.`;
        }
        
        // Send message
        async function sendMessage() {
            const input = document.getElementById('userInput');
            const message = input.value.trim();
            
            if (!message || isLoading) return;
            
            // Add user message to UI
            addMessageToUI('user', message);
            conversationHistory.push({ role: 'user', content: message });
            
            // Clear input
            input.value = '';
            autoResize(input);
            
            // Show typing indicator
            showTypingIndicator();
            isLoading = true;
            
            try {
                const response = await fetch('/xbuilder/api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: message,
                        history: conversationHistory.slice(0, -1), // Don't include the message we just added
                        document: uploadedDocument
                    })
                });
                
                const data = await response.json();
                
                hideTypingIndicator();
                isLoading = false;
                
                if (data.success) {
                    // Add AI response to UI
                    addMessageToUI('assistant', data.message);
                    conversationHistory.push({ role: 'assistant', content: data.message });
                    
                    // Check if HTML was generated
                    if (data.html) {
                        generatedHtml = data.html;
                        showPreview(generatedHtml);
                        document.getElementById('publishBtn').classList.remove('hidden');
                    }
                } else {
                    addMessageToUI('assistant', '⚠️ ' + (data.error || 'Something went wrong. Please try again.'));
                }
            } catch (error) {
                hideTypingIndicator();
                isLoading = false;
                addMessageToUI('assistant', '⚠️ Connection error. Please check your internet and try again.');
            }
            
            // Clear uploaded document after first use
            uploadedDocument = null;
        }
        
        // Add message to UI
        function addMessageToUI(role, content, animate = true) {
            const messagesDiv = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            
            messageDiv.className = `message ${role === 'user' ? 'ml-auto max-w-[85%]' : 'max-w-[85%]'}`;
            
            const bubble = document.createElement('div');
            bubble.className = role === 'user' 
                ? 'bg-indigo-600 rounded-2xl rounded-br-md px-4 py-3'
                : 'bg-dark-700 rounded-2xl rounded-bl-md px-4 py-3';
            
            // Parse markdown-like formatting
            bubble.innerHTML = formatMessage(content);
            
            messageDiv.appendChild(bubble);
            messagesDiv.appendChild(messageDiv);
            
            // Scroll to bottom
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        // Format message with basic markdown
        function formatMessage(content) {
            // Escape HTML
            let html = content
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            
            // Bold
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Italic
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            // Code blocks (remove from display, we'll show in code tab)
            html = html.replace(/```[\w-]*\n([\s\S]*?)```/g, '<div class="my-2 p-2 bg-dark-800 rounded text-sm text-indigo-300">✨ Website code generated! Check the Preview tab.</div>');
            
            // Inline code
            html = html.replace(/`([^`]+)`/g, '<code class="bg-dark-800 px-1.5 py-0.5 rounded text-sm">$1</code>');
            
            // Line breaks
            html = html.replace(/\n/g, '<br>');
            
            return html;
        }
        
        // Typing indicator
        function showTypingIndicator() {
            const messagesDiv = document.getElementById('messages');
            const indicator = document.createElement('div');
            indicator.id = 'typingIndicator';
            indicator.className = 'message max-w-[85%]';
            indicator.innerHTML = `
                <div class="bg-dark-700 rounded-2xl rounded-bl-md px-4 py-3 flex gap-1">
                    <div class="typing-dot w-2 h-2 bg-gray-400 rounded-full"></div>
                    <div class="typing-dot w-2 h-2 bg-gray-400 rounded-full"></div>
                    <div class="typing-dot w-2 h-2 bg-gray-400 rounded-full"></div>
                </div>
            `;
            messagesDiv.appendChild(indicator);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        function hideTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }
        
        // Preview
        function showPreview(html) {
            const placeholder = document.getElementById('previewPlaceholder');
            const frame = document.getElementById('previewFrame');
            const codeContent = document.getElementById('codeContent');
            const codePlaceholder = document.getElementById('codePlaceholder');
            
            // Update preview
            placeholder.classList.add('hidden');
            frame.classList.remove('hidden');
            frame.srcdoc = html;
            
            // Update code view
            codePlaceholder.classList.add('hidden');
            codeContent.classList.remove('hidden');
            codeContent.querySelector('pre').textContent = html;
        }
        
        // Tabs
        function showTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.tab === tab);
            });
            
            document.getElementById('previewTab').classList.toggle('hidden', tab !== 'preview');
            document.getElementById('codeTab').classList.toggle('hidden', tab !== 'code');
        }
        
        // Publish
        async function publishSite() {
            if (!generatedHtml) return;
            
            const btn = document.getElementById('publishBtn');
            btn.innerHTML = '<div class="loading-spinner"></div><span>Publishing...</span>';
            btn.disabled = true;
            
            try {
                const response = await fetch('/xbuilder/api/publish', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ html: generatedHtml })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    btn.innerHTML = '✓ Published!';
                    btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    btn.classList.add('bg-emerald-600');
                    
                    // Show success message
                    addMessageToUI('assistant', `🎉 **Your website is live!**\n\nVisit it at: [${window.location.origin}](${window.location.origin})\n\nYou can continue chatting to make changes, or close this tab.`);
                    
                    setTimeout(() => {
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Publish</span>';
                        btn.classList.add('bg-green-600', 'hover:bg-green-700');
                        btn.classList.remove('bg-emerald-600');
                        btn.disabled = false;
                    }, 3000);
                } else {
                    alert('Failed to publish: ' + (data.error || 'Unknown error'));
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Publish</span>';
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Connection error. Please try again.');
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Publish</span>';
                btn.disabled = false;
            }
        }
        
        // File upload
        function toggleUpload() {
            const uploadArea = document.getElementById('uploadArea');
            uploadArea.classList.toggle('hidden');
        }
        
        function setupDropZone() {
            const dropZone = document.querySelector('.drop-zone');
            const fileInput = document.getElementById('fileInput');
            
            if (!dropZone) return;
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'));
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'));
            });
            
            dropZone.addEventListener('drop', e => {
                const files = e.dataTransfer.files;
                if (files.length) handleFile(files[0]);
            });
            
            fileInput.addEventListener('change', e => {
                if (e.target.files.length) handleFile(e.target.files[0]);
            });
        }
        
        async function handleFile(file) {
            const status = document.getElementById('uploadStatus');
            status.classList.remove('hidden');
            status.innerHTML = '<span class="text-indigo-400">Uploading...</span>';
            
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const response = await fetch('/xbuilder/api/upload', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    uploadedDocument = data.content;
                    status.innerHTML = `<span class="text-green-400">✓ ${file.name} uploaded</span>`;
                    
                    // Add message about the upload
                    addMessageToUI('user', `📄 Uploaded: ${file.name}`);
                    conversationHistory.push({ 
                        role: 'user', 
                        content: `I've uploaded my document: ${file.name}. Here's the content:\n\n${data.content.substring(0, 500)}...`
                    });
                    
                    // Hide upload area after success
                    setTimeout(() => {
                        document.getElementById('uploadArea').classList.add('hidden');
                    }, 1500);
                } else {
                    status.innerHTML = `<span class="text-red-400">✗ ${data.error || 'Upload failed'}</span>`;
                }
            } catch (error) {
                status.innerHTML = '<span class="text-red-400">✗ Upload failed</span>';
            }
        }
        
        // New conversation
        async function startNewConversation() {
            if (!confirm('Start a new conversation? This will clear the current chat.')) return;
            
            try {
                await fetch('/xbuilder/api/chat?action=clear', { method: 'POST' });
                
                // Clear UI
                document.getElementById('messages').innerHTML = '';
                document.getElementById('previewPlaceholder').classList.remove('hidden');
                document.getElementById('previewFrame').classList.add('hidden');
                document.getElementById('codePlaceholder').classList.remove('hidden');
                document.getElementById('codeContent').classList.add('hidden');
                document.getElementById('publishBtn').classList.add('hidden');
                
                // Reset state
                conversationHistory = [];
                generatedHtml = null;
                uploadedDocument = null;
                
                // Show welcome message
                addMessageToUI('assistant', getWelcomeMessage(), false);
            } catch (error) {
                console.error('Failed to start new conversation');
            }
        }
        
        // Keyboard handling
        function handleKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }
        
        // Auto-resize textarea
        function autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 200) + 'px';
        }
    </script>
</body>
</html>

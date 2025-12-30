<?php
// Get XBuilder version
$versionFile = dirname(__DIR__, 2) . '/VERSION';
$version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '0.5.0';
?>
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

        /* Resize handle */
        .resize-handle {
            width: 4px;
            background: transparent;
            cursor: col-resize;
            position: relative;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .resize-handle:hover,
        .resize-handle.dragging {
            background: #6366f1;
        }

        .resize-handle::before {
            content: '';
            position: absolute;
            left: -2px;
            right: -2px;
            top: 0;
            bottom: 0;
        }

        /* Quick select options */
        .quick-select-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .quick-select-btn {
            padding: 0.5rem 1rem;
            background: #1a1a25;
            border: 1px solid #32324a;
            border-radius: 0.5rem;
            color: #e2e8f0;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-select-btn:hover {
            background: #6366f1;
            border-color: #6366f1;
            transform: translateY(-1px);
        }

        .quick-select-btn.large {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .quick-select-section {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(26, 26, 37, 0.5);
            border-radius: 0.75rem;
            border: 1px solid #32324a;
        }

        .quick-select-label {
            font-size: 0.875rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="font-sans text-white h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-dark-800 border-b border-dark-600 px-4 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🚀</span>
                <h1 class="text-lg font-semibold">XBuilder</h1>
            </div>

            <!-- Inline Model Switcher -->
            <div class="flex items-center gap-2 px-3 py-1.5 bg-dark-700 rounded-lg border border-dark-600">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <select id="quickModelSwitch" onchange="quickSwitchModel(this.value)"
                        class="bg-transparent text-sm text-white border-none outline-none cursor-pointer pr-6 appearance-none"
                        style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23888%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-position: right 0.25rem center; background-repeat: no-repeat; background-size: 1em;">
                    <option value="">Loading models...</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- View Public Website Button (shows when site is published) -->
            <a href="/" target="_blank" id="viewSiteBtn"
               class="hidden px-4 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 rounded-lg transition flex items-center gap-2 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                View Public Website
            </a>

            <!-- Update Available Button (shows when update is available) -->
            <button onclick="showUpdateModal()" id="updateBtn"
                    class="hidden px-4 py-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 rounded-lg transition flex items-center gap-2 font-medium animate-pulse">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span id="updateBtnText">Update Available</span>
            </button>

            <button onclick="copyDebugInfo(event)"
                    class="px-3 py-1.5 text-sm bg-dark-700 hover:bg-dark-600 rounded-lg transition flex items-center gap-2"
                    title="Copy debug info to clipboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Debug
            </button>

            <button onclick="startNewConversation()"
                    class="px-3 py-1.5 text-sm bg-dark-700 hover:bg-dark-600 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New
            </button>

            <button onclick="showSettingsModal()"
                    class="px-3 py-1.5 text-sm bg-dark-700 hover:bg-dark-600 rounded-lg transition flex items-center gap-2"
                    title="AI Settings">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
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
        <div id="chatPanel" class="flex flex-col border-r border-dark-600" style="width: 50%">
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
                        <p class="text-xs text-gray-500 mt-1">DOCX, TXT, PDF, DOC supported • <span class="text-indigo-400">DOCX recommended for CVs</span></p>
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

        <!-- Resize Handle -->
        <div id="resizeHandle" class="resize-handle"></div>

        <!-- Preview Panel -->
        <div id="previewPanel" class="flex flex-col bg-dark-900" style="width: 50%">
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
                        class="hidden m-2 px-4 py-2 text-sm bg-green-600 hover:bg-green-700 rounded-lg transition flex items-center gap-2 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Publish to Live Site</span>
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
            initResizeHandle();
            checkPublishedSite();
            loadQuickModelSwitch();
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
            // Create welcome message with interactive options
            setTimeout(() => {
                showQuickSelectOptions();
            }, 500);

            return `Hey! 👋 I'm excited to help you create a website.

**Quick Start**: Choose an option below, or tell me what you need!`;
        }

        function showQuickSelectOptions() {
            const messagesDiv = document.getElementById('messages');
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'message max-w-[85%]';
            optionsDiv.innerHTML = `
                <div class="bg-dark-700 rounded-2xl rounded-bl-md px-4 py-4">
                    <div class="quick-select-label">🎯 What type of website do you need?</div>
                    <div class="quick-select-container">
                        <button class="quick-select-btn large" onclick="selectWebsiteType('Portfolio')">
                            💼 Portfolio
                        </button>
                        <button class="quick-select-btn large" onclick="selectWebsiteType('Business')">
                            🏢 Business
                        </button>
                        <button class="quick-select-btn large" onclick="selectWebsiteType('Landing Page')">
                            🚀 Landing Page
                        </button>
                        <button class="quick-select-btn large" onclick="selectWebsiteType('Personal Brand')">
                            ✨ Personal Brand
                        </button>
                    </div>

                    <div class="quick-select-section">
                        <div class="quick-select-label">🎨 Or pick a vibe:</div>
                        <div class="quick-select-container">
                            <button class="quick-select-btn" onclick="selectVibe('Modern & Minimalist')">Modern & Minimalist</button>
                            <button class="quick-select-btn" onclick="selectVibe('Bold & Creative')">Bold & Creative</button>
                            <button class="quick-select-btn" onclick="selectVibe('Professional & Clean')">Professional & Clean</button>
                            <button class="quick-select-btn" onclick="selectVibe('Playful & Colorful')">Playful & Colorful</button>
                        </div>
                    </div>

                    <div class="quick-select-section">
                        <div class="quick-select-label">📄 Have a CV or LinkedIn profile?</div>
                        <div class="quick-select-container">
                            <button class="quick-select-btn" onclick="document.getElementById('fileInput').click()">
                                📎 Upload CV
                            </button>
                            <button class="quick-select-btn" onclick="promptLinkedIn()">
                                🔗 Enter LinkedIn URL
                            </button>
                        </div>
                    </div>
                </div>
            `;
            messagesDiv.appendChild(optionsDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function selectWebsiteType(type) {
            const message = `I want a ${type} website`;
            document.getElementById('userInput').value = message;
            sendMessage();
        }

        function selectVibe(vibe) {
            const message = `I want a ${vibe} style website`;
            document.getElementById('userInput').value = message;
            sendMessage();
        }

        function promptLinkedIn() {
            const url = prompt('Enter your LinkedIn profile URL:');
            if (url && url.includes('linkedin.com')) {
                document.getElementById('userInput').value = url;
                sendMessage();
            }
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

            // Check if message contains LinkedIn URL
            const linkedinMatch = message.match(/https?:\/\/(www\.)?linkedin\.com\/(in|pub)\/[^\s]+/i);
            let linkedinData = null;

            if (linkedinMatch) {
                const linkedinUrl = linkedinMatch[0];
                console.log('[XBuilder] LinkedIn URL detected:', linkedinUrl);

                // Show status that we're fetching LinkedIn
                showTypingIndicator();
                isLoading = true;

                // Add status message
                const statusMsg = addMessageToUI('assistant', '🔍 Fetching LinkedIn profile...', false);

                try {
                    const linkedinResponse = await fetch('/xbuilder/api/linkedin', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ url: linkedinUrl })
                    });

                    const linkedinResult = await linkedinResponse.json();

                    // Remove status message
                    if (statusMsg && statusMsg.parentElement) {
                        statusMsg.parentElement.removeChild(statusMsg);
                    }

                    if (linkedinResult.success) {
                        linkedinData = linkedinResult.content;
                        console.log('[XBuilder] LinkedIn profile fetched:', linkedinResult.structured.name);
                        addMessageToUI('assistant', `✓ Got it! I've fetched your LinkedIn profile for **${linkedinResult.structured.name}**. Creating your website now...`);
                    } else {
                        console.warn('[XBuilder] LinkedIn fetch failed:', linkedinResult.error);
                        addMessageToUI('assistant', `⚠️ I couldn't access that LinkedIn profile (it may be private). You can upload your CV instead, or tell me about yourself!`);
                        hideTypingIndicator();
                        isLoading = false;
                        return;
                    }
                } catch (error) {
                    console.error('[XBuilder] LinkedIn fetch error:', error);
                    // Remove status message
                    if (statusMsg && statusMsg.parentElement) {
                        statusMsg.parentElement.removeChild(statusMsg);
                    }
                    addMessageToUI('assistant', `⚠️ Couldn't fetch LinkedIn profile. You can upload your CV or tell me about yourself instead!`);
                    hideTypingIndicator();
                    isLoading = false;
                    return;
                }
            }

            // Show typing indicator
            showTypingIndicator();
            isLoading = true;

            // Combine uploaded document and LinkedIn data
            let documentToSend = uploadedDocument;
            if (linkedinData) {
                documentToSend = linkedinData + (uploadedDocument ? '\n\n---\n\n' + uploadedDocument : '');
            }

            // Log if sending document
            if (documentToSend) {
                console.log('[XBuilder] Sending message WITH document:', documentToSend.length, 'chars');
            } else {
                console.log('[XBuilder] Sending message WITHOUT document');
            }

            try {
                const response = await fetch('/xbuilder/api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message: message,
                        history: conversationHistory.slice(0, -1), // Don't include the message we just added
                        document: documentToSend
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
                        console.log('[XBuilder] HTML received from server (' + data.html.length + ' chars)');
                        generatedHtml = data.html;
                        showPreview(generatedHtml);
                        document.getElementById('publishBtn').classList.remove('hidden');
                        console.log('[XBuilder] Preview updated and publish button shown');
                    } else {
                        console.warn('[XBuilder] No HTML in server response');
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
            // Remove code blocks FIRST (before escaping HTML)
            // This prevents the HTML code from being displayed in chat
            // Matches: ```xbuilder-html, ```html, or any ``` code block
            let hasCodeBlock = false;
            content = content.replace(/```[\w-]*\s*\n?([\s\S]*?)```/g, function(match) {
                hasCodeBlock = true;
                return '[CODE_BLOCK_REMOVED]';
            });

            // Also remove any leftover HTML that looks like generated website code
            // (in case code block markers are missing)
            if (content.includes('<!DOCTYPE html>')) {
                content = content.replace(/<!DOCTYPE html>[\s\S]*/i, '[CODE_BLOCK_REMOVED]');
                hasCodeBlock = true;
            }

            // Escape HTML after removing code blocks
            let html = content
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Replace code block placeholders with nice message
            if (hasCodeBlock) {
                html = html.replace(/\[CODE_BLOCK_REMOVED\]/g, '<div class="my-3 p-3 bg-gradient-to-r from-indigo-900/50 to-purple-900/50 rounded-lg text-sm border border-indigo-600/30"><div class="flex items-center gap-2 text-indigo-300"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg><strong>Website generated!</strong></div><p class="mt-1 text-gray-300 text-xs">Check the <strong>Preview</strong> and <strong>Code</strong> tabs to see your website →</p></div>');
            }

            // Bold
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Italic
            html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

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

            // Update preview with cache busting
            placeholder.classList.add('hidden');
            frame.classList.remove('hidden');

            // Force reload by clearing and setting srcdoc
            // This prevents old cached content from showing
            frame.srcdoc = '';
            setTimeout(() => {
                frame.srcdoc = html;
            }, 10);

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
                    btn.innerHTML = '✓ Published to Live Site!';
                    btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    btn.classList.add('bg-emerald-600');

                    // Get the root domain URL (without /xbuilder/ path)
                    const rootUrl = window.location.origin;

                    // Show "View Public Website" button in header
                    const viewSiteBtn = document.getElementById('viewSiteBtn');
                    if (viewSiteBtn) {
                        viewSiteBtn.classList.remove('hidden');
                        // Add cache busting to URL
                        viewSiteBtn.href = `/?v=${Date.now()}`;
                    }

                    // Show success message with clear instructions
                    addMessageToUI('assistant', `🎉 **Your website is now LIVE!**\n\n📍 **Live URL**: [${rootUrl}](${rootUrl}) (open in new tab)\n\n✅ **Published to**: Root domain (\`/site/index.html\`)\n🔧 **Admin Panel**: [${rootUrl}/xbuilder/](${rootUrl}/xbuilder/)\n\n💡 You can continue chatting to make changes, then publish again to update your live site.`);

                    setTimeout(() => {
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Publish to Live Site</span>';
                        btn.classList.add('bg-green-600', 'hover:bg-green-700');
                        btn.classList.remove('bg-emerald-600');
                        btn.disabled = false;
                    }, 5000);
                } else {
                    alert('Failed to publish: ' + (data.error || 'Unknown error'));
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Publish to Live Site</span>';
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Connection error. Please try again.');
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Publish to Live Site</span>';
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
                    const wordCount = Math.round(data.length / 5); // Rough word count estimate

                    console.log('[XBuilder] Uploaded document:', {
                        filename: file.name,
                        length: data.length,
                        preview: data.preview || data.content.substring(0, 200)
                    });

                    // Check if content looks valid (not mostly garbage/binary)
                    const isPDF = file.name.toLowerCase().endsWith('.pdf');
                    const hasValidText = data.content && /[a-zA-Z]{3,}/.test(data.content);
                    const suspiciousContent = data.length < 50 || !hasValidText;

                    // Always keep the document for AI, but warn if extraction looks poor
                    if (isPDF && suspiciousContent) {
                        // PDF extraction might have failed
                        status.innerHTML = `<span class="text-yellow-400">⚠️ ${file.name} uploaded, but text extraction may have failed (${wordCount} words). Sending to AI anyway...</span>`;
                        console.warn('[XBuilder] PDF extraction suspicious - content length:', data.length);

                        // Add message about upload with warning
                        addMessageToUI('user', `📄 Uploaded: ${file.name} (extraction may be incomplete)`);

                        // Add note to help AI understand the situation
                        const contentWithNote = `[Note: PDF text extraction may be incomplete or contain errors. User's CV file: ${file.name}]\n\n` + data.content;
                        uploadedDocument = contentWithNote;

                        conversationHistory.push({
                            role: 'user',
                            content: `I've uploaded my document: ${file.name}. Note: Text extraction from this PDF may be incomplete, but here's what was extracted:\n\n${data.content.substring(0, 500)}...`
                        });
                    } else {
                        // Successful extraction
                        status.innerHTML = `<span class="text-green-400">✓ ${file.name} uploaded (${wordCount} words extracted)</span>`;

                        // Add message about the upload
                        addMessageToUI('user', `📄 Uploaded: ${file.name}`);
                        conversationHistory.push({
                            role: 'user',
                            content: `I've uploaded my document: ${file.name}. Here's the content:\n\n${data.content.substring(0, 500)}...`
                        });

                        // Show hint that document will be sent with next message
                        setTimeout(() => {
                            status.innerHTML = `<span class="text-blue-400">📎 Document ready - will be sent with your next message</span>`;
                        }, 1500);

                        // Hide upload area after 3 seconds
                        setTimeout(() => {
                            document.getElementById('uploadArea').classList.add('hidden');
                        }, 3000);
                    }
                } else {
                    status.innerHTML = `<span class="text-red-400">✗ ${data.error || 'Upload failed'}</span>`;

                    // If it's a PDF error, suggest alternatives
                    if (data.error && data.error.includes('PDF')) {
                        addMessageToUI('assistant', `⚠️ ${data.error}\n\n**Tip**: For best results with CVs, use **.docx** or **.txt** format instead of PDF.`);
                    }
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

        // Check if a site is published and show "View Public Website" button
        async function checkPublishedSite() {
            try {
                // Check if index.html exists by fetching it
                const response = await fetch('/', { method: 'HEAD' });

                // If site exists (200 OK), show the View button
                if (response.ok) {
                    const viewSiteBtn = document.getElementById('viewSiteBtn');
                    if (viewSiteBtn) {
                        viewSiteBtn.classList.remove('hidden');
                        // Add cache busting
                        viewSiteBtn.href = `/?v=${Date.now()}`;
                    }
                }
            } catch (error) {
                // Site doesn't exist, keep button hidden
                console.log('[XBuilder] No published site found');
            }
        }

        // Quick Model Switcher
        async function loadQuickModelSwitch() {
            try {
                const response = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_current' })
                });

                const data = await response.json();
                if (data.success) {
                    updateQuickModelSwitch(data);
                    updateFooterProvider(data);
                }
            } catch (error) {
                console.error('Failed to load model switcher:', error);
            }
        }

        function updateQuickModelSwitch(data) {
            const select = document.getElementById('quickModelSwitch');
            if (!select) return;

            const currentProvider = data.current_provider;
            const currentModel = data.current_model;

            let options = '';

            // Build grouped options by provider
            for (const [providerKey, provider] of Object.entries(data.providers)) {
                if (!provider.has_key) continue; // Skip providers without API keys

                const providerName = provider.name;
                options += `<optgroup label="${providerName}">`;

                for (const [modelKey, modelName] of Object.entries(provider.models)) {
                    const isSelected = (providerKey === currentProvider && (!currentModel || modelKey === currentModel)) ? 'selected' : '';
                    options += `<option value="${providerKey}:${modelKey}" ${isSelected}>${modelName}</option>`;
                }

                options += `</optgroup>`;
            }

            select.innerHTML = options;
        }

        function updateFooterProvider(data) {
            const footer = document.getElementById('footerProvider');
            if (!footer) return;

            const providerName = data.providers[data.current_provider]?.name || 'Unknown';
            const currentModel = data.current_model || 'default';
            const modelDisplay = data.providers[data.current_provider]?.models[currentModel] || currentModel;

            footer.textContent = `${providerName} - ${modelDisplay}`;
        }

        async function quickSwitchModel(value) {
            if (!value) return;

            const [provider, model] = value.split(':');

            try {
                // Get current settings first
                const currentResponse = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_current' })
                });
                const currentData = await currentResponse.json();

                // Switch provider if different
                if (provider !== currentData.current_provider) {
                    const providerResponse = await fetch('/xbuilder/api/settings', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'switch_provider', provider })
                    });

                    const providerResult = await providerResponse.json();
                    if (!providerResult.success) {
                        alert(providerResult.error || 'Failed to switch provider');
                        loadQuickModelSwitch(); // Reload to reset
                        return;
                    }
                }

                // Switch model
                const modelResponse = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'switch_model', model })
                });

                const modelResult = await modelResponse.json();
                if (modelResult.success) {
                    // Reload to update display
                    loadQuickModelSwitch();
                } else {
                    alert(modelResult.error || 'Failed to switch model');
                    loadQuickModelSwitch(); // Reload to reset
                }
            } catch (error) {
                console.error('Model switch failed:', error);
                alert('Failed to switch model. Please try again.');
                loadQuickModelSwitch(); // Reload to reset
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

        // Copy debug information to clipboard
        async function copyDebugInfo(event) {
            const debugInfo = {
                timestamp: new Date().toISOString(),
                version: '<?php echo $version; ?>',
                browser: navigator.userAgent,
                url: window.location.href,

                // Console logs
                consoleLogs: window.xbuilderLogs || [],

                // Conversation state
                conversationLength: conversationHistory.length,
                hasDocument: uploadedDocument !== null,
                documentLength: uploadedDocument ? uploadedDocument.length : 0,
                documentPreview: uploadedDocument ? uploadedDocument.substring(0, 200) : 'N/A',

                // Generated HTML status
                hasGeneratedHtml: generatedHtml !== null,
                htmlLength: generatedHtml ? generatedHtml.length : 0,

                // Recent messages (last 5)
                recentMessages: conversationHistory.slice(-5).map(msg => ({
                    role: msg.role,
                    contentPreview: msg.content.substring(0, 150)
                }))
            };

            const debugText = `XBuilder Debug Information
Generated: ${debugInfo.timestamp}
Version: ${debugInfo.version}
Browser: ${debugInfo.browser}
URL: ${debugInfo.url}

=== CONVERSATION STATE ===
Total messages: ${debugInfo.conversationLength}
Has uploaded document: ${debugInfo.hasDocument}
Document length: ${debugInfo.documentLength} chars
Document preview: ${debugInfo.documentPreview}${debugInfo.documentLength > 200 ? '...' : ''}

Has generated HTML: ${debugInfo.hasGeneratedHtml}
HTML length: ${debugInfo.htmlLength} chars

=== RECENT MESSAGES (Last 5) ===
${debugInfo.recentMessages.map((msg, i) => `${i+1}. [${msg.role.toUpperCase()}] ${msg.contentPreview}...`).join('\n\n')}

=== CONSOLE LOGS ===
${debugInfo.consoleLogs.length > 0 ? debugInfo.consoleLogs.join('\n') : 'No console logs captured'}

=== INSTRUCTIONS ===
Please paste this information when reporting issues to help with debugging.
`;

            try {
                await navigator.clipboard.writeText(debugText);

                // Show success notification
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Copied!`;
                btn.classList.remove('bg-dark-700', 'hover:bg-dark-600');
                btn.classList.add('bg-green-600');

                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('bg-green-600');
                    btn.classList.add('bg-dark-700', 'hover:bg-dark-600');
                }, 2000);
            } catch (err) {
                alert('Failed to copy debug info: ' + err.message);
                console.error('[XBuilder Debug] Copy failed:', err);
            }
        }

        // Capture console logs for debugging
        window.xbuilderLogs = [];
        const originalLog = console.log;
        console.log = function(...args) {
            const message = args.map(arg =>
                typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
            ).join(' ');
            window.xbuilderLogs.push('[LOG] ' + message);
            if (window.xbuilderLogs.length > 100) window.xbuilderLogs.shift(); // Keep last 100
            originalLog.apply(console, args);
        };

        const originalWarn = console.warn;
        console.warn = function(...args) {
            const message = args.map(arg =>
                typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
            ).join(' ');
            window.xbuilderLogs.push('[WARN] ' + message);
            if (window.xbuilderLogs.length > 100) window.xbuilderLogs.shift();
            originalWarn.apply(console, args);
        };

        const originalError = console.error;
        console.error = function(...args) {
            const message = args.map(arg =>
                typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
            ).join(' ');
            window.xbuilderLogs.push('[ERROR] ' + message);
            if (window.xbuilderLogs.length > 100) window.xbuilderLogs.shift();
            originalError.apply(console, args);
        };

        // Resizable panels
        function initResizeHandle() {
            const chatPanel = document.getElementById('chatPanel');
            const previewPanel = document.getElementById('previewPanel');
            const resizeHandle = document.getElementById('resizeHandle');

            // Load saved width from localStorage
            const savedChatWidth = localStorage.getItem('xbuilder-chat-width');
            if (savedChatWidth) {
                const chatWidth = parseFloat(savedChatWidth);
                chatPanel.style.width = chatWidth + '%';
                previewPanel.style.width = (100 - chatWidth) + '%';
            }

            let isDragging = false;
            let startX = 0;
            let startChatWidth = 0;

            resizeHandle.addEventListener('mousedown', (e) => {
                isDragging = true;
                startX = e.clientX;
                startChatWidth = chatPanel.offsetWidth;
                resizeHandle.classList.add('dragging');
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;

                const containerWidth = chatPanel.parentElement.offsetWidth;
                const deltaX = e.clientX - startX;
                const newChatWidth = startChatWidth + deltaX;

                // Constrain between 20% and 80%
                const minWidth = containerWidth * 0.2;
                const maxWidth = containerWidth * 0.8;

                if (newChatWidth >= minWidth && newChatWidth <= maxWidth) {
                    const chatWidthPercent = (newChatWidth / containerWidth) * 100;
                    const previewWidthPercent = 100 - chatWidthPercent;

                    chatPanel.style.width = chatWidthPercent + '%';
                    previewPanel.style.width = previewWidthPercent + '%';
                }
            });

            document.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    resizeHandle.classList.remove('dragging');
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';

                    // Save width to localStorage
                    const containerWidth = chatPanel.parentElement.offsetWidth;
                    const chatWidthPercent = (chatPanel.offsetWidth / containerWidth) * 100;
                    localStorage.setItem('xbuilder-chat-width', chatWidthPercent.toFixed(2));
                }
            });
        }
    </script>

    <!-- Fixed Footer -->
    <footer style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 50; pointer-events: none;"
            class="bg-dark-800 border-t border-dark-700 px-4 py-2">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-4">
                <span>XBuilder v<?php echo htmlspecialchars($version); ?></span>
                <span class="text-gray-600">|</span>
                <span id="footerProvider">Loading...</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="https://github.com/Asif2BD/xBuilderCMS" target="_blank" class="hover:text-gray-400 transition pointer-events-auto">GitHub</a>
                <span class="text-gray-600">|</span>
                <span>© <?php echo date('Y'); ?> Asif Rahman</span>
            </div>
        </div>
    </footer>

    <!-- Update Modal -->
    <div id="updateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
        <div class="bg-dark-800 rounded-xl shadow-2xl border border-dark-600 max-w-2xl w-full mx-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-dark-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-white">System Update</h2>
                </div>
                <button onclick="closeUpdateModal()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6 max-h-96 overflow-y-auto">
                <div id="updateContent">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-dark-600 flex items-center justify-end gap-3">
                <button onclick="closeUpdateModal()" class="px-4 py-2 text-sm bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition">
                    Cancel
                </button>
                <button onclick="performUpdate()" id="updateNowBtn" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-medium">
                    Update Now
                </button>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settingsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
        <div class="bg-dark-800 rounded-xl shadow-2xl border border-dark-600 max-w-3xl w-full mx-4 overflow-hidden max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-dark-600 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-white">AI Settings</h2>
                </div>
                <button onclick="closeSettingsModal()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6 overflow-y-auto flex-1">
                <div id="settingsContent">
                    <div class="flex items-center justify-center py-12">
                        <div class="loading-spinner" style="width: 40px; height: 40px; border-width: 3px;"></div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-dark-600 flex items-center justify-end gap-3 flex-shrink-0">
                <button onclick="closeSettingsModal()" class="px-4 py-2 text-sm bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Update management
        let currentUpdateInfo = null;
        // Settings management
        let currentSettings = null;

        // Check for updates on page load
        async function checkForUpdates() {
            try {
                const response = await fetch('/xbuilder/api/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'check' })
                });

                const data = await response.json();

                if (data.available) {
                    currentUpdateInfo = data;
                    const updateBtn = document.getElementById('updateBtn');
                    const updateBtnText = document.getElementById('updateBtnText');

                    if (updateBtn && updateBtnText) {
                        updateBtn.classList.remove('hidden');
                        updateBtnText.textContent = `Update to v${data.latest_version}`;
                    }

                    console.log('[XBuilder Update] New version available:', data.latest_version);
                }
            } catch (error) {
                console.error('[XBuilder Update] Failed to check for updates:', error);
            }
        }

        // Show update modal
        function showUpdateModal() {
            if (!currentUpdateInfo) return;

            const modal = document.getElementById('updateModal');
            const content = document.getElementById('updateContent');

            content.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400">Current Version</p>
                            <p class="text-xl font-semibold text-white">v${currentUpdateInfo.current_version}</p>
                        </div>
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-400">New Version</p>
                            <p class="text-xl font-semibold text-emerald-500">v${currentUpdateInfo.latest_version}</p>
                        </div>
                    </div>

                    <div class="bg-dark-900 rounded-lg p-4 border border-dark-600">
                        <h3 class="font-semibold text-white mb-2">What's New</h3>
                        <div class="text-sm text-gray-300 prose prose-invert max-w-none" style="white-space: pre-wrap;">${escapeHtml(currentUpdateInfo.changelog || 'No changelog available.')}</div>
                    </div>

                    <div class="bg-blue-900/20 border border-blue-800 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-300">
                                <p class="font-semibold mb-1">Safety Features:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Automatic backup before update</li>
                                    <li>Your website and data are preserved</li>
                                    <li>Rollback available if needed</li>
                                    <li>Process takes 30-60 seconds</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        // Close update modal
        function closeUpdateModal() {
            const modal = document.getElementById('updateModal');
            modal.classList.add('hidden');
        }

        // Perform update
        async function performUpdate() {
            const updateNowBtn = document.getElementById('updateNowBtn');
            const content = document.getElementById('updateContent');

            // Disable button and show progress
            updateNowBtn.disabled = true;
            updateNowBtn.innerHTML = '<div class="loading-spinner mx-auto"></div>';

            content.innerHTML = `
                <div class="text-center py-8">
                    <div class="loading-spinner mx-auto mb-4" style="width: 40px; height: 40px; border-width: 3px;"></div>
                    <p class="text-lg font-semibold text-white mb-2">Updating XBuilder...</p>
                    <p class="text-sm text-gray-400">Please wait, this may take up to 60 seconds</p>
                    <div class="mt-4 space-y-2 text-sm text-gray-500">
                        <p>✓ Creating backup...</p>
                        <p>✓ Downloading update...</p>
                        <p>⏳ Installing files...</p>
                    </div>
                </div>
            `;

            try {
                const response = await fetch('/xbuilder/api/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'perform' })
                });

                const data = await response.json();

                if (data.success) {
                    content.innerHTML = `
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-2">Update Successful!</h3>
                            <p class="text-gray-400 mb-4">XBuilder has been updated to version ${data.new_version}</p>
                            <div class="bg-dark-900 rounded-lg p-4 border border-dark-600 text-sm text-left">
                                <p class="text-gray-400"><span class="text-white font-semibold">Old Version:</span> v${data.old_version}</p>
                                <p class="text-gray-400"><span class="text-white font-semibold">New Version:</span> v${data.new_version}</p>
                                <p class="text-gray-400 mt-2"><span class="text-white font-semibold">Backup:</span> ${data.backup_file}</p>
                            </div>
                            <button onclick="window.location.reload()" class="mt-6 px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-medium">
                                Reload Page
                            </button>
                        </div>
                    `;
                    updateNowBtn.style.display = 'none';
                } else {
                    throw new Error(data.error || 'Update failed');
                }

            } catch (error) {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Update Failed</h3>
                        <p class="text-gray-400 mb-4">${escapeHtml(error.message)}</p>
                        <p class="text-sm text-gray-500">Your system has been rolled back to the previous version.</p>
                        <button onclick="closeUpdateModal(); location.reload();" class="mt-6 px-6 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition">
                            Close
                        </button>
                    </div>
                `;
                updateNowBtn.style.display = 'none';
            }
        }

        // Escape HTML helper
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Settings Management
        async function showSettingsModal() {
            const modal = document.getElementById('settingsModal');
            const content = document.getElementById('settingsContent');

            modal.classList.remove('hidden');

            // Load settings
            try {
                const response = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get_current' })
                });

                const data = await response.json();
                currentSettings = data;

                renderSettings(data);
            } catch (error) {
                content.innerHTML = `
                    <div class="text-center py-8 text-red-400">
                        Failed to load settings. Please try again.
                    </div>
                `;
            }
        }

        function closeSettingsModal() {
            const modal = document.getElementById('settingsModal');
            modal.classList.add('hidden');
        }

        function renderSettings(data) {
            const content = document.getElementById('settingsContent');

            const providersHtml = Object.keys(data.providers).map(key => {
                const provider = data.providers[key];
                const isCurrent = key === data.current_provider;
                const models = provider.models;

                return `
                    <div class="bg-dark-900 rounded-lg border ${isCurrent ? 'border-purple-500' : 'border-dark-600'} overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <h3 class="font-semibold text-white text-lg">${provider.name}</h3>
                                    ${isCurrent ? '<span class="px-2 py-1 text-xs bg-purple-600 text-white rounded">Active</span>' : ''}
                                    ${provider.has_key ? '<span class="px-2 py-1 text-xs bg-emerald-600/20 text-emerald-400 rounded border border-emerald-600">API Key Set</span>' : '<span class="px-2 py-1 text-xs bg-orange-600/20 text-orange-400 rounded border border-orange-600">No API Key</span>'}
                                </div>
                                ${!isCurrent && provider.has_key ? `<button onclick="switchProvider('${key}')" class="px-3 py-1.5 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded transition">Use This</button>` : ''}
                            </div>

                            ${isCurrent ? `
                                <div class="mb-3">
                                    <label class="block text-sm text-gray-400 mb-2">Model:</label>
                                    <select onchange="switchModel(this.value)" class="w-full bg-dark-700 border border-dark-600 rounded px-3 py-2 text-white">
                                        ${Object.keys(models).map(modelKey => `
                                            <option value="${modelKey}" ${modelKey === data.current_model ? 'selected' : ''}>${models[modelKey]}</option>
                                        `).join('')}
                                    </select>
                                </div>
                            ` : ''}

                            <div class="space-y-2">
                                <label class="block text-sm text-gray-400">API Key:</label>
                                <div class="flex gap-2">
                                    <input type="password" id="apikey_${key}" placeholder="${provider.has_key ? '••••••••••••••••' : 'Enter API key'}" class="flex-1 bg-dark-700 border border-dark-600 rounded px-3 py-2 text-white text-sm">
                                    <button onclick="saveApiKey('${key}')" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded transition">
                                        ${provider.has_key ? 'Update' : 'Save'}
                                    </button>
                                    ${provider.has_key ? `<button onclick="deleteApiKey('${key}')" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition">Delete</button>` : ''}
                                </div>
                                <p class="text-xs text-gray-500">
                                    ${key === 'gemini' ? 'Get from: https://aistudio.google.com/app/apikey' : ''}
                                    ${key === 'claude' ? 'Get from: https://console.anthropic.com/settings/keys' : ''}
                                    ${key === 'openai' ? 'Get from: https://platform.openai.com/api-keys' : ''}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            content.innerHTML = `
                <div class="space-y-4">
                    <div class="bg-blue-900/20 border border-blue-800 rounded-lg p-4">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-300">
                                <p class="font-semibold mb-1">Manage Your AI Providers</p>
                                <p>Switch between different AI providers and models. Add multiple API keys to avoid quota limits.</p>
                            </div>
                        </div>
                    </div>

                    ${providersHtml}

                    <!-- Reset XBuilder Section -->
                    <div class="bg-red-900/20 border border-red-800 rounded-lg p-4 mt-8">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-semibold text-red-400 mb-1">Danger Zone</p>
                                <p class="text-sm text-red-300 mb-3">Reset XBuilder to factory settings. This will delete ALL data and return to the install screen.</p>
                                <button onclick="resetXBuilder()" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition font-medium">
                                    Reset XBuilder
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        async function switchProvider(provider) {
            try {
                const response = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'switch_provider', provider })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload settings
                    showSettingsModal();
                    // Show success message
                    alert('AI provider switched successfully! Your next chat will use ' + provider + '.');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Failed to switch provider');
            }
        }

        async function switchModel(model) {
            try {
                const response = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'switch_model', model })
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Failed to switch model');
            }
        }

        async function saveApiKey(provider) {
            const input = document.getElementById('apikey_' + provider);
            const apiKey = input.value.trim();

            if (!apiKey) {
                alert('Please enter an API key');
                return;
            }

            try {
                const response = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_api_key', provider, api_key: apiKey })
                });

                const data = await response.json();

                if (data.success) {
                    input.value = '';
                    showSettingsModal(); // Reload
                    alert('API key saved successfully!');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Failed to save API key');
            }
        }

        async function deleteApiKey(provider) {
            if (!confirm(`Are you sure you want to delete the API key for ${provider}?`)) {
                return;
            }

            try {
                const response = await fetch('/xbuilder/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_api_key', provider })
                });

                const data = await response.json();

                if (data.success) {
                    showSettingsModal(); // Reload
                    alert('API key deleted successfully');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Failed to delete API key');
            }
        }

        async function resetXBuilder() {
            // Multi-step confirmation
            const step1 = confirm('⚠️ WARNING: This will DELETE ALL DATA including:\n\n• All API keys\n• All conversations\n• Generated website\n• All uploads\n• All configuration\n\nThis action CANNOT be undone!\n\nAre you sure you want to reset XBuilder?');

            if (!step1) return;

            const step2 = prompt('To confirm, type "RESET_XBUILDER" (without quotes):');

            if (step2 !== 'RESET_XBUILDER') {
                alert('Reset cancelled - confirmation text did not match.');
                return;
            }

            try {
                const response = await fetch('/xbuilder/api/reset', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ confirm: 'RESET_XBUILDER' })
                });

                const data = await response.json();

                if (data.success) {
                    alert('✅ XBuilder has been reset successfully!\n\nYou will now be redirected to the setup wizard.');
                    window.location.href = '/xbuilder/setup';
                } else {
                    alert('Error: ' + (data.error || 'Failed to reset'));
                }
            } catch (error) {
                console.error('Reset failed:', error);
                alert('Failed to reset XBuilder. Please try again or contact support.');
            }
        }

        // Check for updates on page load
        setTimeout(() => {
            checkForUpdates();
        }, 2000); // Wait 2 seconds after page load
    </script>
</body>
</html>

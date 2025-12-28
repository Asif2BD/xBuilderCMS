<?php
/**
 * XBuilder Setup Wizard View
 *
 * First-time setup for XBuilder.
 * User selects AI provider, enters API key, and creates admin password.
 *
 * Variables available from router:
 * - $security: Security instance
 * - $config: Config instance
 */

$csrfToken = $security->generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XBuilder Setup</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Space Grotesk', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .provider-card {
            transition: all 0.3s ease;
        }
        .provider-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        .provider-card.selected {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease forwards;
        }
        .step { display: none; }
        .step.active { display: block; }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <!-- Logo/Header -->
            <div class="text-center mb-8 animate-fadeIn">
                <h1 class="text-4xl font-bold mb-2">
                    <span class="text-blue-400">X</span>Builder
                </h1>
                <p class="text-gray-400">AI-Powered Website Generator</p>
            </div>

            <!-- Setup Card -->
            <div class="glass rounded-2xl p-8 animate-fadeIn" style="animation-delay: 0.1s;">
                <form id="setupForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Step 1: Choose Provider -->
                    <div id="step1" class="step active">
                        <h2 class="text-xl font-semibold mb-4">Choose Your AI Provider</h2>
                        <p class="text-gray-400 text-sm mb-6">Select which AI will power your website generation.</p>

                        <div class="space-y-3">
                            <!-- Gemini -->
                            <label class="provider-card glass rounded-xl p-4 cursor-pointer block">
                                <div class="flex items-center">
                                    <input type="radio" name="provider" value="gemini" class="sr-only" checked>
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">Gemini</div>
                                        <div class="text-sm text-gray-400">Google AI - Free tier available</div>
                                    </div>
                                    <div class="text-green-400 text-xs font-semibold px-2 py-1 bg-green-400/10 rounded">Recommended</div>
                                </div>
                            </label>

                            <!-- Claude -->
                            <label class="provider-card glass rounded-xl p-4 cursor-pointer block">
                                <div class="flex items-center">
                                    <input type="radio" name="provider" value="claude" class="sr-only">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">Claude</div>
                                        <div class="text-sm text-gray-400">Anthropic - Best quality</div>
                                    </div>
                                </div>
                            </label>

                            <!-- OpenAI -->
                            <label class="provider-card glass rounded-xl p-4 cursor-pointer block">
                                <div class="flex items-center">
                                    <input type="radio" name="provider" value="openai" class="sr-only">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M22.2 8.8c.3-.9.4-1.8.3-2.7-.2-1.4-.9-2.7-1.9-3.7-1.3-1.3-3.1-2-4.9-1.9-1 0-2 .3-2.9.8-.6-.5-1.3-.8-2-.9C9.9.2 9 .2 8.1.4c-1.4.3-2.6 1-3.5 2.1-.9 1.1-1.4 2.5-1.3 3.9 0 .5.1 1.1.3 1.6-.7.5-1.2 1.1-1.6 1.8-.6 1-.9 2.2-.8 3.3.1 1.5.7 2.9 1.8 3.9 1.1 1.1 2.5 1.7 4 1.8.5 0 1.1 0 1.6-.1.5.7 1.1 1.2 1.8 1.6 1 .6 2.2.9 3.3.8 1.5-.1 2.9-.8 3.9-1.8 1-1.1 1.6-2.4 1.7-3.9 0-.6 0-1.1-.1-1.7.6-.5 1.2-1 1.6-1.7.5-1 .8-2.1.7-3.2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold">ChatGPT</div>
                                        <div class="text-sm text-gray-400">OpenAI - Good balance</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <button type="button" onclick="nextStep(2)" class="btn-primary w-full mt-6 py-3 px-6 rounded-xl font-semibold text-white">
                            Continue
                        </button>
                    </div>

                    <!-- Step 2: API Key -->
                    <div id="step2" class="step">
                        <div class="flex items-center mb-4">
                            <button type="button" onclick="prevStep(1)" class="mr-3 text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <h2 class="text-xl font-semibold">Enter API Key</h2>
                        </div>

                        <p class="text-gray-400 text-sm mb-4" id="apiKeyHelp">
                            Get your free API key from <a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-blue-400 hover:underline">Google AI Studio</a>
                        </p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">API Key</label>
                                <input
                                    type="password"
                                    name="api_key"
                                    id="apiKey"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500"
                                    placeholder="Enter your API key"
                                    required
                                >
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="showKey" class="mr-2" onchange="toggleKeyVisibility()">
                                <label for="showKey" class="text-sm text-gray-400">Show API key</label>
                            </div>
                        </div>

                        <button type="button" onclick="nextStep(3)" class="btn-primary w-full mt-6 py-3 px-6 rounded-xl font-semibold text-white">
                            Continue
                        </button>
                    </div>

                    <!-- Step 3: Password -->
                    <div id="step3" class="step">
                        <div class="flex items-center mb-4">
                            <button type="button" onclick="prevStep(2)" class="mr-3 text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <h2 class="text-xl font-semibold">Create Admin Password</h2>
                        </div>

                        <p class="text-gray-400 text-sm mb-4">
                            This password protects your XBuilder admin panel.
                        </p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500"
                                    placeholder="Minimum 8 characters"
                                    minlength="8"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Confirm Password</label>
                                <input
                                    type="password"
                                    name="password_confirm"
                                    id="passwordConfirm"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500"
                                    placeholder="Confirm your password"
                                    minlength="8"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" class="btn-primary w-full mt-6 py-3 px-6 rounded-xl font-semibold text-white">
                            Complete Setup
                        </button>
                    </div>
                </form>

                <!-- Error Message -->
                <div id="errorMsg" class="hidden mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm"></div>

                <!-- Success Message -->
                <div id="successMsg" class="hidden mt-4 p-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-400 text-sm"></div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-gray-500 text-sm animate-fadeIn" style="animation-delay: 0.2s;">
                <p>Your API key is encrypted and stored securely on your server.</p>
            </div>
        </div>
    </div>

    <script>
        // Provider selection
        document.querySelectorAll('input[name="provider"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.provider-card').forEach(card => {
                    card.classList.remove('selected');
                });
                this.closest('.provider-card').classList.add('selected');
                updateApiKeyHelp(this.value);
            });
        });

        // Initialize first provider as selected
        document.querySelector('.provider-card').classList.add('selected');

        function updateApiKeyHelp(provider) {
            const helpText = document.getElementById('apiKeyHelp');
            const links = {
                gemini: 'Get your free API key from <a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-blue-400 hover:underline">Google AI Studio</a>',
                claude: 'Get your API key from <a href="https://console.anthropic.com/settings/keys" target="_blank" class="text-blue-400 hover:underline">Anthropic Console</a>',
                openai: 'Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-400 hover:underline">OpenAI Platform</a>'
            };
            helpText.innerHTML = links[provider] || links.gemini;
        }

        function toggleKeyVisibility() {
            const input = document.getElementById('apiKey');
            input.type = document.getElementById('showKey').checked ? 'text' : 'password';
        }

        function nextStep(step) {
            // Validate current step
            if (step === 2) {
                // Step 1 validation (provider is always selected)
            } else if (step === 3) {
                const apiKey = document.getElementById('apiKey').value.trim();
                if (!apiKey) {
                    showError('Please enter your API key');
                    return;
                }
            }

            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            hideMessages();
        }

        function prevStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            hideMessages();
        }

        function showError(message) {
            const errorEl = document.getElementById('errorMsg');
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            document.getElementById('successMsg').classList.add('hidden');
        }

        function showSuccess(message) {
            const successEl = document.getElementById('successMsg');
            successEl.textContent = message;
            successEl.classList.remove('hidden');
            document.getElementById('errorMsg').classList.add('hidden');
        }

        function hideMessages() {
            document.getElementById('errorMsg').classList.add('hidden');
            document.getElementById('successMsg').classList.add('hidden');
        }

        // Form submission
        document.getElementById('setupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideMessages();

            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('passwordConfirm').value;

            if (password.length < 8) {
                showError('Password must be at least 8 characters');
                return;
            }

            if (password !== passwordConfirm) {
                showError('Passwords do not match');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Setting up...';

            try {
                const formData = new FormData(this);
                const response = await fetch('/xbuilder/api/setup', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess('Setup complete! Redirecting...');
                    setTimeout(() => {
                        window.location.href = '/xbuilder/chat';
                    }, 1500);
                } else {
                    showError(data.error || 'Setup failed. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Setup';
                }
            } catch (error) {
                showError('Connection error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Setup';
            }
        });
    </script>
</body>
</html>

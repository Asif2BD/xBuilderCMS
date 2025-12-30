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
    <title>Setup - XBuilder</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Space Grotesk', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        dark: {
                            900: '#0a0a0f',
                            800: '#12121a',
                            700: '#1a1a25',
                            600: '#252533',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: #0a0a0f;
            background-image: 
                radial-gradient(at 20% 20%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 80% 80%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            min-height: 100vh;
        }
        
        .step { display: none; }
        .step.active { display: block; animation: fadeIn 0.3s ease-out; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .provider-card {
            transition: all 0.2s ease;
        }
        .provider-card:hover {
            transform: translateY(-2px);
        }
        .provider-card.selected {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }
        
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
    </style>
</head>
<body class="font-sans text-white">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-lg">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="text-5xl mb-4">🚀</div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                    Welcome to XBuilder
                </h1>
                <p class="text-gray-400 mt-2">Let's set up your AI-powered website builder</p>
            </div>
            
            <!-- Progress Indicator -->
            <div class="flex justify-center gap-2 mb-8">
                <div class="step-indicator w-3 h-3 rounded-full bg-indigo-500" data-step="1"></div>
                <div class="step-indicator w-3 h-3 rounded-full bg-dark-600" data-step="2"></div>
                <div class="step-indicator w-3 h-3 rounded-full bg-dark-600" data-step="3"></div>
            </div>
            
            <!-- Step 1: Choose AI Provider -->
            <div class="step active" data-step="1">
                <div class="bg-dark-800 rounded-2xl p-6 border border-dark-600">
                    <h2 class="text-xl font-semibold mb-4">Choose your AI assistant</h2>
                    <p class="text-gray-400 text-sm mb-6">Select which AI will help you build your website</p>
                    
                    <div class="space-y-3">
                        <label class="provider-card flex items-center gap-4 p-4 bg-dark-700 rounded-xl border border-dark-600 cursor-pointer">
                            <input type="radio" name="provider" value="gemini" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center text-2xl">
                                ✨
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Gemini</div>
                                <div class="text-sm text-gray-400">By Google • Free tier available</div>
                            </div>
                            <div class="text-green-400 text-sm font-medium">Free</div>
                        </label>
                        
                        <label class="provider-card flex items-center gap-4 p-4 bg-dark-700 rounded-xl border border-dark-600 cursor-pointer">
                            <input type="radio" name="provider" value="claude" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-orange-500/20 flex items-center justify-center text-2xl">
                                🧠
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Claude</div>
                                <div class="text-sm text-gray-400">By Anthropic • Best quality</div>
                            </div>
                            <div class="text-gray-400 text-sm">~$0.03/site</div>
                        </label>
                        
                        <label class="provider-card flex items-center gap-4 p-4 bg-dark-700 rounded-xl border border-dark-600 cursor-pointer">
                            <input type="radio" name="provider" value="openai" class="hidden">
                            <div class="w-12 h-12 rounded-lg bg-green-500/20 flex items-center justify-center text-2xl">
                                🤖
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">ChatGPT</div>
                                <div class="text-sm text-gray-400">By OpenAI • Popular choice</div>
                            </div>
                            <div class="text-gray-400 text-sm">~$0.02/site</div>
                        </label>
                    </div>
                    
                    <button onclick="nextStep()" disabled
                            class="w-full mt-6 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-dark-600 disabled:cursor-not-allowed rounded-xl font-medium transition">
                        Continue →
                    </button>
                </div>
            </div>
            
            <!-- Step 2: Enter API Key -->
            <div class="step" data-step="2">
                <div class="bg-dark-800 rounded-2xl p-6 border border-dark-600">
                    <h2 class="text-xl font-semibold mb-4">Enter your API key</h2>
                    <p class="text-gray-400 text-sm mb-6">
                        <span id="providerInstructions">Your API key is stored securely and never shared.</span>
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">API Key</label>
                            <input type="password" id="apiKey" 
                                   class="w-full px-4 py-3 bg-dark-700 border border-dark-600 rounded-xl focus:border-indigo-500 focus:outline-none"
                                   placeholder="Enter your API key">
                        </div>
                        
                        <p class="text-xs text-gray-500">
                            <span id="getKeyLink"></span>
                        </p>
                        
                        <div id="keyError" class="hidden text-red-400 text-sm"></div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button onclick="prevStep()" 
                                class="px-6 py-3 bg-dark-700 hover:bg-dark-600 rounded-xl font-medium transition">
                            ← Back
                        </button>
                        <button onclick="validateKey()" id="validateBtn"
                                class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-xl font-medium transition flex items-center justify-center gap-2">
                            <span>Validate & Continue</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Step 3: Create Password -->
            <div class="step" data-step="3">
                <div class="bg-dark-800 rounded-2xl p-6 border border-dark-600">
                    <h2 class="text-xl font-semibold mb-4">Create admin password</h2>
                    <p class="text-gray-400 text-sm mb-6">
                        This password protects your XBuilder admin panel.
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Password</label>
                            <input type="password" id="password" 
                                   class="w-full px-4 py-3 bg-dark-700 border border-dark-600 rounded-xl focus:border-indigo-500 focus:outline-none"
                                   placeholder="Create a strong password">
                        </div>
                        
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Confirm Password</label>
                            <input type="password" id="confirmPassword" 
                                   class="w-full px-4 py-3 bg-dark-700 border border-dark-600 rounded-xl focus:border-indigo-500 focus:outline-none"
                                   placeholder="Confirm your password">
                        </div>
                        
                        <div id="passwordError" class="hidden text-red-400 text-sm"></div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button onclick="prevStep()" 
                                class="px-6 py-3 bg-dark-700 hover:bg-dark-600 rounded-xl font-medium transition">
                            ← Back
                        </button>
                        <button onclick="completeSetup()" id="completeBtn"
                                class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-xl font-medium transition flex items-center justify-center gap-2">
                            <span>Complete Setup</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentStep = 1;
        let selectedProvider = null;
        
        const providerInfo = {
            gemini: {
                instructions: 'Get your free Gemini API key from Google AI Studio.',
                link: '<a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-indigo-400 hover:underline">Get free Gemini API key →</a>'
            },
            claude: {
                instructions: 'Get your Claude API key from the Anthropic Console.',
                link: '<a href="https://console.anthropic.com/settings/keys" target="_blank" class="text-indigo-400 hover:underline">Get Claude API key →</a>'
            },
            openai: {
                instructions: 'Get your OpenAI API key from the OpenAI platform.',
                link: '<a href="https://platform.openai.com/api-keys" target="_blank" class="text-indigo-400 hover:underline">Get OpenAI API key →</a>'
            }
        };
        
        // Provider selection
        document.querySelectorAll('input[name="provider"]').forEach(input => {
            input.addEventListener('change', function() {
                selectedProvider = this.value;
                
                // Update UI
                document.querySelectorAll('.provider-card').forEach(card => {
                    card.classList.remove('selected');
                });
                this.closest('.provider-card').classList.add('selected');
                
                // Enable continue button
                document.querySelector('[data-step="1"] button').disabled = false;
            });
        });
        
        function updateStepIndicators() {
            document.querySelectorAll('.step-indicator').forEach(indicator => {
                const step = parseInt(indicator.dataset.step);
                if (step <= currentStep) {
                    indicator.classList.remove('bg-dark-600');
                    indicator.classList.add('bg-indigo-500');
                } else {
                    indicator.classList.remove('bg-indigo-500');
                    indicator.classList.add('bg-dark-600');
                }
            });
        }
        
        function showStep(step) {
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
            currentStep = step;
            updateStepIndicators();
        }
        
        function nextStep() {
            if (currentStep === 1 && selectedProvider) {
                // Update step 2 with provider info
                document.getElementById('providerInstructions').textContent = providerInfo[selectedProvider].instructions;
                document.getElementById('getKeyLink').innerHTML = providerInfo[selectedProvider].link;
                showStep(2);
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }
        
        async function validateKey() {
            const apiKey = document.getElementById('apiKey').value.trim();
            const errorDiv = document.getElementById('keyError');
            const btn = document.getElementById('validateBtn');
            
            if (!apiKey) {
                errorDiv.textContent = 'Please enter your API key';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            // Show loading
            btn.innerHTML = '<div class="loading-spinner"></div><span>Validating...</span>';
            btn.disabled = true;
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/xbuilder/api/setup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'validate_key',
                        provider: selectedProvider,
                        api_key: apiKey
                    })
                });
                
                const data = await response.json();
                
                if (data.valid) {
                    showStep(3);
                } else {
                    errorDiv.textContent = data.error || 'Invalid API key. Please check and try again.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Connection error. Please try again.';
                errorDiv.classList.remove('hidden');
            }
            
            btn.innerHTML = '<span>Validate & Continue</span>';
            btn.disabled = false;
        }
        
        async function completeSetup() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorDiv = document.getElementById('passwordError');
            const btn = document.getElementById('completeBtn');
            
            // Validate password
            if (password.length < 8) {
                errorDiv.textContent = 'Password must be at least 8 characters';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            if (password !== confirmPassword) {
                errorDiv.textContent = 'Passwords do not match';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            // Show loading
            btn.innerHTML = '<div class="loading-spinner"></div><span>Setting up...</span>';
            btn.disabled = true;
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/xbuilder/api/setup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'complete',
                        provider: selectedProvider,
                        api_key: document.getElementById('apiKey').value.trim(),
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to main builder
                    window.location.href = '/xbuilder/';
                } else {
                    errorDiv.textContent = data.error || 'Setup failed. Please try again.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Connection error. Please try again.';
                errorDiv.classList.remove('hidden');
            }
            
            btn.innerHTML = '<span>Complete Setup</span>';
            btn.disabled = false;
        }
    </script>

    <!-- Version Footer -->
    <div class="fixed bottom-4 right-4 text-xs text-gray-500 bg-dark-800 px-3 py-2 rounded-lg border border-dark-600">
        XBuilder v<?php echo htmlspecialchars($version); ?>
    </div>
</body>
</html>

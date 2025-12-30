<?php
// Get XBuilder version
$versionFile = dirname(__DIR__, 2) . '/VERSION';
$version = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '0.3.3';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - XBuilder</title>
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
        <div class="w-full max-w-md">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="text-5xl mb-4">🚀</div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                    XBuilder
                </h1>
                <p class="text-gray-400 mt-2">Sign in to your admin panel</p>
            </div>
            
            <div class="bg-dark-800 rounded-2xl p-6 border border-dark-600">
                <form id="loginForm" onsubmit="handleLogin(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Password</label>
                            <input type="password" id="password" 
                                   class="w-full px-4 py-3 bg-dark-700 border border-dark-600 rounded-xl focus:border-indigo-500 focus:outline-none"
                                   placeholder="Enter your admin password"
                                   autofocus>
                        </div>
                        
                        <div id="error" class="hidden text-red-400 text-sm"></div>
                    </div>
                    
                    <button type="submit" id="submitBtn"
                            class="w-full mt-6 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-xl font-medium transition flex items-center justify-center gap-2">
                        <span>Sign In</span>
                    </button>
                </form>
            </div>
            
            <p class="text-center text-gray-500 text-sm mt-6">
                Forgot password? Delete <code class="text-gray-400">xbuilder/storage/config.json</code> to reset.
            </p>
        </div>
    </div>
    
    <script>
        async function handleLogin(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('error');
            const btn = document.getElementById('submitBtn');
            
            if (!password) {
                errorDiv.textContent = 'Please enter your password';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            // Show loading
            btn.innerHTML = '<div class="loading-spinner"></div><span>Signing in...</span>';
            btn.disabled = true;
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/xbuilder/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/xbuilder/';
                } else {
                    errorDiv.textContent = data.error || 'Invalid password';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Connection error. Please try again.';
                errorDiv.classList.remove('hidden');
            }
            
            btn.innerHTML = '<span>Sign In</span>';
            btn.disabled = false;
        }
    </script>

    <!-- Version Footer -->
    <div class="fixed bottom-4 right-4 text-xs text-gray-500 bg-dark-800 px-3 py-2 rounded-lg border border-dark-600">
        XBuilder v<?php echo htmlspecialchars($version); ?>
    </div>
</body>
</html>

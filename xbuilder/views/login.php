<?php
/**
 * XBuilder Login View
 *
 * Admin login page for XBuilder.
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
    <title>Login - XBuilder</title>
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
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.3s ease;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8 animate-fadeIn">
                <h1 class="text-4xl font-bold mb-2">
                    <span class="text-blue-400">X</span>Builder
                </h1>
                <p class="text-gray-400">Welcome back</p>
            </div>

            <!-- Login Card -->
            <div class="glass rounded-2xl p-8 animate-fadeIn" style="animation-delay: 0.1s;" id="loginCard">
                <form id="loginForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500"
                            placeholder="Enter your password"
                            autofocus
                            required
                        >
                    </div>

                    <button type="submit" id="submitBtn" class="btn-primary w-full py-3 px-6 rounded-xl font-semibold text-white">
                        Login
                    </button>
                </form>

                <!-- Error Message -->
                <div id="errorMsg" class="hidden mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm text-center"></div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 text-gray-500 text-sm animate-fadeIn" style="animation-delay: 0.2s;">
                <p>AI-Powered Website Generator</p>
            </div>
        </div>
    </div>

    <script>
        function showError(message) {
            const errorEl = document.getElementById('errorMsg');
            const card = document.getElementById('loginCard');
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');

            // Shake animation
            card.classList.add('shake');
            setTimeout(() => card.classList.remove('shake'), 300);
        }

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password').value;

            if (!password) {
                showError('Please enter your password');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            document.getElementById('errorMsg').classList.add('hidden');

            try {
                const formData = new FormData(this);
                const response = await fetch('/xbuilder/api/login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '/xbuilder/chat';
                } else {
                    showError(data.error || 'Invalid password');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Login';
                    document.getElementById('password').value = '';
                    document.getElementById('password').focus();
                }
            } catch (error) {
                showError('Connection error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            }
        });

        // Enter key to submit
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>

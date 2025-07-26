<?php
// Start session for CSRF token
session_start();
// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit">Login</button>
    </form>
    
    <div id="message"></div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = {
                email: formData.get('email'),
                password: formData.get('password'),
                csrf_token: formData.get('csrf_token')
            };

            try {
                const response = await fetch('https://server.dpeaceapp.com/_/action_login.php', {
                    method: 'POST',
                    credentials: 'include', // For cookies (if using sessions)
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('message').innerHTML = 
                        `<p class="success">Login successful! Redirecting...</p>`;
                    window.location.href = '/dashboard.php'; // Redirect after login
                } else {
                    document.getElementById('message').innerHTML = 
                        `<p class="error">${result.error || 'Login failed'}</p>`;
                }
            } catch (error) {
                document.getElementById('message').innerHTML = 
                    `<p class="error">Network error. Please try again.</p>`;
            }
        });
    </script>
</body>
</html>
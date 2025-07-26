<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to dpeaceapp.com</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #0066cc; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        #result { margin-top: 20px; padding: 10px; border-radius: 4px; }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form id="loginForm">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <div id="result"></div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            };
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                const resultDiv = document.getElementById('result');
                
                if (result.status === 'success') {
                    resultDiv.className = 'success';
                    resultDiv.innerHTML = `
                        <p>Login successful!</p>
                        <p>Welcome ${result.user.username}</p>
                        <p>Token: ${result.token.substring(0, 20)}...</p>
                    `;
                    // In real app, store token securely
                    localStorage.setItem('authToken', result.token);
                } else {
                    resultDiv.className = 'error';
                    resultDiv.innerHTML = `
                        <p>Login failed</p>
                        <p>${result.message}</p>
                        ${result.details ? `<p>${result.details}</p>` : ''}
                    `;
                }
            } catch (error) {
                document.getElementById('result').className = 'error';
                document.getElementById('result').innerHTML = `
                    <p>Network error</p>
                    <p>${error.message}</p>
                `;
            }
        });
    </script>
</body>
</html>
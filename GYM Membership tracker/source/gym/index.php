<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ESOGÜ Spor Salonu - Giriş Kontrol Sistemi</title>

<link href="assets/vendor/bootstrap.min.css" rel="stylesheet">

<link href="assets/vendor/fontawesome.min.css" rel="stylesheet">

<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    
    <div class="login-container">
        <div class="login-box">
            
            <div class="login-header text-center">
                <div class="logo-container">
                    <i class="fas fa-dumbbell fa-4x text-primary"></i>
                </div>
                <h2 class="mt-3">ESOGÜ Spor Salonu</h2>
                <p class="text-muted">Giriş Kontrol Sistemi v3.0</p>
            </div>

<div id="messageContainer"></div>

<form id="loginForm" class="mt-4">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Kullanıcı Adı
                    </label>
                    <input type="text" class="form-control form-control-lg" id="username" 
                           name="username" required autofocus 
                           placeholder="Kullanıcı adınızı girin">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Şifre
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg" id="password" 
                               name="password" required 
                               placeholder="Şifrenizi girin">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                        Beni Hatırla
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100" id="loginButton">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </button>
            </form>

<div class="system-status mt-4 text-center">
                <small class="text-muted">
                    <i class="fas fa-circle text-success status-indicator"></i>
                    Sistem Aktif - Yerel Ağ
                </small>
            </div>
        </div>

<div class="login-footer text-center mt-4">
            <p class="text-muted mb-1">
                <small>Eskişehir Osmangazi Üniversitesi</small>
            </p>
            <p class="text-muted">
                <small>© 2025 - Tüm hakları saklıdır</small>
            </p>
        </div>
    </div>

<script src="assets/vendor/jquery.min.js"></script>

<script src="assets/vendor/bootstrap.bundle.min.js"></script>
    
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;
        const loginButton = document.getElementById('loginButton');
        const originalText = loginButton.innerHTML;
        loginButton.disabled = true;
        loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Giriş yapılıyor...';
        $.ajax({
            url: 'api/login.php',
            type: 'POST',
            dataType: 'json',
            data: {
                username: username,
                password: password,
                remember_me: rememberMe ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', 'Giriş başarılı! Yönlendiriliyorsunuz...');
                    setTimeout(function() {
                        const redirectPage = response.redirect || 'dashboard.php';
                        window.location.href = 'pages/' + redirectPage;
                    }, 1000);
                } else {
                    showMessage('danger', response.message || 'Giriş başarısız!');
                    loginButton.disabled = false;
                    loginButton.innerHTML = originalText;
                }
            },
            error: function() {
                showMessage('danger', 'Bir hata oluştu! Lütfen tekrar deneyin.');
                loginButton.disabled = false;
                loginButton.innerHTML = originalText;
            }
        });
    });

    function showMessage(type, message) {
        const container = document.getElementById('messageContainer');
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
    </script>
</body>
</html>

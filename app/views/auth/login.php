<?php $this->partial('header'); ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>EXISTING LOGIN FORM</h2>
        
        <h3 class="login-subtitle">LOGIN HERE</h3>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>">
                <?= $_SESSION['flash_message']['message'] ?>
            </div>
        <?php endif; ?>
        
        <form action="/shoppingcart/auth/login" method="POST" class="auth-form">
            <div class="form-group">
                <input type="email" id="email" name="email" required placeholder="EMAIL">
                <i class="fas fa-envelope"></i>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" required placeholder="PASSWORD">
                <i class="fas fa-lock"></i>
            </div>
            
            <div class="form-group remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <a href="/shoppingcart/auth/forgot-password" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="auth-button">LOGIN</button>
        </form>
        
        <div class="register-link">
            <p>To Register New Account — <a href="/shoppingcart/auth/register">Click Here</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: url('/shoppingcart/public/images/auth-bg.jpg') center/cover;
    padding: 2rem;
}

.auth-box {
    background: rgba(0, 0, 0, 0.8);
    padding: 3rem 2rem;
    border-radius: 0;
    width: 100%;
    max-width: 500px;
    color: white;
}

.auth-box h2 {
    text-align: center;
    margin-bottom: 0.5rem;
    font-size: 2rem;
    font-weight: 300;
    letter-spacing: 2px;
}

.login-subtitle {
    text-align: center;
    font-size: 1.5rem;
    margin-bottom: 2rem;
    color: #4CAF50;
    font-weight: 300;
}

.auth-form .form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.auth-form input {
    width: 100%;
    padding: 0.8rem;
    padding-left: calc(2.5rem + 4px);
    background: rgba(0, 0, 0, 0.8);
    border: none;
    border-bottom: 1px solid #4CAF50;
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.auth-form input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.auth-form input:focus {
    outline: none;
    border-bottom-color: #69F0AE;
    background: rgba(0, 0, 0, 0.9);
    box-shadow: 0 2px 4px -4px rgba(76, 175, 80, 0.5);
}

.auth-form .form-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #4CAF50;
    font-size: 1.2rem;
}

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    margin: 1rem 0;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.remember-me input[type="checkbox"] {
    width: auto;
    margin: 0;
    background-color: #444;
    cursor: pointer;
}

.forgot-password {
    color: #4CAF50;
    text-decoration: none;
}

.forgot-password:hover {
    text-decoration: underline;
}

.auth-button {
    width: 100%;
    padding: 1rem;
    background-color: #4CAF50;
    color: white;
    border: none;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    letter-spacing: 1px;
}

.auth-button:hover {
    background-color: #45a049;
}

.register-link {
    text-align: center;
    margin-top: 2rem;
    color: rgba(255, 255, 255, 0.7);
}

.register-link a {
    color: #4CAF50;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0;
    text-align: center;
    background: transparent;
    border: 1px solid;
}

.alert-success {
    color: #4CAF50;
    border-color: #4CAF50;
}

.alert-error {
    color: #f44336;
    border-color: #f44336;
}

@media (max-width: 480px) {
    .auth-box {
        padding: 2rem 1.5rem;
    }
    
    .auth-box h2 {
        font-size: 1.5rem;
    }
    
    .login-subtitle {
        font-size: 1.2rem;
    }
}
</style>

<?php $this->partial('footer'); ?> 
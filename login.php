<?php require_once __DIR__ . '/Verify_License.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Med-Quick</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background-color: var(--card-dark);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
        }
        .login-container h1 {
            color: var(--accent-green);
            margin-bottom: 1.5rem;
        }
        .login-form .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        .login-form input {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 1rem;
        }
        .error-message {
            background-color: rgba(255, 82, 82, 0.1);
            color: #ff5252;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border: 1px solid #ff5252;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1><i class="fas fa-heart-pulse"></i> Med-Quick</h1>
        
        <?php
            session_start();
            if (isset($_SESSION['login_error'])) {
                echo '<div class="error-message">' . $_SESSION['login_error'] . '</div>';
                unset($_SESSION['login_error']); // Clear the error message after displaying it
            }
        ?>

        <form action="login_handler.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            <div style="margin-top: 1rem; text-align: center;">
                <a href="#" id="login-support-link" style="color: var(--text-secondary); font-size: 0.9rem; text-decoration: none;">Don't have a license? Contact Arham Ali</a>
            </div>
        </form>
    </div>

    <!-- Support Modal (Replicated for Login Page) -->
    <div id="support-modal" class="modal-backdrop" style="display: none;">
        <div class="modal-content" style="max-width: 450px; text-align: center; border: 1px solid var(--accent-green);">
            <div class="modal-header" style="justify-content: center; position: relative; border-bottom: none;">
                <h3 style="color: var(--accent-green); font-size: 1.4rem;">License & Support</h3>
                <button class="modal-close" id="close-support-modal" style="position: absolute; right: 1rem; top: 1rem;">&times;</button>
            </div>
            <div class="modal-body" style="padding-top: 0;">
                <p style="margin-bottom: 1.5rem; color: var(--text-primary); font-size: 1.1rem;">
                    Developer: <strong style="color: var(--accent-green);">Arham Ali</strong>
                </p>
                
                <a href="https://wa.me/923278047689?text=Assalam-o-Alaikum%20Arham,%20I%20need%20a%20license%20for%20Med-Quick%20POS" target="_blank" class="btn btn-support btn-block" style="margin-bottom: 1rem; display: block; text-decoration: none;">
                    <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                </a>
                
                <a href="mailto:arhamrehmani048@gmail.com?subject=Med-Quick%20License%20Request" class="btn btn-support btn-block" style="margin-bottom: 2rem; display: block; text-decoration: none;">
                    <i class="fas fa-envelope"></i> Send Email
                </a>
                
                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                    Designed & Developed by <strong style="color: var(--text-primary);">Arham Ali</strong>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <script>
        const supportLink = document.getElementById('login-support-link');
        const supportModal = document.getElementById('support-modal');
        const closeModal = document.getElementById('close-support-modal');

        supportLink.addEventListener('click', (e) => {
            e.preventDefault();
            supportModal.style.display = 'flex';
        });

        closeModal.addEventListener('click', () => {
            supportModal.style.display = 'none';
        });

        supportModal.addEventListener('click', (e) => {
            if (e.target === supportModal) {
                supportModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Approval Document System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='1333' height='900' viewBox='0 0 1333 900' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cy='720' r='362' fill='%23264ECA' opacity='0.3'/%3E%3Ccircle cy='720' r='286' fill='%23244BC5' opacity='0.4'/%3E%3Ccircle cy='720' r='219' fill='%23274FC7' opacity='0.5'/%3E%3Cpath d='M725.5 139C589.9 151.8 497.667 51 468.5 -1L1332.5 2.5V720.5H1236C937.2 682.5 987.5 531 1050 460C1086.5 410.167 1152.5 290.1 1124.5 208.5C1089.5 106.5 895 123 725.5 139Z' fill='%23264ECA' opacity='0.4'/%3E%3C/svg%3E");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }

        .container {
            display: flex;
            max-width: 1200px;
            width: 100%;
            gap: 80px;
            align-items: center;
        }

        .left-section {
            flex: 1;
            text-align: left;
            color: #fff;
        }

        .left-section h1 {
            font-size: 48px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 24px;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .left-section p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.8;
            max-width: 500px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 55px 50px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
            backdrop-filter: blur(10px);
        }

        .login-card h2 {
            color: #1e3c72;
            font-size: 38px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 45px;
        }

        .form-group {
            margin-bottom: 32px;
        }

        .form-group label {
            display: block;
            color: #555;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            letter-spacing: 0.3px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 14px 0;
            background: transparent;
            border: none;
            border-bottom: 2.5px solid #d0d7de;
            color: #1e3c72;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .form-group input:focus {
            border-bottom-color: #2196F3;
            transform: translateY(-2px);
        }

        .form-group input::placeholder {
            color: #aab5c4;
        }

        .toggle-password {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #aab5c4;
            cursor: pointer;
            padding: 8px;
            font-size: 20px;
            transition: all 0.3s;
        }

        .toggle-password:hover {
            color: #2196F3;
            transform: translateY(-50%) scale(1.1);
        }

        .login-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 40px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 25px rgba(33, 150, 243, 0.4);
            letter-spacing: 0.5px;
        }

        .login-button:hover {
            background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(33, 150, 243, 0.5);
        }

        .login-button:active {
            transform: translateY(-1px);
        }

        .forgot-password {
            text-align: center;
            margin-top: 24px;
        }

        .forgot-password a {
            color: #5f6f81;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .forgot-password a:hover {
            color: #2196F3;
        }

        /* Decorative elements */
        .login-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            background: linear-gradient(90deg, #2196F3, #1976D2, #2196F3);
            border-radius: 28px 28px 0 0;
        }

        @media (max-width: 968px) {
            .container {
                flex-direction: column;
                gap: 50px;
            }

            .left-section {
                text-align: center;
            }

            .left-section h1 {
                font-size: 36px;
            }

            .left-section p {
                margin: 0 auto;
            }

            .login-card {
                padding: 45px 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>Approval Document System</h1>
            <p>Streamline your document approval process with our secure and efficient platform. Manage, track, and approve documents seamlessly.</p>
        </div>

        <div class="login-card">
            <h2>Login</h2>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email or Username</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        placeholder="Enter your email" 
                        required 
                        autofocus
                    >
                    @error('email')
                        <span style="color: #f44336; font-size: 12px; margin-top: 8px; display: block; font-weight: 500;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password" 
                            required
                        >
                    </div>
                    @error('password')
                        <span style="color: #f44336; font-size: 12px; margin-top: 8px; display: block; font-weight: 500;">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="login-button">LOGIN</button>

                @if (Route::has('password.request'))
                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">Forgot Password?</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🔓';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
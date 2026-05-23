<?php
// template/auth.php

/**
 * Exibe o formulário de login com uma mensagem de erro opcional.
 */
function output_login_form($error = '', $postedIdentifier = '') {
    ?>
    <div class="auth-card">
        <div class="auth-header">
            <a href="index.php" class="auth-logo">FIT<span>PEAK</span></a>
            <h1>Welcome Back</h1>
            <p>Sign in to access your account</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="auth-alert alert-error">
                <span class="auth-alert-icon">✕</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="login.php" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="identifier">Email or Username</label>
                <input type="text" id="identifier" name="identifier"
                       class="form-input" placeholder="your@email.com"
                       value="<?= htmlspecialchars($postedIdentifier) ?>" required>
                <span class="field-error" id="err-identifier">
                    Please enter your email or username.
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password"
                           class="form-input" placeholder="••••••••" required>
                    <button type="button" class="toggle-pw" data-target="password">👁</button>
                </div>
                <span class="field-error" id="err-password">
                    Please enter your password.
                </span>
            </div>

            <button type="submit" class="main-button full-button auth-submit">
                Sign In
            </button>
        </form>

        <div class="auth-divider">or</div>
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Sign up for free</a>
        </div>
        <div class="auth-footer">
            <a href="forgot_password.php">Forgot password?</a>
        </div>
    </div>
    <?php
}

function output_register_form($error = '', $old = []) {
    ?>
    <div class="auth-card">
        <div class="auth-header">
            <a href="index.php" class="auth-logo">FIT<span>PEAK</span></a>
            <h1>Create Account</h1>
            <p>Join FitPeak and start your fitness journey</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="auth-alert alert-error">
                <span class="auth-alert-icon">✕</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="register.php" id="registerForm">
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input"
                       placeholder="Ana Silva"
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                <span class="field-error" id="err-name">Please enter your full name.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input"
                       placeholder="anasilva"
                       value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                <span class="field-error" id="err-username">Username must have at least 3 characters.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input"
                       placeholder="ana@email.com"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                <span class="field-error" id="err-email">Please enter a valid email address.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="plan">Membership Plan</label>
                    <select id="plan" name="plan" class="form-input" required>
                        <option value="basic" <?= ($old['plan'] ?? 'basic') === 'basic' ? 'selected' : '' ?>>
                            Basic - €19/month
                        </option>
                        <option value="premium" <?= ($old['plan'] ?? '') === 'premium' ? 'selected' : '' ?>>
                            Premium - €39/month
                        </option>
                        <option value="vip" <?= ($old['plan'] ?? '') === 'vip' ? 'selected' : '' ?>>
                            VIP - €69/month
                        </option>
                    </select>
                <span class="field-error" id="err-plan">Please select a membership plan.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Minimum 8 characters" required>
                    <button type="button" class="toggle-pw" data-target="password">👁</button>
                </div>
                <div class="password-strength" id="pwStrength">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>
                <span class="field-error" id="err-password">Password must be at least 8 characters.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm">Confirm Password</label>
                <div class="input-wrap">
                    <input type="password" id="confirm" name="confirm" class="form-input"
                           placeholder="Repeat your password" required>
                    <button type="button" class="toggle-pw" data-target="confirm">👁</button>
                </div>
                <span class="field-error" id="err-confirm">Passwords do not match.</span>
            </div>

            <button type="submit" class="main-button full-button auth-submit">
                Create Free Account
            </button>

            <p class="auth-terms">
                By signing up, you agree to our
                <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>.
            </p>
        </form>

        <div class="auth-divider">already a member?</div>
        <div class="auth-footer">
            <a href="login.php">Sign in to your account</a>
        </div>
    </div>
    <?php
}
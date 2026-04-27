<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$error = '';
$redirect = safeRedirectTarget($_POST['redirect'] ?? ($_GET['redirect'] ?? '../pages/products.php'));

if ($redirect !== '../pages/products.php') {
    $_SESSION['redirect_url'] = $redirect;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = trim((string)($_POST['mode'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $name = trim((string)($_POST['name'] ?? 'Passkey User'));
    $passkeyId = trim((string)($_POST['passkey_id'] ?? ''));

    if ($passkeyId === '') {
        $error = 'Passkey credential was not received. Try again.';
    } elseif ($mode === 'register') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email to register passkey.';
        } else {
            $existsStmt = $conn->prepare('SELECT user_id, name FROM users WHERE email = ? LIMIT 1');
            $existsStmt->bind_param('s', $email);
            $existsStmt->execute();
            $existing = $existsStmt->get_result()->fetch_assoc();

            if ($existing) {
                $uid = (int)$existing['user_id'];
                $finalName = $name !== '' ? $name : (string)$existing['name'];
                $up = $conn->prepare("UPDATE users SET login_type = 'passkey', passkey_id = ?, name = ? WHERE user_id = ?");
                $up->bind_param('ssi', $passkeyId, $finalName, $uid);
                if ($up->execute()) {
                    $conn->query("INSERT IGNORE INTO carts (user_id) VALUES ($uid)");
                    completeLogin(['user_id' => $uid, 'name' => $finalName], $redirect);
                }
                $error = 'Unable to update existing account for passkey.';
            } else {
                $finalName = $name !== '' ? $name : 'Passkey User';
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, login_type, passkey_id) VALUES (?, ?, NULL, 'passkey', ?)");
                $stmt->bind_param('sss', $finalName, $email, $passkeyId);
                if ($stmt->execute()) {
                    $uid = (int)$conn->insert_id;
                    $conn->query("INSERT IGNORE INTO carts (user_id) VALUES ($uid)");
                    completeLogin(['user_id' => $uid, 'name' => $finalName], $redirect);
                }
                $error = 'Unable to create passkey account.';
            }
        }
    } elseif ($mode === 'login') {
        // Primary lookup by credential ID (account chooser friendly)
        $stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE passkey_id = ? AND login_type = 'passkey' LIMIT 1");
        $stmt->bind_param('s', $passkeyId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // Optional fallback: if email provided, validate against that email's passkey record
        if (!$user && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt2 = $conn->prepare("SELECT user_id, name, email, passkey_id FROM users WHERE email = ? AND login_type = 'passkey' LIMIT 1");
            $stmt2->bind_param('s', $email);
            $stmt2->execute();
            $row = $stmt2->get_result()->fetch_assoc();
            if ($row && !empty($row['passkey_id']) && hash_equals((string)$row['passkey_id'], $passkeyId)) {
                $user = $row;
            }
        }

        if ($user) {
            completeLogin($user, $redirect);
        }

        $error = 'No matching passkey account found. Register your passkey first.';
    } else {
        $error = 'Invalid passkey request.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passkey Login - GreenCart</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="auth-body auth-screen">
<div class="auth-container">
    <div class="auth-logo">🌿 GreenCart</div>
    <div class="auth-card">
        <h1 class="auth-title">Passkey Authentication</h1>
        <p class="auth-subtitle">Use your device passkey to register or sign in.</p>
        <?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" class="auth-form" id="passkeyForm">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <input type="hidden" name="mode" id="passkeyMode" value="login">
            <input type="hidden" name="passkey_id" id="passkeyIdField" value="">

            <div class="form-group">
                <label>Email (required for register)</label>
                <input type="email" name="email" id="passkeyEmail" placeholder="you@email.com">
            </div>
            <div class="form-group">
                <label>Name (used during register)</label>
                <input type="text" name="name" id="passkeyName" placeholder="Passkey User">
            </div>

            <div class="passkey-actions">
                <button type="button" class="social-btn passkey-btn" id="passkeyLoginBtn">Login with Passkey</button>
                <button type="button" class="social-btn google-btn" id="passkeyRegisterBtn">Register Passkey</button>
            </div>
        </form>

        <p class="oauth-note">Passkeys require a modern browser and secure context (localhost is supported).</p>
        <a href="login.php?redirect=<?= urlencode($redirect) ?>" class="auth-link-btn" style="margin-top:14px">Back to Login</a>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('passkeyForm');
    const modeField = document.getElementById('passkeyMode');
    const idField = document.getElementById('passkeyIdField');
    const emailEl = document.getElementById('passkeyEmail');
    const nameEl = document.getElementById('passkeyName');
    const loginBtn = document.getElementById('passkeyLoginBtn');
    const registerBtn = document.getElementById('passkeyRegisterBtn');

    function randomBytes(len) {
        const arr = new Uint8Array(len);
        window.crypto.getRandomValues(arr);
        return arr;
    }

    function toBase64Url(buffer) {
        const bytes = new Uint8Array(buffer);
        let str = '';
        for (let i = 0; i < bytes.length; i++) {
            str += String.fromCharCode(bytes[i]);
        }
        return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
    }

    async function createPasskeyCredential() {
        const email = (emailEl.value || '').trim();
        if (!email) {
            throw new Error('Email is required for passkey registration.');
        }

        if (!window.PublicKeyCredential || !navigator.credentials || !navigator.credentials.create) {
            throw new Error('Passkeys are not supported in this browser.');
        }

        const userHandle = new TextEncoder().encode(email);
        const displayName = (nameEl.value || 'Passkey User').trim() || 'Passkey User';

        const cred = await navigator.credentials.create({
            publicKey: {
                challenge: randomBytes(32),
                rp: { name: 'GreenCart' },
                user: {
                    id: userHandle,
                    name: email,
                    displayName: displayName
                },
                pubKeyCredParams: [{ type: 'public-key', alg: -7 }, { type: 'public-key', alg: -257 }],
                timeout: 60000,
                authenticatorSelection: {
                    residentKey: 'preferred',
                    userVerification: 'preferred'
                },
                attestation: 'none'
            }
        });

        return toBase64Url(cred.rawId);
    }

    async function getPasskeyCredential() {
        if (!window.PublicKeyCredential || !navigator.credentials || !navigator.credentials.get) {
            throw new Error('Passkeys are not supported in this browser.');
        }

        const assertion = await navigator.credentials.get({
            publicKey: {
                challenge: randomBytes(32),
                userVerification: 'preferred',
                timeout: 60000
            }
        });

        return toBase64Url(assertion.rawId);
    }

    async function run(mode) {
        try {
            modeField.value = mode;
            let passkeyId = '';

            if (mode === 'register') {
                passkeyId = await createPasskeyCredential();
            } else {
                passkeyId = await getPasskeyCredential();
            }

            if (!passkeyId) {
                throw new Error('No passkey was returned by your device.');
            }

            idField.value = passkeyId;
            form.submit();
        } catch (err) {
            alert(err && err.message ? err.message : 'Passkey failed. Please try again.');
        }
    }

    loginBtn.addEventListener('click', function() { run('login'); });
    registerBtn.addEventListener('click', function() { run('register'); });
})();
</script>
</body>
</html>

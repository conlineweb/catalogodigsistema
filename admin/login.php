<?php
require_once dirname(__DIR__) . '/includes/auth.php';
startSession();
if (isAdmin()) { header('Location: /catalogodigsistema/admin/index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin · AutoRepuestos Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1a2c3e 0%,#2c4b57 60%,#1f3e48 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:#fff;border-radius:28px;padding:2.8rem 2.4rem;width:100%;max-width:420px;box-shadow:0 40px 80px rgba(0,0,0,.35)}
.brand{text-align:center;margin-bottom:2.2rem}
.brand-icon{background:linear-gradient(135deg,#C0392B,#E67E22);border-radius:22px;width:72px;height:72px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem}
.brand-icon i{font-size:2.2rem;color:#fff}
.brand h1{font-size:1.55rem;font-weight:800;color:#1a2c3e}
.brand p{color:#6c8695;font-size:.85rem;margin-top:.2rem}
.field{margin-bottom:1.2rem}
.field label{display:block;font-weight:600;font-size:.83rem;color:#2c4b57;margin-bottom:.4rem}
.field input{width:100%;padding:.9rem 1.1rem;border:2px solid #e2ecea;border-radius:16px;font-size:.95rem;font-family:'Inter',sans-serif;outline:none;transition:.2s;background:#fafcfc}
.field input:focus{border-color:#E67E22;background:#fff}
.btn{width:100%;padding:.95rem;background:linear-gradient(135deg,#C0392B,#E67E22);border:none;border-radius:60px;color:#fff;font-weight:700;font-size:1rem;cursor:pointer;transition:.2s;margin-top:.4rem;font-family:'Inter',sans-serif}
.btn:hover{opacity:.9;transform:translateY(-1px)}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.alert{background:#fee2e2;color:#b91c1c;padding:.8rem 1rem;border-radius:12px;font-size:.84rem;margin-bottom:1rem;display:none}
.alert.show{display:block}
.hint{text-align:center;margin-top:1.2rem;font-size:.73rem;color:#bbb}
.hint a{color:#E67E22;text-decoration:none}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="brand-icon"><i class="fas fa-car"></i></div>
    <h1>AutoRepuestos Pro</h1>
    <p>Panel Administrativo</p>
  </div>
  <div class="alert" id="alertMsg"></div>
  <form id="loginForm" novalidate>
    <div class="field">
      <label><i class="fas fa-user fa-sm"></i> Usuario</label>
      <input type="text" id="uname" placeholder="admin" autocomplete="username" required>
    </div>
    <div class="field">
      <label><i class="fas fa-lock fa-sm"></i> Contraseña</label>
      <input type="password" id="upass" placeholder="••••••••" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn" id="loginBtn"><i class="fas fa-sign-in-alt"></i> Ingresar al Dashboard</button>
  </form>
  <p class="hint">¿Eres cliente? <a href="/catalogodigsistema/client/login.php">Accede aquí</a></p>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  const alert = document.getElementById('alertMsg');
  alert.className = 'alert';
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando…';
  try {
    const res  = await fetch('/catalogodigsistema/api/login.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ username: document.getElementById('uname').value, password: document.getElementById('upass').value })
    });
    const data = await res.json();
    if (data.success && data.role === 'admin') {
      window.location = '/catalogodigsistema/admin/index.php';
    } else {
      alert.textContent = data.error || 'Credenciales incorrectas';
      alert.className = 'alert show';
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Ingresar al Dashboard';
    }
  } catch {
    alert.textContent = 'Error de conexión. Intenta de nuevo.';
    alert.className = 'alert show';
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Ingresar al Dashboard';
  }
});
</script>
</body>
</html>

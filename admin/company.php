<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$fields = [
    'legal_name', 'commercial_name', 'rfc', 'industry', 'company_size', 'employee_count',
    'website', 'company_email', 'company_phone', 'street', 'neighborhood', 'city', 'state',
    'zip', 'contact_name', 'contact_position', 'contact_email', 'contact_phone',
    'lead_source', 'assigned_executive', 'status', 'observations',
];
$company = companyInfo();
$saved = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $next = $company;
    foreach ($fields as $field) {
        $next[$field] = sanitize($_POST[$field] ?? '');
    }
    if ($next['status'] === '') {
        $next['status'] = 'Prospecto';
    }

    foreach (['company_email', 'contact_email'] as $emailField) {
        if ($next[$emailField] !== '' && !filter_var(htmlspecialchars_decode($next[$emailField], ENT_QUOTES), FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingresa correos electrónicos válidos.';
            break;
        }
    }

    if ($error === '') {
        $user = $_SESSION['username'] ?? $_SESSION['name'] ?? 'admin';
        if (empty($next['registered_at'])) {
            $next['registered_at'] = date('c');
            $next['registered_by'] = $user;
        }
        if (empty($next['registered_by'])) {
            $next['registered_by'] = $user;
        }
        $next['updated_at'] = date('c');

        writeJson('company', $next);
        logActivity('company_updated', 'COMPANY', $user, $next['commercial_name'] ?: $next['legal_name']);
        $company = $next;
        $saved = true;
    }
}

function companyValue(array $company, string $key): string {
    return htmlspecialchars(htmlspecialchars_decode($company[$key] ?? '', ENT_QUOTES));
}

function formatCompanyDate(string $value): string {
    if ($value === '') return 'Se asignará al guardar';
    try {
        return (new DateTime($value))->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $value;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Empresa · AutoRepuestos Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root{--orange:#E67E22;--red:#C0392B;--dark:#1a2c3e;--muted:#6c8695}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#f0f4f8;color:#1a2c3e;display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--dark);position:fixed;top:0;left:0;height:100vh;display:flex;flex-direction:column;z-index:50}
.sidebar-brand{padding:1.4rem;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sb-icon{background:linear-gradient(135deg,#C0392B,#E67E22);border-radius:12px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sb-icon i{color:#fff;font-size:1.1rem}
.sb-title{font-weight:800;font-size:.9rem;color:#fff;line-height:1.2}
.sb-sub{color:#7a9ead;font-size:.68rem}
.sidebar-nav{flex:1;padding:1rem .8rem;overflow-y:auto}
.nav-item{display:flex;align-items:center;gap:12px;padding:.7rem 1rem;border-radius:14px;color:#7a9ead;text-decoration:none;font-weight:600;font-size:.86rem;transition:.2s;margin-bottom:.15rem}
.nav-item:hover,.nav-item.active{background:rgba(230,126,34,.15);color:#E67E22}
.nav-item i{width:18px;text-align:center}
.sidebar-footer{padding:.8rem;border-top:1px solid rgba(255,255,255,.08)}
.nav-logout{color:#e87f7f!important}
.nav-logout:hover{background:rgba(192,57,43,.15)!important;color:#e87f7f!important}
.main{margin-left:240px;flex:1;display:flex;flex-direction:column}
.topbar{background:#fff;padding:1rem 2rem;border-bottom:1px solid #e2ecea;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;flex-wrap:wrap;gap:.8rem}
.topbar h2{font-size:1.2rem;font-weight:800}
.user-chip{background:#f0f4f8;padding:.4rem 1rem;border-radius:40px;font-size:.82rem;font-weight:600;color:#2c4b57;display:flex;align-items:center;gap:6px}
.content{padding:1.8rem 2rem;flex:1}
.hero{background:linear-gradient(135deg,#1a2c3e,#29495a);color:#fff;border-radius:26px;padding:1.7rem;margin-bottom:1.4rem;display:flex;justify-content:space-between;gap:1rem;align-items:center;box-shadow:0 14px 32px rgba(26,44,62,.15)}
.hero h1{font-size:1.45rem;margin-bottom:.35rem}
.hero p{color:#c8d7df;font-size:.9rem;max-width:760px;line-height:1.5}
.form-card{background:#fff;border-radius:22px;padding:1.4rem;box-shadow:0 2px 16px rgba(0,0,0,.05)}
.section-title{font-size:.9rem;font-weight:800;color:#1a2c3e;margin:1.2rem 0 .8rem;display:flex;align-items:center;gap:8px;padding-bottom:.6rem;border-bottom:1px solid #eef3f2}
.section-title:first-child{margin-top:0}
.section-title i{color:var(--orange)}
.form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.9rem}
.form-field{display:flex;flex-direction:column;gap:.35rem}
.form-field.full{grid-column:1/-1}
.form-field label{font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
.input{width:100%;border:2px solid #e2ecea;border-radius:14px;padding:.62rem .75rem;font-family:'Inter',sans-serif;font-weight:600;color:#1a2c3e;outline:none;background:#fff}
.input:focus{border-color:var(--orange);box-shadow:0 0 0 3px rgba(230,126,34,.12)}
textarea.input{resize:vertical;min-height:96px}
.control-box{background:#f8fafc;border:1px solid #e2ecea;border-radius:18px;padding:1rem;display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;font-size:.85rem}
.control-box small{display:block;color:var(--muted);font-weight:800;text-transform:uppercase;font-size:.68rem;margin-bottom:.25rem}
.control-box strong{color:#1a2c3e}
.actions{display:flex;justify-content:flex-end;margin-top:1.3rem}
.btn-save{border:none;background:linear-gradient(135deg,#C0392B,#E67E22);color:#fff;border-radius:30px;padding:.75rem 1.2rem;font-weight:800;font-family:'Inter',sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 8px 18px rgba(230,126,34,.22)}
.btn-save:hover{filter:brightness(.96);transform:translateY(-1px)}
@media(max-width:900px){.form-grid{grid-template-columns:1fr}.control-box{grid-template-columns:1fr}}
@media(max-width:768px){.sidebar{width:58px}.sb-title,.sb-sub,.nav-item span{display:none}.main{margin-left:58px}.content{padding:1rem}.topbar{padding:1rem}.hero{align-items:flex-start;flex-direction:column}}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sb-icon"><i class="fas fa-car"></i></div>
    <div><div class="sb-title">AutoRepuestos<br>Pro</div><div class="sb-sub">Panel Admin</div></div>
  </div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="/catalogodigsistema/admin/index.php"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/products.php"><i class="fas fa-tags"></i><span>Catálogo</span></a>
    <a class="nav-item" href="/catalogodigsistema/admin/orders.php"><i class="fas fa-box-open"></i><span>Pedidos</span></a>
    <a class="nav-item active" href="/catalogodigsistema/admin/company.php"><i class="fas fa-building"></i><span>Empresa</span></a>
    <a class="nav-item" href="/catalogodigsistema/index.php" target="_blank"><i class="fas fa-store"></i><span>Ver tienda</span></a>
  </nav>
  <div class="sidebar-footer">
    <a class="nav-item nav-logout" href="/catalogodigsistema/api/logout.php?role=admin"><i class="fas fa-sign-out-alt"></i><span>Cerrar sesión</span></a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h2><i class="fas fa-building" style="color:var(--orange);margin-right:.5rem"></i>Información de la Empresa</h2>
    <div class="user-chip"><i class="fas fa-user-shield"></i><?= htmlspecialchars($_SESSION['name']) ?></div>
  </div>

  <div class="content">
    <section class="hero">
      <div>
        <h1>Datos administrativos y comerciales</h1>
        <p>Esta información se usa como ficha de empresa y se mostrará en los tickets PDF generados desde pedidos.</p>
      </div>
    </section>

    <form class="form-card" method="post">
      <div class="section-title"><i class="fas fa-id-card"></i> Datos de la empresa</div>
      <div class="form-grid">
        <div class="form-field"><label>Razón Social</label><input class="input" name="legal_name" value="<?= companyValue($company, 'legal_name') ?>"></div>
        <div class="form-field"><label>Nombre Comercial</label><input class="input" name="commercial_name" value="<?= companyValue($company, 'commercial_name') ?>"></div>
        <div class="form-field"><label>RFC</label><input class="input" name="rfc" value="<?= companyValue($company, 'rfc') ?>"></div>
        <div class="form-field"><label>Giro o Industria</label><input class="input" name="industry" value="<?= companyValue($company, 'industry') ?>"></div>
        <div class="form-field"><label>Tamaño de Empresa</label><input class="input" name="company_size" value="<?= companyValue($company, 'company_size') ?>"></div>
        <div class="form-field"><label>Número de Empleados</label><input class="input" name="employee_count" value="<?= companyValue($company, 'employee_count') ?>"></div>
        <div class="form-field"><label>Sitio Web</label><input class="input" name="website" value="<?= companyValue($company, 'website') ?>"></div>
        <div class="form-field"><label>Correo Electrónico</label><input class="input" type="email" name="company_email" value="<?= companyValue($company, 'company_email') ?>"></div>
        <div class="form-field"><label>Teléfono</label><input class="input" name="company_phone" value="<?= companyValue($company, 'company_phone') ?>"></div>
      </div>

      <div class="section-title"><i class="fas fa-location-dot"></i> Dirección</div>
      <div class="form-grid">
        <div class="form-field"><label>Calle</label><input class="input" name="street" value="<?= companyValue($company, 'street') ?>"></div>
        <div class="form-field"><label>Colonia</label><input class="input" name="neighborhood" value="<?= companyValue($company, 'neighborhood') ?>"></div>
        <div class="form-field"><label>Ciudad</label><input class="input" name="city" value="<?= companyValue($company, 'city') ?>"></div>
        <div class="form-field"><label>Estado</label><input class="input" name="state" value="<?= companyValue($company, 'state') ?>"></div>
        <div class="form-field"><label>Código Postal</label><input class="input" name="zip" value="<?= companyValue($company, 'zip') ?>"></div>
      </div>

      <div class="section-title"><i class="fas fa-user-tie"></i> Contacto principal</div>
      <div class="form-grid">
        <div class="form-field"><label>Nombre</label><input class="input" name="contact_name" value="<?= companyValue($company, 'contact_name') ?>"></div>
        <div class="form-field"><label>Puesto</label><input class="input" name="contact_position" value="<?= companyValue($company, 'contact_position') ?>"></div>
        <div class="form-field"><label>Correo Electrónico</label><input class="input" type="email" name="contact_email" value="<?= companyValue($company, 'contact_email') ?>"></div>
        <div class="form-field"><label>Teléfono</label><input class="input" name="contact_phone" value="<?= companyValue($company, 'contact_phone') ?>"></div>
      </div>

      <div class="section-title"><i class="fas fa-chart-line"></i> Información comercial</div>
      <div class="form-grid">
        <div class="form-field"><label>Medio de Captación</label><input class="input" name="lead_source" value="<?= companyValue($company, 'lead_source') ?>"></div>
        <div class="form-field"><label>Ejecutivo Asignado</label><input class="input" name="assigned_executive" value="<?= companyValue($company, 'assigned_executive') ?>"></div>
        <div class="form-field">
          <label>Estatus</label>
          <select class="input" name="status">
            <?php foreach (['Prospecto', 'Cliente', 'Inactivo'] as $status): ?>
            <option value="<?= $status ?>" <?= ($company['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-field full"><label>Observaciones</label><textarea class="input" name="observations"><?= companyValue($company, 'observations') ?></textarea></div>
      </div>

      <div class="section-title"><i class="fas fa-lock"></i> Control</div>
      <div class="control-box">
        <div><small>Fecha de Registro</small><strong><?= htmlspecialchars(formatCompanyDate($company['registered_at'] ?? '')) ?></strong></div>
        <div><small>Usuario que Registró</small><strong><?= htmlspecialchars($company['registered_by'] ?: ($_SESSION['username'] ?? 'admin')) ?></strong></div>
        <div><small>Fecha de Actualización</small><strong><?= htmlspecialchars(formatCompanyDate($company['updated_at'] ?? '')) ?></strong></div>
      </div>

      <div class="actions">
        <button class="btn-save" type="submit"><i class="fas fa-save"></i> Guardar información</button>
      </div>
    </form>
  </div>
</div>

<?php if ($saved): ?>
<script>Swal.fire({icon:'success',title:'Información guardada',timer:1300,showConfirmButton:false,toast:true,position:'top-end'});</script>
<?php elseif ($error !== ''): ?>
<script>Swal.fire('Error', <?= json_encode($error) ?>, 'error');</script>
<?php endif; ?>
</body>
</html>

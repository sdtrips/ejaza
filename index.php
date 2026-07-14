<?php
/**
 * إجازة — صفحة الدردشة الخاصة للمسجلين
 * 
 * ملف PHP واحد يقرأ كل الإعدادات من Environment Variables (Coolify).
 * لا يحتاج sed, Base64, أو URL params.
 * 
 * المتغيرات المطلوبة في Coolify:
 *   CHAT_PASSWORD      (إلزامي)       — كلمة سر الدخول
 *   CRISP_WEBSITE_ID   (اختياري)      — معرف Crisp.chat
 *   POSTHOG_API_KEY    (اختياري)      — مفتاح PostHog Analytics
 *   POSTHOG_HOST       (اختياري)      — خادم PostHog (افتراضي: https://us.i.posthog.com)
 */

session_start();

// ============================================================
// الإعدادات — كلها من Environment Variables
// ============================================================
$password   = getenv('CHAT_PASSWORD') ?: 'ejaza123';
$crispId    = getenv('CRISP_WEBSITE_ID') ?: '';
$phKey      = getenv('POSTHOG_API_KEY') ?: '';
$phHost     = getenv('POSTHOG_HOST') ?: 'https://us.i.posthog.com';
$hasCrisp   = $crispId !== '' && $crispId !== 'YOUR_ID';
$hasPosthog = $phKey !== '';

// ============================================================
// المصادقة — من الخادم وليس من JavaScript
// ============================================================
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (hash_equals($password, $_POST['password'])) {
        $_SESSION['ejaza_unlocked'] = true;
        // session cookie فقط — بدون expiry → تمسح بإغلاق المتصفح
    } else {
        $error = true;
    }
}

$unlocked = $_SESSION['ejaza_unlocked'] ?? false;

// ============================================================
// تسجيل الخروج (إذا ضغط المستخدم)
// ============================================================
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إجازة — تواصل للمسجلين</title>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E✈️%3C/text%3E%3C/svg%3E">
<?php if ($hasPosthog): ?>
<!-- PostHog Analytics -->
<script>
!function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="capture identify alias people.set people.set_once set_config register register_once unregister opt_out_capture has_opted_out_capture opt_in_capture reset isFeatureEnabled onFeatureFlags getFeatureFlag getFeatureFlagPayload reloadFeatureFlags group updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures getActiveMatchingSurveys getSurveys getNextSurveyStep onSessionId setPersonProperties".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
posthog.init('<?= htmlspecialchars($phKey, ENT_QUOTES) ?>', {
  api_host: '<?= htmlspecialchars($phHost, ENT_QUOTES) ?>',
  person_profiles: 'identified_only',
});
</script>
<?php endif; ?>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--green:#059669;--green-dark:#047857;--green-light:#d1fae5;--green-pale:#ecfdf5;--red:#dc2626;--gray:#6b7280}
body{
  font-family:'Segoe UI',Tahoma,sans-serif;color:#1a1a2e;
  background:linear-gradient(135deg,#059669,#047857);
  line-height:1.7;direction:rtl;min-height:100vh;
  display:flex;align-items:center;justify-content:center;
}
/* ===== شاشة القفل ===== */
.gate{
  background:#fff;border-radius:24px;padding:40px 32px;
  max-width:420px;width:90%;text-align:center;
  box-shadow:0 20px 60px rgba(0,0,0,.15);
}
.gate h1{font-size:28px;color:#059669;margin-bottom:8px}
.gate p{color:#6b7280;margin-bottom:24px;font-size:14px}
.gate input{
  width:100%;padding:14px 16px;border:2px solid #e5e7eb;
  border-radius:12px;font-size:16px;text-align:center;
  outline:none;transition:border-color .2s;
}
.gate input:focus{border-color:#059669}
.gate .error{color:#dc2626;font-size:13px;margin-top:8px}
.gate button{
  margin-top:16px;width:100%;padding:14px;
  background:#059669;color:#fff;border:none;
  border-radius:12px;font-size:16px;cursor:pointer;
  transition:background .2s;
}
.gate button:hover{background:#047857}
/* ===== الشات ===== */
.chat-area{width:100%;min-height:100vh;position:relative}
.toolbar{
  position:fixed;top:0;left:0;right:0;
  background:rgba(255,255,255,.95);backdrop-filter:blur(8px);
  padding:12px 20px;display:flex;align-items:center;
  justify-content:space-between;z-index:999;
  border-bottom:1px solid #e5e7eb;direction:rtl;
}
.toolbar .logo{font-weight:700;color:#059669;font-size:18px}
.toolbar .status{font-size:13px;color:#6b7280}
.toolbar .logout{
  font-size:12px;color:#dc2626;text-decoration:none;
  padding:4px 12px;border:1px solid #fca5a5;border-radius:8px;
  transition:all .2s;
}
.toolbar .logout:hover{background:#fee2e2}
/* ===== دليل سريع ===== */
.guide{display:flex;align-items:center;justify-content:center;padding:80px 20px 40px;min-height:100vh}
.guide-card{background:#fff;border-radius:20px;padding:36px 32px;max-width:460px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,.06);text-align:center}
.guide-check{font-size:36px;margin-bottom:8px}
.guide-card h2{font-size:22px;color:#1a1a2e;margin-bottom:4px}
.guide-sub{color:#6b7280;font-size:14px;margin-bottom:24px}
.guide-step{display:flex;align-items:flex-start;gap:14px;text-align:right;padding:14px 16px;background:var(--green-pale,#ecfdf5);border-radius:12px;margin-bottom:10px}
.step-icon{font-size:22px;flex-shrink:0;margin-top:2px}
.step-text{display:flex;flex-direction:column}
.step-text strong{font-size:14px;color:#1a1a2e}
.step-text span{font-size:12.5px;color:#6b7280;margin-top:2px}
.guide-note{background:#fef3c7;color:#92400e;font-size:12.5px;padding:12px 16px;border-radius:12px;margin-top:16px;text-align:center;line-height:1.6}
.guide-refresh{margin-top:18px;padding:10px 36px;background:var(--green,#059669);color:#fff;border:none;border-radius:50px;font-size:14px;font-weight:600;cursor:pointer;transition:all .25s;display:inline-block}
.guide-refresh:hover{background:var(--green-dark,#047857);transform:scale(1.03);box-shadow:0 4px 15px rgba(5,150,105,.3)}
</style>
</head>
<body>

<?php if (!$unlocked): ?>
<!-- ===== شاشة القفل ===== -->
<div class="gate">
  <h1>🔒 تواصل للمسجلين</h1>
  <p>هذه الدردشة مخصصة للعملاء المسجلين. أدخل كلمة المرور للوصول.</p>
  <form method="post">
    <input type="password" name="password" placeholder="كلمة المرور" autocomplete="off" autofocus>
    <?php if ($error): ?>
    <div class="error">❌ كلمة المرور غير صحيحة</div>
    <?php endif; ?>
    <button type="submit">دخول</button>
  </form>
</div>

<?php else: ?>
<!-- ===== منطقة الشات ===== -->
<div class="chat-area">
  <div class="toolbar">
    <div class="logo">✈️ إجازة</div>
    <div class="status">🔒 محادثة مؤقتة — جلسة تنتهي بإغلاق المتصفح</div>
    <a href="?logout" class="logout">🚪 خروج</a>
  </div>

  <!-- ===== دليل سريع ===== -->
  <div class="guide">
    <div class="guide-card">
      <div class="guide-check">✅</div>
      <h2>مرحباً بك 👋</h2>
      <p class="guide-sub">الدردشة متاحة الآن. اتبع الخطوات:</p>

      <div class="guide-step">
        <div class="step-icon">👆</div>
        <div class="step-text">
          <strong>اضغط على أيقونة 💬</strong>
          <span>في الزاوية السفلية لفتح نافذة المحادثة</span>
        </div>
      </div>

      <div class="guide-step">
        <div class="step-icon">📧</div>
        <div class="step-text">
          <strong>اترك بريدك الإلكتروني</strong>
          <span>في أول رسالة لتستقبل الردود حتى لو كنت غير متصل</span>
        </div>
      </div>

      <div class="guide-step">
        <div class="step-icon">🔄</div>
        <div class="step-text">
          <strong>إذا اختفت المحادثة</strong>
          <span>اضغط تحديث أو أعد فتح الصفحة</span>
        </div>
      </div>

      <div class="guide-note">
        ⚠️ هذه المحادثة <strong>مؤقتة</strong> — بمجرد إغلاق التبويب تختفي المحادثة بالكامل.
      </div>

      <button class="guide-refresh" onclick="location.reload()">🔄 تحديث الصفحة</button>
    </div>
  </div>
</div>

<?php if ($hasCrisp): ?>
<!-- ============================================================ -->
<!-- Crisp.chat — يُحمّل فقط للمستخدمين المسجلين (بعد فتح القفل)    -->
<!-- ============================================================ -->
<script>
window.$crisp = [];
window.CRISP_WEBSITE_ID = '<?= htmlspecialchars($crispId, ENT_QUOTES) ?>';
(function() {
  var d = document;
  var s = d.createElement('script');
  s.src = 'https://client.crisp.chat/l.js';
  s.async = true;
  s.charset = 'utf-8';
  d.getElementsByTagName('head')[0].appendChild(s);
})();
// جلسة جديدة مع كل دخول
$crisp.push(['do', 'session:reset']);
$crisp.push(['set', 'session:segments', [['guest', Date.now().toString(36)]]]);
</script>
<?php endif; ?>

<?php endif; ?>

</body>
</html>

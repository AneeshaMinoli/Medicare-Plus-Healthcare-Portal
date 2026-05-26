<?php
session_start();
require_once('config/db_connect.php'); // must set $conn as mysqli

// ---------- AUTH ----------
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
  header("Location: login.php");
  exit();
}

$doctor_user_id = (int) $_SESSION['user_id'];
$doctor_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Doctor';
$doctor_user_id = $_SESSION['user_id'];

$feedback_result = mysqli_query($conn, "
    SELECT f.rating, f.comment, f.created_at, p.full_name AS patient_name
    FROM feedbacks f
    JOIN users p ON p.user_id = f.patient_id
    WHERE f.doctor_id = '$doctor_user_id'
    ORDER BY f.created_at DESC
");


// ---------- FETCH PATIENTS ----------
$patients_result = null;
$patients_query = "SELECT user_id, full_name, email, phone FROM users WHERE role='patient' ORDER BY full_name ASC";
$patients_result = mysqli_query($conn, $patients_query);
if ($patients_result === false)
  $patients_result = null;


// ---------- FETCH FEEDBACK (from 'feedbacks' table) ----------

$doctor_id = $_SESSION['user_id']; // logged-in doctor

$feedback_sql = "
    SELECT f.*, u.full_name AS patient_name
    FROM feedbacks f
    JOIN users u ON f.patient_id = u.user_id
    WHERE f.doctor_id = ?
    ORDER BY f.created_at DESC
    LIMIT 4
";

$stmt = $conn->prepare($feedback_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$feedback_result = $stmt->get_result();
$stmt->close();




$doctor_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        f.rating,
        f.comment,
        f.created_at,
        u.full_name AS patient_name
    FROM feedbacks f
    JOIN users u ON f.patient_id = u.user_id
    WHERE f.doctor_id = ?
    ORDER BY f.created_at DESC
    LIMIT 4
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$feedback_result = $stmt->get_result();


// ---------- FETCH APPOINTMENTS ----------
$appointments_result = null;
/*
  Robust join: handle cases where appointments.patient_id is either:
  - patients.patient_id  (common), or
  - users.user_id        (some earlier code used this)

  We LEFT JOIN both paths and COALESCE values so it works either way.
*/
$appointments_sql = "
    SELECT
      a.appointment_id,
      a.patient_id,
      a.appointment_date,
      a.appointment_time,
      a.notes,
      COALESCE(u_from_p.full_name, u_direct.full_name) AS patient_name,
      COALESCE(u_from_p.email, u_direct.email) AS patient_email,
      COALESCE(u_from_p.phone, u_direct.phone) AS patient_phone
    FROM appointments a
    LEFT JOIN patients p ON a.patient_id = p.patient_id
    LEFT JOIN users u_from_p ON p.user_id = u_from_p.user_id
    LEFT JOIN users u_direct ON a.patient_id = u_direct.user_id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time ASC
";
if ($stmt2 = $conn->prepare($appointments_sql)) {
  $stmt2->bind_param("i", $doctor_user_id);
  if ($stmt2->execute()) {
    $appointments_result = $stmt2->get_result();
  } else {
    $appointments_result = null;
  }
  $stmt2->close();
} else {
  $appointments_result = null;
}

// ---------- WEEKLY APPOINTMENT COUNTS (today -> +6 days) ----------
$weekly_counts = array_fill(0, 7, 0);
$weekly_labels = [];
$today = new DateTime('today');
$start_date = $today->format('Y-m-d');
$end_date = (clone $today)->modify('+6 days')->format('Y-m-d');

for ($i = 0; $i < 7; $i++) {
  $d = (clone $today)->modify("+{$i} days");
  $weekly_labels[] = $d->format('D'); // Mon, Tue...
}

$counts_sql = "
    SELECT appointment_date, COUNT(*) AS cnt
    FROM appointments
    WHERE doctor_id = ? AND appointment_date BETWEEN ? AND ?
    GROUP BY appointment_date
";
if ($stmt3 = $conn->prepare($counts_sql)) {
  $stmt3->bind_param("iss", $doctor_user_id, $start_date, $end_date);
  if ($stmt3->execute()) {
    $res = $stmt3->get_result();
    while ($row = $res->fetch_assoc()) {
      $d = $row['appointment_date'];
      $dt = DateTime::createFromFormat('Y-m-d', $d);
      if ($dt) {
        $idx = (int) $dt->diff($today)->format('%a'); // 0..6
        if ($idx >= 0 && $idx < 7)
          $weekly_counts[$idx] = (int) $row['cnt'];
      }
    }
  }
  $stmt3->close();
}

// Utility counts for sidebar (safe defaults)
$patients_count = ($patients_result ? mysqli_num_rows($patients_result) : 0);
$appointments_count = ($appointments_result ? $appointments_result->num_rows : 0);
$feedback_count = ($feedback_result ? $feedback_result->num_rows : 0);

// helper: truncate note preview
function preview_text($text, $len = 80)
{
  if ($text === null)
    return '';
  $text = trim($text);
  if (mb_strlen($text) <= $len)
    return htmlspecialchars($text);
  return htmlspecialchars(mb_substr($text, 0, $len)) . '...';
}

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Doctor Dashboard | MediCare+</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #fbf6ff;
      --panel: #ffffffcc;
      --purple-dark: #4a148c;
      --purple: #6a1b9a;
      --accent: #8e24aa;
      --muted: #6b4b9a;
      --text: #2b2340;
      --glass-border: rgba(255, 255, 255, 0.6);
      --card-shadow: 0 10px 30px rgba(74, 20, 140, 0.06);
      --radius: 12px;
    }

    /* Global */
    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(180deg, #fff, #f4eefd 100%);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }


    /* Header */
    .header {
      position: sticky;
      top: 0;
      z-index: 50;
      background: linear-gradient(90deg, var(--purple-dark), var(--purple));
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 22px;
      box-shadow: 0 6px 24px rgba(74, 20, 140, 0.12);
    }

    .brand {
      display: flex;
      gap: 12px;
      align-items: center
    }

    .logo {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--accent), var(--purple));
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-weight: 700
    }

    .header h1 {
      margin: 0;
      font-size: 18px;
      font-weight: 600
    }

    .header nav {
      display: flex;
      gap: 8px;
      align-items: center
    }

    .header nav a {
      color: #fff;
      text-decoration: none;
      padding: 8px 12px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.08);
      font-weight: 600
    }

    .header nav a:hover {
      background: rgba(255, 255, 255, 0.16);
      transform: translateY(-2px);
    }

    /* Layout */
    .container {
      max-width: 1200px;
      margin: 26px auto;
      padding: 0 18px 80px
    }

    aside {
      width: 260px;
      min-width: 260px;
    }

    aside .card {
      width: 100%;
      max-width: 100%;
      display: block;
    }


    .grid {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 20px;
      align-items: start;
    }


    /* Card / glass */
    .card {
      background: var(--panel);
      border-radius: var(--radius);
      padding: 16px;
      box-shadow: var(--card-shadow);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(6px) saturate(120%);
      transition: transform .18s;
    }

    .card:hover {
      transform: translateY(-6px)
    }

    /* Sidebar */
    .profile {
      display: flex;
      gap: 12px;
      align-items: center
    }

    .avatar {
      width: 56px;
      height: 56px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--purple), var(--accent));
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 18px;
      box-shadow: 0 8px 18px rgba(106, 27, 154, 0.12)
    }

    .kv {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      align-items: center
    }

    .kv .label {
      color: var(--muted);
      font-size: 13px
    }

    .kv .val {
      font-weight: 700;
      color: var(--purple-dark)
    }

    /* Main */
    .main {
      display: flex;
      flex-direction: column;
      gap: 18px
    }

    .top-row {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 16px
    }

    .top-cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px
    }

    .small {
      font-size: 13px;
      color: var(--muted)
    }

    .actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px
    }

    .btn {
      appearance: none;
      border: none;
      cursor: pointer;
      padding: 9px 12px;
      border-radius: 10px;
      color: #fff;
      background: linear-gradient(90deg, var(--purple), var(--accent));
      font-weight: 700
    }

    .btn.ghost {
      background: transparent;
      color: var(--purple-dark);
      border: 1px solid rgba(106, 27, 154, 0.08)
    }

    /* Tables */
    .table-wrap {
      overflow: auto;
      border-radius: 10px
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 720px
    }

    thead th {
      padding: 12px 14px;
      text-align: left;
      background: linear-gradient(90deg, var(--purple), var(--accent));
      color: #fff;
      font-weight: 700;
      font-size: 13px
    }

    tbody td {
      padding: 12px 14px;
      background: rgba(255, 255, 255, 0.7);
      border-bottom: 1px solid rgba(0, 0, 0, 0.03);
      color: var(--text)
    }

    tbody tr:hover td {
      background: rgba(171, 71, 188, 0.04)
    }

    .appt-row {
      cursor: pointer
    }

    /* Chart */
    .chart-row {
      display: flex;
      gap: 12px;
      align-items: center
    }

    #weekChart {
      width: 160px;
      height: 72px
    }

    /* Feedback list */
    .feedback-item {
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.7)
    }

    /* Modal */
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 60
    }

    .modal {
      width: 520px;
      max-width: 95%;
      background: var(--panel);
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18)
    }

    .modal h3 {
      margin: 0
    }

    .close-btn {
      background: transparent;
      border: 0;
      color: var(--muted);
      font-weight: 700;
      cursor: pointer;
      float: right
    }

    /* Responsive */
    @media(max-width:680px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }


    .top-row {
      grid-template-columns: 1fr
    }

    table {
      min-width: unset
    }
    }

    @media(max-width:560px) {

      thead th,
      tbody td {
        padding: 10px
      }
    }

    .fade {
      animation: fadeIn .45s ease both
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(8px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }
  </style>
</head>

<body>
  <header class="header fade">
    <div class="brand">
      <div class="logo">MC</div>
      <div>
        <h1>MediCare+</h1>
        <div style="font-size:12px; color:rgba(255,255,255,0.9)">Doctor Dashboard</div>
      </div>
    </div>

    <nav>
      <a href="doctor_dashboard.php">Dashboard</a>
      <a href="view_lab_results.php">Lab Reports</a>
      <a href="update_lab_reports.php">Update Reports</a>
      <a href="view_messages.php">Messages</a>
      <a href="login.php">Logout</a>
    </nav>
  </header>

  <div class="container fade">
    <div class="grid">
      <!-- SIDEBAR -->
      <aside>
        <div class="card profile">
          <div style="display:flex;gap:12px;align-items:center">
            <div class="avatar"><?php echo strtoupper(substr(htmlspecialchars($doctor_name), 0, 1)); ?></div>
            <div>
              <div style="font-weight:700"><?php echo htmlspecialchars($doctor_name); ?></div>
              <div style="font-size:13px;color:var(--muted)">Doctor ID: <?php echo (int) $doctor_user_id; ?></div>
            </div>
          </div>

          <div style="margin-top:12px">
            <div class="kv">
              <div class="label small">Patients</div>
              <div class="val"><?php echo $patients_count; ?></div>
            </div>
            <div class="kv">
              <div class="label small">Appointments</div>
              <div class="val"><?php echo $appointments_count; ?></div>
            </div>
            <div class="kv">
              <div class="label small">Feedback</div>
              <div class="val"><?php echo $feedback_count; ?></div>
            </div>
          </div>

         
        </div>

        <div class="card" style="margin-top:16px">
          <h3 style="margin-bottom:8px">Weekly Appointments</h3>
          <div class="chart-row">
            <canvas id="weekChart" width="160" height="72"></canvas>
            <div style="font-size:13px;color:var(--muted)">
              <div style="font-weight:700;color:var(--purple-dark)"><?php echo array_sum($weekly_counts); ?></div>
              <div class="small">in next 7 days</div>
            </div>
          </div>
          <div style="margin-top:8px" class="small">Hover rows to preview. Click a row to open appointment.</div>
        </div>

        <div class="card" style="margin-top:16px">
          <h3 style="margin-bottom:8px">Recent Feedback</h3>
        <aside>
    <!-- Other sidebar content -->

    <!-- Recent Feedback Card -->
    <div class="card" style="margin-top:16px">
       <div style="margin-top:14px; text-align:right;">
    <a href="view_all_feedback.php" 
       style="background:#6d3bb8; color:white; padding:8px 14px; border-radius:6px; text-decoration:none; font-size:14px;">
        View All Feedback →
    </a>
</div>

    </div>
</aside>

          
      </aside>

      <!-- MAIN -->
      <main class="main">
        <div class="top-row">
          <div class="card">
            <h3>Welcome back, Dr. <?php echo htmlspecialchars($doctor_name); ?></h3>
            <p class="small">Manage patient reports, appointments and messages from this dashboard.</p>
            <div class="actions">
              <a class="btn" href="view_lab_results.php">View Reports</a>
              <a class="btn" href="update_lab_reports.php">Update Reports</a>
              <a class="btn ghost" href="view_messages.php">Messages</a>
            </div>
          </div>

          <div class="card">
            <h3>Overview</h3>
            <p class="small">Total counts at a glance.</p>
            <div style="margin-top:12px">
              <div class="kv">
                <div class="label">Patients</div>
                <div class="val"><?php echo $patients_count; ?></div>
              </div>
              <div class="kv">
                <div class="label">Appointments</div>
                <div class="val"><?php echo $appointments_count; ?></div>
              </div>
              <div class="kv">
                <div class="label">Feedback</div>
                <div class="val"><?php echo $feedback_count; ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <h3>Appointments</h3>
          <p class="small">All appointments booked with you. Click a row to view details.</p>

          <?php if ($appointments_result && $appointments_result->num_rows > 0): ?>
            <div class="table-wrap" style="margin-top:12px">
              <table role="table" aria-describedby="appointments">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($appt = $appointments_result->fetch_assoc()): ?>
                    <?php
                    $data = [
                      'id' => (int) $appt['appointment_id'],
                      'patient' => $appt['patient_name'] ?? '',
                      'email' => $appt['patient_email'] ?? '',
                      'phone' => $appt['patient_phone'] ?? '',
                      'date' => $appt['appointment_date'],
                      'time' => $appt['appointment_time'],
                      'notes' => $appt['notes'] ?? ''
                    ];
                    $data_json = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr class="appt-row" data-appt='<?php echo $data_json; ?>'>
                      <td><?php echo (int) $appt['appointment_id']; ?></td>
                      <td><?php echo htmlspecialchars($appt['patient_name'] ?? 'Unknown'); ?></td>
                      <td><?php echo htmlspecialchars($appt['patient_email'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($appt['appointment_date']); ?></td>
                      <td><?php echo htmlspecialchars($appt['appointment_time']); ?></td>
                      <td><?php echo preview_text($appt['notes'] ?? '', 80); ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="small" style="color:var(--muted); margin-top:8px">No appointments booked yet.</p>
          <?php endif; ?>
        </div>

        <div class="card">
          <h3>All Patients</h3>
          <p class="small">Registered patients (quick access)</p>

          <?php if ($patients_result && mysqli_num_rows($patients_result) > 0): ?>
            <div class="table-wrap" style="margin-top:12px">
              <table role="table" aria-describedby="patients">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($p = mysqli_fetch_assoc($patients_result)): ?>
                    <tr>
                      <td><?php echo (int) $p['user_id']; ?></td>
                      <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                      <td><?php echo htmlspecialchars($p['email']); ?></td>
                      <td><?php echo htmlspecialchars($p['phone']); ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="small" style="color:var(--muted); margin-top:8px">No patients found.</p>
          <?php endif; ?>
        </div>

      </main>
    </div>
  </div>

  <!-- Modal -->
  <div id="modalBackdrop" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal">
      <button class="close-btn" id="closeModal">✕</button>
      <h3 id="modalTitle">Appointment</h3>
      <div class="meta" id="modalMeta"></div>
      <div id="modalNotes" style="white-space:pre-wrap; margin-top:8px; color:var(--text)"></div>
      <div style="margin-top:14px;display:flex;gap:8px;">
        <button class="btn" id="messagePatient">Message Patient</button>
        <a href="#" id="openProfile" class="btn ghost" style="text-decoration:none">Open Profile</a>
      </div>
    </div>
  </div>

  <script>
    // Modal logic
    const modal = document.getElementById('modalBackdrop');
    const modalTitle = document.getElementById('modalTitle');
    const modalMeta = document.getElementById('modalMeta');
    const modalNotes = document.getElementById('modalNotes');
    const closeModalBtn = document.getElementById('closeModal');

    document.querySelectorAll('.appt-row').forEach(row => {
      row.addEventListener('click', () => {
        const data = JSON.parse(row.getAttribute('data-appt'));
        modalTitle.textContent = `Appointment #${data.id} — ${data.patient || 'Patient'}`;
        modalMeta.innerHTML = `<strong>Date:</strong> ${escapeHtml(data.date)} &nbsp; • &nbsp; <strong>Time:</strong> ${escapeHtml(data.time)} <br>
                           <strong>Email:</strong> ${escapeHtml(data.email)} &nbsp; • &nbsp; <strong>Phone:</strong> ${escapeHtml(data.phone)}`;
        modalNotes.textContent = data.notes || 'No notes provided.';
        document.getElementById('openProfile').href = "patients.php?id=" + encodeURIComponent(data.patient);
        modal.style.display = 'flex';
      });
    });

    closeModalBtn.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    function escapeHtml(str) { return (str + '').replace(/[&<>"']/g, function (m) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]; }); }

    // Weekly mini bar chart (canvas)
    const weeklyCounts = <?php echo json_encode($weekly_counts); ?>;
    const weeklyLabels = <?php echo json_encode($weekly_labels); ?>;

    (function drawBarChart() {
      const canvas = document.getElementById('weekChart');
      if (!canvas || !canvas.getContext) return;
      const ctx = canvas.getContext('2d');
      const DPR = window.devicePixelRatio || 1;
      const w = canvas.width;
      const h = canvas.height;
      ctx.clearRect(0, 0, w, h);

      const max = Math.max(1, ...weeklyCounts);
      const pad = 10;
      const availableW = w - pad * 2;
      const barW = (availableW / weeklyCounts.length) * 0.65;
      const gap = (availableW - barW * weeklyCounts.length) / (weeklyCounts.length - 1);

      weeklyCounts.forEach((v, i) => {
        const x = pad + i * (barW + gap);
        const barH = (v / max) * (h - pad * 3 - 12);
        const y = h - pad - barH;
        const g = ctx.createLinearGradient(x, y, x + barW, y + barH);
        g.addColorStop(0, '#8e24aa');
        g.addColorStop(1, '#6a1b9a');
        ctx.fillStyle = g;
        roundRect(ctx, x, y, barW, barH, 4, true, false);
        ctx.font = "10px Poppins, sans-serif";
        ctx.fillStyle = '#4b2a66';
        ctx.textAlign = "center";
        ctx.fillText(weeklyLabels[i], x + barW / 2, h - 2);
      });
    })();

    function roundRect(ctx, x, y, w, h, r, fill, stroke) {
      if (typeof r === 'undefined') r = 5;
      ctx.beginPath();
      ctx.moveTo(x + r, y);
      ctx.arcTo(x + w, y, x + w, y + h, r);
      ctx.arcTo(x + w, y + h, x, y + h, r);
      ctx.arcTo(x, y + h, x, y, r);
      ctx.arcTo(x, y, x + w, y, r);
      ctx.closePath();
      if (fill) ctx.fill();
      if (stroke) ctx.stroke();
    }
  </script>
</body>

</html>
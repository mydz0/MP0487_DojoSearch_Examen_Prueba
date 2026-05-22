<?php
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

//first commit

session_start();

require_once '../../controllers/db_connection.php';
require_once '../../controllers/UserController.php';
UserController::checkSession();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: events.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $event = null;
}

if (!$event) {
    header('Location: events.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($event['name']); ?> | DojoSearch</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/events.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../assets/images/logoDS.png">
  <style>
    /* ---- Detail page overrides ---- */
    .detail-wrapper {
      max-width: 900px;
      margin: 110px auto 4rem;
      padding: 0 20px;
    }

    .detail-back {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: rgba(255,255,255,0.7);
      font-size: 0.9rem;
      text-decoration: none;
      margin-bottom: 2rem;
      transition: color 0.2s;
    }

    .detail-back:hover {
      color: #fff;
      text-decoration: none;
    }

    .detail-back i {
      color: #7e0000;
    }

    .detail-card {
      background-color: #1a1a1a;
      border: 1px solid rgba(126, 0, 0, 0.3);
      border-radius: 12px;
      overflow: hidden;
    }

    .detail-image-wrap {
      position: relative;
      height: 320px;
      overflow: hidden;
    }

    .detail-image-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .detail-image-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);
    }

    .detail-badge {
      position: absolute;
      top: 20px;
      left: 20px;
      background-color: #7e0000;
      color: #fff;
      padding: 0.4rem 1.2rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      z-index: 2;
    }

    .detail-body {
      padding: 2rem 2.5rem;
    }

    .detail-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 3rem;
      color: #fff;
      letter-spacing: 2px;
      margin-bottom: 1rem;
    }

    .detail-divider {
      width: 80px;
      height: 4px;
      background: #7e0000;
      border-radius: 2px;
      margin-bottom: 1.8rem;
    }

    .detail-meta-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.2rem;
      margin-bottom: 2rem;
    }

    .detail-meta-item {
      display: flex;
      align-items: flex-start;
      gap: 0.8rem;
      background-color: #111;
      border: 1px solid rgba(126,0,0,0.2);
      border-radius: 8px;
      padding: 1rem 1.2rem;
    }

    .detail-meta-item i {
      color: #7e0000;
      font-size: 1.2rem;
      margin-top: 2px;
    }

    .detail-meta-label {
      font-size: 0.75rem;
      color: rgba(255,255,255,0.5);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 0.2rem;
    }

    .detail-meta-value {
      font-size: 1rem;
      color: #fff;
      font-weight: 600;
    }

    .detail-section-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.6rem;
      color: #fff;
      letter-spacing: 1px;
      margin-bottom: 0.8rem;
    }

    .detail-description {
      color: rgba(255,255,255,0.75);
      font-size: 1rem;
      line-height: 1.8;
      margin-bottom: 2.5rem;
    }

    .detail-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: center;
      border-top: 1px solid rgba(126,0,0,0.3);
      padding-top: 1.8rem;
    }

    .btn-enroll {
      padding: 0.8rem 2.2rem;
      background-color: #7e0000;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-weight: 700;
      font-size: 1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      cursor: pointer;
      transition: background-color 0.25s;
    }

    .btn-enroll:hover {
      background-color: #a00000;
    }

    .btn-back-bottom {
      padding: 0.8rem 2.2rem;
      background-color: transparent;
      color: rgba(255,255,255,0.8);
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 6px;
      font-weight: 600;
      font-size: 1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      text-decoration: none;
      transition: border-color 0.25s, color 0.25s;
    }

    .btn-back-bottom:hover {
      border-color: #7e0000;
      color: #fff;
      text-decoration: none;
    }

    .admin-actions {
      margin-left: auto;
      display: flex;
      gap: 0.8rem;
      flex-wrap: wrap;
      align-items: center;
    }

    @media (max-width: 600px) {
      .detail-body { padding: 1.5rem; }
      .detail-title { font-size: 2.2rem; }
      .detail-image-wrap { height: 200px; }
      .admin-actions { margin-left: 0; }
    }
  </style>
</head>

<body>
  <!-- NAVBAR -->
  <div id="navbar">
    <div class="logo-container">
      <a href="../php/index.php" class="logo-link">
        <img src="../assets/images/logoDS.png" alt="Logo" class="logo" />
        <h2>DojoSearch</h2>
      </a>
    </div>
    <input type="checkbox" id="menu-toggle" class="menu-toggle" />
    <label for="menu-toggle" class="menu-toggle-label">&#9776;</label>
    <nav class="nav-menu">
      <a href="../php/events.php">EVENTOS</a>
      <a href="<?php echo isset($_SESSION['user']) ? ($_SESSION['user']['is_admin'] ? 'userAdmin.php' : 'userUser.php') : 'login.php'; ?>">PERFIL</a>
    </nav>
  </div>

  <div class="detail-wrapper">
    <a href="events.php" class="detail-back">
      <i class="fas fa-arrow-left"></i> Volver a eventos
    </a>

    <div class="detail-card">
      <!-- Image -->
      <div class="detail-image-wrap">
        <img src="../assets/images/events/event<?php echo $event['id']; ?>.jpg" alt="<?php echo htmlspecialchars($event['name']); ?>">
        <div class="detail-image-overlay"></div>
        <span class="detail-badge"><?php echo htmlspecialchars($event['name']); ?></span>
      </div>

      <!-- Body -->
      <div class="detail-body">
        <h1 class="detail-title"><?php echo htmlspecialchars($event['name']); ?></h1>
        <div class="detail-divider"></div>

        <!-- Meta grid -->
        <div class="detail-meta-grid">
          <div class="detail-meta-item">
            <i class="fas fa-calendar-alt"></i>
            <div>
              <div class="detail-meta-label">Fecha</div>
              <div class="detail-meta-value">
                <?php echo date('d \d\e F \d\e Y', strtotime($event['date'])); ?>
              </div>
            </div>
          </div>
          <div class="detail-meta-item">
            <i class="fas fa-clock"></i>
            <div>
              <div class="detail-meta-label">Hora</div>
              <div class="detail-meta-value">
                <?php echo date('H:i', strtotime($event['date'])); ?>h
              </div>
            </div>
          </div>
          <div class="detail-meta-item">
            <i class="fas fa-map-marker-alt"></i>
            <div>
              <div class="detail-meta-label">Ubicación</div>
              <div class="detail-meta-value">
                <?php echo htmlspecialchars($event['location'] ?? 'Por confirmar'); ?>
              </div>
            </div>
          </div>
          <div class="detail-meta-item">
            <i class="fas fa-tag"></i>
            <div>
              <div class="detail-meta-label">Precio</div>
              <div class="detail-meta-value">Gratis</div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <h2 class="detail-section-title">Descripción</h2>
        <p class="detail-description">
          <?php echo nl2br(htmlspecialchars($event['description'] ?? 'Sin descripción disponible.')); ?>
        </p>

        <!-- Actions -->
        <div class="detail-actions">
          <button class="btn-enroll">
            <i class="fas fa-fist-raised"></i> ¡Apúntate!
          </button>
          <a href="events.php" class="btn-back-bottom">
            <i class="fas fa-list"></i> Ver todos los eventos
          </a>

          <?php if (!empty($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
            <div class="admin-actions">
              <a href="./manageEvents.php?id=<?php echo $event['id']; ?>" class="btn-edit">
                <i class="fas fa-pen"></i> Editar
              </a>
              <form action="/../../controllers/EventController.php" method="POST" style="display:inline;">
                <input type="hidden" name="deleteEvent" value="1">
                <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                <button type="submit" class="btn-delete"
                  onclick="return confirm('¿Seguro que quieres eliminar este evento?');">
                  <i class="fas fa-trash"></i> Eliminar
                </button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="martial-footer">
    <div class="footer-container">
      <div class="footer-grid">
        <div class="footer-column">
          <div class="footer-brand">
            <img src="../assets/images/logoDS.png" alt="DojoSearch Logo" class="footer-logo">
            <h3 class="footer-title">DojoSearch</h3>
          </div>
          <div class="social-links">
            <a href="#" class="social-icon" aria-label="Instagram">
              <img src="../assets/images/social-media/instagram.png" alt="Instagram">
            </a>
            <a href="#" class="social-icon" aria-label="Facebook">
              <img src="../assets/images/social-media/facebook.png" alt="Facebook">
            </a>
            <a href="#" class="social-icon" aria-label="YouTube">
              <img src="../assets/images/social-media/youtube.png" alt="YouTube">
            </a>
            <a href="#" class="social-icon" aria-label="LinkedIn">
              <img src="../assets/images/social-media/linkedin.png" alt="LinkedIn">
            </a>
          </div>
        </div>
        <div class="footer-column">
          <h4 class="footer-heading">Explora</h4>
          <ul class="footer-links">
            <li><a href="../php/events.php">Eventos</a></li>
            <li><a href="../php/login.php">Mi Perfil</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h4 class="footer-heading">Contacto</h4>
          <ul class="contact-info">
            <li>
              <img src="../assets/images/icons/email.png" alt="Email">
              <span>info@dojosearch.com</span>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer-divider"></div>
      <div class="footer-bottom">
        <p class="copyright">&copy; 2023 DojoSearch. Todos los derechos reservados</p>
      </div>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    var prevScrollpos = window.pageYOffset;
    window.onscroll = function () {
      var currentScrollPos = window.pageYOffset;
      document.getElementById("navbar").style.top = prevScrollpos > currentScrollPos ? "0" : "-80px";
      prevScrollpos = currentScrollPos;
    };
  </script>
</body>

</html>

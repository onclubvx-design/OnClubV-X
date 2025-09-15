<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>OnClub</title>

  <!-- Icons -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>

  <!-- Swiper -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  <link rel="stylesheet" href="../css/Home.css"/>
</head>
<body>
  <header class="header">
    <nav class="nav container">
      <div class="logo">OnClub</div>
      <ul class="nav-links">
        <li><a href="#inicio">Inicio</a></li>
        <li><a href="#caracteristicas">Características</a></li>
        <li><a href="#como-funciona">Cómo Funciona</a></li>
        <li><a href="#contacto">Contacto</a></li>
      </ul>
      <div class="nav-actions">
        <a href="../views/Registro.php" class="cta-button">Registrarse</a>
        <a href="Registro.php?tab=login" class="btn-open-modal">Acceso Administrativo</a>
      </div>
    </nav>
  </header>

  <main>
    <!-- HERO -->
    <section id="inicio" class="hero">
      <div class="container">
        <div class="hero-content">
          <h1>Control de Asistencia Inteligente para tu Club de Golf</h1>
          <p>Moderniza la gestión de tu personal con OnClub. Sistema seguro, eficiente y fácil de usar que revoluciona el control de asistencia en clubes de golf.</p>
          <div class="hero-buttons">
            <a href="#caracteristicas" class="cta-button pulse">Conocer Más</a>
            <a href="#como-funciona" class="cta-button cta-outline">Cómo Funciona</a>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES -->
    <section id="caracteristicas" class="features">
      <div class="container">
        <h2 class="section-title">Características Principales</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Registro Instantáneo</h3>
            <p>Los empleados pueden marcar entrada y salida usando su ID personal de forma instantánea y segura.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Seguridad Garantizada</h3>
            <p>Solo administradores autorizados acceden al panel de control. Registros con timestamp del servidor evitan manipulaciones.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Dashboard Completo</h3>
            <p>Visualiza registros diarios, semanales o mensuales con filtros avanzados por empleado o fecha.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Exportación Fácil</h3>
            <p>Genera reportes en Excel o PDF para nómina y gestión de RRHH de manera automática.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon"></div>
            <h3>Multiplataforma</h3>
            <p>Accede desde web o aplicación móvil. Compatible con todos los dispositivos.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CAROUSEL -->
    <section class="carousel">
      <div class="container">
        <h2 class="section-title">Galería OnClub</h2>

        <div class="swiper stats-swiper">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <img src="https://i.pinimg.com/736x/eb/c9/9d/ebc99d3deca4e7b77bb498ff9857fd22.jpg" alt="Club 1">
            </div>
            <div class="swiper-slide">
              <img src="https://i.pinimg.com/1200x/c3/c6/72/c3c67292dd8d29d5acc054f272d959fe.jpg" alt="Club 2">
            </div>
            <div class="swiper-slide">
              <img src="https://i.pinimg.com/1200x/e0/b4/2f/e0b42ffab6bb255e977d5efe09340c78.jpg" alt="Club 3">
            </div>
            <div class="swiper-slide">
              <img src="https://i.pinimg.com/1200x/10/3a/4e/103a4edb2446ea7b477821566a7b9f7c.jpg" alt="Club 4">
            </div>
          </div>

          <!-- Controles (scoped dentro del slider) -->
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="como-funciona" class="how-it-works">
      <div class="container">
        <h2 class="section-title">¿Cómo Funciona OnClub?</h2>
        <div class="steps">
          <div class="step">
            <div class="step-number">1</div>
            <h3>Registra a tus Empleados</h3>
            <p>Añade fácilmente a todo tu personal con sus datos básicos y asigna códigos ID únicos.</p>
          </div>
          <div class="step">
            <div class="step-number">2</div>
            <h3>Marca Asistencia</h3>
            <p>Los empleados registran entrada/salida con su ID personal en segundos.</p>
          </div>
          <div class="step">
            <div class="step-number">3</div>
            <h3>Monitorea y Reporta</h3>
            <p>Supervisa la asistencia en tiempo real y genera reportes automáticos para RRHH.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section id="contacto" class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>¿Listo para Modernizar tu Club?</h2>
          <p>Únete a los clubes de golf que ya confían en OnClub para gestionar su personal de manera eficiente.</p>
          <a href="../views/Registro.php" class="cta-button pulse">Registrarse Ahora</a>
        </div>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>OnClub</h3>
          <p>Sistema de control de asistencia diseñado especialmente para fundaciones de golf modernos.</p>
        </div>
        <div class="footer-section">
          <h3>Contacto</h3>
          <p>Email: onclubv.x@gmail.com</p>
          <p>Teléfono: 3504610287
            <br>
                       321 4569942
          </p>
          <p>Soporte 24/7</p>
        </div>
        <div class="footer-section">
          <h3>Servicios</h3>
          <a href="#">Control de Asistencia</a><br>
          <a href="#">Gestión de Personal</a><br>
          <a href="#">Reportes Automáticos</a><br>
          <a href="#">Soporte Técnico</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 OnClub. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="../js/Home.js"></script>
</body>
</html>

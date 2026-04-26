<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Estudio Contable Contreras | Página en construcción</title>
  <meta name="description" content="Estudio Contable Contreras - Página web en construcción. Servicios contables, tributarios y empresariales.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #0f766e;
      --primary-dark: #115e59;
      --secondary: #14b8a6;
      --accent: #f59e0b;
      --dark: #0f172a;
      --gray: #64748b;
      --light: #f8fafc;
      --white: #ffffff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Inter", sans-serif;
      min-height: 100vh;
      color: var(--dark);
      background:
        radial-gradient(circle at top left, rgba(20, 184, 166, 0.22), transparent 35%),
        radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.18), transparent 35%),
        linear-gradient(135deg, #f8fafc 0%, #ecfeff 100%);
      overflow-x: hidden;
    }

    .page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 20px;
      position: relative;
    }

    .shape {
      position: absolute;
      border-radius: 999px;
      filter: blur(1px);
      opacity: 0.25;
      animation: float 8s ease-in-out infinite;
    }

    .shape.one {
      width: 220px;
      height: 220px;
      background: var(--secondary);
      top: 8%;
      left: 7%;
    }

    .shape.two {
      width: 160px;
      height: 160px;
      background: var(--accent);
      right: 9%;
      bottom: 12%;
      animation-delay: 1.5s;
    }

    .shape.three {
      width: 90px;
      height: 90px;
      background: var(--primary);
      right: 20%;
      top: 18%;
      animation-delay: 3s;
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-22px);
      }
    }

    .card {
      width: 100%;
      max-width: 960px;
      background: rgba(255, 255, 255, 0.78);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.75);
      border-radius: 32px;
      padding: 54px;
      box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12);
      position: relative;
      z-index: 2;
      overflow: hidden;
    }

    .card::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(120deg, rgba(20,184,166,0.08), rgba(245,158,11,0.08));
      pointer-events: none;
    }

    .content {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 48px;
      align-items: center;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 16px;
      border-radius: 999px;
      background: rgba(20, 184, 166, 0.12);
      color: var(--primary-dark);
      font-weight: 700;
      font-size: 14px;
      margin-bottom: 26px;
    }

    .badge span {
      width: 10px;
      height: 10px;
      background: var(--secondary);
      border-radius: 50%;
      box-shadow: 0 0 0 6px rgba(20, 184, 166, 0.18);
    }

    h1 {
      font-size: clamp(38px, 6vw, 68px);
      line-height: 0.95;
      letter-spacing: -3px;
      font-weight: 800;
      margin-bottom: 24px;
      color: var(--dark);
    }

    h1 strong {
      color: var(--primary);
      display: block;
    }

    .description {
      font-size: 18px;
      line-height: 1.75;
      color: var(--gray);
      max-width: 620px;
      margin-bottom: 32px;
    }

    .domain {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: var(--dark);
      color: var(--white);
      padding: 14px 20px;
      border-radius: 16px;
      font-weight: 700;
      box-shadow: 0 16px 35px rgba(15, 23, 42, 0.18);
    }

    .domain svg {
      width: 20px;
      height: 20px;
      color: var(--secondary);
    }

    .visual {
      background: linear-gradient(145deg, var(--primary), var(--secondary));
      border-radius: 30px;
      padding: 28px;
      min-height: 360px;
      color: var(--white);
      box-shadow: 0 24px 60px rgba(15, 118, 110, 0.28);
      position: relative;
      overflow: hidden;
    }

    .visual::after {
      content: "";
      position: absolute;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.13);
      right: -90px;
      bottom: -90px;
    }

    .mini-card {
      background: rgba(255, 255, 255, 0.18);
      border: 1px solid rgba(255, 255, 255, 0.28);
      border-radius: 22px;
      padding: 22px;
      margin-bottom: 18px;
      position: relative;
      z-index: 1;
    }

    .mini-card small {
      display: block;
      opacity: 0.85;
      margin-bottom: 8px;
      font-weight: 600;
    }

    .mini-card b {
      font-size: 28px;
      display: block;
    }

    .lines {
      margin-top: 28px;
      position: relative;
      z-index: 1;
    }

    .line {
      height: 12px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.35);
      margin-bottom: 14px;
    }

    .line:nth-child(1) {
      width: 92%;
    }

    .line:nth-child(2) {
      width: 70%;
    }

    .line:nth-child(3) {
      width: 82%;
    }

    .services {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 30px;
    }

    .service {
      background: rgba(15, 118, 110, 0.09);
      color: var(--primary-dark);
      border: 1px solid rgba(15, 118, 110, 0.14);
      padding: 10px 14px;
      border-radius: 999px;
      font-size: 14px;
      font-weight: 700;
    }

    footer {
      position: relative;
      z-index: 1;
      margin-top: 42px;
      color: var(--gray);
      font-size: 14px;
    }

    @media (max-width: 820px) {
      .card {
        padding: 34px 24px;
        border-radius: 26px;
      }

      .content {
        grid-template-columns: 1fr;
        gap: 34px;
      }

      .visual {
        min-height: 280px;
      }

      h1 {
        letter-spacing: -2px;
      }

      .domain {
        width: 100%;
        justify-content: center;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <main class="page">
    <div class="shape one"></div>
    <div class="shape two"></div>
    <div class="shape three"></div>

    <section class="card">
      <div class="content">
        <div>
          <div class="badge">
            <span></span>
            Próximamente en línea
          </div>

          <h1>
            Estudio Contable
            <strong>Contreras</strong>
          </h1>

          <p class="description">
            Estamos preparando una nueva experiencia digital para brindarte
            información clara sobre nuestros servicios contables, tributarios
            y empresariales.
          </p>

          <div class="domain">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20Z"></path>
              <path d="M2 12h20"></path>
              <path d="M12 2a15.3 15.3 0 0 1 4 10a15.3 15.3 0 0 1-4 10a15.3 15.3 0 0 1-4-10a15.3 15.3 0 0 1 4-10Z"></path>
            </svg>
            estudiocontablecontreras.net.pe
          </div>

          <div class="services">
            <div class="service">Contabilidad</div>
            <div class="service">Tributación</div>
            <div class="service">Asesoría empresarial</div>
            <div class="service">Declaraciones</div>
          </div>

          <footer>
            © 2026 Estudio Contable Contreras. Todos los derechos reservados.
          </footer>
        </div>

        <div class="visual">
          <div class="mini-card">
            <small>Página web</small>
            <b>En construcción</b>
          </div>

          <div class="mini-card">
            <small>Estado</small>
            <b>Muy pronto</b>
          </div>

          <div class="lines">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
          </div>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
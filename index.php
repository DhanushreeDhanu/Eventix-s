<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventix | Smart Event Coordination</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --dark: #050816;
            --dark2: #0b1024;
            --purple: #7c3aed;
            --pink: #ec4899;
            --cyan: #22d3ee;
            --green: #22c55e;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --glass: rgba(255,255,255,0.08);
            --border: rgba(255,255,255,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: var(--dark);
            color: var(--text);
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 10% 10%, rgba(124,58,237,.35), transparent 30%),
                radial-gradient(circle at 90% 20%, rgba(34,211,238,.25), transparent 28%),
                radial-gradient(circle at 50% 90%, rgba(236,72,153,.20), transparent 30%);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 55px 55px;
            z-index: -1;
        }

        .navbar {
            background: rgba(5,8,22,.78);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--purple), var(--cyan));
            display: inline-grid;
            place-items: center;
            margin-right: 10px;
            color: white;
        }

        .navbar-brand {
            font-size: 27px;
            font-weight: 900;
        }

        .nav-link {
            color: var(--muted) !important;
            font-weight: 600;
        }

        .nav-link:hover {
            color: white !important;
        }

        .admin-btn {
            border-radius: 999px;
            background: linear-gradient(135deg, #ef4444, #ec4899);
            color: white;
            padding: 9px 22px;
            font-weight: 800;
            text-decoration: none;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 95px;
        }

        .badge-soft {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 10px 18px;
            border-radius: 999px;
            background: var(--glass);
            border: 1px solid var(--border);
            color: #c4b5fd;
            margin-bottom: 24px;
        }

        .hero h1 {
            font-size: clamp(45px, 7vw, 86px);
            line-height: .95;
            font-weight: 950;
            letter-spacing: -3px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #a78bfa, #22d3ee, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            color: var(--muted);
            font-size: 19px;
            max-width: 700px;
            margin-top: 24px;
        }

        .btn-glow {
            border-radius: 999px;
            padding: 14px 30px;
            border: 0;
            color: white;
            font-weight: 900;
            text-decoration: none;
            display: inline-block;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            box-shadow: 0 15px 45px rgba(124,58,237,.35);
            transition: .3s;
        }

        .btn-glow:hover {
            transform: translateY(-4px);
            color: white;
            box-shadow: 0 18px 55px rgba(236,72,153,.35);
        }

        .btn-ghost {
            border-radius: 999px;
            padding: 14px 30px;
            border: 1px solid var(--border);
            color: white;
            font-weight: 900;
            text-decoration: none;
            display: inline-block;
            background: var(--glass);
            transition: .3s;
        }

        .btn-ghost:hover {
            transform: translateY(-4px);
            color: white;
            border-color: var(--cyan);
        }

        .dashboard-preview {
            background: rgba(255,255,255,.08);
            border: 1px solid var(--border);
            backdrop-filter: blur(20px);
            border-radius: 34px;
            padding: 26px;
            position: relative;
            box-shadow: 0 30px 90px rgba(0,0,0,.38);
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .preview-title {
            font-weight: 800;
        }

        .live-pill {
            background: rgba(34,197,94,.15);
            color: #86efac;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
        }

        .event-card-mini {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .event-meta {
            color: var(--muted);
            font-size: 14px;
        }

        .floating-chip {
            position: absolute;
            padding: 13px 17px;
            border-radius: 18px;
            background: rgba(255,255,255,.13);
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 50px rgba(0,0,0,.35);
            font-weight: 700;
        }

        .chip-1 {
            top: -20px;
            right: -12px;
        }

        .chip-2 {
            bottom: -20px;
            left: -12px;
        }

        .section {
            padding: 95px 0;
        }

        .section-title {
            font-size: clamp(34px, 4vw, 55px);
            font-weight: 950;
            letter-spacing: -1.5px;
        }

        .section-sub {
            color: var(--muted);
            max-width: 760px;
            margin: 16px auto 0;
            font-size: 17px;
        }

        .glass-card {
            background: var(--glass);
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            border-radius: 30px;
            padding: 34px;
            height: 100%;
            transition: .35s;
        }

        .glass-card:hover {
            transform: translateY(-10px);
            border-color: rgba(34,211,238,.45);
            box-shadow: 0 25px 60px rgba(34,211,238,.10);
        }

        .portal-icon {
            width: 78px;
            height: 78px;
            border-radius: 24px;
            display: grid;
            place-items: center;
            font-size: 34px;
            margin-bottom: 24px;
        }

        .icon-purple {
            background: rgba(124,58,237,.18);
            color: #c4b5fd;
        }

        .icon-green {
            background: rgba(34,197,94,.18);
            color: #86efac;
        }

        .icon-cyan {
            background: rgba(34,211,238,.16);
            color: #67e8f9;
        }

        .feature-line {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            margin-top: 18px;
            color: var(--muted);
        }

        .feature-line i {
            color: var(--cyan);
            margin-top: 4px;
        }

        .detail-box {
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            border-radius: 26px;
            padding: 28px;
            height: 100%;
        }

        .step-card {
            background: rgba(255,255,255,.07);
            border: 1px solid var(--border);
            border-radius: 26px;
            padding: 28px;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .step-card::before {
            content: "";
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(124,58,237,.18);
            right: -40px;
            top: -40px;
        }

        .step-number {
            width: 52px;
            height: 52px;
            border-radius: 17px;
            background: linear-gradient(135deg, var(--purple), var(--cyan));
            display: grid;
            place-items: center;
            font-weight: 950;
            margin-bottom: 18px;
        }

        .info-strip {
            background: linear-gradient(135deg, rgba(124,58,237,.22), rgba(34,211,238,.13));
            border: 1px solid var(--border);
            border-radius: 34px;
            padding: 38px;
        }

        .info-number {
            font-size: 42px;
            font-weight: 950;
            color: white;
        }

        .info-label {
            color: var(--muted);
        }

        .footer {
            border-top: 1px solid var(--border);
            background: rgba(5,8,22,.88);
            padding: 45px 0;
            color: var(--muted);
        }

        @media (max-width: 768px) {
            .hero h1 {
                letter-spacing: -1px;
            }

            .floating-chip {
                position: static;
                margin-top: 14px;
            }

            .hero {
                padding-top: 125px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top px-lg-5 px-3">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
        <span class="brand-mark"><i class="fa-solid fa-bolt"></i></span>
        Eventix
    </a>

    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
            <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
            <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
            <li class="nav-item"><a href="#portals" class="nav-link">Portals</a></li>
            <li class="nav-item"><a href="#works" class="nav-link">How It Works</a></li>
            <li class="nav-item ms-lg-2"><a href="admin/login.php" class="admin-btn">Admin Login</a></li>
        </ul>
    </div>
</nav>

<section class="hero" id="home">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-9" data-aos="fade-up">

                <div class="badge-soft mx-auto">
                    <i class="fa-solid fa-layer-group"></i>
                    Organizer + Volunteer Connected Platform
                </div>

                <h1 class="mt-4">
                    Build events with
                    <span class="gradient-text">people, flow & control.</span>
                </h1>

                <p class="mx-auto">
                    Eventix is a professional college event management system
                    where organizers create events, volunteers join them,
                    and admin monitors the complete process through one
                    smart interlinked platform.
                </p>

                <div class="mt-5 d-flex flex-wrap justify-content-center gap-3">
                    <a href="organizer/register.php" class="btn-glow">
                        Join as Organizer <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>

                    <a href="volunteer/register.php" class="btn-ghost">
                        Join as Volunteer
                    </a>
                </div>

                <!-- Optional Stats -->
                <!-- <div class="row mt-5 g-4 justify-content-center">
                    <div class="col-md-3 col-6">
                        <div class="detail-box text-center">
                            <h3 class="text-white">3</h3>
                            <p class="text-secondary mb-0">Main Panels</p>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="detail-box text-center">
                            <h3 class="text-white">100%</h3>
                            <p class="text-secondary mb-0">Interlinked System</p>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="detail-box text-center">
                            <h3 class="text-white">PHP</h3>
                            <p class="text-secondary mb-0">Backend Powered</p>
                        </div>
                    </div>
                </div> -->

            </div>
        </div>
    </div>
</section>

<section class="section" id="about">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <h2 class="section-title">About Eventix</h2>
            <p class="section-sub">
                Eventix is not just a normal registration website. It is designed as an
                interlinked event coordination system where each user role has a clear purpose.
                The organizer creates the event, the volunteer joins the event, and the admin
                supervises the complete system.
            </p>
        </div>

        <div class="row g-4 mt-5">
            <div class="col-md-4" data-aos="fade-up">
                <div class="glass-card">
                    <div class="portal-icon icon-purple">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <h4>Event Creation</h4>
                    <p class="text-secondary">
                        Organizers can add event name, type, date, time, venue,
                        description, and required volunteer count. This keeps every
                        event properly structured.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="120">
                <div class="glass-card">
                    <div class="portal-icon icon-green">
                        <i class="fa-solid fa-handshake-angle"></i>
                    </div>
                    <h4>Volunteer Joining</h4>
                    <p class="text-secondary">
                        Volunteers can view available events and join only the events
                        they are interested in. Their joined events are stored separately
                        for tracking.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="240">
                <div class="glass-card">
                    <div class="portal-icon icon-cyan">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4>Admin Control</h4>
                    <p class="text-secondary">
                        Admin can monitor organizers, volunteers, and events. This makes
                        the system controlled, clean, and suitable for college-level usage.
                    </p>
                </div>
            </div>
        </div>

        <div class="info-strip mt-5" data-aos="fade-up">
            <div class="row text-center g-4">
                <div class="col-md-3">
                    <div class="info-number">3</div>
                    <div class="info-label">Main Panels</div>
                </div>
                <div class="col-md-3">
                    <div class="info-number">PHP</div>
                    <div class="info-label">Backend</div>
                </div>
                <div class="col-md-3">
                    <div class="info-number">MySQL</div>
                    <div class="info-label">Database</div>
                </div>
                <div class="col-md-3">
                    <div class="info-number">100%</div>
                    <div class="info-label">Connected Flow</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="portals">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Eventix Portals</h2>
            <p class="section-sub">
                Each portal is separated clearly, but connected through the same database.
                This makes the project simple to understand and professional to present.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-6" data-aos="zoom-in">
                <div class="glass-card">
                    <div class="portal-icon icon-purple">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <h3>Organizer Portal</h3>
                    <p class="text-secondary">
                        The organizer portal is used by event creators. After registration
                        and login, the organizer can create events and manage event details.
                    </p>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>Create new events with full details.</span>
                    </div>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>View all events created by that organizer.</span>
                    </div>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>Check volunteers who joined each event.</span>
                    </div>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>Update event status like upcoming, completed, or cancelled.</span>
                    </div>

                    <div class="mt-4">
                        <a href="organizer/register.php" class="btn-glow">Join as Organizer</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="150">
                <div class="glass-card">
                    <div class="portal-icon icon-green">
                        <i class="fa-solid fa-people-carry-box"></i>
                    </div>

                    <h3>Volunteer Portal</h3>
                    <p class="text-secondary">
                        The volunteer portal is used by students or participants who want
                        to help in events. Volunteers can browse events and join them easily.
                    </p>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>View available events created by organizers.</span>
                    </div>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>Join selected events with one click.</span>
                    </div>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>View joined events separately in My Events.</span>
                    </div>

                    <div class="feature-line">
                        <i class="fa-solid fa-check"></i>
                        <span>Cancel participation if needed.</span>
                    </div>

                    <div class="mt-4">
                        <a href="volunteer/register.php" class="btn-glow">Join as Volunteer</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-6" data-aos="fade-right">
                <div class="detail-box">
                    <h4><i class="fa-solid fa-database text-info me-2"></i>Database Connection</h4>
                    <p class="text-secondary mt-3">
                        The system uses common tables like users, events, and volunteer_events.
                        This helps connect organizer-created events with volunteer participation.
                    </p>
                </div>
            </div>

            <div class="col-md-6" data-aos="fade-left">
                <div class="detail-box">
                    <h4><i class="fa-solid fa-lock text-warning me-2"></i>Role Based Access</h4>
                    <p class="text-secondary mt-3">
                        Organizer, volunteer, and admin have separate login paths and dashboards.
                        Each user sees only the features related to their role.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="works">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <h2 class="section-title">How Eventix Works</h2>
            <p class="section-sub">
                The working flow is simple, clear, and fully interlinked. This makes it
                easy to explain during project review or presentation.
            </p>
        </div>

        <div class="row g-4 mt-5">
            <div class="col-md-3" data-aos="fade-up">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Register</h5>
                    <p class="text-secondary">
                        Organizer or volunteer creates an account through their own
                        registration page.
                    </p>
                </div>
            </div>

            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Organizer Creates Event</h5>
                    <p class="text-secondary">
                        Organizer logs in and creates an event with date, time, venue,
                        description, and volunteer requirement.
                    </p>
                </div>
            </div>

            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Volunteer Joins Event</h5>
                    <p class="text-secondary">
                        The event appears in the volunteer panel. Volunteer selects the
                        event and joins it.
                    </p>
                </div>
            </div>

            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5>System Tracks Data</h5>
                    <p class="text-secondary">
                        Organizer can view joined volunteers and admin can monitor all
                        users and events.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="detail-box">
                    <h5>Organizer Side</h5>
                    <p class="text-secondary">
                        Dashboard, create event, my events, volunteers list, and logout.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="120">
                <div class="detail-box">
                    <h5>Volunteer Side</h5>
                    <p class="text-secondary">
                        Dashboard, available events, joined events, profile, and logout.
                    </p>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="240">
                <div class="detail-box">
                    <h5>Admin Side</h5>
                    <p class="text-secondary">
                        Manage organizers, volunteers, events, and overall system control.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h4 class="text-white">Eventix</h4>
                <p>
                    A smart college event management system built using PHP, MySQL,
                    Bootstrap, JavaScript, and AOS animations.
                </p>
            </div>

            <div class="col-md-3">
                <h6 class="text-white">Main Actions</h6>
                <p><a class="text-secondary text-decoration-none" href="organizer/register.php">Join as Organizer</a></p>
                <p><a class="text-secondary text-decoration-none" href="volunteer/register.php">Join as Volunteer</a></p>
            </div>

            <div class="col-md-3">
                <h6 class="text-white">System</h6>
                <p><a class="text-secondary text-decoration-none" href="admin/login.php">Admin Login</a></p>
                <p><a class="text-secondary text-decoration-none" href="#works">How It Works</a></p>
            </div>
        </div>

        <hr class="border-secondary">
        <p class="text-center mb-0">© 2026 Eventix. Smart Event Coordination System.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>

<script>
    AOS.init({
        duration: 900,
        once: true,
        offset: 80
    });
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vehicle Service Tracker</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* General Styles */
    :root {
      --primary: rgba(0, 114, 255, 0.85);
      --secondary: rgba(0, 198, 255, 0.85);
      --dark: #142850;
      --light: #f1f6f9;
      --accent: #ff6b6b;
      --success: #28a745;
      
      /* Light theme (default) */
      --bg-color: #f1f6f9;
      --text-color: #333;
      --card-bg: white;
      --nav-bg: white;
      --footer-bg: #142850;
      --footer-text: white;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --testimonial-bg: linear-gradient(to bottom, #f8fafc, #e3f2fd);
      --promise-bg: #f8fafc;
      --border-color: #ddd;
    }
    
    /* Dark theme - Auto detection */
    @media (prefers-color-scheme: dark) {
      :root:not([data-theme="light"]) {
        --bg-color: #0f172a;
        --text-color: #e2e8f0;
        --card-bg: #1e293b;
        --nav-bg: #1e293b;
        --footer-bg: #0f172a;
        --footer-text: #cbd5e1;
        --shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        --testimonial-bg: linear-gradient(to bottom, #1e293b, #334155);
        --promise-bg: #1e293b;
        --border-color: #334155;
      }
    }
    
    /* Manual dark theme override */
    [data-theme="dark"] {
      --bg-color: #0f172a;
      --text-color: #e2e8f0;
      --card-bg: #1e293b;
      --nav-bg: #1e293b;
      --footer-bg: #0f172a;
      --footer-text: #cbd5e1;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      --testimonial-bg: linear-gradient(to bottom, #1e293b, #334155);
      --promise-bg: #1e293b;
      --border-color: #334155;
    }
    
    /* Manual light theme override */
    [data-theme="light"] {
      --bg-color: #f1f6f9;
      --text-color: #333;
      --card-bg: white;
      --nav-bg: white;
      --footer-bg: #142850;
      --footer-text: white;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --testimonial-bg: linear-gradient(to bottom, #f8fafc, #e3f2fd);
      --promise-bg: #f8fafc;
      --border-color: #ddd;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      line-height: 1.6;
      overflow-x: hidden;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    h1, h2, h3, h4 {
      font-weight: 700;
      line-height: 1.2;
    }
    
    a {
      text-decoration: none;
      color: inherit;
    }
    
    .btn {
      display: inline-block;
      padding: 12px 28px;
      border-radius: 50px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      border: none;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0, 114, 255, 0.2);
    }
    
    .btn-secondary {
      background: white;
      color: var(--primary);
      border: 2px solid var(--primary);
    }
    
    .btn-secondary:hover {
      background: var(--primary);
      color: white;
    }
    
    /* Theme Toggle */
    .theme-toggle {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      padding: 8px 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.9rem;
      color: var(--text-color);
    }
    
    .theme-toggle:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    /* Navbar */
    .navbar {
      background-color: var(--nav-bg);
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
      padding: 15px 0;
      position: sticky;
      top: 0;
      z-index: 1000;
      width: 100%;
      transition: background-color 0.3s ease;
    }
    
    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .logo {
      display: flex;
      align-items: center;
      font-size: 24px;
      font-weight: 700;
      color: var(--primary);
      z-index: 1001;
    }
    
    .logo i {
      margin-right: 10px;
      font-size: 28px;
    }
    
    .nav-links {
      display: flex;
      list-style: none;
      align-items: center;
    }
    
    .nav-links li {
      margin-left: 20px;
    }
    
    .nav-links a {
      font-weight: 500;
      transition: color 0.3s;
    }
    
    .nav-links a:hover {
      color: var(--primary);
    }
    
    .staff-login {
      background: var(--light);
      color: var(--dark);
      padding: 8px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      border: 1px solid var(--border-color);
      transition: all 0.3s;
      position: relative;
      cursor: pointer;
    }
    
    .staff-login:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    .dropdown {
      position: relative;
      display: inline-block;
    }
    
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      background-color: var(--card-bg);
      min-width: 200px;
      box-shadow: var(--shadow);
      border-radius: 10px;
      z-index: 1002;
      margin-top: 10px;
      overflow: hidden;
      border: 1px solid var(--border-color);
    }
    
    .dropdown-content a {
      color: var(--text-color);
      padding: 12px 16px;
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: all 0.3s;
      border-bottom: 1px solid var(--border-color);
    }
    
    .dropdown-content a:last-child {
      border-bottom: none;
    }
    
    .dropdown-content a i {
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    .dropdown-content a:hover {
      background-color: var(--primary);
      color: white;
    }
    
    /* Show dropdown only when clicked */
    .dropdown.active .dropdown-content {
      display: block;
    }
    
    .hamburger {
      display: none;
      cursor: pointer;
      font-size: 24px;
      z-index: 1001;
    }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(to right, var(--secondary), var(--primary)), url('https://images.unsplash.com/photo-1493238792000-8113da705763?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80') no-repeat center center/cover;
      color: white;
      padding: 100px 0;
      text-align: center;
    }
    
    .hero-content {
      max-width: 800px;
      margin: 0 auto;
    }
    
    .hero h2 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    
    .hero p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }
    
    .hero-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
    }
    
    /* Services Section */
    .services-section {
      padding: 80px 0;
      background-color: var(--card-bg);
      transition: background-color 0.3s ease;
    }
    
    .section-title {
      text-align: center;
      margin-bottom: 50px;
      color: var(--text-color);
    }
    
    .section-title h3 {
      font-size: 2rem;
      position: relative;
      display: inline-block;
      padding-bottom: 10px;
    }
    
    .section-title h3::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 70px;
      height: 3px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
    }
    
    .services {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }
    
    .service-card {
      background: var(--card-bg);
      padding: 30px;
      border-radius: 15px;
      box-shadow: var(--shadow);
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.3s ease;
    }
    
    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }
    
    .service-card:hover::before {
      transform: scaleX(1);
    }
    
    .service-icon {
      font-size: 40px;
      margin-bottom: 20px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    .service-card h4 {
      font-size: 1.3rem;
      margin-bottom: 15px;
      color: var(--text-color);
    }
    
    .service-card p {
      color: var(--text-color);
      opacity: 0.8;
    }
    
    /* Management Testimonials */
    .testimonials-section {
      padding: 80px 0;
      background: var(--testimonial-bg);
      transition: background 0.3s ease;
    }
    
    .testimonials {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }
    
    .testimonial-card {
      background: var(--card-bg);
      padding: 30px;
      border-radius: 15px;
      box-shadow: var(--shadow);
      text-align: center;
      transition: transform 0.3s ease;
    }
    
    .testimonial-card:hover {
      transform: translateY(-5px);
    }
    
    .testimonial-img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin: 0 auto 20px;
      border: 4px solid var(--primary);
    }
    
    .testimonial-quote {
      font-style: italic;
      color: var(--text-color);
      opacity: 0.8;
      margin-bottom: 20px;
      position: relative;
    }
    
    .testimonial-quote::before,
    .testimonial-quote::after {
      content: '"';
      font-size: 40px;
      color: var(--primary);
      opacity: 0.2;
      position: absolute;
    }
    
    .testimonial-quote::before {
      top: -20px;
      left: -10px;
    }
    
    .testimonial-quote::after {
      bottom: -40px;
      right: -10px;
    }
    
    .testimonial-author {
      font-weight: 600;
      color: var(--text-color);
    }
    
    .testimonial-role {
      color: var(--primary);
      font-size: 0.9rem;
    }
    
    /* Service Promises */
    .promises-section {
      padding: 80px 0;
      background-color: var(--card-bg);
      transition: background-color 0.3s ease;
    }
    
    .promises {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }
    
    .promise-card {
      text-align: center;
      padding: 30px 20px;
      border-radius: 15px;
      background: var(--promise-bg);
      transition: all 0.3s ease;
    }
    
    .promise-card:hover {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      transform: translateY(-5px);
    }
    
    .promise-card:hover .promise-icon {
      color: white;
    }
    
    .promise-icon {
      font-size: 40px;
      margin-bottom: 20px;
      color: var(--primary);
      transition: all 0.3s ease;
    }
    
    .promise-card h4 {
      margin-bottom: 15px;
      font-size: 1.2rem;
    }
    
    .promise-card p {
      font-size: 0.9rem;
    }
    
    /* Footer */
    .footer {
      background: var(--footer-bg);
      color: var(--footer-text);
      padding: 60px 0 20px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }
    
    .footer-column h4 {
      font-size: 1.2rem;
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }
    
    .footer-column h4::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 2px;
      background: var(--secondary);
    }
    
    .footer-links {
      list-style: none;
    }
    
    .footer-links li {
      margin-bottom: 10px;
    }
    
    .footer-links a {
      color: var(--footer-text);
      opacity: 0.8;
      transition: color 0.3s;
    }
    
    .footer-links a:hover {
      color: var(--secondary);
      opacity: 1;
    }
    
    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }
    
    .social-links a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      transition: all 0.3s;
    }
    
    .social-links a:hover {
      background: var(--primary);
      transform: translateY(-3px);
    }
    
    .footer-bottom {
      text-align: center;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.9rem;
      color: var(--footer-text);
      opacity: 0.7;
    }
    
    /* Responsive Design - Fixed Mobile Navigation */
    @media (max-width: 768px) {
      .nav-links {
        position: fixed;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100vh;
        background: var(--nav-bg);
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.5s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 999;
        padding: 20px;
      }
      
      .nav-links.active {
        left: 0;
      }
      
      .nav-links li {
        margin: 15px 0;
        width: 100%;
        text-align: center;
      }
      
      .dropdown {
        width: 100%;
      }
      
      .staff-login {
        display: block;
        width: 100%;
        padding: 12px 20px;
        text-align: center;
      }
      
      .dropdown-content {
        position: static;
        box-shadow: none;
        background-color: transparent;
        display: none;
        margin-top: 10px;
        width: 100%;
        min-width: unset;
        border: none;
      }
      
      .dropdown.active .dropdown-content {
        display: block;
      }
      
      .dropdown-content a {
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        justify-content: center;
        border-radius: 0;
      }
      
      .hamburger {
        display: block;
        position: relative;
        z-index: 1001;
      }
      
      .hero h2 {
        font-size: 2rem;
      }
      
      .hero-buttons {
        flex-direction: column;
        align-items: center;
      }
      
      .hero-buttons .btn {
        width: 80%;
        margin-bottom: 10px;
      }
      
      .theme-toggle {
        margin-top: 10px;
        justify-content: center;
        width: 100%;
      }
    }
    
    @media (max-width: 576px) {
      .hero {
        padding: 80px 0;
      }
      
      .hero h2 {
        font-size: 1.8rem;
      }
      
      .section-title h3 {
        font-size: 1.6rem;
      }
      
      .service-card, .testimonial-card {
        padding: 20px;
      }
      
      .nav-container {
        padding: 0 15px;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="container nav-container">
      <a href="#" class="logo">
        <i class="fas fa-car"></i>
        AutoCare
      </a>
      
      <ul class="nav-links">
        <li><a href="#services">Services</a></li>
        <li><a href="#testimonials">Leadership</a></li>
        <li><a href="#promises">Our Promise</a></li>
        <li><a href="#contact">Contact</a></li>
        
        <li class="dropdown">
          <a class="staff-login dropdown-toggle"><i class="fas fa-lock"></i> Staff Login <i class="fas fa-chevron-down" style="margin-left: 5px; font-size: 0.8rem;"></i></a>
          <div class="dropdown-content">
            <a href="adminlogin.php"><i class="fas fa-user-shield"></i> Admin Login</a>
            <a href="managerlogin.php"><i class="fas fa-user-tie"></i> Manager Login</a>
            <a href="supervisorlogin.php"><i class="fas fa-user-cog"></i> Supervisor Login</a>
            <a href="mechaniclogin.php"><i class="fas fa-tools"></i> Mechanic Login</a>
          </div>
        </li>
        <li>
          <button class="theme-toggle" id="themeToggle">
            <i class="fas fa-moon"></i>
            <span>Theme</span>
          </button>
        </li>
      </ul>
      
      <div class="hamburger">
        <i class="fas fa-bars"></i>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="hero">
    <div class="container hero-content">
      <h2>Premium Vehicle Service & Maintenance</h2>
      <p>Experience top-quality service with our expert technicians and state-of-the-art equipment</p>
      <div class="hero-buttons">
        <a href="customerlogin.php" class="btn btn-primary">Book Service Now</a>
      </div>
    </div>
  </header>

  <!-- Services Section -->
  <section id="services" class="services-section">
    <div class="container">
      <div class="section-title">
        <h3>Our Services</h3>
      </div>
      
      <div class="services">
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-tools"></i>
          </div>
          <h4>General Maintenance</h4>
          <p>Regular checkups, oil changes, filter replacements and comprehensive inspections</p>
        </div>
        
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-cog"></i>
          </div>
          <h4>Engine Repair</h4>
          <p>Expert diagnostics and repair for all engine types and issues</p>
        </div>
        
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-paint-roller"></i>
          </div>
          <h4>Body Work & Painting</h4>
          <p>Dent removal, scratch repair, and professional painting services</p>
        </div>
        
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-bolt"></i>
          </div>
          <h4>Electrical Systems</h4>
          <p>Battery, alternator, starter and electrical system diagnostics and repair</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Management Testimonials -->
  <section id="testimonials" class="testimonials-section">
    <div class="container">
      <div class="section-title">
        <h3>Leadership Commitment</h3>
      </div>
      
      <div class="testimonials">
        <div class="testimonial-card">
          <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Director" class="testimonial-img">
          <p class="testimonial-quote">"At AutoCare, we're committed to setting the highest standards in vehicle service. Our team undergoes continuous training to stay ahead of automotive technology."</p>
          <h4 class="testimonial-author">James Wilson</h4>
          <p class="testimonial-role">Director of Operations</p>
        </div>
        
        <div class="testimonial-card">
          <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Service Manager" class="testimonial-img">
          <p class="testimonial-quote">"We've invested in the latest diagnostic equipment and tools because we believe our customers deserve nothing less than precision service for their vehicles."</p>
          <h4 class="testimonial-author">Sarah Johnson</h4>
          <p class="testimonial-role">Service Manager</p>
        </div>
        
        <div class="testimonial-card">
          <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="Technical Director" class="testimonial-img">
          <p class="testimonial-quote">"Our technical team includes certified specialists across all major vehicle systems. We're passionate about solving complex automotive challenges."</p>
          <h4 class="testimonial-author">Michael Chen</h4>
          <p class="testimonial-role">Technical Director</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Service Promises -->
  <section id="promises" class="promises-section">
    <div class="container">
      <div class="section-title">
        <h3>Our Service Promise</h3>
      </div>
      
      <div class="promises">
        <div class="promise-card">
          <div class="promise-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h4>Quality Guarantee</h4>
          <p>All our services come with a 12-month warranty for your peace of mind</p>
        </div>
        
        <div class="promise-card">
          <div class="promise-icon">
            <i class="fas fa-clock"></i>
          </div>
          <h4>On-Time Service</h4>
          <p>We value your time and complete most services within the promised timeframe</p>
        </div>
        
        <div class="promise-card">
          <div class="promise-icon">
            <i class="fas fa-tag"></i>
          </div>
          <h4>Transparent Pricing</h4>
          <p>No hidden charges - we provide detailed quotes before starting any work</p>
        </div>
        
        <div class="promise-card">
          <div class="promise-icon">
            <i class="fas fa-headset"></i>
          </div>
          <h4>24/7 Support</h4>
          <p>Our customer service team is always available to address your concerns</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact" class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-column">
          <h4>AutoCare</h4>
          <p>Providing top-quality vehicle service and maintenance since 2005. We pride ourselves on exceptional customer service and technical expertise.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        
        <div class="footer-column">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="#services">Our Services</a></li>
            <li><a href="#testimonials">Leadership</a></li>
            <li><a href="#promises">Our Promise</a></li>
            <li><a href="#">Service Packages</a></li>
            <li><a href="#">Careers</a></li>
          </ul>
        </div>
        
        <div class="footer-column">
          <h4>Contact Us</h4>
          <ul class="footer-links">
            <li><i class="fas fa-map-marker-alt"></i> 123 Auto Street, Car City</li>
            <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
            <li><i class="fas fa-envelope"></i> support@autocare.com</li>
            <li><i class="fas fa-clock"></i> Mon-Fri: 8am - 6pm</li>
            <li><i class="fas fa-clock"></i> Sat: 9am - 4pm</li>
          </ul>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2023 AutoCare. All rights reserved.</p>
      </div>
    </div>
  </footer>

<script>
  // Theme Management
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = themeToggle.querySelector('i');
  const themeText = themeToggle.querySelector('span');

  // Get preferred theme
  function getPreferredTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) return savedTheme;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  // Apply theme
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    if (theme === 'dark') {
      themeIcon.classList.replace('fa-moon', 'fa-sun');
      themeText.textContent = 'Light Mode';
    } else {
      themeIcon.classList.replace('fa-sun', 'fa-moon');
      themeText.textContent = 'Dark Mode';
    }
  }

  // Initialize
  let currentTheme = getPreferredTheme();
  applyTheme(currentTheme);

  // Toggle theme on click
  themeToggle.addEventListener('click', () => {
    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', currentTheme);
    applyTheme(currentTheme);
  });

  // Mobile Menu Toggle
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  const dropdowns = document.querySelectorAll('.dropdown');

  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    const icon = hamburger.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
  });

  // Dropdown Toggle
  dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.dropdown-toggle');
    toggle.addEventListener('click', () => {
      dropdown.classList.toggle('active');
    });
  });
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicare Plus</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }


        body {
            background: linear-gradient(135deg, #eb75e7e3, #cd32d871, #ffffffff);
            overflow-x: hidden;
        }

        /* HEADER */
        header {
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            /* scrolls away normally */
            background: transparent;
        }


        .logo {
            font-size: 26px;
            font-weight: 600;
            color: #ffffffff;
        }

        /* NAV BAR CENTER */
        nav {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 35px;
        }

        nav ul li a {
            text-decoration: none;
            color: #035f5b;
            font-weight: 500;
            font-size: 16px;
            transition: 0.3s;
        }

        nav ul li a:hover {
            color: #013230;
        }

        /* LOGIN BUTTON */
        .login-btn {
            padding: 10px 25px;
            background: white;
            color: #035f5b;
            border-radius: 25px;
            border: none;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .login-btn:hover {
            background: #035f5b;
            color: white;
        }

        /* HERO SECTION */
        .hero {
            margin-top: 120px;
            display: flex;
            justify-content: space-between;
            padding: 50px 7%;
            align-items: center;

        }

        /* Left Side */
        .hero-left {
            max-width: 45%;
        }

        .hero-left h1 {
            font-size: 42px;
            color: #003f3c;
            margin-bottom: 15px;
        }

        .hero-left p {
            font-size: 18px;
            color: #003f3c;
            margin-bottom: 25px;
        }

        .book-btn {
            padding: 14px 35px;
            background: #006f6a;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .book-btn:hover {
            background: #024542;
        }

        /* Right Side Image */
        .doctor-img {
            width: 750px;
            filter: drop-shadow(0px 10px 20px rgba(0, 0, 0, 0.15));
            margin-top: -70px;
            /* move image UP */
        }

        /* ABOUT SECTION */
        .about-section {
            padding: 80px 7%;
            background-color: white;
        }

        .about-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 50px;
        }

        /* LEFT IMAGE */
        .about-image img {
            width: 500px;
            border-radius: 30px;
            object-fit: cover;
            
        }



        /* RIGHT TEXT */
        .about-content h2 {
            font-size: 36px;
            color: #003f3c;
            margin-bottom: 20px;
        }

        .about-content h2 span {
            color: #7b2cbf;
            /* pastel purple */
        }

        .about-content p {
            color: #003f3c;
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
        }

        .about-btn {
            padding: 10px 25px;
            background: #8e108eff;
            color: #ffffffff;
            border-radius: 20px;
            border: none;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            position: relative;
            top: 20px;
            /* moves down */


        }



        .about-btn:hover {
            background: #d53de67d;
            color: white;
        }

        /* STAT BOXES */
        .stats-boxes {
            display: flex;
            gap: 25px;
            margin-top: 30px;
        }

        .stat-box {
            background: white;
            padding: 35px 25px;
            width: 180px;
            height: 180px;
            text-align: center;
            border-radius: 25px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform .4s ease, opacity .4s ease;
        }

        .stat-box h3 {
            font-size: 20px;
            color: #007f7a;
            margin-bottom: 10px;
        }

        .stat-box p {
            font-size: 14px;
        }

        /* ==== FADE ANIMATION BASE STYLES ==== */
        .fade-left,
        .fade-right,
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease-out;
        }

        /* LEFT */
        .fade-left {
            transform: translateX(-50px);
        }

        /* RIGHT */
        .fade-right {
            transform: translateX(50px);
        }

        /* UP */
        .fade-up {
            transform: translateY(50px);
        }

        /* ==== REVEALED STATE ==== */
        .revealed {
            opacity: 1;
            transform: translateX(0) translateY(0);
        }

        .doctor {
            background-color: #ffffffff;
        }

        section.doctors-section {
            padding: 50px 20px;
            max-width: 1300px;
            margin: auto;
            align-items: center;
            background-color: #ffffffff;
        }

        .cards-row {
            display: grid;
            grid-template-columns: repeat(4, 6cm);
            grid-auto-rows: 7cm;
            gap: 20px;
            margin-bottom: 50px;
            opacity: 0;
            /* start hidden */
            transform: translateX(-50px);
            /* left by default */
            transition: all 0.8s ease;
            justify-content: center;
        }

        .cards-row.fade-right {
            transform: translateX(50px);
            /* move from right */
        }

        .cards-row.show {
            opacity: 1;
            transform: translateX(0);
        }

        .card {
            background-color: #db5fbe53;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .card:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 5px 0;
            font-size: 1.1em;
        }

        .card p {
            margin: 3px 0;
            font-size: 0.9em;
            color: #555;
        }

        .stars {
            color: gold;
            font-size: 1em;
            margin-top: 5px;
        }

        .meet {
            width: 100%;
            /* make the section full width */
            display: flex;
            justify-content: center;
            /* centers items horizontally */
        }

        .meet h2 {
            text-align: center;
            /* centers the text inside */
            width: 100%;
            /* allow full centering */
            font-size: 36px;
            color: #003f3c;
            margin-bottom: 20px;
        }

        .meet h2 span {
            color: #7b2cbf;
        }

        .feedback-section {
            display: flex;
            flex-direction: column;
            /* stack items vertically */
            align-items: center;
            /* center everything horizontally */
            padding: 60px 8%;
            gap: 30px;
            /* space between title and cards */
        }




        .feedback-left h2 {
            font-size: 35px;
            color: #003f3c;
        }

        .feedback-left h2 span {
            color: #7b2cbf;
        }

        .feedback-left p {
            color: #555;
            margin-top: 10px;
        }

        .feedback-right {
            display: flex;
            flex-direction: row;
            /* cards stay side-by-side */
            justify-content: center;
            /* center them under the title */
            gap: 20px;
        }


        /* Feedback Cards */
        .feedback-card {
            width: 9cm;
            height: 6cm;
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .feedback-card.show {
            opacity: 1;
            transform: translateY(0);
        }

        .feedback-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .feedback-stars {
            color: gold;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .feedback-text {
            color: #555;
            line-height: 1.4rem;
        }

        /* HERO SECTION */
        .book-section {
            width: 100%;
            height: 70vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 7%;
            background: linear-gradient(135deg, #e9d8ff, #b178f3ff);
            position: relative;
            overflow: hidden;
        }

        /* LEFT TEXT */
        .book-content {
            width: 50%;
        }

        .book-content h1 {
            font-size: 48px;
            font-weight: 700;
            color: #3a2b47;
        }

        .book-content h2 {
            font-size: 40px;
            font-weight: 600;
            margin-top: 5px;
            color: #3a2b47;
        }

        .book-content p {
            margin-top: 15px;
            font-size: 18px;
            color: #4c3f58;
        }

        .book-btn {
            margin-top: 25px;
            display: inline-block;
            padding: 14px 28px;
            background: #7a4bd1;
            color: white;
            font-size: 18px;
            border-radius: 30px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .book-btn:hover {
            background: #653baf;
        }

        /* RIGHT IMAGE */
        .book-image {
            width: 40%;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            height: 50%;
            position: relative;
        }

        .book-image img {
            height: 150%;
            position: absolute;
            bottom: -50px;
            right: 0;
            object-fit: contain;
        }

        /* BUBBLES */
        .bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(5px);
        }

        /* Different sizes */
        .bubble1 {
            width: 180px;
            height: 180px;
            top: 10%;
            left: 65%;
        }

        .bubble2 {
            width: 130px;
            height: 130px;
            bottom: 15%;
            left: 50%;
        }

        .bubble3 {
            width: 90px;
            height: 90px;
            bottom: 30%;
            right: 10%;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .book-section {
                flex-direction: column-reverse;
                height: auto;
                padding-top: 50px;
            }

            .book-image img {
                position: relative;
                width: 60%;
                height: auto;
            }

            .book-content {
                width: 100%;
                text-align: center;
                padding-bottom: 40px;
            }
        }

        /* FOOTER BASE */
        .footer {
            background: linear-gradient(135deg, #d9c3ff, #c7a7ff);
            padding: 50px 80px;
            color: #2f2547;
            font-family: 'Poppins', sans-serif;
        }

        /* GRID LAYOUT */
        .footer-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        /* LEFT SECTION */
        .footer-left {
            width: 30%;
        }

        .footer-brand {
            font-size: 32px;
            font-weight: 700;
            color: #4a2e7f;
        }

        .footer-verse {
            margin-top: 10px;
            font-size: 16px;
            color: #4d3c66;
        }

        /* CENTER LINKS */
        .footer-links h3,
        .footer-contact h3 {
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #4a2e7f;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links ul li {
            margin-bottom: 8px;
        }

        .footer-links ul li a {
            text-decoration: none;
            color: #3a2d54;
            font-size: 16px;
            transition: 0.3s ease;
        }

        .footer-links ul li a:hover {
            color: #6e3dd4;
        }

        /* RIGHT SECTION */
        .footer-contact p {
            margin-bottom: 8px;
            font-size: 16px;
            color: #3a2d54;
        }

        /* BOTTOM BAR */
        .footer-bottom {
            margin-top: 40px;
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 14px;
            color: #3a2d54;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .footer-container {
                flex-direction: column;
                gap: 40px;
            }

            .footer-left,
            .footer-links,
            .footer-contact {
                width: 100%;
                text-align: center;
            }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header>
        <div class="logo">Medicare Plus</div>

        <nav>
            <ul>
                <li><a href="medicare_home.php">Home</a></li>
                <li><a href="aboutus.html">About Us</a></li>
                <li><a href="doctors.html">Our Doctors</a></li>
                <li><a href="contactus.html">Contact Us</a></li>


            </ul>
        </nav>

        <a href="login.php" class="login-btn">Login</a>
    </header>


    <!-- HERO SECTION -->

    <section class="hero">
        <div class="hero-left fade-left">
            <h1>The Best Medical and Treatment Center for You</h1>
            <p>Your health is our top priority. Get connected with our professional doctors.</p>


            <a href="login.php" target=""_blank"" class="book-btn" >Book Appointment</a>
        </div>

        <div class="hero-right fade-right">
            <img src="doc.png" alt="Doctor" class="doctor-img">
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about-section">
        <div class="about-container">

            <!-- IMAGE LEFT -->
            <div class="about-image fade-left">
                <img src="book.jpg" alt="hospital">
            </div>

            <!-- TEXT RIGHT -->
            <div class="about-content fade-right">
                <h2>About Our <span>MediCare Plus</span></h2>

                <p>
                    “MediCare Plus” is one of the largest private medical centers. For 30 years,
                    we have helped people overcome their health obstacles.
                </p>

                <p>
                    We take a comprehensive approach to treatment. You can consult
                    specialized doctors all in one place.
                </p>
                <p>
                    From preventive care to advanced treatments, 
                    our state-of-the-art facilities ensure that 
                    every patient receives personalized attention 
                    and the highest standard of medical care.
                </p>
                <p>
                    Trust, compassion, and excellence guide everything 
                    we do, making Medicare Plus a place where your wellbeing 
                    comes first.
                </p>
                <a href="aboutus.html" class="about-btn">Know More</a>
            </div>
    </section>


    <section class="doctor">
        <section class="meet fade-up">
            <h2>Meet Our <span>Specialists</span></h2>
        </section>
        <section class="doctors-section">
            <div class="cards-row fade-right" id="row1">
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Dr. John Doe">
                    <h3>Dr. John Doe</h3>
                    <p>Cardiologist</p>
                    <p>10 years experience</p>
                    <div class="stars">⭐⭐⭐⭐☆</div>
                </div>
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Dr. Jane Smith">
                    <h3>Dr. Jane Smith</h3>
                    <p>Neurologist</p>
                    <p>8 years experience</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                </div>
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/men/54.jpg" alt="Dr. Mike Brown">
                    <h3>Dr. Mike Brown</h3>
                    <p>Orthopedic</p>
                    <p>12 years experience</p>
                    <div class="stars">⭐⭐⭐⭐☆</div>
                </div>
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/women/23.jpg" alt="Dr. Alice Green">
                    <h3>Dr. Alice Green</h3>
                    <p>Pediatrician</p>
                    <p>9 years experience</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                </div>
            </div>

            <div class="cards-row fade-left" id="row2">
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/men/18.jpg" alt="Dr. Paul White">
                    <h3>Dr. Paul White</h3>
                    <p>Dermatologist</p>
                    <p>7 years experience</p>
                    <div class="stars">⭐⭐⭐⭐☆</div>
                </div>
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/women/56.jpg" alt="Dr. Laura Black">
                    <h3>Dr. Laura Black</h3>
                    <p>Gynecologist</p>
                    <p>11 years experience</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                </div>
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="Dr. Steve Grey">
                    <h3>Dr. Steve Grey</h3>
                    <p>Surgeon</p>
                    <p>15 years experience</p>
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                </div>
                <div class="card">
                    <img src="https://randomuser.me/api/portraits/women/35.jpg" alt="Dr. Nancy Blue">
                    <h3>Dr. Nancy Blue</h3>
                    <p>Psychiatrist</p>
                    <p>6 years experience</p>
                    <div class="stars">⭐⭐⭐⭐☆</div>
                </div>
            </div>
        </section>
        <section class="feedback-section">
            <div class="feedback-left fade-up">
                <h2>What Our Patients <span>Say About Us</span></h2>
                <p>Your health is our priority — hear from those we've cared for.</p>
            </div>

            <div class="feedback-right">
                <div id="feedback-card-1" class="feedback-card"></div>
                <div id="feedback-card-2" class="feedback-card"></div>
            </div>
        </section>

        <section class="book-section">
            <div class="book-image fade-right">
                <img src="BA.png" alt="Doctor">
            </div>

            <!-- Bubble Decoration -->
            <div class="bubble bubble1"></div>
            <div class="bubble bubble2"></div>
            <div class="bubble bubble3"></div>

            <div class="book-content fade-left">
                <h1>Make Your Health a Priority</h1>
                <h2>Don't Overlook It</h2>
                <p>Your well-being matters. Book an appointment with our specialists today.</p>
                <a href="login.php" class="book-btn fade-left">Book Appointment</a>
            </div>
        </section>

        <footer class="footer">
            <div class="footer-container">

                <!-- LEFT: Brand + Verse -->
                <div class="footer-left">
                    <h2 class="footer-brand">Medicare Plus</h2>
                    <p class="footer-verse">
                        "Your health is our mission — your trust is our strength."
                    </p>
                </div>

                <!-- CENTER: Navigation -->
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="medicare_home.php">Home</a></li>
                        <li><a href="aboutus.html">About Us</a></li>
                        <li><a href="doctors.html">Our Doctors</a></li>
                        <li><a href="contactus.html">Contact Us</a></li>
                    </ul>
                </div>

                <!-- RIGHT: Contact + Info -->
                <div class="footer-contact">
                    <h3>Contact Info</h3>
                    <p><strong>Hours:</strong> Mon – Sat: 8.00am – 10.00pm</p>
                    <p><strong>Hotline:</strong> +94 77 123 4567</p>
                    <p><strong>Address:</strong> Colombo, Sri Lanka</p>
                    <p><strong>Email:</strong> support@medicareplus.com</p>
                </div>

            </div>

            <div class="footer-bottom">
                © 2025 Medicare Plus — All Rights Reserved
            </div>
        </footer>




        <script>
            // Feedback data
            const feedbacks = [
                {
                    name: "Evon Johnson",
                    stars: "⭐⭐⭐⭐⭐",
                    text: "The doctors were very understanding and supportive. Highly recommended!"
                },
                {
                    name: "Andrew Lancelot",
                    stars: "⭐⭐⭐⭐☆",
                    text: "Excellent service! The staff took really good care of me."
                },
                {
                    name: "Emma Fin",
                    stars: "⭐⭐⭐⭐⭐",
                    text: "Fast, friendly and very professional. Will definitely visit again!"
                },
                {
                    name: "Nerry Lee",
                    stars: "⭐⭐⭐☆☆",
                    text: "Good experience overall, but waiting time could improve."
                }
            ];

            // Assign card elements
            let card1 = document.getElementById("feedback-card-1");
            let card2 = document.getElementById("feedback-card-2");

            let index1 = 0;
            let index2 = 1;

            // Load feedback into card
            function loadCard(card, data) {
                card.innerHTML = `
        <div class="feedback-name">${data.name}</div>
        <div class="feedback-stars">${data.stars}</div>
        <div class="feedback-text">${data.text}</div>
    `;
            }

            // Initial load
            loadCard(card1, feedbacks[index1]);
            loadCard(card2, feedbacks[index2]);
            card1.classList.add("show");
            card2.classList.add("show");

            // Auto Change Function
            function changeCards() {
                card1.classList.remove("show");
                card2.classList.remove("show");

                setTimeout(() => {
                    index1 = (index1 + 1) % feedbacks.length;
                    index2 = (index2 + 1) % feedbacks.length;

                    loadCard(card1, feedbacks[index1]);
                    loadCard(card2, feedbacks[index2]);

                    card1.classList.add("show");
                    card2.classList.add("show");
                }, 600);

            }

            // Change every 4 seconds
            setInterval(changeCards, 4000);

        </script>

        <script>
            const elements = document.querySelectorAll(".fade-left, .fade-right, .fade-up");

            function revealOnScroll() {
                elements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.top < window.innerHeight - 100 && rect.bottom > 0) {
                        el.classList.add("revealed");
                    } else {
                        el.classList.remove("revealed"); // allows animation every scroll
                    }
                });
            }

            window.addEventListener("scroll", revealOnScroll);
            revealOnScroll();
        </script>

        <script>
            const rows = document.querySelectorAll('.cards-row');

            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                    }
                });
            }, {
                threshold: 0.3
            });

            rows.forEach(row => observer.observe(row));
        </script>


</body>

</html>
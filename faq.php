<?php
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - HabitTracker AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .faq-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 60px 0;
        }
        .faq-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .faq-category {
            margin-bottom: 40px;
        }
        .faq-category-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 4px solid #667eea;
        }
        .accordion-button {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .accordion-button:not(.collapsed) {
            background-color: #667eea;
            color: white;
        }
        .accordion-body {
            font-size: 1rem;
            line-height: 1.8;
        }
        .search-box {
            max-width: 600px;
            margin: 0 auto 40px;
        }
        .badge-new {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bullseye"></i> HabitTracker AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') ? 'admin/admin_dashboard.php' : 'user/dashboard.php'; ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-light ms-2" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light ms-2" href="auth/register.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-header">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-question-circle text-primary"></i>
                    Frequently Asked Questions
                </h1>
                <p class="lead text-muted">Find answers to common questions about HabitTracker AI</p>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <div class="input-group input-group-lg shadow">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control" id="faqSearch" 
                           placeholder="Search for answers...">
                </div>
            </div>

            <!-- Getting Started -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-rocket"></i> Getting Started
                </h3>
                <div class="accordion" id="gettingStarted">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#q1">
                                What is HabitTracker AI and how does it work?
                            </button>
                        </h2>
                        <div id="q1" class="accordion-collapse collapse show" data-bs-parent="#gettingStarted">
                            <div class="accordion-body">
                                HabitTracker AI is your personal companion for building positive habits. It's not just a simple tracker – it's an intelligent system that learns from your behavior and helps you succeed. You create habits you want to build (like exercising, reading, or meditating), mark them as complete each day, and the AI analyzes your patterns to give you personalized insights. For example, if you tend to skip workouts on Mondays, it'll notice that and suggest solutions!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q2">
                                How do I create an account?
                            </button>
                        </h2>
                        <div id="q2" class="accordion-collapse collapse" data-bs-parent="#gettingStarted">
                            <div class="accordion-body">
                                It's super easy! Click the "Sign Up" button in the top-right corner. Enter your name, email, and create a password (at least 6 characters). Click "Create Account" and you're ready to go! You'll be logged in automatically and can start adding your first habit right away.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q3">
                                Is HabitTracker AI free to use?
                            </button>
                        </h2>
                        <div id="q3" class="accordion-collapse collapse" data-bs-parent="#gettingStarted">
                            <div class="accordion-body">
                                Yes! HabitTracker AI is completely free with all features included. No hidden costs, no premium tiers, no credit card required. We believe everyone deserves access to tools that help them improve their lives.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Managing Habits -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-tasks"></i> Managing Habits
                </h3>
                <div class="accordion" id="managingHabits">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q4">
                                How do I add a new habit?
                            </button>
                        </h2>
                        <div id="q4" class="accordion-collapse collapse" data-bs-parent="#managingHabits">
                            <div class="accordion-body">
                                From your Dashboard, click the "Add New Habit" button. Give your habit a name (like "Morning Jog" or "Read for 30 minutes"), choose a category (Health, Productivity, Learning, etc.), select how often you want to do it (Daily or Weekly), and optionally pick your preferred time of day. Click "Create Habit" and you're all set!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q5">
                                How do I mark a habit as completed?
                            </button>
                        </h2>
                        <div id="q5" class="accordion-collapse collapse" data-bs-parent="#managingHabits">
                            <div class="accordion-body">
                                Go to your Dashboard and you'll see "Today's Habits" section. Find the habit you completed and click the green "Done" button. You'll get a celebration popup with confetti! 🎉 You can also mark habits as done from the "My Habits" page.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q6">
                                Can I edit or delete a habit?
                            </button>
                        </h2>
                        <div id="q6" class="accordion-collapse collapse" data-bs-parent="#managingHabits">
                            <div class="accordion-body">
                                Absolutely! Go to "My Habits" page, find the habit you want to change, and click the three dots menu (⋮). You can choose "Edit" to modify the habit details or "Delete" to remove it completely. Don't worry – you'll get a confirmation before anything is deleted.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q7">
                                How many habits can I track at once?
                            </button>
                        </h2>
                        <div id="q7" class="accordion-collapse collapse" data-bs-parent="#managingHabits">
                            <div class="accordion-body">
                                You can track as many habits as you want – there's no limit! However, we recommend starting with 3-5 habits. Research shows that trying to change too many things at once often leads to failure. Start small, build consistency, then add more habits when you're ready.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Insights -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-brain"></i> AI Insights & Analytics
                </h3>
                <div class="accordion" id="aiInsights">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q8">
                                What are AI Insights and how do they help me? <span class="badge-new">HOT</span>
                            </button>
                        </h2>
                        <div id="q8" class="accordion-collapse collapse" data-bs-parent="#aiInsights">
                            <div class="accordion-body">
                                AI Insights are personalized recommendations based on YOUR behavior, not generic advice. The system watches your patterns and tells you things like: "You're doing great with evening habits – try moving your struggling morning habit to evening," or "You skip habits most often on Mondays – consider making Monday habits easier." It's like having a personal coach who knows you!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q9">
                                What is a streak and why does it matter?
                            </button>
                        </h2>
                        <div id="q9" class="accordion-collapse collapse" data-bs-parent="#aiInsights">
                            <div class="accordion-body">
                                A streak is the number of consecutive days you've completed at least one habit. For example, if you've logged habits for 7 days straight, you have a 7-day streak! Streaks are important because they build momentum – the longer your streak, the more motivated you are to keep it going. It's psychology that works!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q10">
                                How does the system know my "weak days"?
                            </button>
                        </h2>
                        <div id="q10" class="accordion-collapse collapse" data-bs-parent="#aiInsights">
                            <div class="accordion-body">
                                The AI looks at your habit completion history over the past 30 days and identifies which days of the week you struggle with most. If you consistently skip habits on Saturdays, that's a weak day. Once identified, the system suggests strategies like planning easier habits for those days or setting extra reminders.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q11">
                                What's the completion rate percentage mean?
                            </button>
                        </h2>
                        <div id="q11" class="accordion-collapse collapse" data-bs-parent="#aiInsights">
                            <div class="accordion-body">
                                Your completion rate is the percentage of habits you've actually completed compared to how many you planned. For example, if you have 5 daily habits and you complete 4 of them each day for a week, your completion rate would be 80%. Aim for at least 70% – that shows you're building solid habits!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports & Data -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-chart-bar"></i> Reports & Data
                </h3>
                <div class="accordion" id="reportsData">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q12">
                                How do I generate a report?
                            </button>
                        </h2>
                        <div id="q12" class="accordion-collapse collapse" data-bs-parent="#reportsData">
                            <div class="accordion-body">
                                Go to the "Reports" page from your dashboard menu. You'll see three options: Weekly Report (last 7 days), Monthly Report (last 30 days), or Custom Date Range. Click "Generate" on the one you want, and a new page will open with your beautiful, detailed report showing all your stats, charts, and AI insights!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q13">
                                Can I download or print my reports?
                            </button>
                        </h2>
                        <div id="q13" class="accordion-collapse collapse" data-bs-parent="#reportsData">
                            <div class="accordion-body">
                                Yes! After generating a report, click the "Print / Save as PDF" button at the top. Your browser's print dialog will open. Select "Save as PDF" as the destination, and you'll have a professional-looking PDF of your habit journey that you can save, share, or print!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q14">
                                What information is included in reports?
                            </button>
                        </h2>
                        <div id="q14" class="accordion-collapse collapse" data-bs-parent="#reportsData">
                            <div class="accordion-body">
                                Reports include everything: your completion statistics, total points earned, current streak, daily progress charts, habit-by-habit breakdown with completion rates, AI insights and recommendations, performance analysis showing your strengths and areas for improvement. It's a complete snapshot of your progress!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social & Gamification -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-trophy"></i> Social Features & Gamification
                </h3>
                <div class="accordion" id="socialGamification">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q15">
                                How does the points system work? <span class="badge-new">NEW</span>
                            </button>
                        </h2>
                        <div id="q15" class="accordion-collapse collapse" data-bs-parent="#socialGamification">
                            <div class="accordion-body">
                                You earn 10 points every time you complete a habit! These points add up and determine your position on the leaderboard. The more consistent you are, the more points you earn. Points are a fun way to track your overall progress and compete with friends or other users.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q16">
                                What are badges and how do I earn them?
                            </button>
                        </h2>
                        <div id="q16" class="accordion-collapse collapse" data-bs-parent="#socialGamification">
                            <div class="accordion-body">
                                Badges are achievement levels based on your total points. Start as a Beginner (0-49 points), work your way up to Rookie (50-199), Expert (200-499), Master (500-999), and finally Legend (1000+ points)! Each badge represents your dedication and progress. They're displayed on the leaderboard for everyone to see!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q17">
                                How does the leaderboard work?
                            </button>
                        </h2>
                        <div id="q17" class="accordion-collapse collapse" data-bs-parent="#socialGamification">
                            <div class="accordion-body">
                                The leaderboard shows the top 10 users based on total points earned. You can see your rank, other users' ranks, and everyone's badges. It updates automatically when anyone completes a habit. The top 3 users get special trophy medals! Check the "Social" page to see where you stand and get motivated by friendly competition.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q18">
                                Can I share my habits with others?
                            </button>
                        </h2>
                        <div id="q18" class="accordion-collapse collapse" data-bs-parent="#socialGamification">
                            <div class="accordion-body">
                                Yes! Go to the "Social" page and you'll see your habits listed. Click the "Share" button next to any habit you want to share. You'll get a unique share code that you can give to friends or family. They can use this code to see your progress and get inspired!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account & Security -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-shield-alt"></i> Account & Security
                </h3>
                <div class="accordion" id="accountSecurity">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q19">
                                Is my data safe and private?
                            </button>
                        </h2>
                        <div id="q19" class="accordion-collapse collapse" data-bs-parent="#accountSecurity">
                            <div class="accordion-body">
                                Absolutely! Your password is encrypted using industry-standard bcrypt hashing – even we can't see your actual password. All database queries use prepared statements to prevent SQL injection attacks. Your personal habit data is only visible to you (unless you choose to share specific habits). We take security seriously!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q20">
                                How do I change my password?
                            </button>
                        </h2>
                        <div id="q20" class="accordion-collapse collapse" data-bs-parent="#accountSecurity">
                            <div class="accordion-body">
                                Click on your name in the top-right corner and select "Profile." Scroll to the "Change Password" section. Enter your current password, then your new password twice. Click "Change Password" and you're all set! Make sure your new password is at least 6 characters long.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q21">
                                What happens if I forget my password?
                            </button>
                        </h2>
                        <div id="q21" class="accordion-collapse collapse" data-bs-parent="#accountSecurity">
                            <div class="accordion-body">
                                Currently, you'll need to contact support through the "Contact" page. We're working on adding an automatic password reset feature via email. In the meantime, our support team can help you regain access to your account securely.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q22">
                                Can I delete my account?
                            </button>
                        </h2>
                        <div id="q22" class="accordion-collapse collapse" data-bs-parent="#accountSecurity">
                            <div class="accordion-body">
                                Yes, but please think carefully! Go to your Profile page and scroll to the "Danger Zone" section. Click "Delete My Account," type "DELETE" to confirm, and your account and ALL your data will be permanently removed. This action cannot be undone, so make sure you're certain before proceeding.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Support -->
            <div class="faq-category">
                <h3 class="faq-category-title">
                    <i class="fas fa-tools"></i> Technical Support
                </h3>
                <div class="accordion" id="technicalSupport">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q23">
                                Which browsers are supported?
                            </button>
                        </h2>
                        <div id="q23" class="accordion-collapse collapse" data-bs-parent="#technicalSupport">
                            <div class="accordion-body">
                                HabitTracker AI works great on all modern browsers: Google Chrome, Mozilla Firefox, Microsoft Edge, Safari, and Opera. We recommend using the latest version of your browser for the best experience. The website is also fully responsive and works perfectly on mobile devices!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q24">
                                Does it work on mobile phones?
                            </button>
                        </h2>
                        <div id="q24" class="accordion-collapse collapse" data-bs-parent="#technicalSupport">
                            <div class="accordion-body">
                                Yes! The website is fully responsive and adapts perfectly to phones and tablets. You can track your habits, view your dashboard, check insights, and do everything you can on a computer – all from your mobile browser. No app download needed!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q25">
                                I found a bug. How do I report it?
                            </button>
                        </h2>
                        <div id="q25" class="accordion-collapse collapse" data-bs-parent="#technicalSupport">
                            <div class="accordion-body">
                                We appreciate you helping us improve! Please go to the "Contact" page and send us a message describing: what you were doing when the bug occurred, what you expected to happen, what actually happened, and if possible, include a screenshot. We'll investigate and fix it as soon as possible!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#q26">
                                Can I suggest new features?
                            </button>
                        </h2>
                        <div id="q26" class="accordion-collapse collapse" data-bs-parent="#technicalSupport">
                            <div class="accordion-body">
                                Absolutely! We love hearing from our users. Visit the "Contact" page and share your ideas. Tell us what features would make HabitTracker AI even better for you. We're constantly improving based on user feedback, and your suggestion might be in the next update!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions? -->
            <div class="text-center mt-5">
                <div class="card bg-primary text-white shadow-lg">
                    <div class="card-body p-5">
                        <i class="fas fa-question-circle fa-4x mb-3"></i>
                        <h3>Still Have Questions?</h3>
                        <p class="lead mb-4">Can't find the answer you're looking for? We're here to help!</p>
                        <a href="contact.php" class="btn btn-light btn-lg">
                            <i class="fas fa-envelope"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 HabitTracker AI. All rights reserved.</p>
            <p>
                <a href="about.php" class="text-white me-3">About</a>
                <a href="faq.php" class="text-white me-3">FAQ</a>
                <a href="contact.php" class="text-white me-3">Contact</a>
                <a href="#" class="text-white">Privacy Policy</a>
            </p>
        </div>
    </footer>

    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // FAQ Search Functionality
        document.getElementById('faqSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const accordionItems = document.querySelectorAll('.accordion-item');
            
            accordionItems.forEach(item => {
                const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = '';
                    
                    // Open accordion if search matches
                    if (searchTerm.length > 2) {
                        const collapse = item.querySelector('.accordion-collapse');
                        if (!collapse.classList.contains('show')) {
                            const button = item.querySelector('.accordion-button');
                            button.click();
                        }
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Here you would typically send an email or store the message in a database
        $success = "Thank you for your message. We will get back to you soon!";
    }
}
?>

<div class="contact-container">
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>Get in touch with our team for support or inquiries</p>
    </div>

    <div class="contact-content">
        <div class="contact-info">
            <div class="info-card">
                <h3>Office Location</h3>
                <p>123 Network Street</p>
                <p>Tech City, TC 12345</p>
            </div>

            <div class="info-card">
                <h3>Contact Information</h3>
                <p>Email: support@fiberopticmanager.com</p>
                <p>Phone: +1 (555) 123-4567</p>
            </div>

            <div class="info-card">
                <h3>Business Hours</h3>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 10:00 AM - 2:00 PM</p>
                <p>Sunday: Closed</p>
            </div>
        </div>

        <div class="contact-form-container">
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="contact-form">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 
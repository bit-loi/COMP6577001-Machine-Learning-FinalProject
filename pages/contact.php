<?php
session_start();
require '../config/config.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $errorMessage = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
            $stmt->execute([':name' => $name, ':email' => $email, ':subject' => $subject, ':message' => $message]);
            $successMessage = 'Your message has been sent. We will respond within 24 hours.';
        } catch (PDOException $e) {
            $errorMessage = 'Something went wrong. Please try again.';
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<!-- Contact Hero -->
<section style="padding: 120px 0 80px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.06);">
    <div style="max-width: 600px; margin: 0 auto; padding: 0 24px;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; letter-spacing: 0.3em; color: rgba(255,255,255,0.2); text-transform: uppercase; margin-bottom: 24px;">Get in Touch</div>
        <h1 style="font-family: 'Playfair Display', serif; font-style: italic; font-size: clamp(2.5rem, 5vw, 4rem); color: white; margin: 0 0 20px 0; line-height: 1.1;">
            We'd love to<br>hear from you
        </h1>
        <p style="color: rgba(255,255,255,0.4); font-size: 1rem; line-height: 1.8; margin: 0;">
            Whether you're looking for a rare edition, have a question about an order, or simply want to talk books — we're here.
        </p>
    </div>
</section>

<!-- Contact Content -->
<section style="max-width: 1100px; margin: 0 auto; padding: 80px 24px;">
    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 80px; align-items: start;">

        <!-- Left: Info -->
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: white; margin: 0 0 32px 0;">Contact Information</h2>

            <?php
            $contacts = [
                ['icon' => 'M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z', 'label' => 'Email', 'value' => 'hello@premeditatio.com'],
                ['icon' => 'M3 5a2 2 0 0 1 2-2h3.28a1 1 0 0 1 .948.684l1.498 4.493a1 1 0 0 1-.502 1.21l-2.257 1.13a11.042 11.042 0 0 0 5.516 5.516l1.13-2.257a1 1 0 0 1 1.21-.502l4.493 1.498a1 1 0 0 1 .684.949V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => 'Phone', 'value' => '+1 (555) 000-0000'],
                ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z', 'label' => 'Address', 'value' => '123 Literary Lane, Book District, NY 10001'],
                ['icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'label' => 'Hours', 'value' => 'Mon–Fri: 9am–6pm EST'],
            ];
            foreach ($contacts as $c): ?>
            <div style="display: flex; gap: 16px; margin-bottom: 28px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo $c['icon']; ?>"/></svg>
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.25); margin-bottom: 4px;"><?php echo $c['label']; ?></div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.6);"><?php echo $c['value']; ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Divider -->
            <div style="height: 1px; background: rgba(255,255,255,0.06); margin: 32px 0;"></div>

            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.3); line-height: 1.8;">
                <em style="font-family: 'Playfair Display', serif; font-size: 1rem; color: rgba(255,255,255,0.5); display: block; margin-bottom: 8px;">"Premeditatio malorum"</em>
                The premeditation of evils — a Stoic practice of contemplating what could go wrong, so that we may face it with equanimity.
            </div>
        </div>

        <!-- Right: Form -->
        <div>
            <?php if ($successMessage): ?>
            <div style="margin-bottom: 24px; padding: 16px 20px; border-radius: 10px; font-size: 0.875rem; color: #4ade80; background: rgba(74,222,128,0.06); border: 1px solid rgba(74,222,128,0.15);">
                ✓ <?php echo $successMessage; ?>
            </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
            <div style="margin-bottom: 24px; padding: 16px 20px; border-radius: 10px; font-size: 0.875rem; color: #f87171; background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.15);">
                <?php echo $errorMessage; ?>
            </div>
            <?php endif; ?>

            <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 0.75rem; font-weight: 500; color: rgba(255,255,255,0.4); letter-spacing: 0.05em;">Full Name *</label>
                        <input type="text" name="name" placeholder="John Doe" required
                            style="height: 44px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; transition: border-color 0.2s; font-family: inherit;"
                            onfocus="this.style.borderColor='rgba(255,255,255,0.25)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 0.75rem; font-weight: 500; color: rgba(255,255,255,0.4); letter-spacing: 0.05em;">Email Address *</label>
                        <input type="email" name="email" placeholder="you@example.com" required
                            style="height: 44px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; transition: border-color 0.2s; font-family: inherit;"
                            onfocus="this.style.borderColor='rgba(255,255,255,0.25)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.75rem; font-weight: 500; color: rgba(255,255,255,0.4); letter-spacing: 0.05em;">Subject</label>
                    <input type="text" name="subject" placeholder="What is this about?"
                        style="height: 44px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; transition: border-color 0.2s; font-family: inherit;"
                        onfocus="this.style.borderColor='rgba(255,255,255,0.25)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.75rem; font-weight: 500; color: rgba(255,255,255,0.4); letter-spacing: 0.05em;">Message *</label>
                    <textarea name="message" placeholder="Tell us what's on your mind..." required rows="6"
                        style="border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); padding: 14px; font-size: 0.875rem; color: white; outline: none; transition: border-color 0.2s; resize: vertical; font-family: inherit; line-height: 1.6;"
                        onfocus="this.style.borderColor='rgba(255,255,255,0.25)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'"></textarea>
                </div>

                <button type="submit"
                    style="height: 48px; border-radius: 8px; background: white; color: #050505; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s; letter-spacing: 0.02em;"
                    onmouseover="this.style.background='#e5e5e5'" onmouseout="this.style.background='white'">
                    Send Message →
                </button>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

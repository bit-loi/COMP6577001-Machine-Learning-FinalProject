<?php require 'includes/header.php'; ?>
<?php require 'config/config.php'; ?>
<?php
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $successMessage = 'Message sent successfully! We\'ll get back to you soon.';
    }
}
?>
<div style="background: #f5f5f5; min-height: 60vh;">
    <div style="background: linear-gradient(135deg, #FF6B35, #EE4D2D); padding: 64px 0;">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 text-center">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: white; margin: 0 0 12px;">Contact Us</h1>
            <p style="font-size: 1.05rem; color: rgba(255,255,255,0.8);">We'd love to hear from you. Send us a message!</p>
        </div>
    </div>
    <div class="max-w-3xl mx-auto px-6 lg:px-12 py-16">
        <?php if($successMessage): ?>
        <div style="padding: 16px; background: #D1FAE5; color: #059669; border-radius: 10px; margin-bottom: 24px; text-align: center;"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <div style="background: #fff; border-radius: 16px; border: 1px solid #eee; padding: 48px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px;">
                <div>
                    <h2 style="font-size: 1.3rem; font-weight: 700; color: #222; margin: 0 0 20px;">Get in Touch</h2>
                    <div style="margin-bottom: 20px;"><div style="font-size: 0.8rem; font-weight: 600; color: #999; margin-bottom: 4px;">Email</div><div style="color: #333;">hello@shopmart.com</div></div>
                    <div style="margin-bottom: 20px;"><div style="font-size: 0.8rem; font-weight: 600; color: #999; margin-bottom: 4px;">Phone</div><div style="color: #333;">+1 (555) 000-0000</div></div>
                    <div><div style="font-size: 0.8rem; font-weight: 600; color: #999; margin-bottom: 4px;">Hours</div><div style="color: #333;">Mon–Fri: 9am–6pm</div></div>
                </div>
                <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Name</label><input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'"></div>
                    <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Email</label><input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'"></div>
                    <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Subject</label><input type="text" name="subject" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'"></div>
                    <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Message</label><textarea name="message" rows="4" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none; box-sizing: border-box; resize: vertical;" onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'"></textarea></div>
                    <button type="submit" style="padding: 14px; background: #FF6B35; color: white; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#EE4D2D'" onmouseout="this.style.background='#FF6B35'">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>

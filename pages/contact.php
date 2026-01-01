<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

<div class="container">  
    <section class="mb-5 mt-5">

        <!-- Section heading -->
        <div class="text-center mb-5 animate-fade-in">
            <h2 class="h1 gradient-text mb-3">Get In Touch</h2>
            <p class="text-muted w-responsive mx-auto">
                Do you have any questions? Please do not hesitate to contact us directly. 
                Our team will come back to you within a matter of hours to help you.
            </p>
        </div>

        <?php if(isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="row g-4">

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card shadow-custom">
                    <div class="card-body p-5">
                        <form id="contact-form" name="contact-form" action="../includes/contact-handler.php" method="POST">

                            <div class="row g-3">
                                
                                <!-- Name -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Your Name *</label>
                                        <input type="text" id="name" name="name" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Your Email *</label>
                                        <input type="email" id="email" name="email" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <input type="text" id="subject" name="subject" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Message -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Your Message *</label>
                                        <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>

                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4">
                
                <!-- Address Card -->
                <div class="card shadow-custom mb-4">
                    <div class="card-body text-center p-4">
                        <div class="icon-box mb-3" style="width: 60px; height: 60px; margin: 0 auto; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-marker-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="mb-2">Address</h5>
                        <p class="text-muted mb-0">San Francisco, CA 94126, USA</p>
                    </div>
                </div>

                <!-- Phone Card -->
                <div class="card shadow-custom mb-4">
                    <div class="card-body text-center p-4">
                        <div class="icon-box mb-3" style="width: 60px; height: 60px; margin: 0 auto; border-radius: 50%; background: var(--success-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-phone fa-2x text-white"></i>
                        </div>
                        <h5 class="mb-2">Phone</h5>
                        <p class="text-muted mb-0">+1 (234) 567-8900</p>
                    </div>
                </div>

                <!-- Email Card -->
                <div class="card shadow-custom">
                    <div class="card-body text-center p-4">
                        <div class="icon-box mb-3" style="width: 60px; height: 60px; margin: 0 auto; border-radius: 50%; background: var(--secondary-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope fa-2x text-white"></i>
                        </div>
                        <h5 class="mb-2">Email</h5>
                        <p class="text-muted mb-0">contact@bookstore.com</p>
                    </div>
                </div>

            </div>

        </div>

    </section>
</div>

<?php require '../includes/footer.php'; ?>

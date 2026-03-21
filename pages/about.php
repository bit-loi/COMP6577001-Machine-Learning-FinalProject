<?php
session_start();
require '../config/config.php';
?>
<?php include '../includes/header.php'; ?>

<!-- About Hero -->
<section style="padding: 120px 0 80px; text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1600&q=80'); background-size: cover; background-position: center; opacity: 0.05;"></div>
    <div style="position: relative; max-width: 700px; margin: 0 auto; padding: 0 24px;">
        <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; letter-spacing: 0.3em; color: rgba(255,255,255,0.2); text-transform: uppercase; margin-bottom: 24px;">Our Story</div>
        <h1 style="font-family: 'Playfair Display', serif; font-style: italic; font-size: clamp(2.5rem, 5vw, 4.5rem); color: white; margin: 0 0 24px 0; line-height: 1.1;">
            Premeditatio<br>Malorum
        </h1>
        <p style="color: rgba(255,255,255,0.4); font-size: 1.05rem; line-height: 1.9; max-width: 560px; margin: 0 auto;">
            A curated bookstore born from a Stoic philosophy — the practice of contemplating adversity to live more fully. We believe the right book at the right moment can transform a life.
        </p>
    </div>
</section>

<!-- Philosophy Section -->
<section style="max-width: 1100px; margin: 0 auto; padding: 80px 24px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; margin-bottom: 100px;">
        <div>
            <div style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(255,255,255,0.2); margin-bottom: 16px;">The Philosophy</div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: white; margin: 0 0 24px 0; line-height: 1.2;">Memento Mori.<br>Amor Fati.</h2>
            <p style="color: rgba(255,255,255,0.45); font-size: 0.95rem; line-height: 1.9; margin: 0 0 20px 0;">
                We named our store after the Stoic practice of <em style="color: rgba(255,255,255,0.65);">premeditatio malorum</em> — the premeditation of evils. Not as a morbid exercise, but as a path to gratitude, resilience, and presence.
            </p>
            <p style="color: rgba(255,255,255,0.45); font-size: 0.95rem; line-height: 1.9; margin: 0;">
                Every book in our collection has been chosen because it confronts something real — mortality, failure, love, meaning. We don't sell escapism. We sell perspective.
            </p>
        </div>
        <div style="position: relative;">
            <div style="border-radius: 16px; overflow: hidden; aspect-ratio: 4/5;">
                <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80" alt="Library" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7;">
            </div>
            <div style="position: absolute; bottom: -20px; left: -20px; background: #0a0a0a; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 20px 24px;">
                <div style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.1rem; color: white; margin-bottom: 4px;">"The obstacle is the way."</div>
                <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3);">— Marcus Aurelius</div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; margin-bottom: 100px;">
        <?php
        $stats = [
            ['number' => '12,000+', 'label' => 'Books Curated'],
            ['number' => '50,000+', 'label' => 'Happy Readers'],
            ['number' => '8', 'label' => 'Genre Collections'],
            ['number' => '4.9★', 'label' => 'Average Rating'],
        ];
        foreach ($stats as $s): ?>
        <div style="background: #0a0a0a; padding: 40px 32px; text-align: center;">
            <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; color: white; margin-bottom: 8px;"><?php echo $s['number']; ?></div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.1em;"><?php echo $s['label']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Values -->
    <div style="margin-bottom: 100px;">
        <div style="text-align: center; margin-bottom: 60px;">
            <div style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(255,255,255,0.2); margin-bottom: 16px;">What We Stand For</div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; color: white; margin: 0;">Our Values</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
            <?php
            $values = [
                ['icon' => '📖', 'title' => 'Curation Over Volume', 'desc' => 'Every title earns its place. We read before we stock, and we remove what no longer serves.'],
                ['icon' => '🧭', 'title' => 'Honest Recommendations', 'desc' => 'We tell you when a book is difficult, challenging, or not for everyone. Honesty builds trust.'],
                ['icon' => '♾️', 'title' => 'Timeless Over Trending', 'desc' => 'We favor books that endure over decades, not just news cycles. Classics and hidden gems.'],
                ['icon' => '🌍', 'title' => 'Global Perspectives', 'desc' => 'Literature from every continent, culture, and century. The world is larger than one tradition.'],
                ['icon' => '🤝', 'title' => 'Community First', 'desc' => 'Our readers are our editors. Reviews, reading groups, and conversations shape our catalogue.'],
                ['icon' => '🌱', 'title' => 'Sustainable Practices', 'desc' => 'Eco-friendly packaging, carbon-neutral shipping, and support for independent publishers.'],
            ];
            foreach ($values as $v): ?>
            <div style="background: #0a0a0a; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 28px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.12)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
                <div style="font-size: 1.75rem; margin-bottom: 16px;"><?php echo $v['icon']; ?></div>
                <div style="font-size: 0.9rem; font-weight: 700; color: white; margin-bottom: 10px;"><?php echo $v['title']; ?></div>
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4); line-height: 1.7;"><?php echo $v['desc']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CTA -->
    <div style="text-align: center; padding: 80px 40px; background: #0a0a0a; border: 1px solid rgba(255,255,255,0.06); border-radius: 20px;">
        <div style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 2rem; color: white; margin-bottom: 16px;">Begin your journey.</div>
        <p style="color: rgba(255,255,255,0.4); font-size: 0.95rem; margin: 0 0 32px 0; max-width: 400px; margin-left: auto; margin-right: auto; line-height: 1.7;">
            Join thousands of readers who have found their next life-changing book with us.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo APPURL; ?>categories/index.php"
                style="display: inline-flex; align-items: center; padding: 12px 28px; background: white; color: #050505; font-size: 0.875rem; font-weight: 600; text-decoration: none; border-radius: 8px; transition: background 0.2s;"
                onmouseover="this.style.background='#e5e5e5'" onmouseout="this.style.background='white'">
                Browse Collection →
            </a>
            <a href="<?php echo APPURL; ?>pages/contact.php"
                style="display: inline-flex; align-items: center; padding: 12px 28px; background: transparent; color: white; font-size: 0.875rem; font-weight: 600; text-decoration: none; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); transition: border-color 0.2s;"
                onmouseover="this.style.borderColor='white'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'">
                Contact Us
            </a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

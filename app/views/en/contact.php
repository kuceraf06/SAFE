<?php
require_once __DIR__ . '/../../lib/data.php';
require_once __DIR__ . '/../../lib/view_helpers.php';
require_once __DIR__ . '/../../lib/contact.php';

$pageTitle = 'SAFE | Contact';
$bodyClass = 'contact-body';
$pageCss   = 'contact';

// zpracování formuláře: e-mail klubu + kopie uživateli
$form        = safe_handle_contact('en');
$formMessage = $form['message'];
$formClass   = $form['class'];
?>
<main class="contact-page page-width">
    <div class="heading">
        <h1>CONTACT US</h1>
    </div>
    <div class="contact-top">
        <div class="contact-box">
            <div class="contact-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1278.4678616738897!2d14.082344583382277!3d50.1436342810659!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470bb7cd8c4a9c9f%3A0x7fdcecadc82d1c9f!2sMiners%20Kladno!5e0!3m2!1scs!2scz!4v1710027633321!5m2!1scs!2scz" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="contact-panel">
                <?php if ($formMessage): ?>
                    <center><div class="alert <?= e($formClass) ?>"><?= e($formMessage) ?></div></center>
                <?php endif; ?>
                <form class="contact-form" method="post" autocomplete="off">
                    <?= safe_antispam_fields() ?>
                    <input type="text" name="name" placeholder="Your full name*" class="field" required>
                    <input type="email" name="email" class="field" placeholder="Your e-mail address*" required>
                    <input type="tel" inputmode="numeric" name="tel" id="tel" class="field" placeholder="Your phone number*" required>
                    <select class="field" name="subject">
                        <option value="Question">Question</option>
                        <option value="Feedback">Feedback</option>
                        <option value="Complaint">Complaint</option>
                    </select>
                    <textarea placeholder="Message*" name="message" class="field" required></textarea>
                    <input type="submit" value="Send" name="send" class="btn">
                </form>
            </div>
        </div>
        <div class="contact-methods">
            <div class="contact-method">
                <i class="fa-solid fa-location-dot contact-icon"></i>
                <article class="contact-text">
                    <h2 class="contact-label">Address</h2>
                    <p class="contact-value"><a target="_blank" href="https://www.google.com/maps/place/Miners+Kladno/@50.1437908,14.0804611,17z/data=!3m1!4b1!4m6!3m5!1s0x470bb7cd8c4a9c9f:0x7fdcecadc82d1c9f!8m2!3d50.1437874!4d14.083036!16s%2Fg%2F11dy_j7q73?entry=ttu&g_ep=EgoyMDI2MDcwOC4wIKXMDSoASAFQAw%3D%3D">U Trati 3489, 272 01 Kladno 1</a></p>
                </article>
            </div>
            <div class="contact-method">
                <i class="fa-solid fa-envelope contact-icon"></i>
                <article class="contact-text contact-text-email">
                    <h2 class="contact-label">Email</h2>
                    <p class="contact-value"><a href="mailto:safe@minerskladno.cz" target="_blank">safe@minerskladno.cz</a></p>
                </article>
            </div>
            <div class="contact-method">
                <i class="fa-solid fa-phone contact-icon"></i>
                <article class="contact-text contact-text-phone">
                    <h2 class="contact-label">Phone</h2>
                    <p class="contact-value"><a href="tel:+420739026342">+420 739 026 342</a></p>
                </article>
            </div>
        </div>
    </div>
</main>

<script>
    const telInput = document.getElementById('tel');
    if (telInput) {
        telInput.addEventListener('input', function () {
            const cleaned = telInput.value.replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < cleaned.length; i++) {
                if (i > 0 && i % 3 === 0) { formatted += ' '; }
                formatted += cleaned[i];
            }
            telInput.value = formatted;
        });
    }
</script>

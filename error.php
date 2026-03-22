<?php
// error.php
$type = $_GET['type'] ?? '500';

$errorTitles = [
    '404' => 'Page Not Found',
    '403' => 'Access Denied',
    '500' => 'System Malfunction',
    '503' => 'Service Unavailable'
];

$errorMessages = [
    '404' => 'The literary piece you are looking for has been misplaced or never existed in our library.',
    '403' => 'You do not have the proper clearance to enter this section of the archive.',
    '500' => 'Our internal systems encountered an unexpected logic error. The engineers have been notified.',
    '503' => 'The database holding our catalog is temporarily out of reach. Please check back shortly.'
];

$title = $errorTitles[$type] ?? 'Unknown Error';
$message = $errorMessages[$type] ?? 'Something went wrong, and we are working to resolve it.';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($type . ' - ' . $title); ?></title>
    <!-- Use Tailwind CSS for a premium, beautiful aesthetic -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500&display=swap');
        
        body {
            background-color: #000000; /* Pure Black background */
            color: #ffffff; /* Pure White text */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-image: none;
        }
        
        .font-serif-custom {
            font-family: 'Playfair Display', serif;
        }

        .error-card {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glitch-text {
            color: #ffffff;
            letter-spacing: -0.05em;
        }

        .glow-btn {
            transition: all 0.3s ease;
        }
        .glow-btn:hover {
            background-color: #ffffff;
            color: #000000;
        }
        
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="error-card max-w-2xl w-full p-10 md:p-16 text-center">

        <!-- Logo representation -->
        <div class="mb-12 flex justify-center items-center gap-3">
            <i class="fas fa-book-open text-xl"></i>
            <span class="font-serif-custom italic text-xl">Premeditatio Malorum</span>
        </div>

        <div class="mb-10">
            <h1 class="text-7xl md:text-9xl font-bold mb-4 glitch-text" data-text="<?php echo htmlspecialchars($type); ?>">
                <?php echo htmlspecialchars($type); ?>
            </h1>
            <h2 class="text-xl md:text-2xl font-light text-zinc-300 tracking-widest uppercase mt-4">
                <?php echo htmlspecialchars($title); ?>
            </h2>
        </div>

        <div class="mb-12">
            <p class="text-zinc-400 font-light text-lg leading-relaxed max-w-lg mx-auto">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-5 justify-center mt-8">
            <a href="/bookstore/" class="glow-btn bg-zinc-900 border border-zinc-700 text-white font-light py-3 px-10 flex items-center justify-center gap-3 text-sm tracking-wide uppercase">
                <i class="fas fa-long-arrow-alt-left"></i> Return to Store
            </a>
            <button onclick="window.history.back()" class="glow-btn bg-transparent border border-zinc-700 text-zinc-400 font-light py-3 px-10 flex items-center justify-center gap-3 text-sm tracking-wide uppercase">
                <i class="fas fa-undo"></i> Go Back
            </button>
        </div>

    </div>

</body>
</html>

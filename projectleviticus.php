<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle proxy requests
if (isset($_GET['url'])) {
    $url = $_GET['url'];

    // If it's a search query, redirect to a search engine
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $url = 'https://www.google.com/search?q=' . urlencode($url);
    }

    // Fetch the content from the target URL
    $options = [
        "http" => [
            "header" => "User-Agent: Project Leviticus\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $content = file_get_contents($url, false, $context);

    // Remove ads (basic example: remove script tags)
    $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);

    // Rewrite links to work through the proxy
    $content = preg_replace_callback('/href="([^"]+)"/', function($matches) {
        return 'href="?url=' . urlencode($matches[1]) . '"';
    }, $content);

    $content = preg_replace_callback('/src="([^"]+)"/', function($matches) {
        return 'src="?url=' . urlencode($matches[1]) . '"';
    }, $content);

    // Output the content
    echo $content;
    exit;
}

// Handle theme toggling
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark';
if (isset($_GET['toggle_theme'])) {
    $theme = ($theme === 'dark') ? 'light' : 'dark';
    setcookie('theme', $theme, time() + (86400 * 30), "/"); // 30 days
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle password generation
if (isset($_GET['generate_password'])) {
    $password = bin2hex(random_bytes(8)); // Generate a random password
    setcookie('generated_password', $password, time() + 3600, "/"); // 1 hour
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Leviticus</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: <?php echo ($theme === 'dark') ? '#1e1e1e' : '#fff'; ?>;
            color: <?php echo ($theme === 'dark') ? '#fff' : '#000'; ?>;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            width: 100%;
            max-width: 1200px;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 80%;
            max-width: 600px;
            padding: 10px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            outline: none;
        }
        button {
            padding: 10px 20px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            margin-left: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .features {
            margin-top: 20px;
        }
        .features button {
            margin: 5px;
        }
        iframe {
            width: 100%;
            height: 70vh;
            border: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Project Leviticus</h1>
        <form action="" method="GET">
            <input type="text" name="url" placeholder="Enter URL or search query..." required>
            <button type="submit">Go</button>
        </form>

        <div class="features">
            <!-- Theme Toggle -->
            <button onclick="window.location.href='?toggle_theme=1'">Toggle Theme</button>

            <!-- Password Generator -->
            <button onclick="window.location.href='?generate_password=1'">Generate Password</button>
            <?php
            if (isset($_COOKIE['generated_password'])) {
                echo "<p>Generated Password: " . $_COOKIE['generated_password'] . "</p>";
            }
            ?>

            <!-- QR Code Generator -->
            <button onclick="generateQRCode()">Generate QR Code</button>
            <div id="qrcode"></div>
            <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
            <script>
                function generateQRCode() {
                    const url = document.querySelector('input[name="url"]').value;
                    if (url) {
                        document.getElementById('qrcode').innerHTML = '';
                        new QRCode(document.getElementById('qrcode'), url);
                    } else {
                        alert('Please enter a URL first.');
                    }
                }
            </script>

            <!-- AI-Powered Search -->
            <button onclick="window.location.href='?ai_search=1'">AI Search</button>
            <?php
            if (isset($_GET['ai_search'])) {
                echo "<p>AI Search Feature Coming Soon!</p>";
            }
            ?>
        </div>

        <!-- Display the website in an iframe -->
        <?php if (isset($_GET['url'])): ?>
            <iframe src="?url=<?php echo urlencode($_GET['url']); ?>"></iframe>
        <?php endif; ?>
    </div>
</body>
</html>
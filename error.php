<?php
// error.php - Custom error page
$errorCode = $_GET['code'] ?? '404';
$errorMessages = [
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Forbidden',
    '404' => 'Page Not Found',
    '500' => 'Internal Server Error'
];

$message = $errorMessages[$errorCode] ?? 'An error occurred';
http_response_code((int)$errorCode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $errorCode; ?> - Mount Carmel School</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        .error-container {
            max-width: 600px;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        h1 {
            font-size: 120px;
            margin: 0;
            color: white;
        }
        h2 {
            font-size: 32px;
            margin: 20px 0;
        }
        p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: white;
            color: #764ba2;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: transform 0.3s;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1><?php echo $errorCode; ?></h1>
        <h2><?php echo $message; ?></h2>
        <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="/" class="btn">Go to Homepage</a>
        <?php if ($errorCode == '401' || $errorCode == '403'): ?>
        <a href="/login" class="btn" style="margin-left: 10px;">Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
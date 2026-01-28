<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #0f0f13;
            color: #d1d1d1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
            text-align: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.05);
            padding: 3rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 68, 68, 0.3);
            max-width: 500px;
        }
        h1 { color: #ff4444; font-size: 3rem; margin-bottom: 1rem; }
        p { margin-bottom: 2rem; }
        .btn {
            background: #ffcc00;
            color: #000;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover { background: #e6b800; }
    </style>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <h2>Access Denied</h2>
        <p>You do not have permission to view this page.</p>
        <p>Your Role: <strong style="color: #ffcc00;"><?php session_start(); echo $_SESSION['role_code'] ?? 'Unknown'; ?></strong></p>
        <a href="index.php" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>

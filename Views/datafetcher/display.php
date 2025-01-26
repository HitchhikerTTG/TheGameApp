
<!DOCTYPE html>
<html>
<head>
    <title>Fetched Data Display</title>
    <style>
        .data-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .error {
            color: #dc3545;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="data-container">
        <?php if (isset($content['error'])): ?>
            <div class="error">
                <?= $content['error'] ?>
            </div>
        <?php else: ?>
            <pre>
                <?php print_r($content); ?>
            </pre>
        <?php endif; ?>
    </div>
</body>
</html>

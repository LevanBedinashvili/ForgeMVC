<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Mini-Laravel', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header>
        <nav>Mini-Laravel</nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Mini-Laravel</p>
    </footer>
</body>
</html>

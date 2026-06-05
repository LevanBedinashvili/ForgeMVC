<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'ForgeMVC', ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>
    <header>
        <nav>ForgeMVC</nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> ForgeMVC</p>
    </footer>
</body>

</html>
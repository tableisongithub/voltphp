<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vite + React + TS</title>
    <script type="module" crossorigin src="/assets/index.js"></script>
    <link rel="stylesheet" crossorigin href="/assets/index.css">
    <?php
    $meta = [
        'description' => 'Vite + React + TS',
        'keywords' => 'Vite, React, TS',
        'author' => 'Vite + React + TS',
        'robots' => 'index, follow',
        'googlebot' => 'index, follow',
        'bingbot' => 'index, follow',
        'og:title' => 'Vite + React + TS',
        'og:description' => 'Vite + React + TS',
        'og:site_name' => 'Vite + React + TS',
        'og:type' => 'website',
        'og:image:alt' => 'Vite + React + TS',
        'og:image:type' => 'image/svg+xml',
        'og:image:width' => '1200',
        'og:image:height' => '630',
        'twitter:card' => 'summary_large_image',
        'twitter:title' => 'Vite + React + TS',
        'twitter:description' => 'Vite + React + TS',
        'twitter:image:alt' => 'Vite + React + TS',
    ];
    foreach ($meta as $name => $content) {
        echo "<meta name='$name' content='$content'>";
    }
    ?>
</head>

<body>
    <div id="root"></div>
</body>

</html>
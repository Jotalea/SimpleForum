<?php

$jsonFile = './data.json';
$data = json_decode(file_get_contents($jsonFile), true);

// Get the 'id' param from URL

// Previous method searched per number ID
// $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// New method searches for string ID
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Search for an article with the specified ID
$article = null;
foreach ($data as $item) {
    if ($item['id'] === $id) {
        $article = $item;
        break;
    }
}

if ($article) {
    // Show the content
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($article['title']); ?></title>
    </head>
    <body>
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <p><small><?php echo date("d/m/Y", $article['time']); ?></small></p>
        <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="< Article image >">
        <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
        <a href="./index.php">< go back</a>
    </body>
    </html>
    <?php
} else {
    // Show an error
    echo '<p>Article not found.</p>';
}
?>

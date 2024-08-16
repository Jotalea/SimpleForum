<?php

$ownerName = 'YOUR NAME'

// Read the JSON file
$data = json_decode(file_get_contents('./data.json'), true);

if (isset($_POST['post'])) {
    $blogTitle = $_POST['title'];
    $blogContent = $_POST['content'];
    $blogBaseURL = "./read.php";
    $blogID = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16); // count($data) + 1;
    $imageUploadPath = '';

    // Check if an image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        //$imageName = basename($_FILES['image']['name']);
        $imageUploadDir = './uploads/';
        $imageUploadPath = $imageUploadDir . $blogID; // $imageName;

        // Create the uploads directory if it doesn't exist
        if (!is_dir($imageUploadDir)) {
            mkdir($imageUploadDir, 0755, true);
        }

        // Move the uploaded image to the uploads directory
        if (move_uploaded_file($imageTmpPath, $imageUploadPath)) {
            $imageUploadPath = $imageUploadPath;
        } else {
            $imageUploadPath = ''; // If it fails, leave the image path value empty
        }
    }

    $newPost = array(
        "id" => $blogID,
        "time" => time(),
        "title" => substr($blogTitle, 0, 128),
        "content" => substr($blogContent, 0, 4096),
        "link" => $blogBaseURL . "?id=" . $blogID,
        "image" => $imageUploadPath // Add the path to the uploaded image
    );

    array_push($data, $newPost);

    file_put_contents('./data.json', json_encode($data, JSON_PRETTY_PRINT));

	header('location:./index.php');

    echo '<p>Posted successfully. Refresh to see changes.</p>';
}

$currentMethod = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$currentUrl = $currentMethod . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $ownerName; ?>'s blog</title>
</head>
<body>
    <h1>Welcome to <?php echo $ownerName; ?>'s blog</h1>

    <?php
    if ($data) {
        foreach (array_reverse($data) as $upload) {
            echo '<br>';
            if ($upload['image']) {
                echo '<img src="' . htmlspecialchars($upload['image']) . '" alt="Post Image" style="max-width: 360px; height: auto;">';
            }
            echo '<h2>' . htmlspecialchars($upload['title']) . '</h2>';
            echo '<p><small>' . date("d/m/Y", htmlspecialchars($upload['time'])) . '</small></p>';
            echo '<p>' . substr(htmlspecialchars($upload['content']), 0, 77) . '... <a href="' . htmlspecialchars($upload['link']) . '">Read more</a></p>';
            echo '<br>';
        }
    } else {
        echo '<br><p>There are no posts yet.</p><br>';
    }
    ?>

    <br>
    
    <form method="post" enctype="multipart/form-data">
        <label for="title">Title:</label><br>
        <textarea name="title" id="title" rows="1" cols="50" required></textarea><br><br>

        <label for="content">Content:</label><br>
        <textarea name="content" id="content" rows="4" cols="50" required></textarea><br><br>

        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" accept="image/*"><br><br>

        <button type="submit" name="post">Post</button>
    </form>
</body>
</html>

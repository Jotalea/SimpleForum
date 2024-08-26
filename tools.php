<?php
$dsn = 'sqlite:data.db'; // SQLite database file
$db = new PDO($dsn);

// Create the table if it does not exist
$db->exec("CREATE TABLE IF NOT EXISTS posts (
    id TEXT PRIMARY KEY,
    time INTEGER,
    title TEXT,
    user TEXT,
    userid TEXT,
    content TEXT,
    link TEXT,
    image TEXT,
    thread TEXT
)");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        deletePost($_POST['id']);
    } elseif (isset($_POST['edit'])) {
        editPost($_POST['id'], $_POST['time'], $_POST['title'], $_POST['user'], $_POST['userid'], $_POST['content'], $_POST['link'], $_POST['image'], $_POST['thread']);
    } elseif (isset($_POST['import'])) {
        importFromJson($_POST['json']);
    } elseif (isset($_POST['export'])) {
        $json = exportToJson();
    }
}

// Function to delete a post by ID
function deletePost($id) {
    global $db;
    // Delete post from the database
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);

    // Delete the associated image from the uploads directory
    $imagePath = __DIR__ . "/uploads/{$id}"; // Assuming images are stored as .jpg files
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Function to edit a post by ID
function editPost($id, $time, $title, $user, $userid, $content, $link, $image, $thread) {
    global $db;
    // Update post in the database
    $stmt = $db->prepare("UPDATE posts SET time = ?, title = ?, user = ?, userid = ?, content = ?, link = ?, image = ?, thread = ? WHERE id = ?");
    $stmt->execute([$time, $title, $user, $userid, $content, $link, $image, $thread, $id]);
}

// Function to convert JSON to a post in the database
function importFromJson($json) {
    global $db;
    $posts = json_decode($json, true);

    foreach ($posts as $post) {
        // Insert or update post in the database
        $stmt = $db->prepare("REPLACE INTO posts (id, time, title, user, userid, content, link, image, thread) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $post['id'],
            $post['time'],
            $post['title'],
            $post['user'],
            $post['userid'],
            $post['content'],
            $post['link'],
            $post['image'],
            $post['thread']
        ]);
    }
}

// Function to export database to JSON
function exportToJson() {
    global $db;
    $stmt = $db->query("SELECT * FROM posts");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return json_encode($posts);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools Panel</title>
</head>
<body>
    <h1>Tools Panel</h1>

    <!-- Delete Post -->
    <h2>Delete Post</h2>
    <form method="POST">
        <label for="delete-id">Post ID:</label>
        <input type="text" id="delete-id" name="id" required>
        <button type="submit" name="delete">Delete Post</button>
    </form>

    <!-- Edit Post -->
    <h2>Edit Post</h2>
    <form method="POST">
        <label for="edit-id">Post ID:</label>
        <input type="text" id="edit-id" name="id" required><br>
        <label for="edit-time">Time:</label>
        <input type="number" id="edit-time" name="time" required><br>
        <label for="edit-title">Title:</label>
        <input type="text" id="edit-title" name="title" required><br>
        <label for="edit-user">User:</label>
        <input type="text" id="edit-user" name="user" required><br>
        <label for="edit-userid">User ID:</label>
        <input type="text" id="edit-userid" name="userid" required><br>
        <label for="edit-content">Content:</label>
        <textarea id="edit-content" name="content" required></textarea><br>
        <label for="edit-link">Link:</label>
        <input type="text" id="edit-link" name="link" required><br>
        <label for="edit-image">Image:</label>
        <input type="text" id="edit-image" name="image"><br>
        <label for="edit-thread">Thread:</label>
        <input type="text" id="edit-thread" name="thread"><br>
        <button type="submit" name="edit">Edit Post</button>
    </form>

    <!-- Import Posts from JSON -->
    <h2>Import Posts from JSON</h2>
    <form method="POST">
        <textarea name="json" rows="10" cols="50" placeholder='[ { "id": "1", "time": 1723073204, "title": "Example post", "content": "Lorem ipsum dolor sit amet...", "link": "./read.php?id=1", "image": "", "user":"RandomUsername", "userid": "1234567890", "thread": "2" }, ... ]' required></textarea><br>
        <button type="submit" name="import">Import JSON</button>
    </form>

    <!-- Export Database to JSON -->
    <h2>Export Database to JSON</h2>
    <form method="POST">
        <button type="submit" name="export">Export to JSON</button>
    </form>

    <?php if (isset($json)): ?>
        <h2>Exported JSON</h2>
        <pre><?php echo htmlspecialchars($json); ?></pre>
    <?php endif; ?>
</body>
</html>

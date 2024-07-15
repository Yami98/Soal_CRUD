<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$servername = "localhost";
$username = "root"; // change if necessary
$password = ""; // change if necessary
$dbname = "soal_crud";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user role
$stmt = $conn->prepare("SELECT role FROM users WHERE username=?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$role = $user['role'];
$_SESSION['role'] = $role; // Store role in session for later use
$stmt->close();

// Handle post deletion for admins only
if ($role === 'admin' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM posts WHERE id=$id");
    header("Location: admin.php");
    exit();
}

$sql = "SELECT * FROM posts ORDER BY tanggal DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">Soal CRUD</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 class="my-4">Dashboard Panel</h2>
        <?php if ($role === 'admin' || $role === 'author'): ?>
            <a href="action/create.php" class="btn btn-success mb-4">Create New Post</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
            <a href="../auth/register.php" class="btn btn-primary mb-4">Create Account</a>
        <?php endif; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <?php echo htmlspecialchars($row['judul']); ?>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($row['isi'])); ?></p>
                    <?php if ($role === 'admin'): ?>
                        <a href="action/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger">Delete</a>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted">
                    Posted on <?php echo $row['tanggal']; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

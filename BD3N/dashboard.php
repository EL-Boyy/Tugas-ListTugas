<?php
include 'includes/auth.php';
include 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Ambil username dari sesi
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id']; // Ambil user_id dari sesi

// Variabel untuk menyimpan data item yang akan diedit
$itemToEdit = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        // Simpan item dengan user_id
        $stmt = $conn->prepare("INSERT INTO items (name, description, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $user_id);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ? AND user_id = ?"); // Pastikan hanya pengguna yang sama yang bisa menghapus
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        // Ambil data item untuk diedit
        $stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $itemToEdit = $stmt->get_result()->fetch_assoc();
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        // Update item
        $stmt = $conn->prepare("UPDATE items SET name = ?, description = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $name, $description, $id, $user_id);
        $stmt->execute();
    } elseif (isset($_POST['logout'])) {
        logout(); // Panggil fungsi logout dari auth.php
    }
}

// Ambil item yang hanya milik pengguna yang sedang login
$stmt = $conn->prepare("SELECT * FROM items WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Dashboard</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            display: flex;
            justify-content: space-between; /* Mengatur ruang antara elemen */
            align-items: center; /* Menyelaraskan item secara vertikal */
            padding: 10px;
            background-color: #343a40; /* Warna latar belakang header */
            color: white;
        }
        .logout-button {
            padding: 10px 15px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .logout-button:hover {
            background-color: #c9302c;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: auto; /* Center the container */
        }
        .edit-form {
            margin-top : 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s; /* Animation for the edit form */
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        th {
            background-color: #343a40;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1; /* Highlight row on hover */
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Dashboard</h2>
        <form method="POST" style="display: inline;">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>
    <div class="container">
        <h3 class="text-center">Selamat datang, <?php echo htmlspecialchars($username); ?>!</h3> <!-- Menampilkan nama pengguna -->
        
        <form method="POST" class="text-center">
            <input type="text" name="name" placeholder="Item Name" required class="form-control mb-2">
            <textarea name="description" placeholder="Item Description" required class="form-control mb-2"></textarea>
            <button type="submit" name="add" class="btn btn-primary">Add Item</button>
        </form>
        
        <h3 class="text-center">Items List</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th> <!-- Kolom untuk nomor urut -->
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td> <!-- Menampilkan nomor urut -->
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="edit" class="btn btn-warning btn-sm">Edit</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($itemToEdit): ?>
        <div class="edit-form">
            <h3>Edit Item</h3>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $itemToEdit['id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($itemToEdit['name']); ?>" required class="form-control mb-2">
                <textarea name="description" required class="form-control mb-2"><?php echo htmlspecialchars($itemToEdit['description']); ?></textarea>
                <button type="submit" name="update" class="btn btn-success">Update Item</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
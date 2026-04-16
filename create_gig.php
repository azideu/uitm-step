<?php
// create_gig.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Only students can create gigs
if ($_SESSION['role'] !== 'student') {
    redirect('index.php');
}

// Fetch available tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $selected_tags = $_POST['tags'] ?? [];

    $errors = [];
    if (empty($title) || empty($description) || empty($category)) {
        $errors[] = "Please fill all required fields.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be a positive number.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert Gig
            $stmt = $pdo->prepare("INSERT INTO gigs (seller_id, title, description, price, category, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category]);
            $gig_id = $pdo->lastInsertId();

            // Insert Tags into Junction Table
            if (!empty($selected_tags)) {
                $stmt_tag = $pdo->prepare("INSERT INTO gig_tags (gig_id, tag_id) VALUES (?, ?)");
                foreach ($selected_tags as $tag_id) {
                    $stmt_tag->execute([$gig_id, $tag_id]);
                }
            }

            $pdo->commit();
            set_toast('success', "Gig created successfully!");
            redirect('user_dashboard.php?mode=selling');
        } catch (\Exception $e) {
            $pdo->rollBack();
            set_toast('error', "Failed to create gig.");
            error_log($e->getMessage());
        }
    } else {
        set_toast('error', implode("<br>", $errors));
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-uitmPurple">Create a New Gig</h2>
    
    <form action="create_gig.php" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Gig Title</label>
            <input type="text" name="title" required placeholder="I will do..." class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Category</label>
            <select name="category" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple bg-white">
                <option value="Tech">Tech / Programming</option>
                <option value="Creative">Creative / Design</option>
                <option value="Writing">Writing / Translation</option>
                <option value="Education">Education / Tutoring</option>
                <option value="Admin">Admin / Data Entry</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Price (RM)</label>
            <input type="number" step="0.01" min="0.01" name="price" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Description</label>
            <textarea name="description" rows="5" required placeholder="Describe your service in detail..." class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple"></textarea>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Tags (Optional)</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                <?php foreach($tags as $tag): ?>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['tag_id']; ?>" class="rounded border-gray-300 text-uitmPurple focus:ring-uitmPurple">
                        <span class="ml-2 text-sm text-gray-700"><?php echo escape($tag['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="flex justify-between items-center">
            <a href="user_dashboard.php?mode=selling" class="text-gray-600 hover:text-gray-900 font-medium">Cancel</a>
            <button type="submit" class="bg-uitmPurple text-white font-bold py-2 px-6 rounded hover:bg-purple-900 transition shadow">Publish Gig</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>

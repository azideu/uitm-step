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
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect('create_gig.php');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $selected_tags = $_POST['tags'] ?? [];

    $youtube_url = trim($_POST['youtube_url'] ?? '');

    $errors = [];
    if (empty($title) || empty($description) || empty($category)) {
        $errors[] = "Please fill all required fields.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be a positive number.";
    }

    // Handle Image Upload
    $image_url = null;
    if (isset($_FILES['gig_image']) && $_FILES['gig_image']['error'] === UPLOAD_ERR_OK) {
        require_once 'includes/storage.php';
        
        $tmp_path = $_FILES['gig_image']['tmp_name'];
        $file_size = $_FILES['gig_image']['size'];
        
        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = "Image size exceeds 5MB limit.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_path);
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime_type, $allowed_mimes)) {
                $errors[] = 'Invalid file type. Only JPG, PNG, and WEBP allowed.';
            } else {
                $ext = 'jpg';
                if ($mime_type === 'image/png') $ext = 'png';
                if ($mime_type === 'image/webp') $ext = 'webp';
                
                $new_filename = uniqid('gig_', true) . '.' . $ext;
                $uploaded_path = Storage::upload($tmp_path, 'gigs/' . $new_filename, $mime_type);
                
                if ($uploaded_path) {
                    $image_url = $uploaded_path;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert Gig
            $stmt = $pdo->prepare("INSERT INTO gigs (seller_id, title, description, price, category, status, image_url, youtube_url) VALUES (?, ?, ?, ?, ?, 'active', ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category, $image_url, $youtube_url]);
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
            error_log("Gig Creation Error: " . $e->getMessage());
            set_toast('error', "Database Error: " . $e->getMessage());
        }
    } else {
        set_toast('error', implode("<br>", $errors));
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300">
    <h2 class="text-2xl font-bold mb-6 text-uitmPurple dark:text-purple-300 font-serif">Create a New Gig</h2>
    
    <form action="create_gig.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Gig Title</label>
            <input type="text" name="title" required placeholder="I will do..." class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Category</label>
            <select name="category" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
                <option value="Tech">Tech / Programming</option>
                <option value="Creative">Creative / Design</option>
                <option value="Writing">Writing / Translation</option>
                <option value="Education">Education / Tutoring</option>
                <option value="Admin">Admin / Data Entry</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Price (RM)</label>
            <input type="number" step="0.01" min="0.01" name="price" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Description</label>
            <textarea name="description" rows="5" required placeholder="Describe your service in detail..." class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all"></textarea>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Gig Image (Optional)</label>
            <input type="file" name="gig_image" accept="image/jpeg, image/png, image/webp" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
            <p class="text-sm text-gray-500 mt-1">Upload a catchy image for your gig. Max 5MB (JPG, PNG, WEBP).</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">YouTube Video URL (Optional)</label>
            <input type="url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
            <p class="text-sm text-gray-500 mt-1">Embed a YouTube video showcasing your service.</p>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Tags (Optional)</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <?php foreach($tags as $tag): ?>
                    <label class="inline-flex items-center bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 p-2 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['tag_id']; ?>" class="rounded border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-uitmPurple focus:ring-uitmPurple dark:focus:ring-purple-900 w-4 h-4 transition-all">
                        <span class="ml-2 text-sm text-gray-700 dark:text-slate-300 font-medium"><?php echo escape($tag['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="flex justify-between items-center mt-8">
            <a href="user_dashboard.php?mode=selling" class="text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white font-medium transition-colors">Cancel</a>
            <button type="submit" class="bg-uitmPurple text-white font-bold py-3 px-8 rounded-full hover:bg-purple-900 transition-colors shadow-md hover:shadow-lg focus:ring-4 focus:ring-uitmPurple/30">Publish Gig</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>

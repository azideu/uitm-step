<?php
// edit.php
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

$gig_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch gig and verify ownership
$stmt = $pdo->prepare("SELECT * FROM gigs WHERE gig_id = ?");
$stmt->execute([$gig_id]);
$gig = $stmt->fetch();

if (!$gig) {
    set_toast('error', 'Gig not found.');
    redirect('marketplace');
}

// Only the owner or admin can edit
if ($gig['seller_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    set_toast('error', 'You do not have permission to edit this gig.');
    redirect('marketplace');
}

// Fetch available tags
$tags = $pdo->query("SELECT * FROM tags ORDER BY name ASC")->fetchAll();

// Fetch currently selected tags for this gig
$stmt_selected_tags = $pdo->prepare("SELECT tag_id FROM gig_tags WHERE gig_id = ?");
$stmt_selected_tags->execute([$gig_id]);
$selected_tags_ids = $stmt_selected_tags->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect("edit?id=$gig_id");
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

    // Handle Image Update
    $image_url = $gig['image_url']; // Default to old image
    if (isset($_FILES['gig_image']) && $_FILES['gig_image']['error'] === UPLOAD_ERR_OK) {
        require_once '../includes/storage.php';
        
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
            
            // Update Gig
            $stmt = $pdo->prepare("UPDATE gigs SET title = ?, description = ?, price = ?, category = ?, image_url = ?, youtube_url = ? WHERE gig_id = ?");
            $stmt->execute([$title, $description, $price, $category, $image_url, $youtube_url, $gig_id]);

            // Update Tags (Delete old, Insert new)
            $stmt_del = $pdo->prepare("DELETE FROM gig_tags WHERE gig_id = ?");
            $stmt_del->execute([$gig_id]);

            if (!empty($selected_tags)) {
                $stmt_tag = $pdo->prepare("INSERT INTO gig_tags (gig_id, tag_id) VALUES (?, ?)");
                foreach ($selected_tags as $tag_id) {
                    $stmt_tag->execute([$gig_id, $tag_id]);
                }
            }

            $pdo->commit();
            set_toast('success', "Gig updated successfully!");
            redirect("details?id=$gig_id");
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Gig Update Error: " . $e->getMessage());
            set_toast('error', "Database Error: " . $e->getMessage());
        }
    } else {
        set_toast('error', implode("<br>", $errors));
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300">
    <h2 class="text-2xl font-bold mb-6 text-uitmPurple dark:text-purple-300 font-serif">Edit Your Gig</h2>
    
    <form action="edit?id=<?php echo $gig_id; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Gig Title</label>
            <input type="text" name="title" required value="<?php echo escape($gig['title']); ?>" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Category</label>
            <select name="category" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
                <?php
                $categories = ['Tech', 'Creative', 'Writing', 'Education', 'Admin', 'Other'];
                foreach ($categories as $cat) {
                    $selected = ($gig['category'] === $cat) ? 'selected' : '';
                    echo "<option value=\"$cat\" $selected>$cat</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Price (RM)</label>
            <input type="number" step="0.01" min="0.01" name="price" required value="<?php echo escape($gig['price']); ?>" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Description</label>
            <textarea name="description" rows="5" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all"><?php echo escape($gig['description']); ?></textarea>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Gig Image (Leave blank to keep current)</label>
            <?php if ($gig['image_url']): ?>
                <div class="mb-2">
                    <img src="<?php echo escape($gig['image_url']); ?>" alt="Current Image" class="w-32 h-20 object-cover rounded-lg border border-gray-200 dark:border-slate-700">
                </div>
            <?php endif; ?>
            <input type="file" name="gig_image" accept="image/jpeg, image/png, image/webp" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
            <p class="text-sm text-gray-500 mt-1">Upload a new image to replace the current one. Max 5MB (JPG, PNG, WEBP).</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">YouTube Video URL (Optional)</label>
            <input type="url" name="youtube_url" value="<?php echo escape($gig['youtube_url']); ?>" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
            <p class="text-sm text-gray-500 mt-1">Embed a YouTube video showcasing your service.</p>
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Tags (Optional)</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <?php foreach($tags as $tag): ?>
                    <?php $checked = in_array($tag['tag_id'], $selected_tags_ids) ? 'checked' : ''; ?>
                    <label class="inline-flex items-center bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 p-2 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['tag_id']; ?>" <?php echo $checked; ?> class="rounded border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-uitmPurple focus:ring-uitmPurple dark:focus:ring-purple-900 w-4 h-4 transition-all">
                        <span class="ml-2 text-sm text-gray-700 dark:text-slate-300 font-medium"><?php echo escape($tag['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="flex justify-between items-center mt-8">
            <a href="details?id=<?php echo $gig_id; ?>" class="text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white font-medium transition-colors">Cancel</a>
            <button type="submit" class="bg-uitmPurple text-white font-bold py-3 px-8 rounded-full hover:bg-purple-900 transition-colors shadow-2xl hover:shadow-2xl focus:ring-4 focus:ring-uitmPurple/30">Update Gig</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

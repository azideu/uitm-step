<?php
// marketplace.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Pagination setup
$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter setups
$campus_filter = $_GET['campus'] ?? 'local';
$tag_filter = $_GET['tag'] ?? '';
$search_query = trim($_GET['search'] ?? '');

$params = [];
$where_clauses = ["g.status = 'active'", "u.role = 'student'"];

// National vs Local Campus Filter
if ($campus_filter === 'local') {
    $where_clauses[] = "u.campus = ?";
    $params[] = $_SESSION['campus'];
}

// Tag filter
$join_tags = "";
if (!empty($tag_filter)) {
    $join_tags = "JOIN gig_tags gt ON g.gig_id = gt.gig_id JOIN tags t ON gt.tag_id = t.tag_id";
    $where_clauses[] = "t.name = ?";
    $params[] = $tag_filter;
}

// Text Search
if (!empty($search_query)) {
    $where_clauses[] = "(g.title LIKE ? OR g.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_sql = implode(' AND ', $where_clauses);

// Count total for pagination
$count_sql = "SELECT COUNT(DISTINCT g.gig_id) FROM gigs g JOIN users u ON g.seller_id = u.user_id $join_tags WHERE $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_gigs = $stmt->fetchColumn();
$total_pages = ceil($total_gigs / $limit);

// Fetch gigs
$sql = "SELECT g.*, u.name as seller_name, u.campus, u.profile_picture
        FROM gigs g
        JOIN users u ON g.seller_id = u.user_id
        $join_tags
        WHERE $where_sql
        GROUP BY g.gig_id
        ORDER BY g.created_at DESC
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gigs = $stmt->fetchAll();

// Fetch all available tags for the filter dropdown
$tags = $pdo->query("SELECT name FROM tags ORDER BY name ASC")->fetchAll();
$campus_label = (isset($_SESSION['campus']) && is_string($_SESSION['campus'])) ? $_SESSION['campus'] : '';
$campus_label = str_replace(['UiTM Kampus ', 'UiTM '], '', $campus_label);
$max_campus_label_length = 20;

if (function_exists('mb_strlen') && function_exists('mb_substr')) {
    if (mb_strlen($campus_label) > $max_campus_label_length) {
        $campus_label = mb_substr($campus_label, 0, $max_campus_label_length - 3) . '...';
    }
} elseif (strlen($campus_label) > $max_campus_label_length) {
    $campus_label = substr($campus_label, 0, $max_campus_label_length - 3) . '...';
}

require_once 'includes/header.php';
?>

<div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
    <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-uitmPurple to-indigo-600 dark:from-purple-300 dark:to-indigo-300 font-serif mb-2 transition-colors duration-300">Marketplace</h1>
        <p class="text-gray-500 dark:text-slate-400 transition-colors duration-300">Discover and hire top talent from your campus.</p>
    </div>
    
    <!-- Filter Form -->
    <form action="marketplace.php" method="GET" class="flex flex-wrap gap-3 items-center bg-white dark:bg-slate-900 p-2 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 transition-colors duration-300">
        <input type="text" name="search" placeholder="Search gigs..." value="<?php echo escape($search_query); ?>" class="px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border-transparent rounded-xl focus:ring-2 focus:ring-uitmGold focus:bg-white dark:focus:bg-slate-700 text-slate-800 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-500 transition-all outline-none w-full sm:w-auto">
        
        <!-- Prominent Toggle for Campus -->
        <div class="flex items-center bg-gray-100/80 dark:bg-slate-800/80 rounded-xl p-1 shrink-0 border border-gray-200 dark:border-slate-700 transition-colors duration-300">
            <label class="relative cursor-pointer px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $campus_filter === 'local' ? 'bg-white dark:bg-slate-700 shadow-sm text-uitmPurple dark:text-purple-300 ring-1 ring-gray-200/50 dark:ring-slate-600/50' : 'text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-slate-200'; ?>">
                <input type="radio" name="campus" value="local" class="sr-only" onchange="this.form.submit()" <?php if($campus_filter === 'local') echo 'checked'; ?>>
                <?php echo escape($campus_label); ?>
            </label>
            <label class="relative cursor-pointer px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $campus_filter === 'all' ? 'bg-white dark:bg-slate-700 shadow-sm text-uitmPurple dark:text-purple-300 ring-1 ring-gray-200/50 dark:ring-slate-600/50' : 'text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-slate-200'; ?>">
                <input type="radio" name="campus" value="all" class="sr-only" onchange="this.form.submit()" <?php if($campus_filter === 'all') echo 'checked'; ?>>
                All Campuses
            </label>
        </div>
        
        <select name="tag" class="px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border-transparent rounded-xl focus:ring-2 focus:ring-uitmGold focus:bg-white dark:focus:bg-slate-700 text-slate-800 dark:text-slate-200 transition-all outline-none cursor-pointer max-w-[150px]">
            <option value="">All Tags</option>
            <?php foreach ($tags as $t): ?>
                <option value="<?php echo escape($t['name']); ?>" <?php if($tag_filter === $t['name']) echo 'selected'; ?>><?php echo escape($t['name']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="bg-gradient-to-r from-uitmPurple to-indigo-900 text-white px-6 py-2.5 rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-300 font-bold">Filter</button>
    </form>
</div>

<?php if (count($gigs) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php $delay = 0; ?>
        <?php foreach ($gigs as $gig): ?>
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-500 flex flex-col overflow-hidden border border-gray-100 dark:border-slate-800 transform hover:-translate-y-2 animate-fade-in-up" style="animation-delay: <?php echo $delay; ?>ms;">
                <!-- Thumbnail Image -->
                <?php
                    $cat = strtolower($gig['category']);
                    $thumb = 'assets/img/cat_programming.jpg'; // fallback
                    if (strpos($cat, 'design') !== false) $thumb = 'assets/img/cat_design.jpg';
                    elseif (strpos($cat, 'video') !== false) $thumb = 'assets/img/cat_video.jpg';
                    elseif (strpos($cat, 'writing') !== false || strpos($cat, 'essay') !== false || strpos($cat, 'proofreading') !== false) $thumb = 'assets/img/cat_writing.jpg';
                    elseif (strpos($cat, 'programming') !== false || strpos($cat, 'tech') !== false) $thumb = 'assets/img/cat_programming.jpg';
                ?>
                <div class="h-48 w-full bg-gray-200 relative overflow-hidden group">
                    <img src="<?php echo $thumb; ?>" alt="<?php echo escape($gig['category']); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-3 left-4">
                        <span class="text-xs font-bold text-white uppercase tracking-widest bg-uitmPurple/80 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow-sm border border-white/10"><?php echo escape($gig['category']); ?></span>
                    </div>
                </div>

                <div class="p-6 flex-grow flex flex-col">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 leading-snug hover:text-uitmPurple dark:hover:text-purple-300 transition-colors cursor-pointer font-serif line-clamp-2"><?php echo escape($gig['title']); ?></h3>
                    <p class="text-gray-500 dark:text-slate-400 mb-6 line-clamp-3 leading-relaxed"><?php echo escape($gig['description']); ?></p>
                    
                    <div class="text-sm text-gray-600 dark:text-slate-400 flex items-center bg-gray-50 dark:bg-slate-800 p-2 rounded-lg border border-gray-100 dark:border-slate-700 mt-auto transition-colors duration-300">
                        <?php
                            $seller_avatar = !empty($gig['profile_picture']) 
                                ? escape($gig['profile_picture']) 
                                : 'https://ui-avatars.com/api/?name=' . urlencode($gig['seller_name']) . '&background=330066&color=FFD700';
                        ?>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-uitmPurple to-indigo-600 flex items-center justify-center text-white font-bold mr-3 overflow-hidden shrink-0 border-2 border-white shadow-sm ring-2 ring-purple-100">
                            <img src="<?php echo $seller_avatar; ?>" alt="<?php echo escape($gig['seller_name']); ?>" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <span class="block font-bold text-gray-900 dark:text-white text-sm"><?php echo escape($gig['seller_name']); ?></span>
                            <span class="flex items-center text-xs text-gray-500 dark:text-slate-500 mt-0.5 font-medium">
                                <svg class="w-3.5 h-3.5 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                <?php echo escape(str_replace(['UiTM Kampus ', 'UiTM '], '', $gig['campus'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50/50 dark:bg-slate-950/50 px-6 py-5 mt-auto border-t border-gray-100 dark:border-slate-800 flex items-center justify-between transition-colors duration-300">
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-400 dark:text-slate-500 uppercase font-bold tracking-wider mb-1">Starting at</span>
                        <span class="text-xl font-extrabold text-gray-900 dark:text-white">RM <?php echo number_format($gig['price'], 2); ?></span>
                    </div>
                    <a href="gig_details.php?id=<?php echo $gig['gig_id']; ?>" class="text-sm bg-gray-900 dark:bg-slate-800 hover:bg-uitmPurple dark:hover:bg-purple-600 text-white px-5 py-2.5 rounded-xl font-bold transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">View Details</a>
                </div>
            </div>
            <?php $delay += 100; ?>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-8 flex justify-center">
        <nav class="inline-flex rounded-xl shadow-sm -space-x-px" aria-label="Pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&campus=<?php echo urlencode($campus_filter); ?>&tag=<?php echo urlencode($tag_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="relative inline-flex items-center px-4 py-2 rounded-l-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Previous</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&campus=<?php echo urlencode($campus_filter); ?>&tag=<?php echo urlencode($tag_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="relative inline-flex items-center px-4 py-2 border <?php echo $i === $page ? 'border-purple-300 dark:border-purple-500/30 bg-purple-50 dark:bg-uitmPurple/30 text-uitmPurple dark:text-purple-300 font-bold z-10' : 'border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors'; ?> text-sm">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&campus=<?php echo urlencode($campus_filter); ?>&tag=<?php echo urlencode($tag_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="relative inline-flex items-center px-4 py-2 rounded-r-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Next</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Empty State -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 text-center py-16 px-4 animate-fade-in-up transition-colors duration-300">
        <svg class="w-16 h-16 text-gray-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="text-2xl font-bold text-gray-700 dark:text-slate-300 mb-2">No Gigs Found</h3>
        <p class="text-gray-500 dark:text-slate-500">We couldn't find any gigs in this campus or category. Try adjusting your filters!</p>
        <a href="marketplace.php" class="mt-4 inline-block text-uitmPurple dark:text-purple-400 hover:underline font-medium">Clear all filters</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

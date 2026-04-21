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
$sql = "SELECT g.*, u.name as seller_name, u.campus
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
$campus_label = $_SESSION['campus'] ?? '';
$max_campus_label_length = 30;

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
        <h1 class="text-4xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-uitmPurple to-indigo-600 font-serif mb-2">Marketplace</h1>
        <p class="text-gray-500">Discover and hire top talent from your campus.</p>
    </div>
    
    <!-- Filter Form -->
    <form action="marketplace.php" method="GET" class="flex flex-wrap gap-3 items-center bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
        <input type="text" name="search" placeholder="Search gigs..." value="<?php echo escape($search_query); ?>" class="px-4 py-2.5 bg-gray-50 border-transparent rounded-xl focus:ring-2 focus:ring-uitmGold focus:bg-white transition-all outline-none w-full sm:w-auto">
        
        <select name="campus" class="px-4 py-2.5 bg-gray-50 border-transparent rounded-xl focus:ring-2 focus:ring-uitmGold focus:bg-white transition-all outline-none cursor-pointer">
            <option value="local" <?php if($campus_filter === 'local') echo 'selected'; ?>>My Campus (<?php echo escape($campus_label); ?>)</option>
            <option value="all" <?php if($campus_filter === 'all') echo 'selected'; ?>>All Campuses</option>
        </select>
        
        <select name="tag" class="px-4 py-2.5 bg-gray-50 border-transparent rounded-xl focus:ring-2 focus:ring-uitmGold focus:bg-white transition-all outline-none cursor-pointer">
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
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-500 flex flex-col overflow-hidden border border-gray-100 transform hover:-translate-y-2 animate-fade-in-up" style="animation-delay: <?php echo $delay; ?>ms;">
                <div class="p-6 flex-grow relative">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-uitmGold/20 to-transparent rounded-bl-full rounded-tr-2xl -z-10"></div>
                    <div class="text-xs font-bold text-uitmPurple uppercase tracking-widest mb-3 bg-purple-50 inline-block px-3 py-1 rounded-full"><?php echo escape($gig['category']); ?></div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3 leading-snug hover:text-uitmPurple transition-colors cursor-pointer"><?php echo escape($gig['title']); ?></h3>
                    <p class="text-gray-500 mb-6 line-clamp-3 leading-relaxed"><?php echo escape($gig['description']); ?></p>
                    
                    <div class="text-sm text-gray-600 flex items-center bg-gray-50 p-2 rounded-lg border border-gray-100 mt-auto">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-uitmPurple to-indigo-600 flex items-center justify-center text-white font-bold mr-3">
                            <?php echo strtoupper(substr($gig['seller_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <span class="block font-semibold text-gray-900"><?php echo escape($gig['seller_name']); ?></span>
                            <span class="block text-xs text-gray-500"><?php echo escape($gig['campus']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50/50 px-6 py-5 mt-auto border-t border-gray-100 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Starting at</span>
                        <span class="text-xl font-extrabold text-gray-900">RM <?php echo number_format($gig['price'], 2); ?></span>
                    </div>
                    <a href="gig_details.php?id=<?php echo $gig['gig_id']; ?>" class="text-sm bg-gray-900 hover:bg-uitmPurple text-white px-5 py-2.5 rounded-xl font-bold transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">View Details</a>
                </div>
            </div>
            <?php $delay += 100; ?>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-8 flex justify-center">
        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&campus=<?php echo urlencode($campus_filter); ?>&tag=<?php echo urlencode($tag_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&campus=<?php echo urlencode($campus_filter); ?>&tag=<?php echo urlencode($tag_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-uitmPurple font-bold bg-gray-100' : 'text-gray-700 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?>&campus=<?php echo urlencode($campus_filter); ?>&tag=<?php echo urlencode($tag_filter); ?>&search=<?php echo urlencode($search_query); ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Empty State -->
    <div class="bg-white rounded shadow text-center py-16 px-4">
        <svg class="empty-state-svg text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="text-2xl font-bold text-gray-700 mb-2">No Gigs Found</h3>
        <p class="text-gray-500">We couldn't find any gigs in this campus or category. Try adjusting your filters!</p>
        <a href="marketplace.php" class="mt-4 inline-block text-uitmPurple hover:underline font-medium">Clear all filters</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

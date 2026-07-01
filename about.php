<?php
// about.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Define Group Members array
$group_members = [
    [
        'name' => 'AWIN AMANI BINTI ADENAN',
        'position' => 'Project Manager',
        'email' => '2024419672@student.uitm.edu.my',
        'phone' => '+60 12-277 4535',
        'image' => 'assets/img/team/awin.jpg',
        'initials' => 'AA',
        'gradient' => 'from-purple-600 to-indigo-600 dark:from-purple-800 dark:to-indigo-800',
        'badge_color' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
    ],
    [
        'name' => 'ADDIN ZIDANE BIN RUZAIDY',
        'position' => 'Lead Developer',
        'email' => '2024801742@student.uitm.edu.my',
        'phone' => '+60 16-205 4072',
        'image' => 'assets/img/team/addin.jpeg',
        'initials' => 'AZ',
        'gradient' => 'from-blue-500 to-cyan-600 dark:from-blue-700 dark:to-cyan-700',
        'badge_color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
    ],
    [
        'name' => 'MUHAMMAD ZAHIN SHAH BIN NORHISAMSHAH',
        'position' => 'System Analyst',
        'email' => '2024236824@student.uitm.edu.my',
        'phone' => '+60 12-392 1940',
        'image' => 'assets/img/team/zahin.jpg',
        'initials' => 'MZ',
        'gradient' => 'from-emerald-500 to-teal-600 dark:from-emerald-700 dark:to-teal-700',
        'badge_color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
    ],
    [
        'name' => 'DANISH DANIEL',
        'position' => 'System Designer',
        'email' => '2024244864@student.uitm.edu.my',
        'phone' => '+60 11-2371 3452',
        'image' => 'assets/img/team/danish.jpg',
        'initials' => 'DD',
        'gradient' => 'from-amber-500 to-orange-600 dark:from-amber-700 dark:to-orange-700',
        'badge_color' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
    ],
];

// Helper function to render a member card for the organizational chart
function render_org_card($member, $is_leader = false) {
    $img_path = __DIR__ . '/' . $member['image'];
    $has_image = file_exists($img_path) && is_file($img_path);
    $border_class = $is_leader 
        ? 'border-uitmPurple/30 dark:border-uitmGold/30 bg-uitmPurple/5 dark:bg-purple-950/20 shadow-md ring-1 ring-uitmPurple/10 dark:ring-uitmGold/10' 
        : 'border-slate-200/60 dark:border-slate-800/85 bg-slate-50/50 dark:bg-slate-800/40 shadow-sm';
    ?>
    <div class="flex items-center space-x-4 <?php echo $border_class; ?> border rounded-xl p-4 w-72 hover:border-uitmPurple dark:hover:border-uitmGold hover:shadow-md transition-all duration-300 select-none">
        <div class="relative w-12 h-12 flex-shrink-0">
            <?php if ($has_image): ?>
                <img src="<?php echo ROOT_URL . escape($member['image']); ?>" alt="<?php echo escape($member['name']); ?>" class="w-12 h-12 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-sm relative z-10">
            <?php else: ?>
                <div class="w-12 h-12 rounded-full bg-gradient-to-br <?php echo $member['gradient']; ?> text-white flex items-center justify-center font-bold text-base shadow-sm">
                    <?php echo escape($member['initials']); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-grow min-w-0">
            <div class="font-bold text-slate-900 dark:text-white text-xs tracking-wide uppercase truncate" title="<?php echo escape($member['name']); ?>">
                <?php echo escape($member['name']); ?>
            </div>
            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                <?php echo escape($member['position']); ?>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="max-w-5xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl border border-gray-100 dark:border-slate-800 p-8 md:p-12 transition-colors duration-300">
        
        <!-- Header Section -->
        <div class="text-center mb-10 animate-fade-in-up">
            <h1 class="text-3xl md:text-4xl font-bold text-uitmPurple dark:text-purple-300 font-serif mb-4">
                Group Members' Information
            </h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto text-base">
                Meet the developers and designers behind UiTM STEP (Group 6). We are dedicated to providing a premium marketplace for UiTM students.
            </p>
        </div>

        <!-- Project Overview Info -->
        <div class="p-6 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-100 dark:border-slate-800/50 mb-10 animate-fade-in-up">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2">About UiTM STEP</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                UiTM Student Talent Engagement Platform (STEP) is an online marketplace designed to support student talent, peer collaboration, and campus commerce. This informational page profiles the four core group members who architected and built the platform.
            </p>
        </div>

        <!-- Organisational Chart Section -->
        <div class="mb-14 animate-fade-in-up">
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-8 text-center font-serif">Organisational Structure</h3>
            
            <!-- Desktop Layout (MD and up) -->
            <div class="hidden md:block">
                <!-- Top Level (Project Manager) -->
                <div class="flex justify-center">
                    <?php render_org_card($group_members[0], true); ?>
                </div>
                
                <!-- Connector Lines -->
                <div class="relative w-full h-12">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                    <div class="absolute top-6 left-[16.666%] right-[16.666%] h-0.5 bg-slate-200 dark:bg-slate-800/80"></div>
                    <div class="absolute top-6 left-[16.666%] w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                    <div class="absolute top-6 left-1/2 -translate-x-1/2 w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                    <div class="absolute top-6 right-[16.666%] w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                </div>
                
                <!-- Bottom Level (3 Members) -->
                <div class="grid grid-cols-3 gap-6">
                    <div class="flex justify-center">
                        <?php render_org_card($group_members[1]); ?>
                    </div>
                    <div class="flex justify-center">
                        <?php render_org_card($group_members[2]); ?>
                    </div>
                    <div class="flex justify-center">
                        <?php render_org_card($group_members[3]); ?>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Layout (hidden on MD and up) -->
            <div class="block md:hidden flex flex-col items-center">
                <?php render_org_card($group_members[0], true); ?>
                <div class="w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                <?php render_org_card($group_members[1]); ?>
                <div class="w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                <?php render_org_card($group_members[2]); ?>
                <div class="w-0.5 h-6 bg-slate-200 dark:bg-slate-800/80"></div>
                <?php render_org_card($group_members[3]); ?>
            </div>
        </div>

        <!-- Controls (Search) -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in-up">
            <div class="relative max-w-md w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="member-search" placeholder="Search team by name or role..." class="block w-full pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:ring-2 focus:ring-uitmPurple dark:focus:ring-uitmGold focus:border-transparent transition-all duration-300">
            </div>
            <div class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                Total Members: <span class="font-bold text-uitmPurple dark:text-purple-400">4</span>
            </div>
        </div>

        <!-- Team Table Container -->
        <div class="animate-fade-in-up">
            <div class="overflow-x-auto border border-slate-100 dark:border-slate-800/60 rounded-xl bg-white dark:bg-slate-900">
                <table class="w-full text-left border-collapse min-w-[750px]" id="members-table">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800/80 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-4 px-6 w-2/5">Member Name</th>
                            <th class="py-4 px-6 w-1/4">Contact Number</th>
                            <th class="py-4 px-6">Academic Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                        <?php foreach ($group_members as $member): ?>
                            <tr class="member-row group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all duration-200" data-name="<?php echo escape($member['name']); ?>" data-role="<?php echo escape($member['position']); ?>">
                                <!-- Member Name -->
                                <td class="py-4 px-6 font-bold text-slate-900 dark:text-white text-sm tracking-wide">
                                    <?php echo escape($member['name']); ?>
                                </td>
                                
                                <!-- Contact Number -->
                                <td class="py-4 px-6 text-sm text-slate-600 dark:text-slate-350 font-medium">
                                    <a href="tel:<?php echo escape(str_replace(' ', '', $member['phone'])); ?>" class="hover:text-uitmPurple dark:hover:text-uitmGold hover:underline transition-all duration-200 tracking-wide">
                                        <?php echo escape($member['phone']); ?>
                                    </a>
                                </td>
                                
                                <!-- Academic Email -->
                                <td class="py-4 px-6 text-sm text-slate-650 dark:text-slate-350 font-mono">
                                    <a href="mailto:<?php echo escape($member['email']); ?>" class="hover:text-uitmPurple dark:hover:text-uitmGold hover:underline transition-all duration-200">
                                        <?php echo escape($member['email']); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- No Results Row -->
                        <tr id="no-results-row" class="hidden">
                            <td colspan="3" class="py-8 text-center text-slate-400 dark:text-slate-500 font-medium">
                                No group members found matching your search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    // Real-time Search Filtering
    document.getElementById('member-search').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.member-row');
        let hasResults = false;
        
        rows.forEach(row => {
            const name = row.getAttribute('data-name').toLowerCase();
            const role = row.getAttribute('data-role').toLowerCase();
            
            if (name.includes(query) || role.includes(query)) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        const noResultsRow = document.getElementById('no-results-row');
        if (hasResults) {
            noResultsRow.classList.add('hidden');
        } else {
            noResultsRow.classList.remove('hidden');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>

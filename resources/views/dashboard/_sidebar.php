<aside class="dash-sidebar">
    <a href="/dashboard" class="<?= str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/dashboard') ? 'active' : '' ?>">Overview</a>
    <a href="/dashboard/profile" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/profile') ? 'active' : '' ?>">Profile</a>
    <a href="/dashboard/links" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/links') ? 'active' : '' ?>">Links</a>
    <a href="/dashboard/appearance" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/appearance') ? 'active' : '' ?>">Appearance</a>
    <a href="/dashboard/account" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/account') ? 'active' : '' ?>">Account</a>
    <a href="/<?= e($user['username']) ?>" target="_blank">View Profile ↗</a>
</aside>

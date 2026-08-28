<?php ob_start(); ?>
<div class="container-sm mt-8 mb-8">
    <h1 class="text-center mb-8">Features</h1>
    <div class="card mb-4">
        <h2>Custom Themes</h2>
        <p>Choose from multiple hand-crafted themes including Terminal, Corporate, and Degenerate. Override colors to make it your own.</p>
    </div>
    <div class="card mb-4">
        <h2>Visual Effects</h2>
        <p>Add some flair with particle systems, snow, scanlines, or CRT effects. (Automatically disabled for users who prefer reduced motion).</p>
    </div>
    <div class="card mb-4">
        <h2>Analytics</h2>
        <p>Track your profile views and link clicks over the last 14 days directly from your dashboard.</p>
    </div>
    <div class="card">
        <h2>Music Player</h2>
        <p>Set a custom audio track to play on your profile. Set the vibe before they even click a link.</p>
    </div>
</div>
<?php $content = ob_get_clean(); require BASE_PATH . '/resources/views/layouts/main.php';

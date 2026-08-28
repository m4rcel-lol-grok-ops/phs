<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Profile;

class DiscoverController
{
    public function index(): void
    {
        if (!env('DISCOVERY_ENABLED', true)) {
            flash('error', 'Discovery is currently disabled.');
            redirect('/');
        }
        $sort = $_GET['sort'] ?? 'popular';
        if (!in_array($sort, ['popular', 'new', 'random'], true)) $sort = 'popular';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['q'] ?? '') ?: null;

        $result = Profile::getDiscover($sort, $page, 12, $search);

        view('pages.discover', [
            'title' => 'Discover — pornhub.singles',
            'description' => 'Browse public profiles on pornhub.singles.',
            'profiles' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['per_page'],
            'sort' => $sort,
            'search' => $search,
        ]);
    }
}

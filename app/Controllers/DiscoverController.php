<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Profile;

class DiscoverController
{
    public function index(): void
    {
        if (!setting_bool('discovery_enabled', true)) {
            flash('error', 'Discovery is currently turned off.');
            redirect('/');
        }

        $sort = $_GET['sort'] ?? 'popular';
        if (!in_array($sort, ['popular', 'new', 'random'], true)) {
            $sort = 'popular';
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim((string)($_GET['q'] ?? ''));
        $search = $search !== '' ? mb_substr($search, 0, 64) : null;

        $result = Profile::getDiscover($sort, $page, 12, $search);

        // A page number past the end should show the last real page, not a
        // blank grid with working pagination underneath it.
        if ($page > $result['pages'] && $result['total'] > 0) {
            $result = Profile::getDiscover($sort, $result['pages'], 12, $search);
        }

        view('pages.discover', [
            'title' => 'Discover — ' . site_name(),
            'description' => 'Browse public profiles on ' . site_name() . '.',
            'profiles' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['per_page'],
            'totalPages' => $result['pages'],
            'sort' => $sort,
            'search' => $search,
        ]);
    }
}

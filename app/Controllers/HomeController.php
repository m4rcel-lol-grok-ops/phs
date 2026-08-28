<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        view('pages.home', [
            'title' => 'pornhub.singles — Your links deserve better.',
            'description' => 'Build your own ridiculous little corner of the internet. A completely unnecessary bio-link platform.',
        ]);
    }

    public function features(): void
    {
        view('pages.features', [
            'title' => 'Features — pornhub.singles',
            'description' => 'Themes, music, effects, stats, and more. Everything you need for an unnecessarily dramatic profile.',
        ]);
    }

    public function about(): void
    {
        view('pages.about', [
            'title' => 'About — pornhub.singles',
            'description' => 'Parody & Independence Notice. pornhub.singles is an independent humor project.',
        ]);
    }

    public function contentPolicy(): void
    {
        view('pages.content-policy', [
            'title' => 'Content Policy — pornhub.singles',
            'description' => 'Content disclosure and rules for pornhub.singles.',
        ]);
    }

    public function privacy(): void
    {
        view('pages.privacy', [
            'title' => 'Privacy Policy — pornhub.singles',
        ]);
    }

    public function terms(): void
    {
        view('pages.terms', [
            'title' => 'Terms of Service — pornhub.singles',
        ]);
    }

    public function contact(): void
    {
        view('pages.contact', [
            'title' => 'Contact — pornhub.singles',
        ]);
    }
}

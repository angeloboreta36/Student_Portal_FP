<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';
require_once __DIR__ . '/auth.php';

$pageTitle = isset($page_title) ? $page_title . ' | PCNL Portal' : 'PCNL - Student Information & Grade Portal';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/pcnl-logo.png') ?>">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-brand-50: #f0f9ff;
            --color-brand-100: #e0f2fe;
            --color-brand-200: #bae6fd;
            --color-brand-300: #7dd3fc;
            --color-brand-400: #38bdf8;
            --color-brand-500: #0284c7;
            --color-brand-600: #1d4ed8;
            --color-brand-700: #1e40af;
            --color-brand-800: #1e3a8a;
            --color-brand-900: #0f172a;

            --color-pcnl-cyan: #00d2ff;
            --color-pcnl-blue: #1d4ed8;
            --color-pcnl-dark: #0f172a;
            --color-pcnl-gold: #f59e0b;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body class="min-h-screen text-slate-800 flex flex-col bg-slate-50 antialiased">

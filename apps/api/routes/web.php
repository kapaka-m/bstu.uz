<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $startedAt = microtime(true);
    $connectionName = config('database.default');
    $connection = config("database.connections.{$connectionName}", []);
    $databaseConnected = false;
    $databaseMessage = 'Connection unavailable';

    try {
        DB::connection($connectionName)->getPdo();
        $databaseConnected = true;
        $databaseMessage = 'Database connection established';
    } catch (Throwable $exception) {
        report($exception);
        $databaseMessage = 'No connection';
    }

    $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
    $databaseHost = $connection['host'] ?? $connection['url'] ?? 'local';
    $databasePort = $connection['port'] ?? null;
    $databaseName = $connection['database'] ?? null;
    $driver = $connection['driver'] ?? $connectionName;
    $statusLabel = $databaseConnected ? 'Connected' : 'Disconnected';
    $statusClass = $databaseConnected ? 'is-up' : 'is-down';
    $dotTitle = $databaseConnected ? 'Database is connected' : 'Database is not connected';
    $localEndpoint = trim((string) config('app.url'), '/') ?: request()->getSchemeAndHttpHost();
    $localTarget = $databaseHost.($databasePort ? ':'.$databasePort : '');

    $connectionName = e($connectionName);
    $driver = e($driver);
    $localTarget = e($localTarget);
    $databaseName = e($databaseName);
    $localEndpoint = e($localEndpoint);
    $databaseMessage = e($databaseMessage);
    $statusLabel = e($statusLabel);
    $statusClass = e($statusClass);
    $dotTitle = e($dotTitle);

    return response()->make(<<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BSTU API</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f8fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #667085;
            --border: #e5e7eb;
            --shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
            --green: #16a34a;
            --red: #dc2626;
            --blue: #1d4ed8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .status-card {
            width: min(560px, 100%);
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: var(--card);
            box-shadow: var(--shadow);
        }

        .eyebrow {
            margin: 0 0 10px;
            color: var(--blue);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 5vw, 42px);
            line-height: 1.05;
        }

        .summary {
            margin: 12px 0 24px;
            color: var(--muted);
            font-size: 15px;
        }

        .panel {
            display: grid;
            gap: 14px;
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #fbfcfe;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .status-name {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
            background: var(--red);
            box-shadow: 0 0 0 5px rgba(220, 38, 38, 0.12);
        }

        .is-up .dot {
            background: var(--green);
            box-shadow: 0 0 0 5px rgba(22, 163, 74, 0.13);
        }

        .status-pill {
            padding: 7px 10px;
            border-radius: 999px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 13px;
            font-weight: 700;
        }

        .is-up .status-pill {
            background: #dcfce7;
            color: #166534;
        }

        dl {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 10px 14px;
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        dt {
            font-weight: 700;
            color: #475467;
        }

        dd {
            margin: 0;
            word-break: break-word;
        }

        code {
            padding: 2px 6px;
            border-radius: 6px;
            background: #eef2ff;
            color: #3730a3;
        }
    </style>
</head>
<body>
    <main class="status-card">
        <p class="eyebrow">BSTU Laravel API</p>
        <h1>Application up</h1>
        <p class="summary">HTTP request received. Response rendered in {$responseTime}ms.</p>

        <section class="panel {$statusClass}" aria-label="Database status">
            <div class="status-row">
                <div class="status-name">
                    <span class="dot" title="{$dotTitle}" aria-hidden="true"></span>
                    <span>Database</span>
                </div>
                <span class="status-pill">{$statusLabel}</span>
            </div>

            <dl>
                <dt>Connection</dt>
                <dd><code>{$connectionName}</code></dd>
                <dt>Driver</dt>
                <dd><code>{$driver}</code></dd>
                <dt>Local</dt>
                <dd><code>{$localTarget}</code></dd>
                <dt>Database</dt>
                <dd><code>{$databaseName}</code></dd>
                <dt>API URL</dt>
                <dd><code>{$localEndpoint}</code></dd>
                <dt>Status</dt>
                <dd>{$databaseMessage}</dd>
            </dl>
        </section>
    </main>
</body>
</html>
HTML);
});

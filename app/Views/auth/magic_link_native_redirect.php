<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open SLAMS Mobile</title>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f7f5;
            color: #122c20;
        }

        .card {
            width: min(92vw, 420px);
            background: #ffffff;
            border: 1px solid #dbe8e1;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 18px 44px rgba(18, 44, 32, 0.10);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 1.5rem;
        }

        p {
            margin: 0 0 16px;
            line-height: 1.55;
            color: #355446;
        }

        .actions {
            display: grid;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 48px;
            padding: 0 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-primary {
            background: #0d604d;
            color: #ffffff;
        }

        .btn-secondary {
            border: 1px solid #b8d1c6;
            color: #0d604d;
            background: #eff8f4;
        }

        .small {
            font-size: 0.9rem;
            color: #5f7b70;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Open SLAMS Mobile</h1>
        <p>
            Your secure sign-in link is ready. If the app is installed, it should open automatically so you can finish signing in.
        </p>
        <p class="small">
            This one-time sign-in link still expires in <?= esc((string) ceil(setting('Auth.magicLinkLifetime') / MINUTE)) ?> minutes.
        </p>

        <div class="actions">
            <a class="btn btn-primary" href="<?= esc($appUrl) ?>">Open SLAMS Mobile</a>
            <a class="btn btn-secondary" href="<?= esc($fallbackUrl) ?>">Continue in browser instead</a>
        </div>
    </main>

    <script>
    (function () {
        const appUrl = <?= json_encode($appUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        if (!appUrl) {
            return;
        }

        window.location.replace(appUrl);
    })();
    </script>
</body>
</html>

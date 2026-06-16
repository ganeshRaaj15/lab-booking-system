<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 &ndash; Page Not Found &mdash; SLAMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d1a15;
            font-family: 'DM Sans', sans-serif;
            color: #c8d8d2;
            padding: 2rem;
        }
        .wrap {
            max-width: 480px;
            text-align: center;
        }
        .code {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(5rem, 18vw, 9rem);
            font-weight: 800;
            line-height: 1;
            color: transparent;
            background: linear-gradient(135deg, #20c7b4, #0f766e);
            -webkit-background-clip: text;
            background-clip: text;
            letter-spacing: -0.04em;
        }
        h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #e6f2ee;
            margin: 0.75rem 0 0.5rem;
        }
        p {
            font-size: 0.95rem;
            opacity: 0.7;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #20c7b4;
            opacity: 0.7;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        a.btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1.35rem;
            border-radius: 12px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        a.btn:hover { opacity: 0.82; }
        a.btn-primary {
            background: linear-gradient(135deg, #20c7b4, #0f766e);
            color: #07241d;
        }
        a.btn-ghost {
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.05);
            color: #c8d8d2;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">&#x25C6;&nbsp; SLAMS</div>
        <div class="code">404</div>
        <h1><?= lang('Errors.pageNotFound') ?></h1>
        <p>The page you are looking for does not exist or has been moved. Try going back to the home page.</p>
        <div class="actions">
            <a href="/" class="btn btn-primary">Go to Home</a>
            <a href="javascript:history.back()" class="btn btn-ghost">Go Back</a>
        </div>
    </div>
</body>
</html>

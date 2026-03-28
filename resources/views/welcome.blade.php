<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>saliksic API</title>
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; color: #334155; }
            .container { text-align: center; }
            h1 { font-size: 2rem; margin-bottom: 0.5rem; }
            p { color: #64748b; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>saliksic API</h1>
            <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
        </div>
    </body>
</html>

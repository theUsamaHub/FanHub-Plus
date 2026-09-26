<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $newsletter->subject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .container { background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .logo { color: #FF922E; font-size: 24px; font-weight: bold; }
        .content { font-size: 16px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #888; }
        .unsubscribe { color: #888; text-decoration: none; }
        .button { display: inline-block; background: #FF922E; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">FanHub+</div>
        </div>

        <div class="content">
            {!! $newsletter->body !!}
        </div>

        <div class="footer">
            <p>You're receiving this because you subscribed to FanHub+ updates.</p>
            <p>
                <a href="{{ $unsubscribeUrl }}" class="unsubscribe">Unsubscribe</a>
                | <a href="{{ url('/') }}" class="unsubscribe">Visit FanHub+</a>
            </p>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to HobbyHub</title>
</head>
<body style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;background:#f5f7fa;margin:0;padding:32px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.05);">
        <tr>
            <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:48px 32px;text-align:center;color:#fff;">
                <h1 style="margin:0;font-size:28px;">Welcome, {{ $name }}! 🎉</h1>
                <p style="margin:8px 0 0;opacity:.9;">Your hobby journey starts here.</p>
            </td>
        </tr>
        <tr>
            <td style="padding:32px;">
                <p style="color:#374151;font-size:16px;line-height:1.6;">
                    We're thrilled to have you in the HobbyHub community. Here are three quick steps to get started:
                </p>
                <ol style="color:#374151;font-size:15px;line-height:1.8;padding-left:20px;">
                    <li><strong>Complete your profile</strong> so others can find you.</li>
                    <li><strong>Pick your interests</strong> — we'll match you with the right groups.</li>
                    <li><strong>Join a group</strong> or create one if it doesn't exist yet.</li>
                </ol>
                <div style="text-align:center;margin:32px 0;">
                    <a href="{{ $profileUrl }}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:14px 28px;border-radius:10px;font-weight:600;">Complete Your Profile</a>
                </div>
                <p style="color:#6b7280;font-size:14px;text-align:center;">
                    Or <a href="{{ $discoverUrl }}" style="color:#4f46e5;">discover groups</a> right now.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;background:#f9fafb;text-align:center;color:#9ca3af;font-size:12px;">
                You're receiving this because you signed up at {{ config('app.name') }}.
            </td>
        </tr>
    </table>
</body>
</html>
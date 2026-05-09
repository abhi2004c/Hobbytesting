<!DOCTYPE html>
<html><body style="font-family:sans-serif;background:#f5f7fa;padding:32px;">
<table style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;">
    <tr><td>
        <h2 style="color:#111827;">You're invited!</h2>
        <p style="color:#374151;line-height:1.6;">
            <strong>{{ $inviter->name }}</strong> invited you to join <strong>{{ $group->name }}</strong> on HobbyHub.
        </p>
        @if($message)
        <blockquote style="border-left:4px solid #4f46e5;padding-left:16px;color:#4b5563;font-style:italic;">
            {{ $message }}
        </blockquote>
        @endif
        <div style="text-align:center;margin:32px 0;">
            <a href="{{ $acceptUrl }}" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Accept Invitation</a>
        </div>
        <p style="color:#9ca3af;font-size:13px;">This link expires in 7 days.</p>
    </td></tr>
</table>
</body></html>
@if(session('gnosis_impersonating'))
<div style="
    background: #7c3aed;
    color: white;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    position: sticky;
    top: 0;
    z-index: 9999;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
">
    <span>
        🛡️ <strong>Gnosis Managed Access</strong>
        &mdash; You are managing
        <strong>{{ session('gnosis_impersonated_school') }}</strong>
    </span>

    <a
        href="{{ session('gnosis_return_url') }}"
        style="
            background: white;
            color: #7c3aed;
            padding: 5px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
        "
    >
        ← Return to Super Admin
    </a>
</div>
@endif

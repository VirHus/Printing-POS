<style>
    :root {
        --brand-accent: {{ $branding['accent_color'] }};
        --color-teal-600: var(--brand-accent);
        --color-teal-500: color-mix(in srgb, var(--brand-accent) 85%, white);
        --color-teal-400: color-mix(in srgb, var(--brand-accent) 65%, white);
        --color-teal-300: color-mix(in srgb, var(--brand-accent) 40%, white);
        --color-teal-200: color-mix(in srgb, var(--brand-accent) 25%, white);
        --color-teal-700: color-mix(in srgb, var(--brand-accent) 85%, black);
        --color-teal-800: color-mix(in srgb, var(--brand-accent) 65%, black);
        --color-teal-900: color-mix(in srgb, var(--brand-accent) 40%, black);
        --color-teal-950: color-mix(in srgb, var(--brand-accent) 25%, black);
    }
    .brand-icon, .avatar, .logo { background: linear-gradient(135deg, var(--brand-accent), color-mix(in srgb, var(--brand-accent) 75%, black)); }
    .menu a.active { background: var(--brand-accent); border-color: var(--brand-accent); }
    .menu a:hover { background: color-mix(in srgb, var(--brand-accent) 20%, transparent); }
    .content .btn-primary, .content .btn:not(.btn-gray):not(.btn-red):not(.btn-danger):not(.btn-secondary):not(.btn-success):not(.btn-warning), .login-button { background: var(--brand-accent); }
    .content .btn-primary:hover, .login-button:hover { background: color-mix(in srgb, var(--brand-accent) 85%, black); }
    .content input:focus, .content select:focus, .content textarea:focus, .login-form input:focus { border-color: var(--brand-accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-accent) 20%, transparent); }
    .brand-text { min-width: 0; }
    .brand-text h2, .brand-text span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .brand-icon { flex-shrink: 0; overflow: hidden; }
    .brand-icon img, .logo img { width: 100%; height: 100%; object-fit: contain; }
    .content { max-width: {{ $branding['content_width'] === 'full' ? 'none' : '1720px' }}; }
    @media (min-width: 751px) {
        .sidebar { width: {{ $branding['sidebar_style'] === 'compact' ? '220px' : '250px' }}; }
        .main { margin-left: {{ $branding['sidebar_style'] === 'compact' ? '220px' : '250px' }}; width: calc(100% - {{ $branding['sidebar_style'] === 'compact' ? '220px' : '250px' }}); }
        @if($branding['sidebar_style'] === 'compact')
            .brand { padding-inline: 16px; }
            .menu a { padding-block: 10px; }
        @endif
    }
</style>

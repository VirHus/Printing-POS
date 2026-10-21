<style>
    :root {
        --brand-accent: {{ $branding['accent_color'] }};
        --system-background: {{ $branding['background_color'] }};
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
    @media screen {
        body.glass-theme {
            font-family: {{ ['instrument' => 'Instrument Sans, ui-sans-serif, system-ui, sans-serif', 'system' => 'ui-sans-serif, system-ui, sans-serif', 'serif' => 'Georgia, serif'][$branding['font_family']] }};
            background: radial-gradient(ellipse at top right, color-mix(in srgb, var(--brand-accent) 16%, transparent), transparent 60%), var(--system-background);
        }
        body.glass-theme .content { padding: {{ ['compact' => '16px', 'comfortable' => '24px', 'spacious' => '32px'][$branding['spacing']] }}; }
        body.glass-theme .content .dashboard-panel { padding: {{ ['compact' => '16px', 'comfortable' => '24px', 'spacious' => '32px'][$branding['spacing']] }}; }
        @if($branding['font_size'] !== 'standard')
            body.glass-theme .content { font-size: {{ $branding['font_size'] === 'small' ? '12px' : '16px' }}; }
            body.glass-theme .text-sm, body.glass-theme .content input, body.glass-theme .content select, body.glass-theme .content textarea, body.glass-theme .content td { font-size: {{ $branding['font_size'] === 'small' ? '12px' : '16px' }}; }
            body.glass-theme .text-xs { font-size: {{ $branding['font_size'] === 'small' ? '10px' : '14px' }}; }
        @endif
        @if($branding['spacing'] !== 'comfortable')
            body.glass-theme .content td { padding-block: {{ $branding['spacing'] === 'compact' ? '10px' : '22px' }}; }
            body.glass-theme .content .glass-surface, body.glass-theme .dashboard-widget .dashboard-card { padding: {{ $branding['spacing'] === 'compact' ? '16px' : '28px' }}; }
        @endif
        @if($branding['corner_style'] === 'square')
            body.glass-theme .content .rounded-2xl, body.glass-theme .content .rounded-xl, body.glass-theme .dashboard-panel, body.glass-theme .glass-surface, body.glass-theme .service-card { border-radius: 4px; }
        @endif
        @if($branding['theme'] === 'solid')
            body.glass-theme .sidebar, body.glass-theme .topbar, body.glass-theme .content .glass-surface, body.glass-theme .content .dashboard-card, body.glass-theme .content .dashboard-panel, body.glass-theme .login-container, body.glass-theme .modal-box, body.glass-theme .service-card, body.glass-theme .card, body.glass-theme .stat-card, body.glass-theme .glass-shortcut {
                -webkit-backdrop-filter: none;
                backdrop-filter: none;
                background: color-mix(in srgb, var(--system-background) 90%, white);
                box-shadow: none;
            }
        @endif
        @media (max-width: 750px) {
            body.glass-theme .content, body.glass-theme .content .dashboard-panel { padding: 12px; }
        }
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

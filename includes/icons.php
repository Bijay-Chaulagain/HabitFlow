<?php
/**
 * SVG Icon System
 * Habit Tracker Web Application
 *
 * Lightweight inline SVG icon set (no external libraries).
 * Icons are line-style, inherit currentColor via stroke="currentColor".
 */

const LEGACY_ICON_PATHS = [
    'logo'    => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
    'home'    => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    'target'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
    'plus'    => '<path d="M12 5v14"/><path d="M5 12h14"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
    'chart'   => '<path d="M3 20V4"/><path d="M3 20h18"/><path d="M8 20v-6"/><path d="M13 20V8"/><path d="M18 20v-9"/>',
    'user'    => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'users'   => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    'tags'    => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
    'file'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h8"/>',
    'sun'     => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
    'moon'    => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
    'check'   => '<path d="M20 6 9 17l-5-5"/>',
    'check-circle' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
    'edit'    => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    'archive' => '<rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="M10 12h4"/>',
    'trash'   => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    'arrow-left'  => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
    'arrow-right' => '<path d="m5 12 7 7 7-7"/><path d="M5 12h14"/>',
    'flame'   => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
    'trophy'  => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
    'clock'   => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'bell'    => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
    'repeat'  => '<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>',
    'trending' => '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
];

/**
 * Render an inline SVG icon.
 *
 * @param string $name      Icon key from LEGACY_ICON_PATHS
 * @param int    $size      Width/height in pixels
 * @param string $className Extra CSS classes appended to "icon"
 * @param string $ariaLabel Accessible label (omit for aria-hidden)
 * @return string Empty string if icon name is unknown.
 */
function icon(string $name, int $size = 18, string $className = '', string $ariaLabel = ''): string {
    $paths = LEGACY_ICON_PATHS;
    if (!isset($paths[$name])) {
        return '';
    }

    $iconClass = 'icon' . ($className !== '' ? ' ' . $className : '');
    $aria = $ariaLabel !== ''
        ? 'role="img" aria-label="' . htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') . '"'
        : 'aria-hidden="true" focusable="false"';

    return '<svg class="' . $iconClass . '" width="' . $size . '" height="' . $size
        . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"'
        . ' stroke-linecap="round" stroke-linejoin="round" ' . $aria . '>'
        . $paths[$name]
        . '</svg>';
}
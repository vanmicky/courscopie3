<?php
/**
 * Jeu d'icônes SVG.
 *
 * Toutes les icônes sont dessinées dans une grille 24x24, en traits, sans
 * remplissage : elles prennent donc la couleur du texte (`currentColor`) et
 * suivent automatiquement la couleur de la matière.
 *
 * Le sprite est inséré une seule fois en bas de page (`icon_sprite()`), ce qui
 * évite toute requête supplémentaire : les `<use href="#i-...">` pointent vers
 * des symboles présents dans le même document.
 */

/** Tracés des icônes : nom => contenu du <symbol>. */
function icon_paths(): array {
    return [
        // --- Navigation et matières -------------------------------------
        'book'      => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A1.5 1.5 0 0 1 5.5 19H19v2H5.5A1.5 1.5 0 0 1 4 20.5z"/>',
        'tools'     => '<path d="M14.7 6.3a4 4 0 1 0 5 5L21 21l-2 .5L9.5 11.5"/><path d="M6.5 3.5 9 6l-2 2-2.5-2.5A2 2 0 0 1 6.5 3.5z"/>',
        'flask'     => '<path d="M9 3h6"/><path d="M10 3v6.5L5.5 18A2 2 0 0 0 7.3 21h9.4a2 2 0 0 0 1.8-3L14 9.5V3"/><path d="M7.5 15h9"/>',
        'calculator' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 11h2M12 11h2M16 11h.01M8 15h2M12 15h2M16 15h.01"/>',
        'code'      => '<path d="m9 8-4 4 4 4M15 8l4 4-4 4"/>',
        // --- Contenus de chapitre ---------------------------------------
        'file'      => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>',
        'pencil'    => '<path d="M4 20h4l10-10a2.5 2.5 0 0 0-3.5-3.5L4.5 16.5z"/><path d="M13.5 6.5 17.5 10.5"/>',
        'award'     => '<circle cx="12" cy="9" r="5"/><path d="m8.5 13.5-1.5 7.5 5-2.5 5 2.5-1.5-7.5"/>',
        'cap'       => '<path d="m3 9 9-4 9 4-9 4z"/><path d="M7 11v5c0 1.5 2.2 3 5 3s5-1.5 5-3v-5"/>',
        'video'     => '<rect x="3" y="6" width="12" height="12" rx="2"/><path d="m15 10 6-3v10l-6-3z"/>',
        'check'     => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.5 2.5L16 9.5"/>',
        'cards'     => '<rect x="3" y="6" width="12" height="14" rx="2"/><path d="M8 3h9a2 2 0 0 1 2 2v12"/>',
        'target'    => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3.5"/>',
        // --- Outils de classe -------------------------------------------
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/>',
        'users'     => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 5.5a3 3 0 0 1 0 5.6M17.5 20a5.5 5.5 0 0 0-2-4.3"/>',
        'dice'      => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 9h.01M15 9h.01M12 12h.01M9 15h.01M15 15h.01"/>',
        'sign'      => '<path d="M4 5h13l3 3-3 3H4z"/><path d="M12 11v10"/><path d="M9 21h6"/>',
        'activity'  => '<path d="M3 12h4l3-7 4 14 3-7h4"/>',
        // --- Interface ---------------------------------------------------
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-up'    => '<path d="M12 19V5M6 11l6-6 6 6"/>',
        'printer'     => '<path d="M7 9V3h10v6"/><rect x="4" y="9" width="16" height="8" rx="2"/><path d="M7 15h10v6H7z"/>',
        'copy'        => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4V4h11v1"/>',
        'expand'      => '<path d="M4 9V4h5M20 15v5h-5M15 4h5v5M9 20H4v-5"/>',
        'sun'         => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon'        => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
        'calendar'    => '<rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'search'      => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'download'    => '<path d="M12 4v11M7.5 10.5 12 15l4.5-4.5"/><path d="M4.5 19.5h15"/>',
        'chart'       => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'home'        => '<path d="m4 11 8-7 8 7"/><path d="M6.5 9.5V20h11V9.5"/>',
        'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'lock'        => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'close'       => '<path d="M6 6l12 12M18 6 6 18"/>',
        'star'        => '<path d="m12 4 2.4 5 5.6.8-4 4 1 5.6-5-2.7-5 2.7 1-5.6-4-4 5.6-.8z"/>',
        'lightbulb'   => '<path d="M9.5 18h5"/><path d="M10 21h4"/><path d="M12 3a6 6 0 0 0-3.5 10.8V18h7v-4.2A6 6 0 0 0 12 3z"/>',
    ];
}

/**
 * Une icone en ligne.
 *
 * @param string $name  Nom dans le jeu d'icones.
 * @param string $class Classe CSS supplementaire.
 * @param int    $size  Taille en pixels (carre).
 */
function icon(string $name, string $class = '', int $size = 20): string {
    if (!isset(icon_paths()[$name])) return '';
    $cls = trim('icon ' . $class);
    return '<svg class="' . h($cls) . '" width="' . $size . '" height="' . $size
        . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
        . '<use href="#i-' . h($name) . '"></use></svg>';
}

/** Le sprite complet, insere une seule fois en bas de page. */
function icon_sprite(): string {
    $symboles = '';
    foreach (icon_paths() as $nom => $traces) {
        $symboles .= '<symbol id="i-' . h($nom) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
            . ' stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' . $traces . '</symbol>';
    }
    return '<svg class="icon-sprite" width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute;overflow:hidden">'
        . $symboles . '</svg>';
}

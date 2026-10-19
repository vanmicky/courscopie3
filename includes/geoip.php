<?php
/**
 * Géolocalisation des visiteurs, hors ligne.
 *
 * Le fichier `data/geoip-country.bin` contient un code pays (2 lettres) pour
 * chacun des 65 536 blocs IPv4 /16. Il est construit à partir du jeu de données
 * public GeoLite2 Country (licence CC BY-SA) et pèse 128 Ko : aucune requête
 * vers un service externe, aucune adresse IP envoyée à un tiers.
 *
 * Régénération : voir `tools/build-geoip.php`.
 */

declare(strict_types=1);

const GEOIP_FICHIER = __DIR__ . '/../data/geoip-country.bin';

/** Noms français des pays les plus fréquents (repli : le code ISO). */
function geoip_noms(): array {
    return [
        'FR' => 'France', 'BE' => 'Belgique', 'CH' => 'Suisse', 'CA' => 'Canada',
        'LU' => 'Luxembourg', 'MC' => 'Monaco', 'DE' => 'Allemagne', 'ES' => 'Espagne',
        'IT' => 'Italie', 'PT' => 'Portugal', 'GB' => 'Royaume-Uni', 'IE' => 'Irlande',
        'NL' => 'Pays-Bas', 'AT' => 'Autriche', 'SE' => 'Suède', 'NO' => 'Norvège',
        'DK' => 'Danemark', 'FI' => 'Finlande', 'PL' => 'Pologne', 'CZ' => 'Tchéquie',
        'RO' => 'Roumanie', 'GR' => 'Grèce', 'TR' => 'Turquie', 'RU' => 'Russie',
        'UA' => 'Ukraine', 'US' => 'États-Unis', 'MX' => 'Mexique', 'BR' => 'Brésil',
        'AR' => 'Argentine', 'CL' => 'Chili', 'CO' => 'Colombie', 'PE' => 'Pérou',
        'MA' => 'Maroc', 'DZ' => 'Algérie', 'TN' => 'Tunisie', 'EG' => 'Égypte',
        'SN' => 'Sénégal', 'CI' => "Côte d'Ivoire", 'CM' => 'Cameroun', 'ML' => 'Mali',
        'BF' => 'Burkina Faso', 'BJ' => 'Bénin', 'TG' => 'Togo', 'GA' => 'Gabon',
        'CD' => 'Congo (RDC)', 'CG' => 'Congo', 'MG' => 'Madagascar', 'MU' => 'Maurice',
        'RE' => 'La Réunion', 'GP' => 'Guadeloupe', 'MQ' => 'Martinique', 'GF' => 'Guyane',
        'YT' => 'Mayotte', 'CN' => 'Chine', 'JP' => 'Japon', 'KR' => 'Corée du Sud',
        'IN' => 'Inde', 'SG' => 'Singapour', 'HK' => 'Hong Kong', 'TW' => 'Taïwan',
        'VN' => 'Viêt Nam', 'TH' => 'Thaïlande', 'ID' => 'Indonésie', 'MY' => 'Malaisie',
        'PH' => 'Philippines', 'PK' => 'Pakistan', 'BD' => 'Bangladesh', 'AU' => 'Australie',
        'NZ' => 'Nouvelle-Zélande', 'ZA' => 'Afrique du Sud', 'NG' => 'Nigéria',
        'KE' => 'Kenya', 'IL' => 'Israël', 'SA' => 'Arabie saoudite', 'AE' => 'Émirats arabes unis',
        'IR' => 'Iran', 'IQ' => 'Irak', 'SY' => 'Syrie', 'LB' => 'Liban',
        'GE' => 'Géorgie', 'AM' => 'Arménie', 'AZ' => 'Azerbaïdjan', 'KZ' => 'Kazakhstan',
        'GH' => 'Ghana', 'ET' => 'Éthiopie', 'TZ' => 'Tanzanie', 'UG' => 'Ouganda',
        'RW' => 'Rwanda', 'SD' => 'Soudan', 'LY' => 'Libye', 'MZ' => 'Mozambique',
        'LT' => 'Lituanie', 'LV' => 'Lettonie', 'EE' => 'Estonie', 'BY' => 'Biélorussie',
        'SK' => 'Slovaquie', 'HU' => 'Hongrie', 'BG' => 'Bulgarie', 'HR' => 'Croatie',
        'RS' => 'Serbie', 'SI' => 'Slovénie', 'IS' => 'Islande', 'MT' => 'Malte',
    ];
}

/**
 * Code pays (2 lettres) d'une adresse IPv4.
 *
 * Renvoie « LOCAL » pour un réseau privé, « ZZ » si l'adresse est inconnue ou
 * si le fichier de données est absent.
 */
function geoip_code(string $ip): string {
    // IPv6 : le réseau local et la boucle locale sont reconnus, le reste n'est
    // pas couvert par la table (qui ne contient que l'IPv4).
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $ip = (string)$ip;
        if ($ip === '::1' || $ip === '::' || stripos($ip, 'fe80:') === 0
            || preg_match('/^f[cd][0-9a-f]{2}:/i', $ip)) {
            return 'LOCAL';
        }
        // Adresse IPv4 encapsulée : ::ffff:90.110.1.1
        if (preg_match('/^::ffff:(\d+\.\d+\.\d+\.\d+)$/i', $ip, $m)) {
            $ip = $m[1];
        } else {
            return 'ZZ';
        }
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return 'ZZ';
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return 'LOCAL';
    }
    static $poignee = null;
    if ($poignee === null) {
        $poignee = is_file(GEOIP_FICHIER) ? fopen(GEOIP_FICHIER, 'rb') : false;
    }
    if (!$poignee) {
        return 'ZZ';
    }
    $entier = ip2long($ip);
    if ($entier === false) {
        return 'ZZ';
    }
    $bloc = ($entier >> 16) & 0xFFFF;
    if (fseek($poignee, $bloc * 2) !== 0) {
        return 'ZZ';
    }
    $code = fread($poignee, 2);
    if ($code === false || strlen($code) !== 2 || $code === "\0\0") {
        return 'ZZ';
    }
    return strtoupper($code);
}

/** Nom lisible d'un code pays. */
function geoip_nom(string $code): string {
    if ($code === 'LOCAL') return 'Réseau local';
    if ($code === 'ZZ' || $code === '') return 'Inconnu';
    $noms = geoip_noms();
    return $noms[$code] ?? $code;
}

/** Drapeau emoji d'un code pays (repli : globe). */
function geoip_drapeau(string $code): string {
    if (strlen($code) !== 2 || !ctype_alpha($code) || $code === 'ZZ') return '🌍';
    $base = 0x1F1E6;
    $a = $base + (ord(strtoupper($code[0])) - ord('A'));
    $b = $base + (ord(strtoupper($code[1])) - ord('A'));
    return mb_chr($a, 'UTF-8') . mb_chr($b, 'UTF-8');
}

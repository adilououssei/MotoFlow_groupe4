<?php
/* ============================================================
   fonctions.php — Fonctions utilitaires (VERSION STATIQUE)
   ============================================================ */

function mf_htmlspecialchars($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function mf_number_format($number, $decimals = 0, $dec_point = ',', $thousands_sep = ' ') {
    return number_format($number, $decimals, $dec_point, $thousands_sep);
}

function mf_date($format, $timestamp = null) {
    if ($timestamp === null) {
        return date($format);
    }
    return date($format, $timestamp);
}

function mf_strtotime($time) {
    return strtotime($time);
}

function mf_substr($string, $start, $length = null) {
    if ($length === null) {
        return substr($string, $start);
    }
    return substr($string, $start, $length);
}

function mf_strtoupper($string) {
    return strtoupper($string);
}
?>
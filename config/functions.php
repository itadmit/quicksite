<?php

/**
 * בודק אם מחרוזת היא JSON תקין
 * 
 * @param mixed $string המחרוזת לבדיקה
 * @return bool האם המחרוזת היא JSON תקין
 */
function isJson($string) {
    if (!is_string($string)) return false;
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
} 
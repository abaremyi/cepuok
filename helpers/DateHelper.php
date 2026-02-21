<?php
/**
 * Date Helper Functions
 * File: helpers/DateHelper.php
 */

if (!function_exists('time_elapsed_string')) {
    /**
     * Convert datetime to time elapsed string (e.g., "2 hours ago")
     * @param string $datetime MySQL datetime
     * @param bool $full Whether to show full details
     * @return string Time elapsed string
     */
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
        
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date in readable format
     * @param string $date MySQL date/datetime
     * @param string $format PHP date format
     * @return string Formatted date
     */
    function format_date($date, $format = 'M d, Y') {
        if (empty($date)) return '—';
        return date($format, strtotime($date));
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime in readable format
     * @param string $datetime MySQL datetime
     * @return string Formatted datetime
     */
    function format_datetime($datetime) {
        if (empty($datetime)) return '—';
        return date('M d, Y H:i', strtotime($datetime));
    }
}
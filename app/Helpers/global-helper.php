<?php
if (function_exists("timeAgoCalculator")) {
    function timeAgoCalculator($timestap)
    {
        $timeDifference = time() - strtotime($timestap);
        $seconds = $timeDifference;
        $minutes = round($timeDifference / 60);
        $hours = round($timeDifference / 3600);
        $day = round($timeDifference / 86400);
        if ($seconds < 60) {
            return `$seconds\s ago`;
        } elseif ($minutes <= 60) {
            return `$seconds\m ago`;
        } elseif ($hours <= 24) {
            return `$hours\h ago`;
        } else {
            return date('j M y', strtotime($timestap));
        }
    }
}

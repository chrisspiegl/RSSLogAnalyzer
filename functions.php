<?php
/**
 * functions.php
 *
 * @author Christoph Spiegl <chris@chrissp.com>
 * @package default
 */


$data = array();
$analyzedData = array();


/**
 *
 */
function processLog() {
    global $data;

    readLog();
    calcLog();
}


/**
 *
 */
function readLog() {
    global $data;

    $lines = file(LOG_FILE);
    $i = 0;
    $data = array();
    foreach ($lines as $line) {
        $i++;
        trim_value($line);
        if (substr($line, 0, 1) != "#") {
            $entries = explode("|", $line);
            for ($j = 1; $j < count($entries); $j++) {
                if (isset($entries[$j]) && $entries[$j] != "" ) {
                    trim_value($entries[$j]);
                    $entry = explode("/", $entries[$j]);
                    $sitename = $entry[0];
                    unset($entry[0]);
                    $data[$sitename][$entries[0]] = $entry;
                }
            }
        }
    }
}


/**
 *
 */
function calcLog() {
    global $data, $analyzedData;

    $date7daysAgo = mktime(0, 0, 0, date('m'), date('d') - 7, date("Y"));

    foreach ($data as $key => $val) {
        $maxOverall = 0;
        $maxOverallDay = 0;
        $max7day = 0;
        $max7dayDay = 0;
        $avgOverall = 0;
        $avg7day = 0;
        $countDays = 0;

        foreach ($val as $k=>$v) {
            $avgOverall += $v[4];
            if ($v[4] > $maxOverall) {
                $maxOverall = $v[4];
                $maxOverallDay = $k;
            }
            if ($date7daysAgo < strtotime($k)) {
                $countDays++;
                $avg7day += $v[4];
                if ($v[4] > $max7day) {
                    $max7day = $v[4];
                    $max7dayDay = $k;
                }
            }
        }

        $analyzedData[$key] = array(
            'maxOverall' => $maxOverall,
            'maxOverallDay' => $maxOverallDay,
            'max7day' => $max7day,
            'max7dayDay' => $max7dayDay,
            'avgOverall' => $avgOverall/count($val),
            'avg7day' => $avg7day/$countDays
        );
    }
}


/**
 *
 *
 * @param unknown $value (reference)
 */
function trim_value(&$value) {
    $value = trim($value);
}


/**
 * Display arrays() in a readable way
 *
 * @param unknown $var
 * @param unknown $bPrint (optional)
 * @return unknown
 */
function printr($var, $bPrint=true) {
    ob_start();
    if (is_array($var)) { echo '<pre>'; print_r($var); echo '</pre>';
    }else echo $var;
    $sMessage = ob_get_contents();
    ob_end_clean();
    if ($bPrint == true) echo $sMessage;
    return ($bPrint == true) ? true : $sMessage;
}

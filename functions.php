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
    global $data, $siteAggregators;

    $lines = file(LOG_FILE);
    $i = 0;
    $data = array();
    $siteAggregators = array();
    $date7daysAgo = mktime(0, 0, 0, date('m'), date('d') - 7, date("Y"));
    $datesInCalculation = array();

    foreach ($lines as $line) {
        trim_value($line);
        if (substr($line, 0, 1) != "#") {
            $entries = unserialize($line);
            $date = $entries['date'];
            foreach ($entries['data'] as $sitename => $stats) {
                $totalSubs = 0;
                $aggregators = array();
                // If the date is already calculated do not add it
                if ( ! isset($datesInCalculation[$sitename][date("d.m.y", $date)])) {
                    $datesInCalculation[$sitename][date("d.m.y", $date)] = true;

                    foreach ($stats as $value) {
                        $aggregators[$value['user_agent']] = $value['total_subs'];
                        if ($date7daysAgo < $date) {
                            if ( ! isset($siteAggregators[$sitename][$value['user_agent']])) {
                                $siteAggregators[$sitename][$value['user_agent']] = $value['total_subs'];
                            } else {
                                $siteAggregators[$sitename][$value['user_agent']] += $value['total_subs'];
                            }
                        }

                        $totalSubs += $value['total_subs'];
                    }

                    $data[$sitename][$date] = array(
                        'total_subs' => $totalSubs,
                        'aggregators' => $aggregators
                    );
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

    foreach ($data as $sitename => $pageData) {
        $maxOverall = 0;
        $maxOverallDay = 0;
        $max7day = 0;
        $max7dayDay = 0;
        $avgOverall = 0;
        $avg7day = 0;
        $countDays = 0;

        foreach ($pageData as $date=>$stats) {
            $totalSubs = $stats['total_subs'];

            $avgOverall += $totalSubs;
            if ($totalSubs > $maxOverall) {
                $maxOverall = $totalSubs;
                $maxOverallDay = $date;
            }
            if ($date7daysAgo < $date) {
                $countDays++;
                $avg7day += $totalSubs;
                if ($totalSubs > $max7day) {
                    $max7day = $totalSubs;
                    $max7dayDay = $date;
                }
            }
        }

        $analyzedData[$sitename] = array(
            'maxOverall' => $maxOverall,
            'maxOverallDay' => $maxOverallDay,
            'max7day' => $max7day,
            'max7dayDay' => $max7dayDay,
            'avgOverall' => $avgOverall/count($pageData),
            'avg7day' => ($avg7day > 0) ? $avg7day/$countDays : 0
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

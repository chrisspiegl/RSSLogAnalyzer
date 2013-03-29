<?php
/**
 * index.php
 *
 * @author Christoph Spiegl <chris@chrissp.com>
 * @package default
 */


ob_start();
require_once realpath(dirname(__FILE__)) . '/config.php';
ob_end_clean();

if (! isset($_SERVER['PHP_AUTH_USER']) ||
    ! isset($_SERVER['PHP_AUTH_PW']) ||
    ! isset($API_AUTH[$_SERVER['PHP_AUTH_USER']]) ||
    sha1($_SERVER['PHP_AUTH_PW']) != $API_AUTH[$_SERVER['PHP_AUTH_USER']][0]
) {
    header('WWW-Authenticate: Basic realm="RSS Stats"');
    header('HTTP/1.0 401 Unauthorized');
    echo "<h1>You are not allowed in here</h1><p>".$_SERVER['PHP_AUTH_USER']." ".$_SERVER['PHP_AUTH_PW'];
    exit;
}

processLog();
?>

<html>
<head>
    <title>RSS Stats</title>
    <link rel='stylesheet' href='assets/style.css' type='text/css' media='all' />
</head>
<body>

<header>
    <h1>RSS Stats</h1>
</header>
<?php
if(! isset($_GET['details']) || empty($_GET['details'])){
?>

<table>
    <tr>
        <td>Domain</td>
        <td>Max Overall</td>
        <td>Max 7 Days</td>
        <td>Avg Overall</td>
        <td>Avg 7 Day</td>
        <td></td>
    </tr>
    <?php foreach($analyzedData as $page=>$val): ?>
        <?php if(preg_match($API_AUTH[$_SERVER['PHP_AUTH_USER']][1], $page)): ?>
        <tr>
            <td><?=$page; ?></td>
            <td><abbr title="<?=$val['maxOverallDay']; ?>"><?=$val['maxOverall']; ?></abbr></td>
            <td><abbr title="<?=$val['max7dayDay']; ?>"><?=$val['max7day']; ?></abbr></td>
            <td><?=sprintf('%5.02f', round($val['avgOverall'],2)); ?></td>
            <td><?=sprintf('%5.02f', round($val['avg7day'],2)); ?></td>
            <td><a href="?details=<?=$page; ?>">Details</a></td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

<?php
}else if( isset($_GET['details']) && ! empty($_GET['details']) && preg_match($API_AUTH[$_SERVER['PHP_AUTH_USER']][1], $_GET['details'])){
?>

<h2>Stats for: <?=$_GET['details']; ?> <small><a href="http://<?=$_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];?>">Overview</a></small></h2>

<?php

$reversed = array_reverse($data[$_GET['details']]);

?>

<table>
    <tr>
        <td>Date</td>
        <td>Google</td>
        <td>Aggregators</td>
        <td>Direct</td>
        <td>Overall</td>
    </tr>
    <?php
    $i = 30;
    foreach($reversed as $row=>$rowVal){
        $i--;
        if($i <= 0) break;
        $max = '';
        if($row == $analyzedData[$_GET['details']]['maxOverallDay']) $max = 'maxOverall';
        elseif($row == $analyzedData[$_GET['details']]['max7dayDay']) $max = 'max7';
    ?>
    <tr class="<?=$max; ?>">
        <td><?=$row; ?></td>
        <td><?=$rowVal[1]; ?></td>
        <td><?=$rowVal[2]; ?></td>
        <td><?=$rowVal[3]; ?></td>
        <td><?=$rowVal[4]; ?></td>
    </tr>
    <?php } ?>
</table>
<?php
}
?>
</body>
</html>
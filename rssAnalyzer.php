<?php /*

    rssAnalyzer.php

    Based on a script created by Marco Arment.

    Usage: Pipe an Apache access log into stdin, e.g.:
        php -f rssAnalyzer.php < /var/log/httpd/access_log

    It's up to you whether you want to restrict its input to certain date ranges.
    In theory, it doesn't actually matter very much, and you may need a span of
    a few days to include everyone.

    Output looks something like this:

    28644   TOTAL
    8993    31.40%  + NewsBlur
    4632    16.17%  = NetNewsWire
    2766    9.66%   + Feed Wrangler
    1431    5.00%   = Reeder
    1366    4.77%   = Stringer
    1235    4.31%   = Fever
    ...

    Each user-agent is prefixed with a '+' or '=':
        + : This user-agent reports subscribers, so we're using that count instead of IPs.
        = : This user-agent doesn't report subscribers, so unique IPs are counted.

    Note that Google Reader is NOT included by default, since while its crawler is
    still running at the time of writing, nobody is seeing its results.

*/

require(DOC_ROOT . '/config.php');

function normalize_user_agent_string($user_agent)
{
    static $user_agent_replacements = array(
        // regex  =>  replacement
        '/^Feedfetcher-Google.*$/' => 'Google Reader',
        '/^NewsBlur .*$/' => 'NewsBlur',
        '/^Feedly.*$/' => 'Feedly',
        '/^Feed Wrangler.*$/' => 'Feed Wrangler',
        '/^Fever.*$/' => 'Fever',
        '/^AolReader.*$/' => 'AOL Reader',
        '/^FeedHQ.*$/' => 'FeedHQ',
        '/^BulletinFetcher.*$/' => 'Bulletin',
        '/^Digg (Feed )?Fetcher.*$/' => 'Digg',
        '/^Bloglovin.*$/' => 'Bloglovin',
        '/^InoReader.*$/' => 'InoReader',
        '/^Xianguo.*$/' => 'Xianguo',
        '/^HanRSS.*$/' => 'HanRSS',
        '/^FeedBlitz.*$/' => 'FeedBlitz',
        '/^Feedshow.*$/' => 'Feedshow',
        '/^FeedSync.*$/' => 'FeedSync',
        '/^Slickreader Feed Fetcher.*$/' => 'Slickreader',
        '/^NetNewsWire.*$/' => 'NetNewsWire',
        '/^NewsGatorOnline.*$/' => 'NewsGator',
        '/^FeedDemon\/.*$/' => 'FeedDemon',
        '/^Netvibes.*$/' => 'Netvibes',
        '/^livedoor FeedFetcher.*$/' => 'livedoor',
        '/^Superfeedr.*$/' => 'Superfeedr',
        '/^g2reader-bot.*$/' => 'g2reader',
        '/^Feedbin - .*$/' => 'Feedbin',
        '/^CurataRSS.*$/' => 'CurataRSS',
        '/^Reeder.*$/' => 'Reeder',
        '/^Sleipnir.*$/' => 'Sleipnir',
        '/^BlogshelfII.*$/' => 'BlogshelfII',
        '/^Caffeinated.*$/' => 'Caffeinated',
        '/^RSSOwl\/.*$/' => 'RSSOwl',
        '/^NewsFire\/.*$/' => 'NewsFire',
        '/^NewsLife\/.*$/' => 'NewsLife',
        '/^Vienna.*$/' => 'Vienna',
        '/^Lector;.*$/' => 'Lector',
        '/^Sylfeed.*$/' => 'Sylfeed',
        '/^Status(%20)?Board.*$/' => 'StatusBoard',
        '/^curl\/.*$/' => 'curl',
        '/^Wget\/.*$/' => 'wget',
        '/^rss2email\/.*$/' => 'rss2email',
        '/^Python-urllib\/.*$/' => 'Python',
        '/^feedzirra .*$/' => 'feedzira',
        '/^newsbeuter.*$/' => 'newsbeuter',
        '/^Leselys.*$/' => 'Leselys',
        '/^Java\/.*$/' => 'Java',
        '/^Jakarta.*$/' => 'Java',
        '/^Apache-HttpClient\/.*[Jj]ava.*$/' => 'Java',
        '/^Ruby\/.*$/' => 'Ruby',
        '/^PHP\/.*$/' => 'PHP',
        '/^Zend.*Http.*$/' => 'PHP',
        '/^Leaf\/.*$/' => 'Leaf',
        '/^lire\/.*$/' => 'lire',
        '/^SimplePie.*$/' => 'SimplePie',
        '/^ReadKit.*$/' => 'ReadKit',
        '/^NewsRack.*$/' => 'NewsRack',
        '/^Pulp\/.*$/' => 'Pulp',
        '/^Liferea\/.*$/' => 'Liferea',
        '/^TBRSS\/.*$/' => 'TBRSS',
        '/^SushiReader\/.*$/' => 'SushiReader',
        '/^Akregator\/.*$/' => 'Akregator',
        '/^Mozilla\/5\.0 \(Sage\)$/' => 'Sage',
        '/^Tiny Tiny RSS.*$/' => 'Tiny Tiny RSS',
        '/^FreeRSSReader.*$/' => 'FreeRSSReader',
        '/^Yahoo Pipes.*$/' => 'Yahoo Pipes',
        '/^WordPress.*$/' => 'WordPress',
        '/^FeedBurner\/.*$/' => 'FeedBurner',
        '/^Dreamwith Studios.*$/' => 'Dreamwith Studios',
        '/^LiveJournal.*$/' => 'LiveJournal',
        '/^Apple-PubSub.*$/' => 'Apple PubSub',
        '/^Multiplexer\.me.*$/' => 'Multiplexer.me',
        '/^Microsoft Office.*$/' => 'Microsoft Office',
        '/^Windows-RSS-Platform.*$/' => 'Windows RSS',
        '/^.*FriendFeedBot\/.*$/' => 'FriendFeed',
        '/^.*Yahoo! Slurp.*$/' => 'Yahoo! Slurp',
        '/^.*YahooFeedSeekerJp.*$/' => 'YahooFeedSeekerJp',
        '/^.*YoudaoFeedFetcher\/.*$/' => 'Youdao',
        '/^.*PushBot\/.*$/' => 'PushBot',
        '/^.*FeedBooster\/.*$/' => 'FeedBooster',
        '/^.*Squider\/.*$/' => 'Squider',
        '/^.*Downcast\/.*$/' => 'Downcast',
        '/^.*Instapaper\/.*$/' => 'Instapaper',
        '/^.*Thunderbird\/.*$/' => 'Mozilla Thunderbird',
        '/^.*Flipboard(Proxy|RSS).*$/' => 'Flipboard',
        '/^.*Genieo.*$/' => 'Genieo',
        '/^.*Hivemined.*$/' => 'Hivemined',
        '/^.*theoldreader.com.*$/' => 'The Old Reader',
        '/^.*AppEngine-Google.*appid: s~(.*?)\)$/' => '\1 (Google App Engine)',
        '/^.*Googlebot\/.*$/' => 'Googlebot',
        '/^.*UniversalFeedParser\/.*$/' => 'UniversalFeedParser',
        '/^.*Opera.*$/' => 'Opera',
        '#^Mozilla/.* AppleWebKit.*? \(KHTML, like Gecko\) Version/.*? Safari/[^ ]*$#' => 'Safari',
        '#^Mozilla/.* AppleWebKit.*? \(KHTML, like Gecko\)$#' => 'Safari',
        '#^Mozilla/.* AppleWebKit.*? \(KHTML, like Gecko\) AdobeAIR/[^ ]*$#' => 'Adobe AIR',
        '#^Mozilla/.* Gecko/[^ ]* Firefox/[^ ]* Firefox/[^ ]*$#' => 'Firefox',
        '#^Mozilla/.* Gecko/[^ ]* Firefox/[^ ]*$#' => 'Firefox',
        '#^Mozilla/.* Gecko/[^ ]* Firefox/[^ ]* \(\.NET.*?\)$#' => 'Firefox',
        '#^Mozilla/.* AppleWebKit/.*? \(KHTML, like Gecko\) Chrome/.*? Safari/[^ ]*$#' => 'Chrome',
        '#^Mozilla/.* \(compatible; MSIE.*?\)$#' => 'MSIE',
        '#^Mozilla/.*$#' => 'Mozilla (other)',
        '/^([^\/]+)\/[\.0-9]+ CFNetwork[^ ]* Darwin[^ ]*$/' => '\1',
        '/^([^\/]+)\/[\.0-9]+$/' => '\1',
    );

    $user_agent = preg_replace(array_keys($user_agent_replacements), array_values($user_agent_replacements), $user_agent);
    return $user_agent;
}

function sortArray(&$data, $field)
{
    if (!is_array($field)) $field = array($field);

    usort($data, function($a, $b) use($field)
    {
        $retval = 0;
        foreach ($field as $fieldname) {
            if ($retval == 0) $retval = strnatcmp($a[$fieldname], $b[$fieldname]);
        }
        return $retval;
    });
    return $data;
}

function print_all_stats(&$output_uas, $echo = true){
    ob_start();
    foreach($output_uas as $url => $url_totals){
        $site = print_stats($url, $output_uas, false);
        echo $site;
        echo "\n\n=======================================\n\n";
    }
    $output = ob_get_clean();
    if($echo) echo $output;
    return $output;
}

function print_stats($url, &$output_uas, $echo = true) {
    global $minimum_subscribers_to_display, $include_google_reader;

    ob_start();
    if (! $include_google_reader) unset($output_uas[$url]['+ Google Reader']);
    $total = 0;
    foreach($output_uas[$url] as $ua => $ua_total){
        $total += $ua_total['total_subs'];
    }
    echo "$total\tTOTAL for $url\n_______________________________________\n";

    if ($total > 0) {
        $output_uas[$url] = sortArray($output_uas[$url], 'total_subs');
        $output_uas[$url] = array_reverse($output_uas[$url]);
        foreach ($output_uas[$url] as $ua => $ua_total) {
            if ($ua_total['total_subs'] < $minimum_subscribers_to_display) break;
            $display_pct = number_format(100 * $ua_total['total_subs'] / $total, 2);
            printf("%5d\t%10s%%\t%s %s\n", $ua_total['total_subs'], $display_pct, $ua_total['prefix'], $ua_total['user_agent']);
        }
    }

    $output = ob_get_clean();

    if($echo) echo $output;
    return $output;
}

/* Procedural Code: % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % *** % */
// PROCESSING:
date_default_timezone_set('Europe/Berlin');
$date_1day_ago = date('d/M/o', strtotime('-1 days'));
$date_2day_ago = date('d/M/o', strtotime('-2 days'));

$feed_uris = array_flip($feed_uris);
$domains_data = array();
while (false !== ($line = fgets(STDIN)) ) {
    // Parse IP, URI, User-Agent from Apache common log line
    //                  IP         GET     /uri       UA
    if (preg_match('/([A-z]+.[A-z]+):([.0-9]+) ([.0-9]+) - - \[((' . preg_quote($date_1day_ago, '/') . ')|(' . preg_quote($date_2day_ago, '/') . ')).*?"[A-Z]+ ([^ ]+) .*"(.*?)"$/', $line, $matches)) {
        $url = $matches[1];
        $ip = $matches[3];
        $uri = $matches[7];
        $user_agent = $matches[8];
    } else continue;
    // Skip requests for URIs we don't care about
    if (! isset($feed_uris[$uri])) continue;

    $user_agent_nice = normalize_user_agent_string($user_agent);

    // Parse "X subscriber[s]", "X reader[s]"
    $subscribers = false;
    if (preg_match('/([0-9]+) subscribers?/i', $user_agent, $matches) ||
        preg_match('/([0-9]+) readers?/i', $user_agent, $matches)
    ) {
        $subscribers = $matches[1];
        $user_agent = str_replace($matches[0], '$SUBS$', $user_agent);
        $user_agent_nice = $user_agent;
    }

    // Parse "feed-id=X", "feedid: X"
    $feed_id = false;
    if (preg_match('/feed-id=([0-9]+)/i', $user_agent, $matches) ||
        preg_match('/feedid: ([0-9]+)/i', $user_agent, $matches)
    ) {
        $feed_id = $matches[1];
        $user_agent = str_replace($matches[0], '$ID$', $user_agent);
        $user_agent_nice = $user_agent;
    }

    if (! isset($domains_data[$url][$user_agent_nice])) $domains_data[$url][$user_agent_nice] = array('_direct' => array());

    if ($subscribers === false) {
        $domains_data[$url][$user_agent_nice]['_direct'][$ip] = 1;
    } else {
        $domains_data[$url][$user_agent_nice][$feed_id === false ? '-' : $feed_id] = intval($subscribers);
    }
}

// NORMALIZING AND COALESCING:
$output_uas = array();
foreach($domains_data as $url => $url_data){
    foreach ($url_data as $user_agent => $feed_ids) {
        $total_subs = count($feed_ids['_direct']);
        unset($feed_ids['_direct']);
        $is_reporting_multiple_subs = count($feed_ids) > 0;
        $total_subs += array_sum($feed_ids);
        $feed_ids = $total_subs;

        // Prefix UA with whether this feed represents multiple subscribers (+) or direct IPs (=)
        if (false !== strpos($user_agent, '$SUBS$')) {
            $prefix = '+ ';
        } else {
            $prefix = '= ';
        }

        $output_ua = $prefix . normalize_user_agent_string($user_agent);

        if (isset($output_uas[$url][$output_ua]['total_subs']))
            $output_uas[$url][$output_ua]['total_subs'] += $total_subs;
        else $output_uas[$url][$output_ua] = array(
                'user_agent' => $user_agent,
                'prefix' => $prefix,
                'total_subs' => $total_subs,
            );
    }

}
ksort($output_uas);

// MAIL AND DISPLAYING:
print_all_stats($output_uas);


if( $email ){
    sendMail($email_full_stats, '[' . date('o-m-d', time()) . '] RSS Stats', print_all_stats($output_uas, false));
    foreach($email_stats_per_domain as $domain => $email){
        sendMail($email, '[' . date('o-m-d', time()) . '] RSS Stats for ' . $domain, print_stats($domain, $output_uas, false));
    }
}


$handle = fopen(LOG_FILE, 'a');
flock($handle, LOCK_EX);

fputs($handle, serialize(
    array(
        'date' => time(),
        'data' => $output_uas,
    )) . "\n");

flock($handle, LOCK_UN);
fclose($handle);

function sendMail($to, $subject, $body) {
    require_once 'swiftmail/lib/swift_required.php';
    global $email_smtp, $email_from;
    $transport = Swift_SmtpTransport::newInstance($email_smtp['host'], $email_smtp['port'], $email_smtp['auth'])->setUsername($email_smtp['username'])->setPassword($email_smtp['password']);
    $mailer = Swift_Mailer::newInstance($transport);
    $message = Swift_Message::newInstance($subject)
      ->setFrom(array($email_from => 'RSS-Analyzer'))
      ->setTo(array($to))
      ->setBody($body);

    $result = $mailer->send($message);
}
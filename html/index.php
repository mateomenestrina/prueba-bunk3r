<?php

require_once '../vendor/autoload.php';

$app = new \Slim\Slim(array('mode' => 'production', 'debug' => false ));
$app->config('debug', true);

$loader = new Twig_Loader_Filesystem('../templates');
$twig = new Twig_Environment($loader, array());

$db = new PDO('mysql:host=localhost;port=3306;dbname=bunker', 'root', 'bunker', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET SESSION query_cache_type = OFF;"));
define('REPORT_START', '2014-08-22'); //for some reason let's consider the report is taken from this date onwards

$app->get('/', function () use ($twig) {
    echo $twig->render('index.twig');
});

$app->get('/chart', function () use ($twig, $db, $app) {




    $stmt = $db->prepare("SELECT tracking_type, tracking_term FROM twitter_tracking");
    $stmt->execute();

    $data = array('hashtags' => array(), 'mentions' => array(), 'users' => array());

    foreach ($stmt->fetchAll() as $track) {


        if ($track['tracking_type'] == 'hashtag')
        {

            $stmt = $db->prepare("SELECT DATE(twitter_created_at_datetime) d, count(1) q FROM twitter_tweets JOIN twitter_tweet_entities on twitter_tweets.tweet_id = twitter_tweet_entities.tweet_id and type = 'hashtag' and tag = ? WHERE twitter_created_at_datetime > ? group by d");
            $stmt->execute(array($track['tracking_term'], REPORT_START));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($data['hashtags'][$row['d']])) $data['hashtags'][$row['d']] = 0;
                $data['hashtags'][$row['d']] += $row['q'];
            }

        }

        elseif ($track['tracking_type'] == 'mention')
        {
            $stmt = $db->prepare("SELECT DATE(twitter_created_at_datetime) d, count(1) q FROM twitter_tweets JOIN twitter_tweet_entities on twitter_tweets.tweet_id = twitter_tweet_entities.tweet_id and tag = ? and type = 'mentions' WHERE twitter_created_at_datetime > ? group by d");
            $stmt->execute(array($track['tracking_term'], REPORT_START));

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($data['mentions'][$row['d']])) $data['mentions'][$row['d']] = 0;
                $data['mentions'][$row['d']] += $row['q'];

            }

        }
        elseif ($track['tracking_type'] == 'user') {

            $stmt = $db->prepare("SELECT DATE(twitter_created_at_datetime) d, count(1) q FROM twitter_tweets JOIN twitter_actors on twitter_actors.twitter_user_id = twitter_tweets.twitter_user_id and username = ? WHERE twitter_created_at_datetime > ? group by d");
            $stmt->execute(array($track['tracking_term'], REPORT_START));

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($data['users'][$row['d']])) $data['users'][$row['d']] = 0;
                $data['users'][$row['d']] += $row['q'];

            }
        }


    }
    //   return;

    $data = fillDates($data, REPORT_START);

    $data['hashtags'] = array_values($data['hashtags']);
    $data['mentions'] = array_values($data['mentions']);
    $data['users']     = array_values($data['users']);
    $data['dates']    = array_values($data['dates']);

    echo json_encode($data);

    //echo $twig->render('chart.twig', array('data' => $data));

});

$app->run();

function fillDates($dates, $min)
{
    ksort($dates['mentions']);
    ksort($dates['hashtags']);
    ksort($dates['users']);

    $max = end(array_keys($dates['mentions']));

    if ($max < end(array_keys($dates['hashtags'])))
        $max = end(array_keys($dates['hashtags']));

    if ($max < end(array_keys($dates['users'])))
        $max = end(array_keys($dates['users']));


    while ($min <= $max) {

        if (isset($dates['mentions'][$min])) {
            $return['mentions'][$min] = $dates['mentions'][$min];
        } else {
            $return['mentions'][$min] = 0;
        }

        if (isset($dates['hashtags'][$min])) {
            $return['hashtags'][$min] = $dates['hashtags'][$min];
        } else {
            $return['hashtags'][$min] = 0;
        }

        if (isset($dates['users'][$min])) {
            $return['users'][$min] = $dates['users'][$min];
        } else {
            $return['users'][$min] = 0;
        }
        $return['dates'][$min] = $min;
        $min = date('Y-m-d', strtotime($min . ' + 1 day'));
    }

    return $return;

}


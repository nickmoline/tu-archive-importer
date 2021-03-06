#!/usr/bin/env php
<?php

// Instead of messing around with SQL statements myself
require_once 'meekrodb.2.1.class.php';
require_once 'config.php';


$directory = "tweets/";
$files = glob($directory . "*.js");

echo "Running...";

$processedfilescount = 0;
$totalcount = 0;
$filecount = 0;

foreach($files as $file)
{
	$processedfilescount++;
	$filecount = 0;
	echo "\n\t".sprintf("%20s",$file)."\t";
	$str_data = file_get_contents($file);
	$str_data = substr($str_data, 32);
	
	$data = json_decode($str_data);
	
	foreach ($data as $tweet) {
		if ($filecount % 100 == 0 && $filecount != 0) echo "\t".sprintf("%20s",number_format($filecount))."\n\t".str_repeat(" ", 20)."\t";
		$totalcount++;
		$filecount++;

		$parsed_tweet = array(
			'post_id'             => $tweet->id_str,
			'author_username'     => $tweet->user->screen_name,
			'author_fullname'     => $tweet->user->name,
			'author_avatar'       => $tweet->user->profile_image_url_https,
			'is_protected'        => $tweet->user->protected,
			'author_user_id'      => (string)$tweet->user->id,
			'post_text'           => (string)$tweet->text,
			'pub_date'            => gmdate("Y-m-d H:i:s", strToTime($tweet->created_at)),
			'source'              => (string)$tweet->source,
			'network'             => 'twitter'
		);

		if (isset($tweet->place->full_name)) {
			$parsed_tweet['place'] = (string)$tweet->place->full_name;
		}

		if (isset($tweet->in_reply_to_status_id)) {
			$parsed_tweet['in_reply_to_post_id'] = (string)$tweet->in_reply_to_status_id;
		}

		if (isset($tweet->in_reply_to_user_id)) {
			$parsed_tweet['in_reply_to_user_id'] = (string)$tweet->in_reply_to_user_id;
		}

		// Check to see if a tweet with the specific id already exists
		//  Make sure to only check twitter posts
		//  Limit the response to 1 (since that should be the maximum there would be
		$count = DB::queryFirstField("SELECT COUNT(*) FROM " . $table_name . " WHERE post_id=%i AND network='twitter' LIMIT 1", $parsed_tweet['post_id']);
		if ($count == 0) {
			DB::insert($table_name, $parsed_tweet);
		}
		echo ".";
	}
	echo "\t".sprintf("%20s",number_format($filecount));
}
echo "\n\nProcessed ".number_format($totalcount)." tweets in ".number_format($processedfilescount)." files.\n\n";

echo "Finished!\n\n";

?>


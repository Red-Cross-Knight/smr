<?
if(USING_AJAX)
{
	$template->ignoreMiddle();//Stop new news being lost.
	return;
}	

$template->assign('PageTopic','CURRENT NEWS');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_news_menue();
//we we check for a lotto winner...
$db->lockTable('player_has_ticket');
$db->query('SELECT count(*) as num, min(time) as time FROM player_has_ticket WHERE ' . 
			'game_id = '.$player->getGameID().' AND time > 0 GROUP BY game_id ORDER BY time DESC');
$db->nextRecord();
if ($db->getField('num') > 0) {
	$amount = ($db->getField('num') * 1000000 * .9) + 1000000;
	$first_buy = $db->getField('time');
} else {
	$amount = 1000000;
	$first_buy = TIME;
}
//find the time remaining in this jackpot. (which is 2 days from the first purchased ticket)
$time_rem = ($first_buy + (2 * 24 * 60 * 60)) - TIME;
$val =0;
if ($time_rem <= 0) {
	//we need to pick a winner
	$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' ORDER BY rand()');
	if ($db->nextRecord()) {
		$winner_id = $db->getField('account_id');
		$time = $db->getField('time');
	}
	$db->query('SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID());
	if ($db->nextRecord()) {
		
		$amount += $db->getField('prize');
		$db->query('DELETE FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID());
		
	}
	$db->query('SELECT * FROM player_has_ticket WHERE time = 0 AND game_id = '.$player->getGameID().' AND account_id = '.$winner_id);
	$db->query('UPDATE player_has_ticket SET time = 0, prize = '.$amount.' WHERE time = '.TIME.' AND ' .
					'account_id = '.$winner_id.' AND game_id = '.$player->getGameID());
	//delete losers
	$db->query('DELETE FROM player_has_ticket WHERE time > 0 AND game_id = '.$player->getGameID());
	//get around locked table problem
	$val = 1;

}
$db->unlock();
if ($val == 1) {
	// create news msg
	$winner =& SmrPlayer::getPlayer($winner_id, $player->getGameID());
	$news_message = '<font color=yellow>'.$winner->getPlayerName().'</font> has won the lotto!  The jackpot was ' . number_format($amount) . '.  <font color=yellow>'.$winner->getPlayerName().'</font> can report to any bar to claim their prize!';
	// insert the news entry
	$db->query('DELETE FROM news WHERE type = \'lotto\' AND game_id = '.$player->getGameID());
	$db->query('INSERT INTO news ' .
	'(game_id, time, news_message, type) ' .
	'VALUES('.$player->getGameID().', ' . TIME . ', ' . $db->escape_string($news_message, false) . ',\'lotto\')');
	
}
$db->unlock();
//end lotto check
$curr_allowed = $player->getLastNewsUpdate();
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'news_read.php';
$container['breaking'] = 'yes';
$var_del = TIME - 86400;
$db->query('DELETE FROM news WHERE time < '.$var_del.' AND type = \'breaking\'');
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'breaking\' ORDER BY time DESC LIMIT 1');
if ($db->nextRecord()) {

    $time = $db->getField('time');
    $PHP_OUTPUT.=create_link($container, '<b>MAJOR NEWS! - ' . date(DATE_FULL_SHORT, $time) . '</b>');
    $PHP_OUTPUT.=('<br /><br />');

}
if (isset($var['breaking'])) {

    $db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'breaking\' ORDER BY time DESC LIMIT 1');
    $text = stripslashes($db->getField('news_message'));
    $time = $db->getField('time');
    $PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Time</span></th>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Breaking News</span></th>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td align="center"> ' . date(DATE_FULL_SHORT, $time) . ' </td>');
    $PHP_OUTPUT.=('<td align="left">'.$text.'</td>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('</table>');
    $PHP_OUTPUT.=('<br /><br />');

}
//display lottonews if we have it
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'lotto\' ORDER BY time DESC');
while ($db->nextRecord()) {
	$PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Time</span></th>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Message</span></th>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('<tr>');
    $time = $db->getField('time');
    $PHP_OUTPUT.=('<td align="center"> ' . date(DATE_FULL_SHORT, $time) . ' </td>');
    $PHP_OUTPUT.=('<td align="left">' . $db->getField('news_message') . '</td>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<br /><br />');
}
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND time > '.$curr_allowed.' AND type = \'regular\' ORDER BY news_id DESC');
$player->updateLastNewsUpdate();
$player->update();

if ($db->getNumRows()) {

    $PHP_OUTPUT.=('<b><big><div align="center" style="color:blue;">You have ' . $db->getNumRows() . ' news entries.</div></big></b>');
    $PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center">Time</span>');
    $PHP_OUTPUT.=('<th align="center">News</span>');
    $PHP_OUTPUT.=('</tr>');

    while ($db->nextRecord()) {

        $time = $db->getField('time');
        $news = stripslashes($db->getField('news_message'));

        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<td align="center">' . date(DATE_FULL_SHORT, $time) . '</td>');
        $PHP_OUTPUT.=('<td align="left">'.$news.'</td>');
        $PHP_OUTPUT.=('</tr>');

    }

    $PHP_OUTPUT.=('</table>');

} else
    $PHP_OUTPUT.=('You have no current news.');

?>
<?php

require_once(get_file_loc('menu.inc'));
create_bar_menu();

$db->query('SELECT message_id FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY message_id DESC');
if ($db->nextRecord()) {
	$amount = $db->getInt('message_id') + 1;
}
else {
	$amount = 1;
}
$gossip_tell = $_REQUEST['gossip_tell'];
if (isset($gossip_tell))
	$db->query('INSERT INTO bar_tender (game_id, message_id, message) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($amount) . ',  ' . $db->escapeString($gossip_tell) . ' )');

$db->query('SELECT * FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY rand() LIMIT 1');

if ($db->nextRecord()) {
	$PHP_OUTPUT.=('I heard ');
	$message = $db->getField('message');
	$PHP_OUTPUT.=($message.'<br /><br />');
	$PHP_OUTPUT.=('Got anything else to tell me?<br />');
}
else {
	$PHP_OUTPUT.=('I havent heard anything recently...got anything to tell me?<br /><br />');
}


$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_talk_bartender.php'));
$PHP_OUTPUT.=('<input type="text" name="gossip_tell" size="30" id="InputFields">');
$PHP_OUTPUT.=create_submit('Tell him');
$PHP_OUTPUT.=('</form><br />');

$PHP_OUTPUT.=('What else can I do for ya?');
$PHP_OUTPUT.=('<br /><br />');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_buy_drink_processing.php'));
$PHP_OUTPUT.=create_submit('Buy a drink ($10)');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Buy some water ($10)');
$PHP_OUTPUT.=('</form><br />');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_talk_bartender.php'));
$PHP_OUTPUT.=create_submit('Talk to bartender');
$PHP_OUTPUT.=('</form>');

?>
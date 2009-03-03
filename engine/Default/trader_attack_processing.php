<?
$timeBetweenAttacks = microtime(true)-$var['time'];
if($timeBetweenShots<MIN_TIME_BETWEEN_SHOTS)
{
	$sleepTime = (MIN_TIME_BETWEEN_SHOTS-$timeBetweenAttacks);
//	echo '$sleepTime' . $sleepTime . ' ';
	$sleepTimeMicro = $sleepTime - floor($sleepTime);
	$sleepTimeSecs = $sleepTime - $sleepTimeMicro;
	$sleepTimeNano = round($sleepTimeMicro*1000000000);
//	echo 'Sleeping for: ' . $sleepTimeSecs . 's ' . round($sleepTimeMicro*1000000000) . 'ns' . ' ';
	time_nanosleep($sleepTimeSecs, $sleepTimeNano);
}
$var['time']=microtime(true);
$db->query('INSERT INTO debug VALUES (\'attack_speed\','.$player->getAccountID().','.($timeBetweenAttacks).')');

if($player->hasNewbieTurns())
	create_error('You are under newbie protection.');
if($player->hasFederalProtection())
	create_error('You are under federal protection.');
if($player->isLandedOnPlanet())
	create_error('You cannot attack whilst on a planet!');
if($player->getTurns() < 3)
	create_error('You have insufficient turns to perform that action.');
if(!$player->canFight())
	create_error('You are not allowed to fight!');

$targetPlayer =& SmrPlayer::getPlayer($var['target'],$player->getGameID());

	if($player->traderNAPAlliance($targetPlayer))
		create_error('Your alliance does not allow you to attack this trader.');
	else if($targetPlayer->isDead())
		create_error('Target is already dead.');
	else if($targetPlayer->getSectorID() != $player->getSectorID())
		create_error('Target is no longer in this sector.');
	else if($targetPlayer->hasNewbieTurns())
		create_error('Target is under newbie protection.');
	else if($targetPlayer->isLandedOnPlanet())
		create_error('Target is protected by planetary shields.');
	else if($targetPlayer->hasFederalProtection())
		create_error('Target is under federal protection.');

$sector =& SmrSector::getSector($player->getGameID(),$player->getSectorID(),$player->getAccountID());
$fightingPlayers =& $sector->getFightingTraders($player,$targetPlayer);


// Cap fleets to the required size
foreach($fightingPlayers as $team => &$teamPlayers)
{
	$fleet_size = count($teamPlayers);
	if($fleet_size > MAXIMUM_FLEET_SIZE)
	{
		// We use random key to stop the same people being capped all the time
		for($j=0;$j<$fleet_size-MAXIMUM_FLEET_SIZE;++$j)
		{
			do
			{
				$key = array_rand($teamPlayers);
			} while($player->equals($teamPlayers[$key]) || $targetPlayer->equals($teamPlayers[$key]));
			unset($teamPlayers[$key]);
		}
	}
} unset($teamPlayers);
	
//decloak all fighters
foreach($fightingPlayers as &$teamPlayers)
{
	foreach($teamPlayers as &$teamPlayer)
	{
		$teamPlayer->getShip()->decloak();
	} unset($teamPlayer);
} unset($teamPlayers);

// Take off the 3 turns for attacking
$player->takeTurns(3);
$player->update();

$results = array('Attackers' => array('Traders' => array(), 'TotalDamage' => 0), 
				'Defenders' => array('Traders' => array(), 'TotalDamage' => 0));
foreach($fightingPlayers['Attackers'] as $accountID => &$teamPlayer)
{
	$playerResults =& $teamPlayer->shootPlayers($fightingPlayers['Defenders']);
	$results['Attackers']['Traders'][$teamPlayer->getAccountID()]  =& $playerResults;
	$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($teamPlayer);
foreach($fightingPlayers['Defenders'] as $accountID => &$teamPlayer)
{
	$playerResults =& $teamPlayer->shootPlayers($fightingPlayers['Attackers']);
	$results['Defenders']['Traders'][$teamPlayer->getAccountID()]  =& $playerResults;
	$results['Defenders']['TotalDamage'] += $playerResults['TotalDamage'];
} unset($teamPlayer);
$ship->removeUnderAttack(); //Don't show attacker the under attack message.

$serializedResults = serialize($results);
$db->query('INSERT INTO combat_logs VALUES(\'\',' . $player->getGameID() . ',\'PLAYER\',' . $player->getSectorID() . ',' . TIME . ',' . $player->getAccountID() . ',' . $player->getAllianceID() . ',' . $var['target'] . ',' . $targetPlayer->getAllianceID() . ',' . $db->escape_string(gzcompress($serializedResults)) . ', \'FALSE\')');
unserialize($serializedResults); //because of references we have to undo this.

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'trader_attack.php';

// If their target is dead there is no continue attack button
if(!$targetPlayer->isDead())
	$container['target'] = $var['target'];
else
	$container['target'] = 0;

// If they died on the shot they get to see the results
if($player->isDead())
{
	$container['override_death'] = TRUE;
	$container['target'] = 0;
}

$container['results'] = $serializedResults;
forward($container);

?>
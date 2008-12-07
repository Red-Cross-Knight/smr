<?

$smarty->assign('PageTopic','CREATE UNIVERSE - ADDING WEAPONS (8/10)');

$PHP_OUTPUT.=('<dl>');
$db->query('SELECT * FROM game WHERE game_id = ' . $var['game_id']);
if ($db->next_record())
	$PHP_OUTPUT.=('<dt style="font-weight:bold;">Game<dt><dd>' . $db->f('game_name') . '</dd>');
$PHP_OUTPUT.=('<dt style="font-weight:bold;">Task:<dt><dd>Adding weapon dealers</d>');
$PHP_OUTPUT.=('<dt style="font-weight:bold;">Description:<dt><dd style="width:50%;">');
$PHP_OUTPUT.=('Weapon dealers sells weapons that can be mounted to ships. Each shop lists the type it sells. The values you provide here are absolute numbers per galaxies.</dd>');
$PHP_OUTPUT.=('</dl>');

// put galaxies into one array
$galaxies = array();
$db->query('SELECT DISTINCT galaxy.galaxy_id as id, galaxy_name as name
			FROM sector, galaxy
			WHERE game_id = ' . $var['game_id'] . ' AND
				  sector.galaxy_id = galaxy.galaxy_id
			ORDER BY galaxy.galaxy_id');
while ($db->next_record())
	$galaxies[$db->f('id')] = $db->f('name');

$container = array();
$container['url']		= 'universe_create_weapons_processing.php';
$container['game_id']	= $var['game_id'];
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p><table cellpadding="5" border="0">');
$PHP_OUTPUT.=('<tr><td></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<th>'.$galaxy_name.'</th>');
$PHP_OUTPUT.=('</tr>');

$db2 = new SMR_DB();

// iterate over all weapon shops
$db->query('SELECT DISTINCT location_type.location_type_id as type_id, location_name FROM location_type, location_sells_weapons ' .
					'WHERE location_type.location_type_id = location_sells_weapons.location_type_id ' .
					'ORDER BY location_name');
while ($db->next_record()) {

	$location_name		= $db->f('location_name');
	$location_type_id	= $db->f('type_id');

	// get all weapons that are sold here
	$db2->query('SELECT * FROM location_type, location_sells_weapons, weapon_type ' .
						 'WHERE location_type.location_type_id = location_sells_weapons.location_type_id AND ' .
						 	   'location_sells_weapons.weapon_type_id = weapon_type.weapon_type_id AND ' .
						 	   'location_type.location_type_id = '.$location_type_id);

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">'.$location_name.'</b><br>');
	while ($db2->next_record()) $PHP_OUTPUT.=('<span style="font-size:65%;">' . $db2->f('weapon_name') . '</span><br>');
	$PHP_OUTPUT.=('</td>');
	foreach ($galaxies as $galaxy_id => $galaxy_name)
		$PHP_OUTPUT.=('<td align="center"><input type="input" name="id['.$location_type_id.']['.$galaxy_id.']" size="3" id="InputFields" value="0" style="text-align:center;"></td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table></p>');

$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Skip >>');
$PHP_OUTPUT.=('</form>');

?>
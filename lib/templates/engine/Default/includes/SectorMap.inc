<table class="lmt"><?php
	foreach($MapSectors as $MapSector)
	{ ?>
		<tr><?php
			foreach($MapSector as $Sector)
			{
				$isCurrentSector = $ThisSector->equals($Sector);
				$isLinkedSector = $ThisSector->isLinked($Sector); ?>
				<td>
					<div class="<?php if($isCurrentSector){ ?>currentSeclm<?php }else if($isLinkedSector){ ?>connectSeclm<?php }else if($Sector->isVisited()){ ?>normalSeclm<?php }else{ ?>normalSeclmu<?php } ?> lm_sector">
						<div class="lmup"><?php
							if($Sector->isVisited())
							{ ?>
								<div class="center lmup"><?php
									if($Sector->getLinkUp())
									{ ?>
										<img src="images/link_hor.gif" alt="Up" title="Up" /><?php
									} ?>
								</div><?php
								if($Sector->hasWarp() || $Sector->hasMine() || $ThisPlayer->isPartOfCourse($Sector))
								{ ?>
									<div class="lmp"><?php
										if($ThisPlayer->isPartOfCourse($Sector))
										{ ?>
											<img title="Course" alt="Course" src="images/plot_icon.gif"/><?php
										}
										if($Sector->hasWarp())
										{
											if($isCurrentSector)
											{
												$WarpSector = $Sector->getLinkWarpSector(); ?>
												<a href="<?php echo $WarpSector->getLocalMapHREF() ?>"><?php
											} ?>
												<img title="Warp to #<?php echo $Sector->getLinkWarp() ?>" alt="Warp to #<?php echo $Sector->getLinkWarp() ?>" src="images/warp.gif" /><?php
											if($isCurrentSector){ ?></a><?php }
										}
										if($Sector->hasMine())
										{ ?>
											<img src="images/asteroid.gif" alt="Mining Available Here" title="Mining Available Here" /><?php
										} ?>
									</div><?php
								}
							}
							if((($ThisShip->hasScanner() && $isLinkedSector) || $isCurrentSector) && ($Sector->hasForces() || $Sector->hasTraders()))
							{ ?>
								<div class="lmtf"><?php
									if(($isCurrentSector && $Sector->hasOtherTraders()) || ($isLinkedSector && $Sector->hasTraders()))
									{?>
										<img title="Trader" alt="Trader" src="images/trader.jpg"/><?php
									}
									if($Sector->hasForces())
									{ ?>
										<img title="Forces" alt="Forces" src="images/forces.jpg"/><?php
									} ?>
								</div><?php
							} ?>
						</div><?php
						if($Sector->isVisited())
						{
							if($Sector->hasLocation() || $Sector->hasPlanet())
							{ ?>
								<div class="center lmloc fullwidth"><?php
								if($Sector->hasLocation())
								{
									foreach($Sector->getLocations() as $Location)
									{
										if($isCurrentSector && $Location->hasAction())
										{ ?>
											<a href="<?php echo $Location->getExamineHREF() ?>"><?php
										} ?>
											<img src="<?php echo $Location->getImage() ?>" alt="<?php echo $Location->getName() ?>" title="<?php echo $Location->getName() ?>" /><?php
										if($isCurrentSector && $Location->hasAction()){ ?></a><?php }
									}
								}
								if($Sector->hasPlanet())
								{
									if($isCurrentSector)
									{ ?>
										<a href="<?php echo $Sector->getPlanet()->getExamineHREF() ?>"><?php
									} ?>
										<img title="Planet" alt="Planet" src="images/planet.gif"/><?php
									if($isCurrentSector){ ?></a><?php }
								} ?>
								</div><?php
							} ?>
							<div class="left lmleft"><?php
								if($Sector->getLinkLeft())
								{ ?>
									<img src="images/link_ver.gif" alt="Left" title="Left" /><?php
								} ?>
							</div><?php
						} ?>
						<div class="lmsector center <?php if($Sector->isVisited()){ ?>dgreen<?php }else{ ?>yellow<?php } ?> fullwidth">
							<?php echo $Sector->getSectorID() ?>
						</div><?php
						if($Sector->isVisited())
						{ ?>
							<div class="right lmright"><?php
								if($Sector->getLinkRight())
								{ ?>
									<img src="images/link_ver.gif" alt="Right" title="Right" /><?php
								} ?>
							</div><?php
							if(($isCurrentSector && $Sector->hasPort()) || $Sector->hasCachedPort())
							{
								if($isCurrentSector && $Sector->hasPort())
								{
									$Port =& $Sector->getPort();
								}
								else if($Sector->hasCachedPort())
								{
									$Port =& $Sector->getCachedPort();
								} ?>
								<div class="left lmport"><?php
									if($isCurrentSector)
									{ ?>
										<a href="<?php echo $Port->getTradeHREF() ?>"><?php
									} ?>
										<img src="images/port/buy.gif" alt="Buy" title="Buy" /><?php
											foreach($Port->getVisibleGoodsSold($ThisPlayer) as $Good)
											{ ?>
												<img src="<?php echo $Good['ImageLink'] ?>" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name'] ?>" /><?php
											} ?><br />
										<img src="images/port/sell.gif" alt="Sell" title="Sell" /><?php
											foreach($Port->getVisibleGoodsBought($ThisPlayer) as $Good)
											{ ?>
												<img src="<?php echo $Good['ImageLink'] ?>" title="<?php echo $Good['Name'] ?>" alt="<?php echo $Good['Name'] ?>" /><?php
											}
									if($isCurrentSector){ ?></a><?php } ?>
								</div><?php
								} ?>
							<div class="center lmdown fullwidth"><?php
								if($Sector->getLinkDown()){ ?>
									<img src="images/link_hor.gif" alt="Down" title="Down" /><?php
								} ?>
							</div>
						<?php
						}
						if($isLinkedSector)
						{ ?>
							<a href="<?php echo $Sector->getLocalMapHREF() ?>"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a><?php
						}
						else if($isCurrentSector)
						{ ?>
							<a href="<?php echo Globals::getCurrentSectorHREF() ?>"><img title="" alt="" class="move_hack" src="images/blank.gif"/></a><?php
						} ?>
					</div>
				</td>
			<?php } ?>
		</tr>
	<?php } ?>
</table>
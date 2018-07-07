<div id="app-navigation">
	<ul class="with-icon">

		<?php

		$pinned = 0;
		foreach ($_['navigationItems'] as $item) {
			$pinned = NavigationListElements($item, $l, $pinned);
		}
		?>

		<li id="quota"
			class="pinned <?php p($pinned === 0 ? 'first-pinned ' : '') ?><?php
			if ($_['quota'] !== \OCP\Files\FileInfo::SPACE_UNLIMITED) {
			?>has-tooltip" title="<?php p($_['usage_relative'] . '%');
		} ?>">
			<a href="#" class="icon-quota svg">
				<p id="quotatext"><?php
					if ($_['quota'] !== \OCP\Files\FileInfo::SPACE_UNLIMITED) {
						if($_['usage_blocked'] > 0){
							p($l->t('%s of %s. %s%% are blocked!', [$_['usage'], $_['total_space'], $_['usage_blocked']]));
						}else{
							p($l->t('%s of %s', [$_['usage'], $_['total_space']]));
						}
					} else {
						p($l->t('%s used', [$_['usage']]));
					} ?></p>
				<div id="progress" value="<?php p($_['usage_relative']) ?>" unavailable="<?php p($_['usage_blocked']) ?>"></div>
			</a>
		</li>
	</ul>
	<div id="app-settings">
		<div id="app-settings-header">
			<button class="settings-button"
					data-apps-slide-toggle="#app-settings-content">
				<?php p($l->t('Settings')); ?>
			</button>
		</div>
		<div id="app-settings-content">
			<div id="files-setting-showhidden">
				<input class="checkbox" id="showhiddenfilesToggle"
					   checked="checked" type="checkbox">
				<label for="showhiddenfilesToggle"><?php p($l->t('Show hidden files')); ?></label>
			</div>

			<div id="files-setting-sorting">
				<label for="sortingstrategy"><?php p($l->t('Sorting-Strategy')); ?></label><br>
				<select id="sortingstrategy" name="sortingstrategy">
					<option value="alphabet"><?php p($l->t('Alphabet (Default)')); ?></option>
					<option value="alphabet"><?php p($l->t('Alphabet')); ?></option>
					<option value="customorder"><?php p($l->t('Custom Order')); ?></option>
					<option value="datemodified"><?php p($l->t('Date Modified')); ?></option>
				</select>
			</div>

			<label for="webdavurl"><?php p($l->t('WebDAV')); ?></label>
			<input id="webdavurl" type="text" readonly="readonly"
				   value="<?php p(\OCP\Util::linkToRemote('webdav')); ?>"/>
			<em><?php print_unescaped($l->t('Use this address to <a href="%s" target="_blank" rel="noreferrer noopener">access your Files via WebDAV</a>', array(link_to_docs('user-webdav')))); ?></em>

		</div>
	</div>

</div>


<?php

/**
 * Prints the HTML for a single Entry.
 *
 * @param $item The item to be added
 * @param $l Translator
 * @param $pinned IntegerValue to count the pinned entries at the bottom
 *
 * @return int Returns the pinned value
 */
function NavigationListElements($item, $l, $pinned) {
	strpos($item['classes'], 'pinned') !== false ? $pinned++ : '';
	?>
	<li <?php if (isset($item['sublist'])){ ?>id="button-collapse-parent-<?php p($item['id']); ?>"<?php } ?>
		data-id="<?php p(isset($item['href']) ? $item['href'] : $item['id']) ?> "
		class="nav-<?php p($item['id']) ?> <?php p($item['classes']) ?> <?php p($pinned === 1 ? 'first-pinned' : '') ?> <?php if ($item['defaultExpandedState'] === 'true') { ?> open<?php } ?>"
		<?php if (isset($item['folderPosition'])) { ?> folderposition="<?php p($item['folderPosition']); ?>" <?php } ?>>

		<a href="<?php p(isset($item['href']) ? $item['href'] : '#') ?>"
		   class="nav-icon-<?php p($item['icon'] !== '' ? $item['icon'] : $item['id']) ?> svg"><?php p($item['name']); ?></a>


		<?php
		NavigationElementMenu($item);
		if (isset($item['sublist'])) {
			?>
			<button id="button-collapse-<?php p($item['id']); ?>"
					class="collapse app-navigation-noclose" <?php if (sizeof($item['sublist']) == 0) { ?> style="display: none" <?php } ?>></button>
			<ul id="sublist-<?php p($item['id']); ?>" <?php if ($item['draggableSublist'] === 'true') { ?> draggable="true" style="resize: none;"<?php } ?>>
				<?php
				foreach ($item['sublist'] as $item) {
					$pinned = NavigationListElements($item, $l, $pinned);
				}
				?>
			</ul>
		<?php } ?>
	</li>


	<?php
	return $pinned;
}

/**
 * Prints the HTML for a dotmenu.
 *
 * @param $item The item to be added
 *
 * @return void
 */
function NavigationElementMenu($item) {
	if ($item['menubuttons'] === 'true') {
		?>
		<div id="dotmenu-<?php p($item['id']); ?>"
			 class="app-navigation-entry-utils" <?php if ($item['enableMenuButton'] === 0) { ?> style="display: none"<?php } ?>>
			<ul>
				<li class="app-navigation-entry-utils-menu-button svg">
					<button id="dotmenu-button-<?php p($item['id']) ?>"></button>
				</li>
			</ul>
		</div>
		<div id="dotmenu-content-<?php p($item['id']) ?>"
			 class="app-navigation-entry-menu">
			<ul>

			</ul>
		</div>
	<?php }
}
<?php
	/*
	Plugin Name: Random Links
	Plugin URI:
	Description: Enter one or more link names, and have each display one of a list of links sequentially or randomly.
	Author: Alex Isakov
	Version: 1
	Author URI:
	*/

	if(!class_exists("RandomLinks")) {
		class RandomLinks {
			var $optionsName = "RandomLinksAdminOptions";
			var $category_table, $link_table;

			/**BEGIN Function table db**/
			function RandomLinks() {
				global $wpdb;
				$this->category_table = $wpdb->prefix . 'randomlinks_categories';
				$this->link_table = $wpdb->prefix . 'randomlinks_links';
			}
            /**END Function table db**/

			/** BEGIN Activate Plugin function**/
			function activatePlugin() {
				add_option($this->optionsName);
				$default_options = array();
				$options = get_option($this->optionsName);
				if (!empty($options)) {
					foreach ($options as $key => $option)
					$default_options[$key] = $option;
				}
				update_option($this->optionsName, $default_options);
				global $wpdb;
				if($wpdb->get_var("SHOW TABLES LIKE '". $this->category_table . "'") != $this->category_table) { // Table doesn't exist
					$sql = "CREATE TABLE " . $this->category_table . " (
						category_id bigint(11) NOT NULL AUTO_INCREMENT,
						title varchar(128) NOT NULL,
						short_title varchar(128) NOT NULL,
						sort varchar(32) NOT NULL,
						added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						enabled tinyint(1) NOT NULL,
						PRIMARY KEY category_id (category_id),
						UNIQUE(short_title)
					);";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
				}
				if($wpdb->get_var("SHOW TABLES LIKE '" . $this->link_table . "'") != $this->link_table) { // Table doesn't exist
					$sql = "CREATE TABLE " . $this->link_table . " (
						link_id bigint(11) NOT NULL AUTO_INCREMENT,
						category_id bigint(11) NOT NULL,
						url varchar(128) NOT NULL,
						percent TINYINT(3) NOT NULL,
						click_count bigint(11) NOT NULL,
						added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						PRIMARY KEY link_id (link_id)
					);";
					dbDelta($sql);
				}
			}
            /**END Activate Plugin function**/

            /**BEGIN Deactivare Plugin function (Drop Table)**/
			function deactivatePlugin() {
				global $wpdb;
				$wpdb->query("DROP TABLE " .$this->category_table);
				$wpdb->query("DROP TABLE " .$this->link_table);
				
				delete_option($this->optionsName);
			}
            /**END Deactivare Plugin function (Drop Table)**/

            /**BEGIN **/
			function addHeaderCode() {
				echo '<link rel="stylesheet" type = "text/css" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/random-links/css/random-links.css"/>';
				echo '<link rel="stylesheet" type = "text/css" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/random-links/css/socrates-pretty-style.css"/>';
			}
			
			function getAdminPageHeader() { 
				// First, fetch the RSS feed
				$rss_url = "test";
				
				$feed = fetch_feed($rss_url);
				
				if(!is_wp_error($feed)) {
					$maxitems = $feed->get_item_quantity(5);
					$feed_items = $feed->get_items(0, $maxitems); 
				}
				?>
			<div id="socrates_plugin_header">
                <div id="socrates_plugin_header_title">
					<div id="socrates_plugin_header_title_title">Random Links</div>
				</div>
				<div id="socrates_plugin_header_content">
					<div id="socrates_plugin_header_content_left">
						<ul>
							<?php /*
								if ($maxitems == 0) echo '<li>Error Retrieving Feed</li>';
								else
								foreach($feed_items as $item) { ?>
									<li><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a>
									<?php if($feed_items[count($feed_items) - 1] != $item) echo '<li><div class="socrates_plugin_header_content_left_division"></div></li>'; ?>
								<?php } */?>
						</ul>
					</div>
				</div>
				<div id="socrates_plugin_header_bottom"></div>
			</div>
			<?php
			}
			function getAdminPage() {
				// For adding or editing a new category
				if(isset($_GET['do'])) {
					if($_GET['do'] === 'add') {
                        ?>
                        <!-- Form add a New Link-->
							<div class="wrap">
								<form method="post" action="<?php echo strtok($_SERVER['REQUEST_URI'], '&') ?>">
									<h2>Add a New Link</h2>
									<label for="category-title" style="display: block"><h3>Link Name:</h3><label style="font-size: .725em; margin-left: 1em; display: block;">Just used for admin purposes.</label>
									<input style="width: 40%" type="text" name="category-title" id="category-title" /></label>
									<label for="category-url"><h3>Link URL:</h3>
									<?php echo bloginfo('wpurl'); ?>/</label><input style="width: 15%" type="text" name="category-url" id="category-url" />
									<label style="font-size: .725em; margin-left: 1em; display: block;">WARNING: Be sure this is unique - otherwise, you may overwrite another link. Can use directory name, .htm, .html or .php extension.</label>
									<label for="category-links" style="display: block"><h3 style="margin-bottom: 0em">Link Sources:</h3>
									<p style="margin: .5em">Add one link per line. Link format example:  google.com   | http:// or www not required</p></label>
									<textarea id="category-links" name="category-links" style="width: 40%; height: 300px"></textarea>
                                    <ol id="link_rotations">
                                        <li>
                                            <input style="..." type="text" name="category-link-1" id="category-url" />
                                            <?php esc_html_e('weight:', 'default'); ?>
                                            <?php require_once ('class/LinksRotation.php');
                                            $target_url_weight = 0;
                                            LinksRotation::rotation_weight_dropdown((($target_url_weight == 0 || !empty($target_url_weight))?$target_url_weight:'100'),'target_url_weight-1'); ?>
                                        </li>
                                        <li>
                                            <input style="..." type="text" name="category-link-2" id="category-url" />
                                            <?php esc_html_e('weight:', 'default'); ?>
                                            <?php require_once ('class/LinksRotation.php');
                                            $target_url_weight = 0;
                                            LinksRotation::rotation_weight_dropdown((($target_url_weight == 0 || !empty($target_url_weight))?$target_url_weight:'100'),'target_url_weight-2'); ?>
                                        </li>
                                        <li>
                                            <input style="..." type="text" name="category-link-3" id="category-url" />
                                            <?php esc_html_e('weight:', 'default'); ?>
                                            <?php require_once ('class/LinksRotation.php');
                                            $target_url_weight = 0;
                                            LinksRotation::rotation_weight_dropdown((($target_url_weight == 0 || !empty($target_url_weight))?$target_url_weight:'100'),'target_url_weight-3'); ?>
                                        </li>
                                        <?php
                                        /*for($i=0;$i<count($url_rotations);$i++) {
                                            $rotation = ((isset($url_rotations[$i]) && !empty($url_rotations[$i]))?$url_rotations[$i]:'');
                                            $weight   = (isset($url_rotation_weights[$i])?$url_rotation_weights[$i]:0);
                                            LinksRotation::rotation_row($rotation, $weight, 'url_rotations[]', 'url_rotation_weights[]');
                                        }*/
                                        ?>
                                    </ol>
                                    <h3>Display Method:</h3>
									<label for="random"><input type="radio" id="random" name="sort-type" value="random" checked="checked" /> Random	</label>										&nbsp;&nbsp;&nbsp;&nbsp;
									<label for="sequential"><input type="radio" id="sequential" name="sort-type" value="sequential"/> Sequential</label>
                                    <div class="submit"><input type="submit" name="update_links" value="Add new link" onClick="return validateSubmit()"/></div>
                                </form>
							</div>
                        <?php
					} elseif($_GET['do'] === 'edit') {
						if(isset($_GET['category'])) {
							global $wpdb;
							$category_information = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE category_id = '%s'", $_GET['category']), ARRAY_A);
							if(!$category_information) {
								header('Location: ' . strtok($_SERVER['REQUEST_URI'], '&') . "&error=nocategory");
								exit();
							}
							?>
							
								<div class="wrap">
									<form method="post" action="<?php echo strtok($_SERVER['REQUEST_URI'], '&') ?>">
										<h2>Edit Link</h2>
										<h3>Link Title:</h3>
										<input style="width: 40%" type="text" name="category-title" id="category-title" value="<?php echo $category_information['title'] ?>" />
										<h3>Link URL:</h3>
										<?php echo bloginfo('wpurl'); ?>/<input style="width: 15%" type="text" id="category-url" name="category-url" value="<?php echo $category_information['short_title'] ?>" />
										<label style="font-size: .725em; margin-left: 1em; display: block;">WARNING: Be sure this is unique - otherwise, you may overwrite another link.</label>
										<h3 style="margin-bottom: 0em">Link Sources:</h3>
										<p style="margin: .5em">Separate by spaces, commas, or newlines</p>
										<textarea name="category-links" style="width: 40%; height: 300px"><?php
											//$existant_links = array();
											// Determine the current list of links
											$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $this->link_table WHERE category_id = %s ORDER BY link_id ASC", $category_information['category_id']), ARRAY_A);
											$urls = array();
											foreach($results as $result)
												$urls[] = $result['url'];
												
											// Sort them alphabetically before outputting them
											sort($urls);
											foreach($urls as $url) {
												echo($url);
												if($url != $urls[count($urls) - 1])
													echo "\n";
											}
										?>
                                        </textarea>
										<h3>Display Method:</h3>
										<label for="random"><input type="radio" id="random" name="sort-type" value="random" <?php if ($category_information['sort'] === "random") { echo("checked=\"checked\""); } ?>/> Random</label>											&nbsp;&nbsp;&nbsp;&nbsp;
										<label for="sequential"><input type="radio" id="sequential" name="sort-type" value="sequential" <?php if ($category_information['sort'] === "sequential") { echo("checked=\"checked\""); } ?>/> Sequential
										</label>
                                        <input type="hidden" id="category-id" name="category-id" value="<?php echo $category_information['category_id'] ?>"/>
										<div class="submit"><input type="submit" name="update_links" value="Save" onClick="return validateSubmit()"/></div>
									</form>
								</div>

								<script type="text/javascript">
                                    function validateSubmit() {
										if(document.getElementById('category-title').value == "") {
											document.getElementById('category-title').focus();
											document.getElementById('category-title').select();
											return false;
										}
										if(document.getElementById('category-url').value == "") {
											document.getElementById('category-url').focus();
											document.getElementById('category-url').select();
											return false;
										}
										return true;
									}
								</script>
							<?php
						}
					}
				// Default Page - Category Listing
				} else {
					
					if(isset($_GET['go_ahead']) && $_GET['go_ahead'] == 'regenerate') {
						global $wp_rewrite;
						$wp_rewrite->flush_rules();?>
						<div class="updated"><p><strong>Rules Regenerated.</strong></p></div>
						<?php
					}
					
					if(isset($_POST['update_links'])) {					
						if(isset($_POST['category-url']) && isset($_POST['category-title']) && isset($_POST['category-links']) && isset($_POST['sort-type']) && $_POST['category-url']) {
							global $wpdb;
							
							$category_information = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE short_title = '%s'", $_POST['category-url']), ARRAY_A);
							if(!$category_information) {
								// Category doesn't exist
								
								// Check to see if we just changed the short_name of the table, if so, update that value instead of adding a new table
								$category_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE category_id = '%s'", $_POST['category-id']), ARRAY_A);
								if(!$category_info) {
									$wpdb->insert( $this->category_table, array( 'title' => $_POST['category-title'], 'short_title' => $_POST['category-url'], 'sort' => $_POST['sort-type'], 'enabled' => '1' ) );
								
									// Get new category information for use later
									$category_information = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE short_title = '%s'", $_POST['category-url']), ARRAY_A);
									
									// Create an options page for this category to store the last link used
									$categories = get_option($this->optionsName);
									$categories[$category_information['category_id']] = array('lastlink' => '');
									update_option($this->optionsName, $categories);
								
									$action = "Created";
								} else {							
									$wpdb->update($this->category_table, array('short_title' => $_POST['category-url']), array('category_id' => $category_info['category_id']));
									$category_information = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE short_title = '%s'", $_POST['category-url']), ARRAY_A);
									$categories = get_option($this->optionsName);
									$categories[$category_information['category_id']] = array('lastlink' => '');
									update_option($this->optionsName, $categories);
									$action = "Updated";
								}
							} else {
								$category_information = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE short_title = '%s'", $_POST['category-url']), ARRAY_A);
								$categories = get_option($this->optionsName);
								$categories[$category_information['category_id']] = array('lastlink' => '');
								update_option($this->optionsName, $categories);
								$action = "Updated";
							}
							
							// Determine if the category title or sort method has changed
							if($_POST['category-title'] != $category_information['title']) {
								$wpdb->update($this->category_table, array('title' => $_POST['category-title']), array('category_id' => $category_information['category_id']));
							}
							if($_POST['sort-type'] != $category_information['sort']) {
								$wpdb->update($this->category_table, array('sort' => $_POST['sort-type']), array('category_id' => $category_information['category_id']));
							}
							
							// Clean up links, remove doubles, sort, and place them in a list
							/*$new_links = preg_split('/ +/', preg_replace('#,|\n|http://#', ' ', $_POST['category-links']), 0, PREG_SPLIT_NO_EMPTY);
							function trim_value(&$value) { 
		    					$value = strtolower(rtrim(trim($value), '/'));
							}
							array_walk($new_links, 'trim_value');
							$new_links = array_unique($new_links);*/
							
							// Determine which links we've cast out
							/*$existant_links = array();
							$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $this->link_table . " WHERE category_id = %s", $category_information['category_id']), ARRAY_A);

							foreach($results as $result) $existant_links[] = $result['url'];*/
							
							// Determine which links have been removed from the list, and remove them
							/*foreach($existant_links as $existant_link) {
								if(!in_array($existant_link, $new_links)) {
									$wpdb->query($wpdb->prepare("DELETE FROM " . $this->link_table . " WHERE url = '%s' AND category_id='%s'", $existant_link, $category_information['category_id']));
								}
							}*/
                            $wpdb->insert( $this->link_table, array( 'url' => $_POST['category-link-1'], 'percent'=>$_POST['target_url_weight-1'], 'click_count' => 0, 'category_id' => $category_information['category_id'] ) );
                            $wpdb->insert( $this->link_table, array( 'url' => $_POST['category-link-2'], 'percent'=>$_POST['target_url_weight-2'], 'click_count' => 0, 'category_id' => $category_information['category_id'] ) );
                            if(!empty($_POST['category-link-3'])){
                            $wpdb->insert( $this->link_table, array( 'url' => $_POST['category-link-3'], 'percent'=>$_POST['target_url_weight-3'], 'click_count' => 0, 'category_id' => $category_information['category_id'] ) );}
							// Sort the links into links that already exist or need to exist
							/*foreach($new_links as $link) {
								// If links exist
								if(in_array($link, $existant_links)) {
									// Ignore them; We don't need to touch these
								} else {
									//$wpdb->insert( $this->link_table, array( 'url' => $link, 'click_count' => 0, 'category_id' => $category_information['category_id'] ) );
								}
							}*/
							
							$categories = get_option($this->optionsName);
							unset($categories[$category_information['category_id']]);
							$categories[$category_information['category_id']] = array('lastlink' => '');
							update_option($this->optionsName, $categories);		

							$this->rewriteRules();
							
							if(is_writable(WP_CONTENT_DIR . "/../.htaccess")) {
								?><div class="updated"><p><strong><?php echo "Category '" . $category_information['title'] . "' " . $action ?>.</strong></p></div><?php
							} else {
								?><div class="error"><h2 style="text-align: center"><strong>Error writing to .htaccess file.<br/>Check that it is writable, and that permalinks are enabled.</strong></h2></div><?php
							}
							
						}
						
					} 
					
					if(isset($_GET['remove'])) {
						global $wpdb;
						$wpdb->query($wpdb->prepare("DELETE FROM " . $this->category_table . " WHERE category_id = '%s'", $_GET['remove']));
						$wpdb->query($wpdb->prepare("DELETE FROM " . $this->link_table . " WHERE category_id = '%s'", $_GET['remove']));
						?><div class="updated"><p><strong>Category Removed.</strong></p></div><?php
					}
					
					if(isset($_GET['error'])) {
						if($_GET['error'] === 'nocategory') {
							echo("<h2 style=\"text-align: center; margin: 1em auto;\">Sorry, that category doesn't exist.</h2>");
						}
					}
					
					?> <div class="wrap"> <?php
					$this->getAdminPageHeader();
					if(isset($_GET['statistics'])) {
						if($_GET['statistics'] === 'all') {
							global $wpdb;
							$urls = array();
							$results = $wpdb->get_results("SELECT url FROM $this->link_table ORDER BY link_id ASC", ARRAY_A);
							foreach ($results as $result) {
								$urls[] = $result['url'];
							}
							if(!$urls) {?>
								<h2 style="text-align: center; margin: 1em auto">You don't have any links<br/>Please <a href="<?php bloginfo('wpurl')?>/wp-admin/options-general.php?page=random-links.php&do=add">click here to add a new link</a>.</h2>
							<?php } else {
								$urls = array_unique($urls);
								$site_info = array();
								foreach($urls as $url) {
									$hits = $wpdb->get_row("SELECT sum(click_count) FROM $this->link_table WHERE url = '$url'", ARRAY_A);
									$site_info[$url] = $hits['sum(click_count)'];
								}
								asort($site_info, SORT_NUMERIC);
								$site_info = array_reverse($site_info);?>
								<div class="socrates_plugin_chunk">
									<div class="socrates_plugin_chunk_title">Statistics</div>
									<table id="randomlink-categories" style="width: 70%; margin-bottom: 4em; margin-top: 1em">
										<thead>
											<tr>
												<th scope="col">URL</th>
												<th scope="col">Clicks</th>
											</tr>
										</thead>
										<tbody><?php $counter = 0; foreach($site_info as $url => $hits) {?>
											<tr <?php if($counter % 2 != 0) echo "style = \"background-color: #FFEFD5\"";?>>
												<td style="width: 70%"><?php echo $url ?></td>
												<td style="width: 30%"><?php echo $hits; $counter += 1;?></td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
							<?php
							}
						} else {
							global $wpdb;
							
							$urls = array();
							$results = $wpdb->get_results($wpdb->prepare("SELECT url FROM $this->link_table WHERE category_id = '%s' ORDER BY link_id ASC", $_GET['statistics']), ARRAY_A);
							foreach ($results as $result) {
								$urls[] = $result['url'];
							}
							
							if(!$urls) {?>
								<div class="socrates_plugin_chunk">
									<div class="socrates_plugin_chunk_title">Statistics</div>
                                    <h2 style="text-align: center; margin: 1em auto">This link doesn't have any link sources.</h2>
                                </div>
								</div>
							<?php } else {
							
								$urls = array_unique($urls);
								$site_info = array();
								
								foreach($urls as $url) {
									$hits = $wpdb->get_row($wpdb->prepare("SELECT sum(click_count) FROM $this->link_table WHERE url = '$url' AND category_id = '%s'", $_GET['statistics']), ARRAY_A);
									$site_info[$url] = $hits['sum(click_count)'];
								}
								
								asort($site_info, SORT_NUMERIC);
								$site_info = array_reverse($site_info);
								$category_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->category_table . " WHERE category_id = %s", $_GET['statistics']), ARRAY_A);?>				
								
								<div class="socrates_plugin_chunk">
									<div class="socrates_plugin_chunk_title">Statistics about '<?php echo $category_info['title'] ?>'</div>
									<table id="randomlink-categories" style="width: 70%; margin-bottom: 4em; margin-top: 1em">
										<thead>
											<tr>
												<th scope="col">URL</th>
												<th scope="col">Clicks</th>
											</tr>
										</thead>
										<tbody><?php $counter = 0; foreach($site_info as $url => $hits) {?>
											<tr <?php if($counter % 2 != 0) echo "style = \"background-color: #FFEFD5\"";?>>
												<td style="width: 70%"><?php echo $url ?></td>
												<td style="width: 30%"><?php echo $hits; $counter += 1;?></td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
								</div>

                                <?php
							}
						}
					}?>

						<?php
							global $wpdb;
							$existant_categories = array();
							// Determine the current list of links
							$results = $wpdb->get_results("SELECT * FROM $this->category_table ORDER BY category_id ASC", ARRAY_A);
							if($results) {
								?>
								<div class="socrates_plugin_chunk">
									<div class="socrates_plugin_chunk_title">Links</div>
									<table id="randomlink-categories">
										<thead>
											<tr>
												<th scope="col">Link Name</th>
												<th scope="col">Link - Click to Copy</th>
												<th scope="col">Link Count</th>
												<th scope="col">Total Clicks</th>
												<th scope="col">Sort Type</th>
												<th scope="col">Statistics</th>
												<th scope="col">Options</th>
												<th scope="col">Manage</th>
											</tr>
										</thead>
										<tbody ">
                                        <?php
                                        foreach($results as $result) {
                                            $link_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $this->link_table . " WHERE category_id = '%s'", $result['category_id']));
                                            $total_clicks = $wpdb->get_var($wpdb->prepare("SELECT SUM(click_count) FROM " . $this->link_table . " WHERE category_id = '%s'", $result['category_id']));
                                            if(!$total_clicks) $total_clicks = 0;?>
											<tr>
												<td><?php echo $result['title']; ?></td>
												<td>
													<label class="socrates_plugin_fakelink" id="some_unninja_id_<?php echo $result['title'] ?>" onClick="document.getElementById('some_ninja_id_<?php echo $result['title'] ?>').style.display='block'; this.style.display='none'; document.getElementById('some_ninja_id_<?php echo $result['title'] ?>').focus(); document.getElementById('some_ninja_id_<?php echo $result['title'] ?>').select();"><?php echo get_bloginfo('wpurl') . '/' . $result['short_title']?></label>
													<input type="text" onBlur="document.getElementById('some_unninja_id_<?php echo $result['title'] ?>').style.display='block'; this.style.display='none';" id="some_ninja_id_<?php echo $result['title'] ?>" style="display: none; width:100%" value="<?php echo get_bloginfo('wpurl') . '/' . $result['short_title']?>" />
												</td>
												<td><?php echo $link_count; ?></td>
												<td><?php echo $total_clicks; ?></td>
												<td><?php echo ucwords($result['sort']); ?></td>
												
												<td><?php echo '<a href="' . get_bloginfo('wpurl') .'/wp-admin/options-general.php?page=random-links.php&statistics=' . $result['category_id'] . '">View</a>' ?></td>
												<td><?php echo '<a href="' . get_bloginfo('wpurl') .'/wp-admin/options-general.php?page=random-links.php&do=edit&category=' . $result['category_id'] . '">Edit</a>' ?></td>
												<td><?php echo '<a href="' . get_bloginfo('wpurl') .'/wp-admin/options-general.php?page=random-links.php&remove=' . $result['category_id'] . '">Remove</a>' ?></td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
									<div class="socrates_plugin_header_content_left_division" style="width: 60%; margin: 20px auto"></div>
									<p style="margin-left: 40px"><a href="<?php bloginfo('wpurl')?>/wp-admin/options-general.php?page=random-links.php&do=add">Add a new Link</a></p>
									<p style="margin-left: 40px"><a href="<?php bloginfo('wpurl')?>/wp-admin/options-general.php?page=random-links.php&statistics=all">View Statistics for All Websites</a></p>
									<p style="margin-left: 40px"><a href="<?php bloginfo('wpurl')?>/wp-admin/options-general.php?page=random-links.php&go_ahead=regenerate">Regenerate Rules for Links</a></p>
								<?php } else { ?>
									<h2 style="text-align: center; margin: 3em auto">You don't have any links.<br/>Please <a href="<?php bloginfo('wpurl')?>/wp-admin/options-general.php?page=random-links.php&do=add">click here to add a new link</a>.</h2>
								<?php } ?>
                                </div>
					<?php
				}
				echo("</div>");
			}
			
			function templateRedirectIntercept() {
				if(isset($_GET['rl_page'])) {
					global $wpdb;
					$row = $wpdb->get_row($wpdb->prepare("SELECT category_id, sort from " . $this->category_table . " WHERE short_title = '%s'", $_GET['rl_page']), ARRAY_A);				
					if(!$row)
						return;
					
					$links = array();
					$links = $wpdb->get_results('SELECT link_id, url, percent, click_count from ' . $this->link_table . ' WHERE category_id = "' . $row['category_id'] . ' ORDER BY link_id ASC"', ARRAY_A);
					if(!$links)	return;
					if($row['sort'] === 'random') {
						/*$random_index = array_rand($links);
						$link = $links[$random_index];
						$wpdb->query($wpdb->prepare("UPDATE " . $this->link_table . " SET click_count = '%s' WHERE link_id = '%s'", $link['click_count'] + 1, $link['link_id']));
						header('Location: http://' . $link['url']);
						exit;*/
                        // My Rand link percent
                        $allLink = $links;
                        $percent_rand = mt_rand(1, 100); // Generating primes from ... to ...
                        $current = 0; // Current item = 0;
                        for ($i=0; $i<count ($allLink); $i++){
                            $link_id[$i] = $allLink[$i] ['link_id']; // id
                            $click_count[$i] = $allLink[$i] ['click_count']; // click count
                            $action_data[$i]=$allLink[$i] ['url']; // URL
                            $percent[$i]=$allLink[$i] ['percent']; // Процент
                            $current += $percent[$i]; // Add to the current percentage failed verification
                            // If a value is found
                            if($current >= $percent_rand){
                                //$wpdb->query($wpdb->prepare("UPDATE " . $this->link_table . " SET click_count = '%s' WHERE link_id = '%s'", $link['click_count'] + 1, $link['link_id']));
                                $wpdb->query($wpdb->prepare("UPDATE " . $this->link_table . " SET click_count = '%s' WHERE link_id = '%s'", $click_count[$i] + 1, $link_id[$i]));
                                header('Location: http://' . $action_data[$i]);
                                //$message = $action_data[$i];
                                break;
                            }
                        }
                        exit;
					}

					$options = get_option($this->optionsName);
					$lastlink_id = $options[$row['category_id']]['lastlink'];
					// If no last link is set, choose the first from the list
					if(!$lastlink_id) {
						$options[$row['category_id']] = array('lastlink' => $links[0]['link_id']);
						update_option($this->optionsName, $options);
						$link = $links[0];
					
					// If it's the last link in the list
					} elseif($lastlink_id === $links[count($links) - 1]['link_id']) {
						// Set 'lastlink' option to the first link in the list
						$options[$row['category_id']] = array('lastlink' => $links[0]['link_id']);
						update_option($this->optionsName, $options);
						$link = $links[0];
						
					} else {
						// Find the index of the last link in $links
						$old_link_index = 0;
						for($index = 0;$index <= count($links);$index++) {
							if($links[$index]['link_id'] == $options[$row['category_id']]['lastlink']) {
								$old_link_index = $index;
								break;
							}
						}
						$options[$row['category_id']] = array('lastlink' => $links[$old_link_index + 1]['link_id']);
						update_option($this->optionsName, $options);
						$link = $links[$old_link_index + 1];
					}
					$wpdb->query($wpdb->prepare("UPDATE " . $this->link_table . " SET click_count = '%s' WHERE link_id = '%s'", $link['click_count'] + 1, $link['link_id']));
					//header('Location: http://' . $link['url']);
                    header('Location: https://' . $link['url']);
				}
			}
			
			function generateRewriteRules($wp_rewrite) {
				global $wpdb;
				$results = $wpdb->get_results($wpdb->prepare("SELECT short_title from " . $this->category_table), ARRAY_A);				
				if(!$results)
					return;
					
				$short_titles = array();
				
				foreach($results as $result)
					$short_titles[] = $result['short_title'];
					
				if(!$wp_rewrite->non_wp_rules) {
					$wp_rewrite->non_wp_rules = array();
				}
				
				foreach($short_titles as $title) {
					$wp_rewrite->non_wp_rules = $wp_rewrite->non_wp_rules + array( $title => "index.php?rl_page=$title" );
				}
				
				if(is_writable(WP_CONTENT_DIR . "/../.htaccess")) {
					file_put_contents(WP_CONTENT_DIR . "/../.htaccess", $wp_rewrite->mod_rewrite_rules());
				}
			}
			
			function rewriteRules() {
				global $wp_rewrite;
				$wp_rewrite->flush_rules();
			}
			
		}
	}
	if(class_exists("RandomLinks")) {
		$randomLinksPlugin = new RandomLinks();
	}
	//Initialize the admin panel
	if (!function_exists("RandomLinks_ap")) {
		function RandomLinks_ap() {
			global $randomLinksPlugin;
			
			if (!isset($randomLinksPlugin)) {
				return;
			}
			
			if (function_exists('add_options_page')) {
				add_options_page('Random Links Plugin', 'Random Links', 9, basename(__FILE__), array(&$randomLinksPlugin, 'getAdminPage'));
			}
		}
	}
	if(isset($randomLinksPlugin)) {
		// Actions
		add_action('admin_head', array(&$randomLinksPlugin, 'addHeaderCode'), 1);
		register_activation_hook( __FILE__, array(&$randomLinksPlugin, 'activatePlugin'));
		register_deactivation_hook( __FILE__, array(&$randomLinksPlugin, 'deactivatePlugin'));
		add_action('admin_menu', 'RandomLinks_ap');
        //do_action( 'admin_footer', string $data )

		add_action('template_redirect', array(&$randomLinksPlugin, 'templateRedirectIntercept'));
		add_action('generate_rewrite_rules',  array(&$randomLinksPlugin, 'generateRewriteRules'));
        //wp_footer();
        // Filters
	}
	?>

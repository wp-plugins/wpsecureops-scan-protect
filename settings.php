<?PHP
// create custom plugin settings menu
add_action('admin_menu', 'wpsecureops_scan_protect_create_menu');

function wpsecureops_scan_protect_create_menu()
{

    //create new top-level menu
        add_submenu_page('options-general.php', 'WPSecureOps Scan Protect', 'WPSecureOps Scan Protect', 'administrator', __FILE__, 'wpsecureops_scan_protect_settings_page');

    //call register settings function
    add_action('admin_init', 'wpsecureops_scan_protect_register_settings');
    add_action('admin_init', 'wpsecureops_scan_protect_is_save_triggered');
}

function wpsecureops_scan_protect_register_settings()
{
    register_setting('wpsecureops-scan-protect-settings-group', 'wpsecureops_scan_protect_interval');
    register_setting('wpsecureops-scan-protect-settings-group', 'wpsecureops_scan_protect_max_attempts');
    register_setting('wpsecureops-scan-protect-settings-group', 'wpsecureops_scan_protect_lock_duration');
    register_setting('wpsecureops-scan-protect-settings-group', 'wpsecureops_scan_protect_allowed_bots');
    register_setting('wpsecureops-scan-protect-settings-group', 'wpsecureops_scan_protect_notify_email');
}

function wpsecureops_scan_protect_is_save_triggered()
{
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true' && isset($_GET['page']) && $_GET['page'] === "wpsecureops-scan-protect/" . basename(__FILE__)) {
        do_action("wpsecureops-scan-protect/settings-updated");
    }
}

function wpsecureops_scan_protect_get_interval()
{
    $v = get_option('wpsecureops_scan_protect_interval');
    if (!$v) {
        $v = 5;
    }

    return $v;
}
function wpsecureops_scan_protect_get_max_attempts()
{
    $v = get_option('wpsecureops_scan_protect_max_attempts');
    if (!$v) {
        $v = 15;
    }

    return $v;
}
function wpsecureops_scan_protect_get_lock_duration()
{
    $v = get_option('wpsecureops_scan_protect_lock_duration');
    if (!$v) {
        $v = 3600;
    }

    return $v;
}
function wpsecureops_scan_protect_get_allowed_bots()
{
    $v = get_option('wpsecureops_scan_protect_allowed_bots');
    if (!$v) {
        $v = '*.search.msn.com
*.yahoo.com
*.googlebot.com';
    }

    return explode("\n", wpsecureops_scan_protect_normalize_line_endings($v));
}
function wpsecureops_scan_protect_get_notify_email()
{
    $v = get_option('wpsecureops_scan_protect_notify_email');
    if (!$v) {
        $v = get_option('admin_email');
    }

    return $v;
}

function wpsecureops_scan_protect_settings_page()
{
    ?>
	<div class="wrap">
		<h2>WPSecureOps Scan Protect Settings</h2>

		<form method="post" action="options.php">
			<?php settings_fields('wpsecureops-scan-protect-settings-group');
    ?>
			<?php do_settings_sections('wpsecureops-scan-protect-settings-group');
    ?>
			<table class="form-table">
								<tr valign="top">
					<th scope="row"><?php echo __('Interval', 'wpsecureops-scan-protect') ?></th>
										<td>
						<input type="text" name="wpsecureops_scan_protect_interval" value="<?php echo esc_attr(wpsecureops_scan_protect_get_interval());
    ?>" />
						<div class="description">Interval in seconds</div>
					</td>
									</tr>
								<tr valign="top">
					<th scope="row"><?php echo __('Max requests for that interval', 'wpsecureops-scan-protect') ?></th>
										<td>
						<input type="text" name="wpsecureops_scan_protect_max_attempts" value="<?php echo esc_attr(wpsecureops_scan_protect_get_max_attempts());
    ?>" />
						<div class="description">The number of requests (to WordPress) that the user is allowed to make, before getting banned</div>
					</td>
									</tr>
								<tr valign="top">
					<th scope="row"><?php echo __('Lock duration (seconds)', 'wpsecureops-scan-protect') ?></th>
										<td>
						<input type="text" name="wpsecureops_scan_protect_lock_duration" value="<?php echo esc_attr(wpsecureops_scan_protect_get_lock_duration());
    ?>" />
						<div class="description">Number of seconds for which the user will be blocked if marked as a &quot;scanning bot&quot;</div>
					</td>
									</tr>
								<tr valign="top">
					<th scope="row"><?php echo __('Allowed bots that will not get banned if they burst too much HTTP requests', 'wpsecureops-scan-protect') ?></th>
										<td>
						<textarea name="wpsecureops_scan_protect_allowed_bots" class="wpso_multi_text"><?php echo implode("\n", wpsecureops_scan_protect_get_allowed_bots());
    ?></textarea>
						<div class="description">Use * to add the reverse host names (the only 100% way of identifying bots)</div>
					</td>
									</tr>
								<tr valign="top">
					<th scope="row"><?php echo __('Notification email (optional)', 'wpsecureops-scan-protect') ?></th>
										<td>
						<input type="text" name="wpsecureops_scan_protect_notify_email" value="<?php echo esc_attr(wpsecureops_scan_protect_get_notify_email());
    ?>" />
						<div class="description">E-mail address to which (if provided) an e-mail message will be sent when someone gets banned.</div>
					</td>
									</tr>
							</table>

			<?php submit_button();
    ?>

		</form>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.wpso_multi_text').each(function() {
				var $textarea = $(this);
				var $container = $("<div/>");

				var changed = function() {
					var v = "";
					$('input', $container).each(function() {
						if($(this).val()) {
							v += $(this).val() + "\n";
						}
					});

					$textarea.val(v.replace(/\n$/mi, ""));
				};

				var addRow = function(v) {
					var $wrapper = $("<div/>");

					var $input = $('<input type="text" />');
					if(v) {
						$input.val(v);
					}

					var $removeButton = $('<a href="javascript:;">Remove</a>');
					$removeButton.bind('click', function() {
						$wrapper.remove();
						changed();
					});

					$input.bind('blur change', changed);


					$wrapper.append($input);
					$wrapper.append($removeButton);

					$container.append($wrapper);
				};

				$($(this).val().split("\n")).each(function(k, v) {
					addRow(v);
				});

				$textarea.hide();
				$textarea.data('wpso_multi_text_container', $container);

				var $addButton = $('<a href="javascript:;">Add more</a>');
				$addButton.bind('click', function() {
					addRow();
				});

				$container.insertAfter($textarea);
				$addButton.insertAfter($container);
			})
		});
	</script>
<?php

}

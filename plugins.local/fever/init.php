<?php
class Fever extends Plugin {
    private $host;

    function about() {
        return array(2.2,
            "Emulates the Fever API for Tiny Tiny RSS",
            "DigitalDJ, mestrode & murphy");
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_PREFS_TAB, $this);
        $host->add_hook($host::HOOK_PREFS_EDIT_FEED, $this);
    	$host->add_hook($host::HOOK_PREFS_SAVE_FEED, $this);
    }

    /* plugins/main/init.php hook_prefs_tab */

    function hook_prefs_tab($args) {
        if ($args != "prefPrefs") return;

        print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"" . __("Fever Emulation") . "\">";

        print "<h3>" . __("Fever Emulation") . "</h3>";

        print "<p>" . __("Since the Fever API uses a different authentication mechanism to Tiny Tiny RSS, you must set a separate password to login. This password may be the same as your Tiny Tiny RSS password.") . "</p>";

        print "<p>" . __("Set a password to login with Fever:") . "</p>";

        print "<p><b>" . __("WARNING: The Fever API uses an UNSECURE unsalted MD5 hash. Consider the use of a disposable application-specific password and use HTTPS.") . "</b></p>";

        print "<form dojoType=\"dijit.form.Form\">";

        print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
            evt.preventDefault();
            if (this.validate()) {
                new Ajax.Request('backend.php', {
                    parameters: dojo.objectToQuery(this.getValues()),
                    onComplete: function(transport) {
                        notify_info(transport.responseText);
                    }
                });
                //this.reset();
            }
            </script>";

        print_hidden("op", "pluginhandler");
        print_hidden("method", "save");
        print_hidden("plugin", "fever");

        print "<input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" type=\"password\" name=\"password\" />";
        print "<button dojoType=\"dijit.form.Button\" type=\"submit\">" . __("Set Password") . "</button>";
        print "</form>";

        print "<p>" . __("To login with the Fever API, set your server details in your favourite RSS application to: ") . get_self_url_prefix() . "/plugins.local/fever/" . "</p>";
        print "<p>" . __("Additional details can be found at ") . "<a href=\"http://www.feedafever.com/api\" target=\"_blank\">https://feedafever.com/api</a></p>";

        print "<p>" . __("Note: Due to the limitations of the API and some RSS clients (for example, Reeder on iOS), some features are unavailable: \"Special\" Feeds (Published / Tags / Labels / Fresh / Recent), Nested Categories (hierarchy is flattened)") . "</p>";

        print "</div>";
    }

    function save()
    {
        if (isset($_POST["password"]) && isset($_SESSION["uid"]))
        {
            $sth = $this->pdo->prepare("SELECT login FROM ttrss_users WHERE id = ?");
            $sth->execute([clean($_SESSION["uid"])]);
            if ($line = $sth->fetch())
            {
                $password = md5($line["login"] . ":" . $_POST["password"]);
                $this->host->set($this, "password", $password);
                echo __("Password saved.");
            }
        }
    }

	function hook_prefs_edit_feed($feed_id) {
		print "<header>".__("Fever API")."</header>";
		print "<section>";

		$enabled_feeds = $this->host->get($this, "enabled_feeds");
		if (!is_array($enabled_feeds)) $enabled_feeds = array();

		$key = array_search($feed_id, $enabled_feeds);
		$checked = $key !== FALSE ? "checked" : "";

		print "<fieldset>";

		print "<label class='checkbox'><input dojoType='dijit.form.CheckBox' type='checkbox' id='fever_cacheImages_enabled'
			name='fever_cacheImages_enabled' $checked>&nbsp;".__('Use cached images mandatorily')."</label>";

		print "</fieldset>";

		print "</section>";
    }

    function hook_prefs_save_feed($feed_id) {
		$enabled_feeds = $this->host->get($this, "enabled_feeds");
		if (!is_array($enabled_feeds)) $enabled_feeds = array();

		$enable = checkbox_to_sql_bool($_POST["fever_cacheImages_enabled"]);
		$key = array_search($feed_id, $enabled_feeds);

		if ($enable) {
			if ($key === FALSE) {
				array_push($enabled_feeds, $feed_id);
			}
		} else {
			if ($key !== FALSE) {
				unset($enabled_feeds[$key]);
			}
		}

		$this->host->set($this, "enabled_feeds", $enabled_feeds);
    }

    function api_version() {
        return 2;
    }
}

?>

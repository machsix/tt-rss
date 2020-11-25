<?php

use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;

class mteam extends Plugin {

  /* @var PluginHost $host */
  private $host;

  function about() {
    return array(1.0,
      "Fix mteam link",
      "machsix");
  }

  function init($host)
  {
    $this->host = $host;
    $host->add_hook($host::HOOK_ENCLOSURE_ENTRY, $this);
  }

  function unparse_url($parsed_url) {
    $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
    $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
    $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
    $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
    $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return "$scheme$user$pass$host$port$path$query$fragment";
  }

  function hook_enclosure_entry($enc, $article_id) {
    $sth = $this->pdo->prepare("SELECT feed_url FROM ttrss_feeds WHERE ttrss_feeds.id IN 
      (SELECT ttrss_user_entries.feed_id FROM ttrss_user_entries WHERE ttrss_user_entries.ref_id = ? LIMIT 1)");
    $sth->execute([$article_id]);
    $result = $sth->fetchAll();
    $rssLink = $result[0]['feed_url'];

    if(strpos($rssLink, "m-team") !== false) {
      $query = parse_url($rssLink, PHP_URL_QUERY);
      parse_str($query, $queryDict);
      $passkey = $queryDict["passkey"];
      $contentUrl = $enc["content_url"];

      $url = parse_url($contentUrl);
      parse_str($url["query"], $queryDict);

      $url["query"] = http_build_query(array(
               "id" => $queryDict["id"],
               "passkey" => $passkey 
             ));
      $enc["content_url"] = $this->unparse_url($url);
    }
    return $enc;
  }

  function api_version() {
    return 2;
  }
}

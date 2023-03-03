<?php
namespace FOPG\Component\UtilsBundle\Contracts;

interface UriInterface {

  const VAR_PUNCT			= '.';
  const VAR_COLUMN			= ':';
  const VAR_SLASH			= '/';
  const VAR_PATH_ABEMPTY		= '//';
  const VAR_INTERROGATION		= '?';
  const VAR_DIESE			= '#';
  const VAR_QUERY_AND			= '&';
  const VAR_SCHEME_FILE		= 'file';

  const REGEXP_SCHEME_PROTOCOL	= "(?<scheme>[a-z0-9*]*)";
  const REGEXP_SCHEME_COLUMN  	= "[:]";
  const REGEXP_SCHEME_PATH_ABEMPTY 	= "\/\/";
  const REGEXP_SLASH			= "\/";
  const REGEXP_HOST_SUBDOMAIN		= "(((?<subdomain>(([^(\/.?)]+[.])*)[^\/.]+)[.]){0,1})";
  const REGEXP_HOST_DOMAIN		= "(?<domain>[^(.\/?)]{3,})";
  const REGEXP_HOST_TOP_LEVEL_DOMAIN	= "([.](?<topLevelDomain>(([^(\/.?)]{2,3}[.])*)[^(\/.?)]{2,})){1}";
  const REGEXP_HOST_IPV4		= "(?<domain>(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)[.](25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)[.](25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)[.](25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))";
  const REGEXP_PATH_FOLDER		= "[\/]*(((?<folder>((([^\/]*)[\/])*)([^\/]{1,100}))[\/]){0,1})";
  const REGEXP_PATH_FILENAME		= "(?<filename>[^?\/]*)";
  const REGEXP_PATH_FILENAME_EXTENSION= "(?<filenameExtension>[^\.\\\\\/]+?)";
  const REGEXP_QUERY			= "([?](?<query>[^#]*))?";
  const REGEXP_FRAGMENT		= "([#](?<fragment>.*))?";

  const PORT_MODE_NORMAL	= "normal";
  const PORT_MODE_FORCED	= "forced";
  const PORT_MODE_NONE		= "none";

  const SCHEME_DEFAULT		= "http";
  const SUBDOMAIN_DEFAULT = "www";

  const SCHEME_OPTION_FULL        = 'full';
  const SCHEME_OPTION_SCHEME_ONLY = 'scheme_only';
  const SCHEME_OPTION_FUZZY       = 'fuzzy';
}

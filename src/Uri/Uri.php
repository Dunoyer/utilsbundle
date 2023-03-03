<?php
namespace FOPG\Component\UtilsBundle\Uri;

/**
 * Value object representing a URI.
 *
 * This class is meant to represent URIs according to RFC 3986 and to
 * provide methods for most common operations. Additional functionality for
 * working with URIs can be provided on top of the interface or externally.
 * Its primary use is for HTTP requests, but may also be used in other
 * contexts.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * Typically the Host header will be also be present in the request message.
 * For server-side requests, the scheme will typically be discoverable in the
 * server parameters.
 *
 * @link http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
use FOPG\Component\UtilsBundle\Contracts\UriInterface;
use FOPG\Component\UtilsBundle\Exception\InvalidArgumentException;
use FOPG\Component\UtilsBundle\Exception\InvalidUriException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Uri implements UriInterface
{
  /**
   * Scheme component of the URI.
   *
   * @var string Scheme component
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   */
  private ?string $scheme=null;

  /**
   * Folder component of the URI.
   *
   * The folder is the first part of the path section.
   *
   * @var ?string Folder component
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.3
   */
  private ?string $folder=null;

  /**
   * Query component of the URI
   *
   * The query is a set of key/value data
   *
   * @var string Query component
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.4
   */
  private ?string $query=null;

  /**
   * Subdomain component of the URL
   *
   * The subdomain is part of Authority section. It's provided only with the Domain Name Service way
   *
   * @var ?string Subdomain component
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   */
  private ?string $subdomain=null;

  /**
   * Domain component of the URL
   *
   * The domain is part of Authority section.
   * There is two ways to understand what the domain is
   * 	- first case : a well-naming string like "google" or "twitter" (Domain Name Service way)
   *  - second case : an IP address compound of numbers and punct like "127.0.0.1"
   *
   * @var ?string Domain component
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   */
  private ?string $domain=null;

  /**
   * Top level domain  of the URL
   *
   * This corresponds to the domain extension is part of authority section. It's provided only with
   * the Domain Name Service way
   *
   * @var ?string Top Level Domain component
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   */
  private ?string $topLevelDomain=null;

  /**
   * Filename component of the URL
   *
   * The filename is part of path section.
   *
   * @var ?string Part component
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.3
   */
  private ?string $filename=null;

  /**
   * Filename extension component of the URL
   *
   * The filename extension is part of path section.
   *
   * @var ?string File extension component
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.3
   */
  private ?string $filenameExtension=null;

  /**
   * Port of the URL
   *
   * @var ?int Port component
   */
  private ?int $port=null;

  /**
   * Fragment of the URL
   *
   * @var string Fragment component
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.5
   */
  private ?string $fragment=null;

  /**
   * @var ?string user info for htaccess access
   *
   */
  private ?string $userInfo=null;

  /**
   * @var array
   *
   * Array of active schemes (must be referenced in "open_actu_url.url.schemes" section in config.yml)
   */
  private static array $active_schemes = [];

  /**
   * @var array
   *
   * Array of relationships between schemes and defaults ports
   */
  private static array $default_ports = [];

  /**
   * @var ?string Port mode
   *
   * port mode
   */
  private static ?string $port_mode=null;

  /**
   * scheme default
   *
   * @var ?string Scheme default
   */
  private static ?string $scheme_default=null;

  public function __construct(
    array $active_schemes=['http'],
    array $default_ports=[80],
    string $port_mode=self::PORT_MODE_NONE,
    string $scheme_default=self::SCHEME_DEFAULT
  ) {
    self::$active_schemes = $active_schemes;
    self::$default_ports  = $default_ports;
    self::$port_mode	    = $port_mode;
    self::$scheme_default	= $scheme_default;
  }

  /**
   * Retrieve the scheme component of the URI.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   * @return string The URI scheme.
   */
  public function getScheme(): string {
    return (null === $this->scheme) ? self::$scheme_default : $this->scheme;
  }

  /**
   * @var string
   *
   * Set the scheme component of the URI.
   *
   * The value returned MUST be normalized to lowercase, per RFC 3986
   * Section 3.1.
   *
   * The trailing ":" character is not part of the scheme and is added.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.1
   * @return
   */
  public function setScheme(string $scheme): self {
    $this->scheme	= null;
    // is scheme valid ?
    $regexp = $this->getSchemeRegexp(self::SCHEME_OPTION_SCHEME_ONLY);
    if(!preg_match($regexp,$scheme)) {
      throw new InvalidUriException(
        InvalidUriException::INVALID_SCHEME_FORMAT_MESSAGE,
        InvalidUriException::INVALID_SCHEME_FORMAT_CODE,
        ['name' => $scheme]
      );
    }

    $this->scheme = strtolower($scheme);
    return $this;
  }

  /**
   * Retrieve the host component of the URI.
   *
   * An host can be nullable only if the scheme is of type file
   *
   * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
   * @return ?string The URI host.
   */
  public function getHost(): ?string {
    if( (null === $this->domain) && ($this->scheme === self::VAR_SCHEME_FILE) )
      return null;

    if(null === $this->domain) {
      throw new InvalidUriException(
        InvalidUriException::NO_HOST_FOUND_MESSAGE,
        InvalidUriException::NO_HOST_FOUND_CODE,
        ['scheme' => self::VAR_SCHEME_FILE, 'current_scheme' => $this->getScheme()]
      );
    }
    /** @var string $host */
    $host = "";

    if(null !== $this->subdomain)
      $host = $this->subdomain.self::VAR_PUNCT;
    elseif(in_array($this->getScheme(),[self::SCHEME_DEFAULT]))
      $host = self::SUBDOMAIN_DEFAULT.self::VAR_PUNCT;
    $host.=$this->domain;

    if(null !== $this->topLevelDomain)
      $host.=self::VAR_PUNCT.$this->topLevelDomain;
    return $host;
  }

  /**
   * Set the host component of the URI.
   *
   * Host is the concatenation between subdomain, domain and top level domain separated with punct in
   * case of Domain Name service. If the host is an IP, so only the domain must be used. If no host is
   * present, this method MUST return an empty string.
   *
   * The value returned MUST be normalized to lowercase, per RFC 3986
   * Section 3.2.2.
   *
   * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
   * @return string The URI host.
   */
  public function setHost($host): self {
    $this->domain 		= null;
    $this->subdomain  	= null;
    $this->topLevelDomain	= null;
    // is host an IP address ?
    $regexp = $this->getHostRegexp('ip_v4');
    $regexpDns = $this->getHostRegexp('dns');
    if(preg_match($regexp,$host,$matches))
      $this->domain		= $host;
    elseif(preg_match($regexpDns,$host,$matches)) {
      $this->subdomain	= (!empty($matches['subdomain'])) ? strtolower($matches['subdomain']) : null;
      $this->domain 		= (!empty($matches['domain'])) ? strtolower($matches['domain']) : null;
      $this->topLevelDomain   = (!empty($matches['topLevelDomain'])) ? strtolower($matches['topLevelDomain']) : null;
    }
    return $this;
  }

  /**
   * Retrieve the port component of the URI.
   *
   * Important information from port mode (defined in config.yml)
   *
   * If the port mode is at "normal" (RECOMMANDED), and if the port is the standard port used with
   * the current scheme, this method return null.
   *
   * If the port mode is at "forced", if the port has no port information given, then it return the default
   * port number as described in port defaults area, else return null.
   *
   * If no port is present, and no scheme is present, this method return a null value.
   *
   * @return ?int The URI port.
   */
  public function getPort(): ?int {
    switch(self::$port_mode) {
      case self::PORT_MODE_NORMAL:
        if(count(self::$default_ports)) {
          foreach(self::$default_ports as $item) {
            $scheme = $item['scheme'];
            $port	= (int)$item['port'];

            if($this->scheme === $scheme && $this->port === $port)
              return null;
          }
        }
        break;
      case self::PORT_MODE_FORCED:
        if(count(self::$default_ports)) {
          foreach(self::$default_ports as $item) {
            $scheme = $item['scheme'];
            $port	= (int)$item['port'];
            if($this->scheme === $scheme && $this->port === null)
              return $port;
          }
        }
        break;
      case self::PORT_MODE_NONE:
      default:
    }
    return $this->port;
  }

  /**
   * Set the port component of the URL.
   *
   * The port must be an integer.
   *
   * @param ?int $port	Port
   * @throws InvalidUriException if the port is not an integer nor null
   */
  public function setPort(?int $port): self {
    $this->port = null;
    // is int ?
    if(ctype_digit(strval($port))) {
      $this->port = (int)$port;
      return $this;
    }
    elseif($port === null)
      return $this;

    throw new InvalidUriException(
      InvalidUriException::INVALID_PORT_FORMAT_MESSAGE,
      InvalidUriException::INVALID_PORT_FORMAT_CODE,
      ['name' => $port]
    );
  }

  /**
   * Retrieve the path component of the URL.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.3
   * @return string The URL path.
   */
  public function getPath(): ?string {
    $path = $this->folder;
    if(null !== $this->folder)
      $path.=self::VAR_SLASH;

    if(null !== $this->filename)
      $path.=$this->filename;

    if(null !== $this->filenameExtension)
      $path.=self::VAR_PUNCT.$this->filenameExtension;

    return $path;
  }

  /**
   * Set the path component of the URL.
   *
   * The path can either be empty or absolute (starting with a slash) or
   * rootless (not starting with a slash). Implementations support all
   * three syntaxes.
   *
   * Normally, the empty path "" and absolute path "/" are considered equal as
   * defined in RFC 7230 Section 2.7.3. But this method is not automatically
   * do this normalization because in contexts with a trimmed base path, e.g.
   * the front controller, this difference becomes significant. It's the task
   * of the user to handle both "" and "/".
   *
   * The value returned MUST be percent-encoded, but MUST NOT double-encode
   * any characters. To determine what characters to encode, please refer to
   * RFC 3986, Sections 2 and 3.3.
   *
   * As an example, if the value should include a slash ("/") not intended as
   * delimiter between path segments, that value MUST be passed in encoded
   * form (e.g., "%2F") to the instance.
   *
   * @param string $path Path
   * @param bool $encodeURL Option to protect the path area (DEFAULT=false) with RFC3986
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.3
   */
  public function setPath(string $path,$encodeURL=false): self {
    $this->folder 		= null;
    $this->filename  	= null;
    $this->filenameExtension= null;

    $regexp = $this->getPathRegexp('rootless');
    // is empty ?
    if(empty($path))
      return $this;

    // is a valid path ?
    if(preg_match($regexp,$path,$matches)) {

      if(!empty($matches['folder']))
        $this->folder = $matches['folder'];

      if(!empty($matches['filename'])) {
        $data = explode('.',$matches['filename']);
        // is extension exists ?
        if(count($data)>1 && !empty($data[0])) {
          $last_position 		= count($data)-1;
          $this->filenameExtension= $data[$last_position];
          unset($data[$last_position]);
          $this->filename		= implode('.',$data);
        }
        else
          $this->filename = $matches['filename'];

        if((null !== $this->filename) && (true === $encodeURL))
          $this->filename = rawurlencode($this->filename);
      }
      return $this;
    }

    throw new InvalidUriException(
      InvalidUriException::INVALID_PATH_MESSAGE,
      InvalidUriException::INVALID_PATH_CODE,
      ['name' => $path]
    );
  }

  /**
   * Retrieve the user information component of the URI.
   *
   * If no user information is present, this method MUST return an empty string.
   *
   * If a user is present in the URI, this will return that value;
   * additionally, if the password is also present, it will be appended to the
   * user value, with a colon (":") separating the values.
   *
   * The trailing "@" character is not part of the user information and MUST
   * NOT be added.
   *
   * @return ?string The URI user information, in "username[:password]" format.
   */
  public function getUserInfo(): ?string {
    return $this->userInfo;
  }

  /**
   * Retrieve the user information component of the URI.
   *
   * If no user information is present, this method MUST return an empty string.
   *
   * If a user is present in the URI, this will return that value;
   * additionally, if the password is also present, it will be appended to the
   * user value, with a colon (":") separating the values.
   *
   * The trailing "@" character is not part of the user information and MUST NOT be added.
   *
   * @return string The URI user information, in "username[:password]" format.
   */
  public function setUserInfo(?string $userinfo): self {
    $this->userInfo = $userinfo;
    return $this;
  }

  /**
   * Retrieve the query string of the URI.
   *
   * If no query string is present, this method MUST return an empty string.
   *
   * The leading "?" character is not part of the query and MUST NOT be added.
   *
   * @param bool $toArray Option to send in array format
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.4
   * @return ?string The URI query string.
   */
  public function getQuery(bool $toArray=false): ?string {
    return ($toArray === true) ? self::queryToArray($this->query) : $this->query;
  }

  /**
   * Set the query string of the URI.
   *
   * The value returned MUST be percent-encoded, but MUST NOT double-encode
   * any characters. To determine what characters to encode, please refer to
   * RFC 3986, Sections 2 and 3.4.
   *
   * As an example, if a value in a key/value pair of the query string should
   * include an ampersand ("&") not intended as a delimiter between values,
   * that value MUST be passed in encoded form (e.g., "%26") to the instance.
   *
   * @param string $query Query string
   * @param bool $encodeURL (OPTIONAL) Encoding to URL recommandation RFC3986
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.4
   */
  public function setQuery(string $query,bool $encodeURL=false): self {
    $this->query = null;
    if(!empty($query)) {
      $tab        = self::queryToArray($query,false);
      $new_query  = self::arrayToQuery($tab,$encodeURL);
      $this->query= $new_query;
    }
    return $this;
  }

  /**
   * Retrieve the folder component of the URL.
   *
   * The folder is the first part of the path element as described below
   *
   *  -------(/*)-------/(.?)-----(.*)-----(.------------------?)
   *  \________________/ \________________/\____________________/
   *         folder           filename       filenameExtension
   *  \_________________________________________________________/
   *			 	 path
   *
   * @return ?string the URL folder
   */
  public function getFolder(): ?string {
    return $this->folder;
  }

  /**
   * Retrieve the filename component of the URL.
   *
   * The filename is the second part of the path element as described below
   *
   *  -------(/*)-------/(.?)-----(.*)-----(.------------------?)
   *  \________________/ \________________/\____________________/
   *         folder           filename       filenameExtension
   *  \_________________________________________________________/
   *			 	 path
   *
   * @return ?string the URL filename
   */
  public function getFilename(): ?string {
    return $this->filename;
  }

  /**
   * Retrieve the filename extension component of the URL.
   *
   * The filename extension is the third part of the path element as described below
   *
   *  -------(/*)-------/(.?)-----(.*)-----(.------------------?)
   *  \________________/ \________________/\____________________/
   *         folder           filename       filenameExtension
   *  \_________________________________________________________/
   *			 	 path
   *
   * @return ?string the URL filename
   */
  public function getFilenameExtension(): ?string {
    return $this->filenameExtension;
  }

  /**
   * Retrieve the fragment component of the URL.
   *
   * If no fragment is present, this method return an empty string.
   *
   * The leading "#" character is not part of the fragment and is not added.
   *
   * The value returned can be percent-encoded (option decodeURL at false). To determine
   * what characters to encode, please refer to RFC 3986, Sections 2 and 3.5.
   *
   * @param bool $decodeURL (OPTIONAL) Decoding to URL recommandation
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.5
   * @return ?string The URL fragment.
   */
  public function getFragment(bool $decodeURL=false): ?string {
    return (true === $decodeURL) ? rawurldecode($this->fragment) : $this->fragment;
  }

  /**
   * Set the fragment component of the URI.
   *
   * The value returned MUST be percent-encoded, but MUST NOT double-encode
   * any characters. To determine what characters to encode, please refer to
   * RFC 3986, Sections 2 and 3.5.
   *
   * @param string $fragment Fragment component
   * @param bool $encodeURL (OPTIONAL) Encoding to URL recommandation
   * @see https://tools.ietf.org/html/rfc3986#section-2
   * @see https://tools.ietf.org/html/rfc3986#section-3.5
   */
  public function setFragment(string $fragment, bool $encodeURL=false): self {
    $this->fragment = null;
    if(!empty($fragment))
      $this->fragment = ($encodeURL=== true) ? rawurlencode($fragment) : $fragment;
    return $this;
  }

  /**
   * Retrieve the authority component of the URL.
   *
   * If no authority information is present, this method MUST return an empty string.
   *
   * The authority syntax of the URL is:
   *
   * <pre>
   * [user-info@]host[:port]
   * </pre>
   *
   * If the port component is not set or is the standard port for the current
   * scheme, it SHOULD NOT be included.
   *
   * @see https://tools.ietf.org/html/rfc3986#section-3.2
   * @return string The URI authority, in "[user-info@]host[:port]" format.
   */
  public function getAuthority(): string {
    $authority = !empty($this->getUserInfo()) ? $this->getUserInfo().'@' : "";
    $authority.= $this->getHost();
    if(null !== $this->getPort()) {
      $authority.=self::VAR_COLUMN.$this->getPort();
    }
    return $authority;
  }

  /**
   * Retrieve the subdomain component of the URL.
   *
   * The subdomain is the first part of the host (only in DNS view) element as described below
   *
   *  ----------------------:----------------------@---------(.?)---------.----------------------.----------------------
   *  \____________________/ \____________________/ \____________________/ \____________________/ \____________________/
   *         username               password              subdomain               domain             topLevelDomain
   *  \________________________________________________________________________________________________________________/
   *			 	                          host
   *
   * @return ?string the URL subdomain
   */
  public function getSubdomain(): ?string {
    return $this->subdomain;
  }

  /**
   * Retrieve the domain component of the URL.
   *
   * The domain is the second part of the host (only in DNS view) element OR the IP address (only in IP view) as
   * described below
   *
   *  ----------------------:----------------------@---------(.?)---------.----------------------.----------------------
   *  \____________________/ \____________________/ \____________________/ \____________________/ \____________________/
   *         username               password              subdomain               domain             topLevelDomain
   *  \________________________________________________________________________________________________________________/
   *			 	                          host
   *
   * @return ?string the URL domain
   */
  public function getDomain(): ?string {
    return $this->domain;
  }

  /**
   * Retrieve the top level domain component of the URL.
   *
   * The top level domain is the third part of the host (only in DNS view) element as described below
   *
   *  ----------------------:----------------------@---------(.?)---------.----------------------.----------------------
   *  \____________________/ \____________________/ \____________________/ \____________________/ \____________________/
   *         username               password              subdomain               domain             topLevelDomain
   *  \________________________________________________________________________________________________________________/
   *			 	                          host
   *
   * @return ?string the URL domainExtension
   */
  public function getTopLevelDomain(): ?string {
    return $this->topLevelDomain;
  }

  /**
   * Retrieve the keys from the query
   *
   * @param bool $decodeURL Option to decode key/value set (DEFAULT=false)
   * @return array list of query keys
   */
  public function getQueryParameterKeys(bool $decodeURL=false): array {
    $tab = self::queryToArray($this->query,$decodeURL);
    return (count($tab)) ? array_keys($tab) : [];
  }

  /**
   * Query conversion to array
   *
   * @param string $query Query
   * @param bool $decodeURL Option to decode key/value set (DEFAULT=false)
   * @return array
   */
  private static function queryToArray(string $query, bool $decodeURL=false): array {
    $tab	 = array();
    $items = explode(self::VAR_QUERY_AND, $query);

    if(count($items)) {
      foreach($items as $item) {
        $tmp = explode('=',$item);
        if(count($tmp) === 2)
          $tab[$tmp[0]]=$tmp[1];
        elseif(count($tmp) === 1)
          $tab[$tmp[0]]="";
      }

      if($decodeURL === true) {
        $ttab = array();
        foreach($tab as $key => $value)
          $ttab[rawurldecode($key)] = rawurldecode($value);
        $tab = $ttab;
      }
    }
    asort($tab);
    return $tab;
  }

  /**
   * Array conversion to query
   *
   * Array given in parameters is a single set of key/value where key and value are scalars data.
   * If value is not a scalar, the key/value is ignored to the rendering of query
   *
   * @param array $array Array of parameters in key/value mode
   * @param bool $encodeURL Option to protect the query and fragment area (DEFAULT=false) with RFC3986
   * @return string Query well formed
   */
  public static function arrayToQuery(array $array, bool $encodeUrl=false): string {
    $query = "";
    if(count($array) > 0) {
      foreach($array as $key => $value) {
        if(!is_array($value) && !is_object($value)) {
          if($query !== "")
            $query.=self::VAR_QUERY_AND;
          if($encodeUrl === true)
            $query.=rawurlencode($key).'='.rawurlencode($value);
          else
            $query.=$key.'='.$value;
        }
      }
    }
    return $query;
  }

  /**
   * Retrieve the value from a key
   *
   * If no key found in the query, the method return NULL
   *
   * @param string $key Key from query
   * @param bool $decodeURL Option to decode key/value set (DEFAULT=false)
   * @return ?string value
   */
  public function getQueryParameter(string $key, bool $decodeURL=false): ?string {
    $tab = self::queryToArray($this->query,$decodeURL);
    return ($tab && !empty($tab[$key])) ? $tab[$key] : null;
  }

  /**
   * Return the port mode
   *
   * @return ?string value
   */
  public function getPortMode(): ?string {
    return self::$port_mode;
  }

  /**
   * Check the scheme format
   * @Assert\Callback
   */
  public function isSchemeValid(ExecutionContextInterface $context): void {
    if(null !== $this->scheme && !in_array($this->scheme,self::$active_schemes)) {
      // get message
      $message = str_replace("%name%",$this->scheme,InvalidUriException::INVALID_SCHEME_FORMAT_MESSAGE);
      $context->buildViolation($message)->atPath("scheme")->addViolation();
    }
  }

  /**
   * Change port mode
   *
   * three modes are availabled: "normal", "forced" and "none"
   * - "normal" 		:(RECOMMANDED) If the port is the standard port used with the current scheme, the port will
   *                          be omitted.
   * - "forced"		: force the port information. If port is not given, the port takes the default port
   *                         relative to the current scheme
   * - "none"		: use port only if the information is done
   * @param string $port_mode	Port mode
   * @throws InvalidUriException if the port mode is not in the validated modes
   */
  public function changePortMode($port_mode): self {
    if(in_array($port_mode, [self::PORT_MODE_NORMAL,self::PORT_MODE_FORCED,self::PORT_MODE_NONE])) {
      self::$port_mode = $port_mode;
      return $this;
    }

    throw new InvalidUriException(
      InvalidUriException::INVALID_PORT_MODE_DEFINED_MESSAGE,
      InvalidUriException::INVALID_PORT_MODE_DEFINED_CODE,
      ['name' => $port_mode]
    );
  }

  /**
   * Init all attributes to null
   */
  public function reset(): void {
    $this->scheme 		= null;
    $this->subdomain	= null;
    $this->domain		= null;
    $this->topLevelDomain	= null;
    $this->port		= null;
    $this->folder 		= null;
    $this->filename  	= null;
    $this->filenameExtension= null;
    $this->query		= null;
    $this->fragment		= null;
    $this->userInfo		= null;
  }

  /**
   * @param  string $str regexp without delimiters
   * @param  bool $closureMode Define if the regexp is part (false) or terminal (true) regexp
   * @return string the regexp with delimiters
   */
  private static function formatRegexp(string $str, bool $closureMode=true): string {
    return ($closureMode === true) ? "/^".$str."$/i" : $str;
  }

  /**
   * @param  enum $option mode of regexp path building
   *         - rootless	: regexp without slash at begin
   *         - absolute	: regexp with slash at begin
   *
   * @return string the regexp path validation
   */
  private function getPathRegexp(string $option = "absolute"): string {
    switch($option) {
      case 'rootless':
        $regexp = "(".self::REGEXP_PATH_FOLDER.self::REGEXP_PATH_FILENAME."|\/|)";
        break;
      case 'absolute':
      default:
        $regexp = "(".self::REGEXP_SLASH.self::REGEXP_PATH_FOLDER.self::REGEXP_PATH_FILENAME."|\/|)";
    }
    return $regexp;
  }

  /**
   * Get host regexp
   *
   * @param  enum $option mode of regexp host building
   *         - dns		: regexp with dns requirements
   *         - ip_v4          : regexp with ip requirements
   *         - fuzzy		: regexp with both dns or ip requirements
   * @param  bool $closureMode Define if the regexp is part (false) or terminal (true) regexp
   * @return string the regexp host validation among option selected
   */
  private function getHostRegexp(string $option = "dns", bool $closureMode=true): string {
    $host_regexp = null;
    switch($option) {
      case 'fuzzy':
        $host_regexp = "(".
          self::REGEXP_HOST_SUBDOMAIN.
          self::REGEXP_HOST_DOMAIN.
          self::REGEXP_HOST_TOP_LEVEL_DOMAIN.
          "|".
          str_replace("domain","domain0",self::REGEXP_HOST_IPV4).
          ")";
        break;
      case 'ip_v4':
        $host_regexp = self::REGEXP_HOST_IPV4;
        break;
      case 'dns':
      default:
        $host_regexp = self::REGEXP_HOST_SUBDOMAIN.
          self::REGEXP_HOST_DOMAIN.
          self::REGEXP_HOST_TOP_LEVEL_DOMAIN;
    }
    return self::formatRegexp($host_regexp,$closureMode);
  }

  /**
   */
  private function getCompleteURLRegexp(string $option='default'): string {
    $regexp = "";
    switch($option) {
      case 'default':
        $regexp =
          // scheme part
          $this->getSchemeRegexp(self::SCHEME_OPTION_FUZZY,false).
          // host part
          "(?<host>".$this->getHostRegexp("fuzzy",false)."|)".
          // path part
          "(?<path>".$this->getPathRegexp("absolute",false).")".
          // query part
          self::REGEXP_QUERY.
          self::REGEXP_FRAGMENT
        ;
        break;
    }
    return self::formatRegexp($regexp);
  }

  /**
   * Get scheme regexp
   *
   * @param  enum $option mode of regexp scheme building
   *         - full 		: regexp with scheme protocol, punct and path
   *         - scheme_only 	: regexp with scheme protocol
   * 	       - fuzzy		: regexp with scheme protocol (optionnal)
   * @param  bool $closureMode Define if the regexp is part (false) or terminal (true) regexp
   * @return string the regexp scheme validation among option selected
   */
  private function getSchemeRegexp(string $option = self::SCHEME_OPTION_FULL, bool $closureMode=true): string {
    $scheme_regexp = null;
    switch($option) {
      case self::SCHEME_OPTION_SCHEME_ONLY:
        $scheme_regexp = self::REGEXP_SCHEME_PROTOCOL;
        break;
      case self::SCHEME_OPTION_FUZZY:
        $scheme_regexp = "(".
          // example : "http://"
          self::REGEXP_SCHEME_PROTOCOL.
          self::REGEXP_SCHEME_COLUMN.
          self::REGEXP_SCHEME_PATH_ABEMPTY.
          "|".
          ")";
        break;
      case self::SCHEME_OPTION_FULL:
      default:
        $scheme_regexp =
          self::REGEXP_SCHEME_PROTOCOL.
          self::REGEXP_SCHEME_COLUMN.
          self::REGEXP_SCHEME_PATH_ABEMPTY;

    }
    return self::formatRegexp($scheme_regexp,$closureMode);
  }

  /**
   * Get Url without query nor fragment
   *
   * @return string Url component
   */
  public function getUrlWithoutQueryNorFragment(): string {
    $strict_url = $this->getScheme().
      self::VAR_COLUMN.
      self::VAR_PATH_ABEMPTY.
      $this->getAuthority().
      self::VAR_SLASH.
      $this->getPath();
    return $strict_url;
  }

  /**
   * Get Url without path
   *
   * @return string Url component
   */
  public function getUrlWithoutPath(): string {
    $strict_url = $this->getScheme().
      self::VAR_COLUMN.
      self::VAR_PATH_ABEMPTY.
      $this->getAuthority();

    return $strict_url;
  }

  /**
   * Check if the url given is equivalent to the target url
   *
   * @param string $url Url to compare with current instance
   * @return boolean
   */
  public function isEquals(string $url): bool {
    // isolate the fragment section
    $strict_url  	= null;
    $strict_query	= null;
    $strict_fragment= null;

    $tmp = explode('#',$url);
    if(count($tmp) === 2) {
      $strict_fragment= $tmp[1];
      $strict_url	= $tmp[0];
    }
    elseif(count($tmp) === 1)
      $strict_url	= $url;
    else
      return false;

    $tmp = explode('?',$strict_url);
    if(count($tmp) === 2) {
      $strict_query	= $tmp[1];
      $strict_url	= $tmp[0];
    }
    elseif(count($tmp) > 2)
      return false;

    // is target strict url is equals to current strict url ?
    $my_url = $this->getUrlWithoutQueryNorFragment();
    if($my_url != $strict_url)
      return false;

    // is target fragment is equals to current fragment ?
    $my_fragment = $this->getFragment();
    if($my_fragment != $strict_fragment)
      return false;

    // is target query is equals to current query ?
    $array_source = self::queryToArray($this->getQuery());
    $array_target = self::queryToArray($strict_query);

    return ($array_source == $array_target);
  }

  /**
   * Return the string representation as a URL reference.
   *
   * Depending on which components of the URL are present, the resulting
   * string is either a full URI or relative reference according to RFC 3986,
   * Section 4.1. The method concatenates the various components of the URI,
   * using the appropriate delimiters:
   *
   * - If a scheme is present, it will be suffixed by ":".
   * - If an authority is present, it will be prefixed by "//".
   * - The path can be concatenated without delimiters. But there are two
   *   cases where the path has to be adjusted to make the URI reference
   *   valid as PHP does not allow to throw an exception in __toString():
   *     - If the path is rootless and an authority is present, the path will
   *       be prefixed by "/".
   *     - If the path is starting with more than one "/" and no authority is
   *       present, the starting slashes MUST be reduced to one.
   * - If a query is present, it will be prefixed by "?".
   * - If a fragment is present, it will be prefixed by "#".
   *
   * @see http://tools.ietf.org/html/rfc3986#section-4.1
   * @return string
   */
  public function __toString() {
    $url = $this->getUrlWithoutQueryNorFragment();

    if($this->getQuery())
      $url.=self::VAR_INTERROGATION.$this->getQuery();

    if($this->getFragment())
      $url.=self::VAR_DIESE.$this->getFragment();

    return $url;
  }

  /**
   * Sanitize an url
   *
   * The object is to validate the URL construct and optionnaly return the normalized well-formed URL
   * @param string $url Url to sanitize
   * @param array $parameters Parameters to sanitize
   * @param bool $encodeURL Option to protect the query and fragment area (DEFAULT=false) with RFC3986
   */
  public function sanitize(string $url, array $parameters=[], bool $encodeURL=false): self {
    $this->reset();
    $newParameters = [];
    foreach($parameters as $key => $value) {
      $oldUrl = $url;
      $url = str_replace("{{".$key."}}",$value, $url);
      if($oldUrl === $url)
        $newParameters[$key]=$value;
    }
    $regexp = $this->getCompleteURLRegexp();
    $url = trim($url);
    /**
     * hack
     * ====
     * in case of url with format "/..." at start,
     * there is conversion to "file:///"
     */
    $url = preg_replace("/^\//i",self::VAR_SCHEME_FILE.self::VAR_COLUMN.self::VAR_PATH_ABEMPTY.self::VAR_SLASH,$url);

    /**
     * hack
     * ====
     * in case of windows filesystem at start,
     * there is conversion to "file:///"
     */
    if(preg_match("/^([a-z]{1}[:])/i",$url)) {
      $url = preg_replace(
        "/^([a-z]{1}[:])/i",
        self::VAR_SCHEME_FILE.self::VAR_COLUMN.self::VAR_PATH_ABEMPTY.self::VAR_SLASH."$1",
        $url
      );
      $url = str_replace('\\','/',$url);
    }

    if(preg_match($regexp,$url,$matches)) {
      if(!empty($matches['scheme']))
        $this->setScheme($matches['scheme']);
      if(!empty($matches['host']))
        $this->setHost($matches['host']);
      if(!empty($matches['path'])) {
        $path = preg_replace("/^\//i","",$matches['path']);
        $this->setPath($path,$encodeURL);
      }
      if(!empty($matches['query']) || count($newParameters)) {
        $newStr = !empty($matches['query']) ? $matches['query'] : '';
        $newStr.= $newStr ? "&" : "";
        $i=0;
        foreach($newParameters as $key => $value) {
          $newStr.=(($i>0) ? '&' : '').$key.'='.$value;
          $i++;
        }
        $newStr = preg_replace("/[&]$/","", $newStr);
        $this->setQuery($newStr,$encodeURL);
      }

      if(!empty($matches['fragment'])) {
        $this->setFragment($matches['fragment'],$encodeURL);
      }
    }
    else {
      throw new InvalidUriException(
        InvalidUriException::INVALID_SANITIZE_MESSAGE,
        InvalidUriException::INVALID_SANITIZE_CODE,
        ['name' => $url]
      );
    }
    return $this;
  }
}

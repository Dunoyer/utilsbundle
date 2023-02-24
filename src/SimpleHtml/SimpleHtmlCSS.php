<?php
namespace FOPG\Component\UtilsBundle\SimpleHtml;

class SimpleHtmlCSS{
  private static function is_xpath($str){
    return preg_match('/^\(?\.?\//', $str);
  }

  static function do_id($str){
    if(!preg_match('/^#(.*)/', $str, $m)) die('no attribute match!');
    return "@id = '" . $m[1] . "'";
  }

  static function do_class($str){
    if(!preg_match('/^\.(.*)/', $str, $m)) die('no attribute match!');
    return "contains(concat(' ', normalize-space(@class), ' '), ' " . $m[1] . " ')";
  }

  private static function parse_nth($str){
    switch(true){
      case preg_match('/^(-?\d+)(?:n\+(\d+))$/', $str, $m): return array(intval($m[1]), intval($m[2]));
      case preg_match('/^(-?\d+)(?:n\+(\d+))$/', $str, $m): return array(intval($m[1]), intval($m[2]));
      case preg_match('/^n\+(\d+)$/', $str, $m): return array(1, intval($m[1]));
      case preg_match('/^-n\+(\d+)$/', $str, $m): return array(-1, intval($m[1]));
      case preg_match('/^(\d+)n$/', $str, $m): return array(intval($m[1]), 0);
      case preg_match('/^even$/', $str, $m): return self::parse_nth('2n+0');
      case preg_match('/^odd$/', $str, $m): return self::parse_nth('2n+1');
      case preg_match('/^(-?\d+)$/', $str, $m): return array(null, intval($m[1]));;
      default: die('no match: ' . $str);
    }
  }

  private static function nth($str, $last = false){
    list($a, $b) = self::parse_nth($str);
    //echo $a . ":" . $b . "\n";
    $tokens = array();
    if($last){
      if($a === null) return "position() = last() - " . ($b - 1);
      if($b > 0 && $a >= 0) $tokens[] = "((last()-position()+1) >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "((last()-position()+1) <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "((((last()-position()+1)-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "((last()-position()+1) mod " . abs($a) . ") = 0";
    } else {
      if($a === null) return "position() = " . $b;
      if($b > 0 && $a >= 0) $tokens[] = "(position() >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "(position() <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "(((position()-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "(position() mod " . abs($a) . ") = 0";
    }
    return implode(' and ', $tokens);
  }

  // This stuff is wrong, I need to look at this some more.
  private static function nth_child($str, $last = false){
    list($a, $b) = self::parse_nth($str);
    //echo $a . ":" . $b . "\n";
    $tokens = array();
    if($last){
      if($a === null) return "count(following-sibling::*) = " . ($b - 1);
      if($b > 0 && $a >= 0) $tokens[] = "((last()-position()+1) >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "((last()-position()+1) <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "((((last()-position()+1)-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "((last()-position()+1) mod " . abs($a) . ") = 0";
    } else {
      if($a === null) return "count(preceding-sibling::*) = " . ($b - 1);
      if($b > 0 && $a >= 0) $tokens[] = "(position() >= " . $b . ")";
      if($b > 0 && $a < 0)  $tokens[] = "(position() <= " . $b . ")";
      if($a != 0 && $b != 0)  $tokens[] = "(((position()-" . $b . ") mod " . abs($a) . ") = 0)";
      if($a != 0 && $b == 0)  $tokens[] = "(position() mod " . abs($a) . ") = 0";
    }
    return implode(' and ', $tokens);
  }

  private static function not($str){
    switch(true){
      case preg_match('/^\.(\w+)$/', $str, $m): return self::do_class($str);
      case preg_match('/^\#(\w+)$/', $str, $m): return self::do_id($str);
      case preg_match('/^(\w+)$/', $str, $m): return "self::" . $str;
      case preg_match('/^\[(.*)\]$/', $str, $m): return substr(self::do_braces($str), 1, -1);
      default: return self::translate($str);
    }
  }


  static function do_pseudo($str, $name){
    if(!preg_match('/^:([\w-]+)(?:\((.*)\))?$/', $str, $m)) die('no attribute match!');
    //var_dump($m); exit;
    @list($_, $pseudo, $value) = $m;

    switch(true){
      #case preg_match('/^\[.*\]$/', $value): $inner = preg_replace('/^\[(.*)\]$/', '\1', self::do_braces($value)); break;
      default: $inner = self::translate($value); break;
    }

//    self::translate_part($value)
    switch($pseudo){
      case 'last': return "[position() = last()]";
      case 'first': return "[position() = 1]";
      case 'parent': return "[node()]";
      case 'contains': return "[contains(., " . $value . ")]";
      case 'nth': return "[position() = " . $value . "]";
      case 'gt': return "[position() > " . $value . "]";
      case 'lt': return "[position() < " . $value . "]";
      case 'eq': return "[position() = " . $value . "]";
      case 'root': return "[not(parent::*)]";
#      case 'nth-child': return "[count(preceding-sibling::*) = " . ($value - 1) . "]";
      case 'nth-child': return "[" . self::nth_child($value) . "]";
#      case 'nth-last-child': return "[count(following-sibling::*) = " . ($value - 1) . "]";
      case 'nth-last-child': return "[" . self::nth_child($value, true) . "]";
#      case 'nth-of-type': return "[position() = " . $value . "]";
      case 'nth-of-type': return "[" . self::nth($value) . "]";
#      case 'nth-last-of-type': return $value ? "[position() = last() - " . ($value - 1) . "]" : "[position() = last()";
      case 'nth-last-of-type': return "[" . self::nth($value, true) . "]";
      case 'first-child': return "[count(preceding-sibling::*) = 0]";
      case 'first-of-type': return "[position() = 1]";
      case 'last-child': return "[count(following-sibling::*) = 0]";
      case 'last-of-type': return "[position() = last()]";
      case 'only-child': return "[count(preceding-sibling::*) = 0 and count(following-sibling::*) = 0]";
      case 'only-of-type': return "[last() = 1]";
      case 'empty': return "[not(node())]";
      case 'not': return "[not(" . self::not($value) . ")]";
#      case 'has': return "[" . $inner . "]";
      case 'has': return "[" . $inner . "]";
  //      case 'link': return "[link(.)]";
      case 'link':
      case 'visited':
      case 'hover':
      case 'active':
        return "[" . $pseudo . "(.)]";

      default: die('unknown pseudo element: ' . $str);
    }
  }

  static function do_braces($str){
    $re = '/("(?>[^"]|(?R))*\)"|\'(?>[^\']|(?R))*\'|[~^$*|]?=)\s*/';

    $tokens = preg_split($re, substr($str, 1, strlen($str) - 2), 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
//    var_dump($tokens);
    $attr = trim(array_shift($tokens));
//     && )
    if(!$op = @trim(array_shift($tokens))){
      switch(true){
        case preg_match('/^\d+$/', $attr): return "[count(preceding-sibling::*) = " . ($attr - 1) . "]"; // [2] -> [count(preceding-sibling::*) = 1]
        default: return "[@" . $attr . "]"; // [foo] => [@foo]
      }
    }
    switch(true){
      case preg_match('/^(text|comment)$/', $attr, $m): $attr = $m[1] . "()"; break;
      case !preg_match('/[@(]/', $attr): $attr = '@' . $attr; break;
    }
//    if(!preg_match('/[@(]/', $attr)) $attr = '@' . $attr;
    $value = @trim(array_shift($tokens));
    if(!preg_match('/^["\'].*["\']$/', $value)) $value = "'" . $value . "'";
//    $value = "'" . preg_replace('/^["\'](.*)["\']$/', '\1', $value) . "'";

    switch($op){
      case '*=': return "[contains(" . $attr . ", " . $value . ")]";
      case '^=': return "[starts-with(" .$attr. ", " . $value . ")]";
      case '~=': return "[contains(concat(\" \", " . $attr . ", \" \"),concat(\" \", " . $value . ", \" \"))]";
      case '$=': return "[substring(" . $attr . ", string-length(" . $attr . ") - string-length(" . $value . ") + 1, string-length(" . $value . ")) = " . $value . "]";
      case '|=': return "[" . $attr . " = " . $value . " or starts-with(" . $attr . ", concat(" . $value . ", '-'))]";
      case '=': return "[" . $attr . " = " . $value . "]";
      default: die('unknown op: ' . $op);
    }
  }

  static function translate_nav($str){
    switch($str){
      case '+': return '/following-sibling::';
      case '~': return '/following-sibling::';
      case '>': return '/';
      case '': return '//';
    }
  }

  static function translate_part($str, $last_nav = ''){
    $str = preg_replace('/:contains\(([^()]*)\)/', '[text*=\\1]', $str); // quick and dirty contains fix
    $retval = array();
    $re = '/(:(?:nth-last-child|nth-of-type|nth-last-of-type|first-child|last-child|first-of-type|last-of-type|only-child|only-of-type|nth-child|first|last|gt|lt|eq|root|nth|empty|not|has|contains|parent|link|visited|hover|active)(?:\((?>[^()]|(?R))*\))?|\[(?>[^\[\]]|(?R))*\]|[#.][\w-]+)/';
    $name = '*';
    foreach(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token){
      switch(true){
        case preg_match('/^:/', $token): $retval[] = self::do_pseudo($token, $name); break;
        case preg_match('/^\[/', $token): $retval[] = self::do_braces($token); break;
        case preg_match('/^#/', $token): $retval[] = "[" . self::do_id($token) . "]"; break;
        case preg_match('/^\./', $token): $retval[] = "[" . self::do_class($token) . "]"; break;
        default: $name = $token;
      }
    }
    if(in_array($name, array('text', 'comment'))) $name .= '()';
    return ($last_nav === '+' ? "*[1]/self::" : '') . $name . implode('', $retval);
    //return $name . implode('', $retval);
  }

  static function translate($str){
    $retval = array();
    $re = '/(\((?>[^()]|(?R))*\)|\[(?>[^\[\]]|(?R))*\]|\s*[+~>]\s*| \s*)/';
    $item = '';

    $last_nav = null;
    //echo "\n!" . $str . "!\n";
    //var_dump(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
    foreach(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token){
      $token = trim($token);
      //echo $token . "-\n";
      switch($token){
        case '>':
        case '~':
        case '+':
        case '':
          if(!empty($item)) $retval[] = self::translate_part(trim($item), $last_nav);
          $item = '';
          $last_nav = $token;
          if(!isset($first_nav)){
            $first_nav = trim($token);
          } else {
            $retval[] = self::translate_nav(trim($token));
          }
          break;
        default:
          if(!isset($first_nav)) $first_nav = '';
          $item .= $token;
      }
    }
    //    var_dump($first_nav, $retval); exit;

    $retval[] = self::translate_part(trim($item), $last_nav);
    if(!isset($first_nav)) $first_nav = '';
    return '.' . self::translate_nav($first_nav) . implode('', $retval);
  }

  private static function get_expressions($str){
    $retval = array();
    $re = '/(\((?>[^()]|(?R))*\)|\[(?>[^\[\]]|(?R))*\]|,)/';
    $item = '';
    foreach(preg_split($re, $str, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $token){
      if(',' == $token){
        $retval[] = trim($item);
        $item = '';
      } else{
        $item .= $token;
      }
    }
    $retval[] = trim($item);
    return $retval;
  }

  public static function xpath_for($str){

    if(self::is_xpath($str))
    {
        return $str;
    }

    $str = preg_replace('/\b(text|comment)\(\)/', '\1', $str);
    $retval = array();
    foreach(self::get_expressions($str) as $expr)
    {
      $retval[] = self::translate($expr);
    }
    return implode('|', $retval);
  }
}

<?php

namespace Rain\Tpl\Plugin;
require_once __DIR__ . '/../Plugin.php';

class CustomParser extends \Rain\Tpl\Plugin
{
  protected $hooks = array('beforeParse');
  private $tags = array('a', 'img', 'link', 'script', 'input', 'object', 'embed');

  /**
   * Custom parser
   * @param \ArrayAccess $context
   */
  public function beforeParse(\ArrayAccess $context)
  {
    $code = $context->code;
    $openExist = $openHas = 0;
    $tags = "/({exist.*?})|({\/exist})|({has.*?})|({\/has})/";
    $codeSplit = preg_split($tags, $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $parsedCode = '';
    foreach ($codeSplit as $html)
    {
      if (preg_match('/{exist="([^"]*)"}/', $html, $matches))
      {
        $openExist++;
        $tag = $matches[1];
        $parsedCode .= "<?php if(isset($tag)) { ?>";
      }
      elseif (preg_match("/{\/exist}/", $html, $matches))
      {
        $openExist--;
        $parsedCode .= '<?php } ?>';
      }
      elseif (preg_match('/{has="([^"]*)"}/', $html, $matches))
      {
        $openHas++;
        $tag = $matches[1];
        $parsedCode .= "<?php if(isset($tag) && !empty($tag)) { ?>";
      }
      elseif (preg_match("/{\/has}/", $html, $matches))
      {
        $openHas--;
        $parsedCode .= '<?php } ?>';
      }
      else
        $parsedCode .= $html;
    }

    if ($openExist > 0) {
        $e = new SyntaxException("Error! You need to close an {exist} tag in {$context->template_filepath} template");
        throw $e->templateFile($templateFilepath);
    }

    if ($openHas > 0) {
        $e = new SyntaxException("Error! You need to close the {has} tag in {$context->template_filepath} template");
        throw $e->templateFile($templateFilepath);
    }

    $context->code = $parsedCode;
  }
}

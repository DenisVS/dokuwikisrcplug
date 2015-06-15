<?php

/**
 * DokuWiki Plugin src (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  DenisVS <denisvs@gmail.com>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
  die();

class syntax_plugin_src extends DokuWiki_Syntax_Plugin {

  /**
   * @return string Syntax mode type
   */
  public function getType() {
    return 'substition';
  }

  /**
   * @return string Paragraph type
   */
  public function getPType() {
    return 'block';
  }

  /**
   * @return int Sort order - Low numbers go before high numbers
   */
  public function getSort() {
    return 200;
    //return 321;
  }

  /**
   * Connect lookup pattern to lexer.
   *
   * @param string $mode Parser mode
   */
  public function connectTo($mode) {
    $this->Lexer->addSpecialPattern('\{\{src\}\}', $mode, 'plugin_src');
//        $this->Lexer->addEntryPattern('<FIXME>',$mode,'plugin_src');
  }

//    public function postConnect() {
//        $this->Lexer->addExitPattern('</FIXME>','plugin_src');
//    }

  /**
   * Handle matches of the src syntax
   *
   * @param string $match The match of the syntax
   * @param int    $state The state of the handler
   * @param int    $pos The position in the document
   * @param Doku_Handler    $handler The handler
   * @return array Data for the renderer
   */
  public function handle($match, $state, $pos, Doku_Handler &$handler) {
    $data = array();

    return $data;
  }

  /**
   * Render xhtml output or metadata
   *
   * @param string         $mode      Renderer mode (supported modes: xhtml)
   * @param Doku_Renderer  $renderer  The renderer
   * @param array          $data      The data from the handler() function
   * @return bool If rendering was successful.
   */
  public function render($mode, Doku_Renderer $renderer, $data) {
    $lang = 'sh';
    $fileName = '/usr/local/www/apache24/data/wiki/lib/plugins/addnewpage/syntax.php';
    //$ok = true;
    //$end = 10;
    //$start = 0;

    if ($mode != 'xhtml') {
      return false;
    }


    //var_dump($this->_fetchFile($fileName));
    $code = $this->_assembling($this->_fetchFile($fileName));

    // highlighting by Geshi
    $geshi = new GeSHi($code, $lang, DOKU_INC . 'inc/geshi');
    $geshi->set_encoding('utf-8');
    $geshi->enable_classes();
    $geshi->set_header_type(GESHI_HEADER_PRE);
    $geshi->set_link_target($conf['target']['extern']);
    // when you like to use your own wrapper, remove GeSHi's wrapper element
// we need to use a GeSHi wrapper to avoid <BR> throughout the highlighted text
    $code = trim(preg_replace('!^<pre[^>]*>|</pre>$!', '', $geshi->parse_code()), "\n\r");
    $code = mb_str_replace("&nbsp;\n", "", $code);



    $renderer->doc .= 'test';
    //$renderer->doc .= '<dl class="code">';
    $renderer->doc .= '<dt><a href="/wiki/_media/freebsd/network/ipfw/antiddos2.sh" title="Download Snippet" class="mediafile mf_php">1.php</a></dt>';
    $renderer->doc .= '<dt>';
    $renderer->doc .= '<pre class="code php">';
    //$renderer->doc .= '<span class="code php">';
    $renderer->doc .= $code;
    $renderer->doc .= '</pre>';
    $renderer->doc .= '</dt>';
    //$renderer->doc .= '</span>';
    //$renderer->doc .= '</dl>';
  //  $renderer->doc .= $this->_render_link($filename, $filepath, $result['basedir'], $result['webdir'], $params, $renderer);
    




    return true;
  }

  function _fetchFile($fileName) {
    return file($fileName);
  }

  /**
   * Преобразование массива в строку
   * @param array $contentFromArray 
   * Массив с контентом построчно
   * @return string
   * Неформатированный текст на выходе
   */
  function _assembling($contentFromArray) {
    $this->contentFromArray = $contentFromArray;
    $this->txtContent = FALSE;
    foreach ($this->contentFromArray as $key => $val) {
      $this->txtContent .= $val . "\n";
    }
    $this->txtContent = substr($this->txtContent, 0, -1);  ///< Отрубаем последний перенос
    return $this->txtContent;
  }

}

/**
 * Аналог str_replace для мультибайтных строк
 * @param string $needle Вхождение
 * @param string $replacement Замена
 * @param string $haystack Строка на входе
 * @return string Строка на выходе
 */
function mb_str_replace($needle, $replacement, $haystack) {
  $needle_len = mb_strlen($needle);
  $replacement_len = mb_strlen($replacement);
  $pos = mb_strpos($haystack, $needle);
  while ($pos !== false) {
    $haystack = mb_substr($haystack, 0, $pos) . $replacement
        . mb_substr($haystack, $pos + $needle_len);
    $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
  }
  return $haystack;
}

/**
 * Renders the files as a table, including details if configured that way.
 *
 * @param $result the filelist to render
 * @param $params the parameters of the filelist call
 * @param $renderer the renderer to use
 * @return void
 */
function _render_link($filename, $filepath, $basedir, $webdir, $params, &$renderer) {
  global $conf;

  //prepare for formating
  $link['target'] = $conf['target']['extern'];
  $link['style'] = '';
  $link['pre'] = '';
  $link['suf'] = '';
  $link['more'] = '';
  $link['class'] = 'media';
  if (!$params['direct']) {
    $link['url'] = ml(':' . $this->_convert_mediapath($filepath));
  }
  else {
    $link['url'] = $webdir . substr($filepath, strlen($basedir));
  }
  $link['name'] = $filename;
  $link['title'] = $renderer->_xmlEntities($link['url']);
  if ($conf['relnofollow'])
    $link['more'] .= ' rel="nofollow"';

  list($ext, $mime) = mimetype(basename($filepath));
  $link['class'] .= ' mediafile mf_' . $ext;

  //output formatted
  $renderer->doc .= $renderer->_formatLink($link);
}

// vim:ts=4:sw=4:et:

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
    $this->Lexer->addSpecialPattern('\{\{src.+?\}\}', $mode, 'plugin_src');
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
    $match = (preg_replace("/\s+/", " ", substr($match, 5, -2))); // Чистим от повторяющихся пробелов
    $t = explode(' -', $match); //бьём строку параметров на отдельные
    foreach ($t as $key => $value) {
      if (!empty($value)) {
        $k = explode(' ', $value);  //если параметр есть, разбить на праметр-значение через пробел
        $tempData[$k['0']] = trim($k['1']);
        $data[$k['0']] = trim($k['1']);
      }
    }
    if (isset($tempData['p'])) {
      $tempArray = explode(':', $tempData['p']);
      $data['start'] = $tempArray[0];
      $data['end'] = $tempArray[1];
      unset($data['p']);
      unset($tempData);
      unset($tempArray);
    }
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
    global $INFO;
    global $conf;

    $namespaceTypecasted = $this->_mb_str_replace(':', '/', $INFO['namespace']); //относительный путь в пространстве имён
    $file = $this->_wikiPathFileToAbsolute($data['f'], $namespaceTypecasted, $conf['mediadir']);
    $fileName = pathinfo($file, PATHINFO_BASENAME);

    if ($mode != 'xhtml') {
      return false;
    }

    $code = $this->_assembling($this->_fetchFile($file, $data['start'], $data['end']));

    if (empty($data['l'])) {
      $lang = pathinfo($file, PATHINFO_EXTENSION); //синтаксис по расширению
    }
    else {
      $lang = $data['l'];
    }

    if (!empty($data['e'])) {
      $code = mb_convert_encoding($code, "utf-8", $data['e']); //применяем перекодировку
    }

    // highlighting by Geshi
    $geshi = new GeSHi($code, $lang, DOKU_INC . 'inc/geshi');
    $geshi->set_encoding('utf-8');
    $geshi->enable_classes();
    $geshi->set_header_type(GESHI_HEADER_PRE);
    $geshi->set_link_target($conf['target']['extern']);
    // when you like to use your own wrapper, remove GeSHi's wrapper element
    // we need to use a GeSHi wrapper to avoid <BR> throughout the highlighted text
    $code = trim(preg_replace('!^<pre[^>]*>|</pre>$!', '', $geshi->parse_code()), "\n\r");
    $code = $this->_mb_str_replace("&nbsp;\n", "", $code);
    //var_dump($data);
    $renderer->doc .= '<dt><a href=' . $link . ' title="Download bg" class="mediafile mf_' . $lang . '">' . $fileName . '</a></dt>';
    $renderer->doc .= '<dt>';
    $renderer->doc .= '<pre class="code">';
    $renderer->doc .= $code;
    $renderer->doc .= '</pre>';
    $renderer->doc .= '</dt>';

    return true;
  }

  function _fetchFile($fileName, $start = 0, $end = 0) {
    $content = file($fileName);
    if ($end != 0) {
      for ($i = 0; $i < $end + 1; $i++) {
        $content2[] = $content[$i];
      }
    }
    else {
      $content2 = $content;
    }
    unset($content);
    if ($start != 0) {
      for ($i = $start; $i < sizeof($content2); $i++) {
        $content3[] = $content2[$i];
      }
    }
    else {
      $content3 = $content2;
    }
    unset($content2);
    return $content3;
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

  /**
   * Аналог str_replace для мультибайтных строк
   * @param string $needle Вхождение
   * @param string $replacement Замена
   * @param string $haystack Строка на входе
   * @return string Строка на выходе
   */
  function _mb_str_replace($needle, $replacement, $haystack) {
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
   * Функция подъёма по директориям выше на заданное количество ступеней (слэшей). 
   * Обрубает лишние низлежащие. Дефолтно 1 уровень.
   * @param string $url Исходный URL
   * @param int $level Уровень, на который надо подняться
   * @return string Результирующий URL
   */
  function _dirUp($url, $level = 1) {
    $i = 0;
    do {
      $pos = mb_strrpos($url, '/');
      $url = mb_substr($url, 0, $pos);
      $i++;
    } while ($level > $i);
    return $url;
  }

  /**
   * Преобразование пути к файлу из викиформата в абсолютный стантдартный.
   * @param string $filePath путь к файлу в Wiki формате
   * @param string $namespace текущий namespace
   * @param string $startDir Директория, от которой строить путь.
   */
  function _wikiPathFileToAbsolute($filePath, $namespace, $startDir) {
    // From current level
    if (preg_match('/^(((\.:{0,1}){0,1}(\w+\.*\-*)+)|\.(\:)?(\w+\-*\:*)+)\z/sm', $filePath)) {
      $filePath = preg_replace('/^(\.)(\:)?((\w+\-*\:*)+\z)/sm', '$3', $filePath);
      $filePath = preg_replace('/^((\.:{0,1}){0,1}((\w+\.*\-*)+)\z)/sm', '$3', $filePath);
      $filePath = $this->_mb_str_replace(':', '/', $filePath); //путь к файлу (запись согласно namespaces) в пространстве имён
      $filePath = $startDir . '/' . $namespace . '/' . $filePath;
    }
    // From root level
    else if (preg_match('/^(\w+\-*\.*\:{0,5})+(\:+)(\w+\.*\-*\:{0,5})*\z|^(\:(\w+\.*\-*\:{0,5})*)\z/sm', $filePath)) {
      $filePath = preg_replace('/^(\:*)((\w+\.*\-*\:{0,5})*\z)/sm', '$2', $filePath);
      $filePath = $this->_mb_str_replace(':', '/', $filePath); //путь к файлу (запись согласно namespaces) в пространстве имён
      $filePath = $startDir . '/' . '' . $filePath;
    }
    // From parent level
    else if (preg_match('/^(\.:)?(\.\.):?(\w+\.*\-*\:{0,5})*\z/sm', $filePath)) {
      $filePath = preg_replace('/^(\.:)?(\.\.):?((\w+\.*\-*\:{0,5})*\z)/sm', '$3', $filePath);
      $filePath = $this->_mb_str_replace(':', '/', $filePath); //путь к файлу (запись согласно namespaces) в пространстве имён
      if ($this->dirUp($namespace) != '')
        $midSlash = '/';
      $filePath = $startDir . '/' . $this->dirUp($namespace) . $midSlash . $filePath;
    }
    return $filePath;
  }

}


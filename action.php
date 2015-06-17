<?php
/**
 * DokuWiki Plugin srcbutton (Action Component)
 * $Id:$
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  DenisVS <denisvs@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_src extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
  
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button');
   
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function insert_button(Doku_Event &$event, $param) {
            $event->data[] = array (
            'type' => 'format',
            'title' => $this->getLang('embed_file'),
            'icon' => '../../plugins/src/img/src.png',
            'open' => '{{src ',
            'close' => '}}\n',
        );
    }

}


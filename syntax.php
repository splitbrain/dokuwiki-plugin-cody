<?php
/**
 * DokuWiki Plugin cody (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class syntax_plugin_cody extends DokuWiki_Syntax_Plugin {
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
        return 155;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{cody:\w+>[^}]+\}\}', $mode, 'plugin_cody');
    }

    /**
     * Handle matches of the cody syntax
     *
     * @param string $match The match of the syntax
     * @param int $state The state of the handler
     * @param int $pos The position in the document
     * @param Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = trim(substr($match, 7, -2));
        return explode('>', $match, 2);
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        if($mode != 'xhtml') return false;
        list($lang, $file) = $data;
        $file = fullpath($file);
        if(!$this->isAllowedPath($file)) {
            $renderer->doc .= 'Not within allowed paths';
            return true;
        }

        $code = io_readFile($file, false);

        /** @var Doku_Renderer_xhtml $renderer */
        $renderer->_highlight('code cody', $code, $lang, $file);

        return true;
    }

    protected function isAllowedPath($check) {
        $paths = $this->getAllowedPaths();
        foreach($paths as $path) {
            if(strlen($check) < strlen($path)) continue;
            if($path == substr($check, 0, strlen($path))) {
                return true;
            }
        }
        return false;
    }

    protected function getAllowedPaths() {
        $paths = explode("\n", $this->getConf('paths'));
        $paths = array_map('trim', $paths);
        $paths = array_filter($paths);
        $paths = array_map('fullpath', $paths);
        return $paths;
    }
}

// vim:ts=4:sw=4:et:

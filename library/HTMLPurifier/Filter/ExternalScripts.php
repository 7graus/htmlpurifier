<?php

class HTMLPurifier_Filter_ExternalScripts extends HTMLPurifier_Filter
{

    /**
     * @type string
     */

    function __construct($urls) {
        $this->urls = $urls;
    }

    private $urls;

    public $name = 'ExternalScripts';

    /**
     * Função que corre antes do filtro do htmlpurifier e faz match de todas as tags script dentro do código
     * html e as converte para [SCRIPT...]...[/SCRIPT] de forma a não serem eliminadas.
     * Para cada uma das tags também faz verificação de se o src do script é seguro (verifica se o src está dentro de $urls).
     * Se o src for seguro, converte para a forma [SCRIPT...]...[/SCRIPT], senão elimina essa tag.
     * @param string $html
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return string
     */
    public function preFilter($html, $config, $context)
    {
        $pre_regex = '#<script(.*?)>(.*?)<\/script>#';
        $replaced = preg_replace_callback($pre_regex, array($this, 'preFilterCallback'), $html);
        return $replaced;
    }

    /**
     * Função que corre depois do filtro do htmlpurifier e faz match de todas as tags [SCRIPT...]...[/SCRIPT] dentro do código
     * html e as converte para a tag script de html.
     * Não é feito nenhum tipo de filtragem ao src pois isso já é feito no preFilter.
     * @param string $html
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return string
     */
    public function postFilter($html, $config, $context)
    {
        $post_regex = '#\[SCRIPT(.*?)\](.*?)\[/SCRIPT\]#';
        $replaced = preg_replace_callback($post_regex, array($this, 'postFilterCallback'), $html);
        return $replaced;
    }

    /**
     * @param array $matches
     * @return string
     */
    protected function preFilterCallback($matches)
    {
        $res = [];
        preg_match("#src=\"(.*?)\"|src='(.*?)'#", $matches[0], $res);
        if (isset($res[1])){
            if (in_array($res[1], $this->urls))
                return '[SCRIPT'.$matches[1].']'.$matches[2].'[/SCRIPT]';
        }
        return '';
    }

    /**
     * @param array $matches
     * @return string
     */
    protected function postFilterCallback($matches)
    {
        return '<script'.$matches[1].'>'.$matches[2]."</script>";
    }
}

// vim: et sw=4 sts=4

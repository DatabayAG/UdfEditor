<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\TextAreaInputGUI;

use ilTemplate;
use ilTextAreaInputGUI;

class TextAreaInputGUI extends ilTextAreaInputGUI
{
    /**
     * @var string
     */
    protected $inline_style = '';
    /**
     * @var int
     */
    protected $maxlength = 1000;


    public function customPrepare(): void
    {
        $this->addPlugin('latex');
        $this->addButton('latex');
        $this->addButton('pastelatex');
        $this->setUseRte(true);
        $this->setRteTags([
            'p',
            'br',
            'b',
            'span'
        ]);
        $this->usePurifier(true);
        $this->disableButtons([
            'charmap',
            'undo',
            'redo',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'justifyfull',
            'anchor',
            'fullscreen',
            'cut',
            'copy',
            'paste',
            'pastetext',
            'formatselect'
        ]);
    }


    public function getInlineStyle(): string
    {
        return $this->inline_style;
    }


    public function setInlineStyle(string $inline_style): void
    {
        $this->inline_style = $inline_style;
    }


    public function getMaxlength(): int
    {
        return $this->maxlength;
    }


    public function setMaxlength(int $maxlength): void
    {
        $this->maxlength = $maxlength;
    }


    public function render(): string
    {
        $tpl = new ilTemplate(__DIR__ . '/templates/tpl.text_area_helper.html', false, false);
        $this->insert($tpl);
        $tpl->setVariable('INLINE_STYLE', $this->getInlineStyle());

        return $tpl->get();
    }
}

<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;

class twigParser extends AbstractParser
{
    public const string DOC_LINK = "https://twig.symfony.com/doc/1.x/templates.html";
    public const string NAME = "twig";


    public function __construct()
    {
        parent::__construct();
    }


    public function getOptionsFields(): array
    {
        return [
            "autoescape" => $this->dic
                ->ui()
                ->factory()
                ->input()
                ->field()
                ->checkbox(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape"))
                ->withByline(nl2br(implode("\n", [
                    sprintf(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_1"), "|raw"),
                    sprintf(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_2"), "|e"),
                    "<b>" . self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_3") . "</b>",
                    self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser_option_autoescape_info_4")
                ]), false))
        ];
    }


    /**
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function parse(string $text, array $placeholders = [], array $options = []): string
    {
        $twig = new Environment(new ArrayLoader(), [
            "autoescape" => (bool) $options["autoescape"]
        ]);

        $template = $twig->createTemplate($text);

        return $this->fixLineBreaks($template->render($placeholders));
    }
}

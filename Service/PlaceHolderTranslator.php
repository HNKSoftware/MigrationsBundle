<?php


namespace Hnk\MigrationsBundle\Service;


class PlaceHolderTranslator
{
    /**
     * @var array
     */
    private $placeHolders;

    /**
     * PlaceholderTranslator constructor.
     * @param array $placeHolders
     */
    public function __construct(array $placeHolders)
    {
        $this->placeHolders = $placeHolders;
    }

    /**
     * @return array
     */
    public function getPlaceHolders()
    {
        return $this->placeHolders;
    }

    /**
     * @param string $value
     * @return string
     */
    public function replacePlaceholders($value)
    {
        if (!$value) {
            return '';
        }

        $placeHolders = [];
        $replacements = [];

        foreach ($this->placeHolders as $placeHolder => $replacement) {
            $placeHolders[] = $placeHolder;
            $replacements[] = $replacement;
        }

        return str_replace($placeHolders, $replacements, $value);
    }
}
<?php
namespace App\Services\MealReplacement;

use Illuminate\Support\Collection;

class MealReplacementParams
{
    /**
     * @var string
     */
    protected $mealId;

    /**
     * @var string
     */
    protected $substituteMealId;

    /**
     * @var Collection
     */
    protected $sizeMapping;

    /**
     * @var Collection
     */
    protected $addonMapping;

    /**
     * @var Collection
     */
    protected $componentOptionMapping;

    /**
     * Create new instance of MealReplacementParams
     *
     * @param string $mealId
     * @param string $substituteMealId
     * @param Collection $sizeMapping
     * @param Collection $addonMapping
     * @param Collection $componentOptionMapping
     */
    public function __construct(
        string $mealId,
        string $substituteMealId,
        Collection $sizeMapping,
        Collection $addonMapping,
        Collection $componentOptionMapping
    ) {
        $this->mealId = $mealId;
        $this->substituteMealId = $substituteMealId;
        $this->sizeMapping = $sizeMapping;
        $this->addonMapping = $addonMapping;
        $this->componentOptionMapping = $componentOptionMapping;
    }

    /**
     * Get the value of mealId
     *
     * @return string
     */
    public function getMealId()
    {
        return $this->mealId;
    }

    /**
     * Set the value of mealId
     *
     * @param string  $mealId
     *
     * @return self
     */
    public function setMealId(string $mealId)
    {
        $this->mealId = $mealId;

        return $this;
    }

    /**
     * Get the value of substituteMealId
     *
     * @return string
     */
    public function getSubstituteMealId()
    {
        return $this->substituteMealId;
    }

    /**
     * Set the value of substituteMealId
     *
     * @param string  $substituteMealId
     *
     * @return self
     */
    public function setSubstituteMealId(string $substituteMealId)
    {
        $this->substituteMealId = $substituteMealId;

        return $this;
    }

    /**
     * Get the value of sizeMapping
     *
     * @return Collection
     */
    public function getSizeMapping()
    {
        return $this->sizeMapping;
    }

    /**
     * Set the value of sizeMapping
     *
     * @param Collection  $sizeMapping
     *
     * @return self
     */
    public function setSizeMapping(Collection $sizeMapping)
    {
        $this->sizeMapping = $sizeMapping;

        return $this;
    }

    /**
     * Get the value of addonMapping
     *
     * @return Collection
     */
    public function getAddonMapping()
    {
        return $this->addonMapping;
    }

    /**
     * Set the value of addonMapping
     *
     * @param Collection  $addonMapping
     *
     * @return self
     */
    public function setAddonMapping(Collection $addonMapping)
    {
        $this->addonMapping = $addonMapping;

        return $this;
    }

    /**
     * Get the value of componentOptionMapping
     *
     * @return Collection
     */
    public function getComponentOptionMapping()
    {
        return $this->componentOptionMapping;
    }

    /**
     * Set the value of componentOptionMapping
     *
     * @param Collection  $componentOptionMapping
     *
     * @return self
     */
    public function setComponentOptionMapping(
        Collection $componentOptionMapping
    ) {
        $this->componentOptionMapping = $componentOptionMapping;

        return $this;
    }

    /**
     * Has substitite for given size ID
     *
     * @param string $sizeId
     * @return boolean
     */
    public function hasSizeSubstitute($sizeId)
    {
        return $this->getSizeMapping()->has($sizeId);
    }

    /**
     * Get substitite for given size ID
     *
     * @param string $sizeId
     * @return string
     */
    public function getSizeSubstitute($sizeId)
    {
        return $this->getSizeMapping()->get($sizeId);
    }

    /**
     * Get substitite for given option ID
     *
     * @param string $optionId
     * @return string
     */
    public function hasComponentOptionSubstitute($optionId)
    {
        return $this->getComponentOptionMapping()->has($optionId);
    }

    /**
     * Get substitite for given option ID
     *
     * @param string $optionId
     * @return string
     */
    public function getComponentOptionSubstitute($optionId)
    {
        return $this->getComponentOptionMapping()->get($optionId);
    }

    /**
     * Has substitite for given addon ID
     *
     * @param string $addonId
     * @return boolean
     */
    public function hasAddonSubstitute($addonId)
    {
        return $this->getAddonMapping()->has($addonId);
    }

    /**
     * Get substitite for given addon ID
     *
     * @param string $addonId
     * @return string
     */
    public function getAddonSubstitute($addonId)
    {
        return $this->getAddonMapping()->get($addonId);
    }
}

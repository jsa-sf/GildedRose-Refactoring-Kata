<?php

declare(strict_types=1);

namespace GildedRose;

use GildedRose\Enum\Name;
use GildedRose\Enum\Operation;

final class GildedRose
{
    /**
     * @param Item[] $items
     */
    public function __construct(
        private array $items
    ) {
    }

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            if (Name::tryFrom($item->name) !== Name::SULFURAS) {
                $item = $this->updateSellInStep($item);
                $item = $this->updateQualityStep($item);
            }
        }
    }

    private function updateQualityStep(Item $item): Item
    {
        $operation = Operation::DECREASE;
        $value = 1;
        switch (Name::tryFrom($item->name)) {
            case Name::BACKSTAGE_PASSES:
                if ($item->sellIn < 0) {
                    $operation = Operation::RESET;
                } else {
                    $operation = Operation::INCREASE;
                    if ($item->sellIn < 6) {
                        $value = 3;
                    } elseif ($item->sellIn < 11) {
                        $value = 2;
                    }
                }
                break;
            case Name::AGED_BRIE:
                $operation = Operation::INCREASE;
                break;
            case Name::CONJURED:
                $value = 2;
        }

        return $this->changeQuality($item, $operation, $value);
    }

    private function updateSellInStep(Item $item): Item
    {
        $item->sellIn--;
        return $item;
    }

    private function changeQuality(Item $item, Operation $operation, int $value): Item
    {
        return match ($operation) {
            Operation::DECREASE => $this->decreaseQuality($item, $item->sellIn < 0 ? $value * 2 : $value),
            Operation::INCREASE => $this->increaseQuality($item, $value),
            Operation::RESET => $this->resetQuality($item)
        };
    }

    private function decreaseQuality(Item $item, int $value): Item
    {
        if ($item->quality > 0) {
            $newValue = $item->quality - $value;
            $item->quality = $newValue < 0 ? 0 : $newValue;
        }

        return $item;
    }

    private function increaseQuality(Item $item, int $value = 1): Item
    {
        if ($item->quality < 50) {
            $newValue = $item->quality + $value;
            $item->quality = $newValue > 50 ? 50 : $newValue;
        }

        return $item;
    }

    private function resetQuality(Item $item): Item
    {
        $item->quality = 0;

        return $item;
    }
}
